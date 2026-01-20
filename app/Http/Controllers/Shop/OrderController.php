<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = Order::query()
            ->where('buyer_id', $request->user()->id)
            ->with(['items.item'])
            ->latest()
            ->paginate(10);

        return view('orders.index', compact('orders'));
    }

    public function store(Request $request, Item $item)
    {
        $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:10'],
            'shipping_address' => ['nullable', 'string', 'max:2000'],
        ]);

        abort_unless($item->is_active, 404);

        DB::transaction(function () use ($request, $item) {
            $qty = (int) $request->input('quantity');


            $lockedItem = Item::query()->whereKey($item->id)->lockForUpdate()->first();
            if ($lockedItem->stock < $qty) {
                abort(400, 'สต็อกไม่พอ');
            }

            $total = $lockedItem->price * $qty;


            $user = User::query()->whereKey($request->user()->id)->lockForUpdate()->first();


            if (isset($user->wallet_balance) && $user->wallet_balance < $total) {
                abort(400, 'ยอดเงินในกระเป๋าไม่พอ');
            }

            $order = Order::create([
                'buyer_id' => $user->id,
                'status' => 'pending',
                'total_price' => $total,
                'shipping_address' => $request->input('shipping_address'),
            ]);

            OrderItem::create([
                'order_id' => $order->id,
                'item_id' => $lockedItem->id,
                'quantity' => $qty,
                'price_at_purchase' => $lockedItem->price,
            ]);

            $lockedItem->decrement('stock', $qty);

            if (isset($user->wallet_balance)) {
                $user->decrement('wallet_balance', $total);
            }
        });

        return redirect()->route('orders.index')->with('success', 'สั่งซื้อเรียบร้อย');
    }

    public function cancel(Request $request, Order $order)
    {
        abort_unless($order->buyer_id === $request->user()->id, 403);

        DB::transaction(function () use ($request, $order) {
            $locked = Order::query()->whereKey($order->id)->lockForUpdate()->first();
            abort_unless($locked->status === 'pending', 400);

            $locked->status = 'cancelled';
            $locked->cancelled_at = now();
            $locked->save();

            $locked->load('items.item');

            // คืนสต็อก
            foreach ($locked->items as $oi) {
                $it = Item::query()->whereKey($oi->item_id)->lockForUpdate()->first();
                $it->increment('stock', $oi->quantity);
            }

            // คืนเงิน
            $user = User::query()->whereKey($request->user()->id)->lockForUpdate()->first();
            if (isset($user->wallet_balance)) {
                $user->increment('wallet_balance', $locked->total_price);
            }
        });

        return back()->with('success', 'ยกเลิกออเดอร์แล้ว');
    }

    public function requestRefund(Request $request, Order $order)
    {
        abort_unless($order->buyer_id === $request->user()->id, 403);

        DB::transaction(function () use ($order) {
            $locked = Order::query()->whereKey($order->id)->lockForUpdate()->first();
            abort_unless($locked->status === 'shipped', 400);

            $locked->status = 'refunding';
            $locked->refund_requested_at = now();
            $locked->save();
        });

        return back()->with('success', 'ส่งคำขอคืนเงินแล้ว (รอแอดมินอนุมัติ)');
    }
}
