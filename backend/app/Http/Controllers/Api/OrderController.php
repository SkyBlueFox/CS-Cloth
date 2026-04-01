<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Models\UserAddress;
use App\Support\ApiData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class OrderController extends Controller
{
    private const REFUND_REASON_CODES = [
        'damaged_item', 'wrong_item', 'missing_parts', 'not_as_described', 
        'quality_issue', 'changed_mind', 'other',
    ];

    private const DELIVERY_METHODS = [
        'thailand_post' => 'Thailand Post',
        'kerry_express' => 'Kerry Express',
        'flash_express' => 'Flash Express',
        'jt_express' => 'J&T Express',
        'thunder_express' => 'Thunder Express',
    ];

    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));

        $orders = Order::query()
            ->where('buyer_id', $request->user()->id)
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('order_number', 'like', "%{$search}%")
                        ->orWhereHas('items.item', fn($q) => $q->where('name', 'like', "%{$search}%"));
                });
            })
            ->with(['items.item', 'items.refundEvents', 'shippingAddress'])
            ->latest()
            ->paginate(10);

        return response()->json(ApiData::pagination($orders, fn (Order $order) => ApiData::order($order)));
    }

    public function show(Request $request, Order $order)
    {
        abort_unless($order->buyer_id === $request->user()->id, 403);
        $order->load(['items.item', 'items.refundEvents', 'shippingAddress']);
        return response()->json(['order' => ApiData::order($order)]);
    }

    /**
     * ดำเนินการ Checkout (สร้างคำสั่งซื้อ)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_id' => ['required', 'integer', 'exists:items,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'address_id' => ['nullable', 'integer'],
            'delivery_method' => ['required', 'string', 'in:' . implode(',', array_keys(self::DELIVERY_METHODS))],
            'shipping_address.label' => ['required_without:address_id', 'string', 'max:100'],
            'shipping_address.recipient_name' => ['required_without:address_id', 'string', 'max:255'],
            'shipping_address.phone' => ['required_without:address_id', 'string', 'max:50'],
            'shipping_address.line_1' => ['required_without:address_id', 'string', 'max:255'],
            'shipping_address.district' => ['required_without:address_id', 'string', 'max:255'],
            'shipping_address.province' => ['required_without:address_id', 'string', 'max:255'],
            'shipping_address.postal_code' => ['required_without:address_id', 'string', 'max:20'],
            'shipping_address.country' => ['required_without:address_id', 'string', 'max:100'],
            'save_address' => ['nullable', 'boolean'],
            'set_as_default' => ['nullable', 'boolean'],
        ]);

        try {
            $order = DB::transaction(function () use ($request, $validated) {
                $user = User::query()->whereKey($request->user()->id)->lockForUpdate()->first();
                $totalPrice = 0;
                $lineItems = [];

                // 1. ตรวจสอบ Stock และคำนวณราคา
                foreach ($validated['items'] as $entry) {
                    $item = Item::query()->whereKey($entry['item_id'])->lockForUpdate()->first();
                    
                    if (!$item || !$item->is_active) {
                        throw new \RuntimeException("Item {$entry['item_id']} is no longer available.");
                    }
                    if ($item->stock < $entry['quantity']) {
                        throw new \RuntimeException("Stock insufficient for {$item->name}.");
                    }

                    $totalPrice += $item->price * $entry['quantity'];
                    $lineItems[] = [
                        'model' => $item,
                        'qty' => $entry['quantity'],
                        'price' => $item->price
                    ];
                }

                // 2. ตรวจสอบเงินในวอลเล็ท
                if ($user->balance < $totalPrice) {
                    throw new \RuntimeException('ยอดเงินใน Wallet ไม่เพียงพอ');
                }

                // 3. เตรียมที่อยู่
                [$shippingAddressId, $addressSnapshot] = $this->resolveShippingAddress($request, $user);

                // 4. สร้างออเดอร์หลัก
                $order = Order::create([
                    'order_number' => Order::generateOrderNumber(),
                    'buyer_id' => $user->id,
                    'shipping_address_id' => $shippingAddressId,
                    'status' => 'pending',
                    'total_price' => $totalPrice,
                    'shipping_address' => ApiData::formatAddress($addressSnapshot),
                    'shipping_address_snapshot' => $addressSnapshot,
                    'delivery_method' => $validated['delivery_method'],
                    'delivery_method_label' => self::DELIVERY_METHODS[$validated['delivery_method']],
                ]);

                // 5. บันทึกรายการสินค้า ตัดสต็อก หักเงิน
                foreach ($lineItems as $line) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'item_id' => $line['model']->id,
                        'quantity' => $line['qty'],
                        'price_at_purchase' => $line['price'],
                    ]);
                    $line['model']->decrement('stock', $line['qty']);
                }
                $user->decrement('balance', $totalPrice);

                // 6. ล้างรายการที่สั่งออกจากตะกร้า
                $itemIds = collect($validated['items'])->pluck('item_id')->toArray();
                $user->cartItems()->detach($itemIds);

                return $order;
            });

            return response()->json([
                'message' => 'Order created successfully',
                'order' => ApiData::order($order->load(['items.item', 'shippingAddress'])),
            ], 201);

        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function cancel(Request $request, Order $order)
    {
        abort_unless($order->buyer_id === $request->user()->id, 403);

        DB::transaction(function () use ($order) {
            $lockedOrder = Order::query()->whereKey($order->id)->lockForUpdate()->first();
            abort_unless($lockedOrder->status === 'pending', 400, 'Only pending orders can be cancelled.');

            $lockedOrder->update(['status' => 'cancelled', 'cancelled_at' => now()]);
            foreach ($lockedOrder->items as $item) {
                Item::query()->whereKey($item->item_id)->increment('stock', $item->quantity);
            }
            User::query()->whereKey($lockedOrder->buyer_id)->increment('balance', $lockedOrder->total_price);
        });

        return response()->json(['message' => 'Order cancelled and refunded.']);
    }

    public function requestRefund(Request $request, Order $order)
    {
        abort_unless($order->buyer_id === $request->user()->id, 403);

        $validated = $request->validate([
            'order_item_id' => ['required', 'integer'],
            'quantity' => ['required', 'integer', 'min:1'],
            'reason_code' => ['required', 'string', 'in:' . implode(',', self::REFUND_REASON_CODES)],
            'reason_detail' => ['nullable', 'string', 'max:255'],
            'issue_description' => ['required', 'string', 'min:10', 'max:2000'],
            'evidence_image' => ['required', 'image', 'max:10240'],
        ]);

        DB::transaction(function () use ($order, $validated, $request) {
            $lockedOrder = Order::query()->whereKey($order->id)->with('items')->lockForUpdate()->first();
            abort_unless(in_array($lockedOrder->status, ['shipped', 'partially_refunded', 'refunding']), 400);

            $orderItem = $lockedOrder->items->firstWhere('id', (int) $validated['order_item_id']);
            $evidencePath = $request->file('evidence_image')->store('refund-evidence', 'public');

            $orderItem->update([
                'refund_requested_quantity' => $validated['quantity'],
                'refund_reason_code' => $validated['reason_code'],
                'refund_issue_description' => $validated['issue_description'],
                'refund_evidence_image_path' => $evidencePath,
                'refund_requested_at' => now(),
            ]);

            $orderItem->refundEvents()->create([
                'event_type' => 'requested',
                'quantity' => $validated['quantity'],
                'reason_code' => $validated['reason_code'],
                'issue_description' => $validated['issue_description'],
                'evidence_image_path' => $evidencePath,
                'acted_by_user_id' => $request->user()->id,
                'happened_at' => now(),
            ]);

            $lockedOrder->update(['status' => 'refunding', 'refund_requested_at' => now()]);
        });

        return response()->json(['message' => 'Refund requested.']);
    }

    private function resolveShippingAddress(Request $request, User $user): array
    {
        if ($request->filled('address_id')) {
            $address = UserAddress::query()
                ->where('user_id', $user->id)
                ->whereKey((int) $request->input('address_id'))
                ->firstOrFail();
            return [$address->id, $address->toSnapshot()];
        }

        $snapshot = $request->input('shipping_address');
        if ($request->boolean('save_address')) {
            if ($request->boolean('set_as_default')) {
                $user->addresses()->update(['is_default' => false]);
            }
            $address = $user->addresses()->create([
                ...$snapshot,
                'is_default' => !$user->addresses()->exists() || $request->boolean('set_as_default'),
            ]);
            return [$address->id, $address->toSnapshot()];
        }
        return [null, $snapshot];
    }
}