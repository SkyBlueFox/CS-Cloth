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
        'damaged_item',
        'wrong_item',
        'missing_parts',
        'not_as_described',
        'quality_issue',
        'changed_mind',
        'other',
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
                    $subQuery
                        ->where('order_number', 'like', "%{$search}%")
                        ->orWhereHas('items.item', function ($itemQuery) use ($search) {
                            $itemQuery->where('name', 'like', "%{$search}%");
                        });
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

        return response()->json([
            'order' => ApiData::order($order),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'item_id' => ['required', 'integer', 'exists:items,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'address_id' => ['nullable', 'integer'],
            'delivery_method' => ['required', 'string', 'in:' . implode(',', array_keys(self::DELIVERY_METHODS))],
            'shipping_address.label' => ['required_without:address_id', 'string', 'max:100'],
            'shipping_address.recipient_name' => ['required_without:address_id', 'string', 'max:255'],
            'shipping_address.phone' => ['required_without:address_id', 'string', 'max:50'],
            'shipping_address.line_1' => ['required_without:address_id', 'string', 'max:255'],
            'shipping_address.line_2' => ['nullable', 'string', 'max:255'],
            'shipping_address.district' => ['required_without:address_id', 'string', 'max:255'],
            'shipping_address.province' => ['required_without:address_id', 'string', 'max:255'],
            'shipping_address.postal_code' => ['required_without:address_id', 'string', 'max:20'],
            'shipping_address.country' => ['required_without:address_id', 'string', 'max:100'],
            'save_address' => ['nullable', 'boolean'],
            'set_as_default' => ['nullable', 'boolean'],
        ]);

        $item = Item::query()->findOrFail((int) $validated['item_id']);

        if ((int) $validated['quantity'] > $item->stock) {
            return response()->json(['message' => 'Requested quantity exceeds available stock.'], 422);
        }

        if (!$item->is_active) {
            return response()->json(['message' => 'Item is not active.'], 422);
        }

        try {
            $order = DB::transaction(function () use ($request, $item, $validated) {
                $qty = (int) $request->input('quantity');

                $lockedItem = Item::query()->whereKey($item->id)->lockForUpdate()->first();
                if ($lockedItem->stock < $qty) {
                    throw new \RuntimeException('Out of stock.');
                }

                $total = $lockedItem->price * $qty;
                $user = User::query()->whereKey($request->user()->id)->lockForUpdate()->first();

                if ($user->balance < $total) {
                    throw new \RuntimeException('Insufficient balance.');
                }

                [$shippingAddressId, $addressSnapshot] = $this->resolveShippingAddress($request, $user);

                $orderData = [
                    'order_number' => Order::generateOrderNumber(),
                    'buyer_id' => $user->id,
                    'shipping_address_id' => $shippingAddressId,
                    'status' => 'pending',
                    'total_price' => $total,
                    'shipping_address' => ApiData::formatAddress($addressSnapshot),
                    'shipping_address_snapshot' => $addressSnapshot,
                ];

                if (Schema::hasColumn('orders', 'delivery_method')) {
                    $orderData['delivery_method'] = $validated['delivery_method'];
                }

                if (Schema::hasColumn('orders', 'delivery_method_label')) {
                    $orderData['delivery_method_label'] = self::DELIVERY_METHODS[$validated['delivery_method']];
                }

                $order = Order::create($orderData);

                OrderItem::create([
                    'order_id' => $order->id,
                    'item_id' => $lockedItem->id,
                    'quantity' => $qty,
                    'price_at_purchase' => $lockedItem->price,
                ]);

                $lockedItem->decrement('stock', $qty);
                $user->decrement('balance', $total);

                return $order;
            });
        } catch (\RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        $order->load(['items.item', 'items.refundEvents', 'shippingAddress']);

        return response()->json([
            'order' => ApiData::order($order),
        ], 201);
    }

    public function cancel(Request $request, Order $order)
    {
        abort_unless($order->buyer_id === $request->user()->id, 403);

        DB::transaction(function () use ($order) {
            $lockedOrder = Order::query()->whereKey($order->id)->lockForUpdate()->first();
            abort_unless($lockedOrder->status === 'pending', 400, 'Only pending orders can be cancelled.');

            $lockedOrder->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
            ]);

            $lockedOrder->load('items');
            foreach ($lockedOrder->items as $orderItem) {
                Item::query()->whereKey($orderItem->item_id)->lockForUpdate()->first()?->increment('stock', $orderItem->quantity);
            }

            User::query()->whereKey($lockedOrder->buyer_id)->lockForUpdate()->first()?->increment('balance', $lockedOrder->total_price);
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
            $lockedOrder = Order::query()
                ->whereKey($order->id)
                ->with('items')
                ->lockForUpdate()
                ->first();
            abort_unless(in_array($lockedOrder->status, ['shipped', 'partially_refunded', 'refunding'], true), 400, 'Only shipped orders can request a refund.');

            $orderItem = $lockedOrder->items->firstWhere('id', (int) $validated['order_item_id']);
            abort_unless($orderItem, 404, 'Order item not found.');

            $availableQuantity = max(0, $orderItem->quantity - $orderItem->refunded_quantity - $orderItem->refund_requested_quantity);
            abort_unless((int) $validated['quantity'] <= $availableQuantity, 422, 'Requested refund quantity is not available.');

            if ($validated['reason_code'] === 'other') {
                abort_unless(filled($validated['reason_detail'] ?? null), 422, 'Please provide a reason detail for Other.');
            }

            $evidencePath = $request->file('evidence_image')->store('refund-evidence', 'public');

            $orderItem->update([
                'refund_requested_quantity' => (int) $validated['quantity'],
                'refund_reason_code' => $validated['reason_code'],
                'refund_reason_detail' => $validated['reason_detail'] ?? null,
                'refund_issue_description' => $validated['issue_description'],
                'refund_evidence_image_path' => $evidencePath,
                'refund_requested_at' => now(),
            ]);

            $orderItem->refundEvents()->create([
                'event_type' => 'requested',
                'quantity' => (int) $validated['quantity'],
                'reason_code' => $validated['reason_code'],
                'reason_detail' => $validated['reason_detail'] ?? null,
                'issue_description' => $validated['issue_description'],
                'evidence_image_path' => $evidencePath,
                'acted_by_user_id' => $request->user()->id,
                'happened_at' => now(),
            ]);

            $lockedOrder->update([
                'status' => 'refunding',
                'refund_requested_at' => now(),
            ]);
        });

        return response()->json(['message' => 'Refund requested.']);
    }

    private function resolveShippingAddress(Request $request, User $user): array
    {
        if ($request->filled('address_id')) {
            $address = UserAddress::query()
                ->where('user_id', $user->id)
                ->whereKey((int) $request->input('address_id'))
                ->first();

            if (!$address) {
                throw new \RuntimeException('Selected address was not found.');
            }

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
