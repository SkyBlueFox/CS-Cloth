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

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = Order::query()
            ->where('buyer_id', $request->user()->id)
            ->with(['items.item', 'shippingAddress'])
            ->latest()
            ->paginate(10);

        return response()->json(ApiData::pagination($orders, fn (Order $order) => ApiData::order($order)));
    }

    public function store(Request $request, Item $item)
    {
        $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:'.$item->stock],
            'address_id' => ['nullable', 'integer'],
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

        if (!$item->is_active) {
            return response()->json(['message' => 'Item is not active.'], 422);
        }

        try {
            $order = DB::transaction(function () use ($request, $item) {
                $qty = (int) $request->input('quantity');
                $addressId = $request->input('address_id');

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

                $order = Order::create([
                    'buyer_id' => $user->id,
                    'shipping_address_id' => $shippingAddressId,
                    'status' => 'pending',
                    'total_price' => $total,
                    'shipping_address' => ApiData::formatAddress($addressSnapshot),
                    'shipping_address_snapshot' => $addressSnapshot,
                ]);

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

        $order->load(['items.item', 'shippingAddress']);

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

        DB::transaction(function () use ($order) {
            $lockedOrder = Order::query()->whereKey($order->id)->lockForUpdate()->first();
            abort_unless($lockedOrder->status === 'shipped', 400, 'Only shipped orders can request a refund.');

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
