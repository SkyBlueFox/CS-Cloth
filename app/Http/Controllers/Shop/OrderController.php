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
            'quantity' => ['required', 'integer', 'min:1', 'max:' . $item->stock],
            'shipping_address' => ['required', 'string', 'max:2000'],
        ]);

        if (!$item->is_active) {
            return back()->with('error', 'สินค้านี้ไม่พร้อมจำหน่าย (Item is not active)');
        }

        // Use a try-catch to handle errors inside the transaction gracefully
        try {
            DB::transaction(function () use ($request, $item) {
                $qty = (int) $request->input('quantity');

                $lockedItem = Item::query()->whereKey($item->id)->lockForUpdate()->first();
                if ($lockedItem->stock < $qty) {
                    // Throwing an exception inside the transaction triggers an automatic ROLLBACK
                    throw new \Exception('สต็อกไม่พอ (Out of stock)');
                }

                $total = $lockedItem->price * $qty;
                $user = User::query()->whereKey($request->user()->id)->lockForUpdate()->first();

                if ($user->balance < $total) {
                    throw new \Exception('ยอดเงินไม่พอ (Insufficient balance)');
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
                $user->decrement('balance', $total);
            });
        } catch (\Exception $e) {
            // Redirect back with the specific error message from the Exception
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('orders.index')->with('success', 'สั่งซื้อเรียบร้อย');
    }

    public function cancel(Request $request, Order $order)
    {
        // Ensure only the buyer can cancel their own order
        abort_unless($order->buyer_id === $request->user()->id, 403);

        DB::transaction(function () use ($order) {
            // Lock the order to prevent double-canceling
            $lockedOrder = Order::query()->whereKey($order->id)->lockForUpdate()->first();

            // Only pending orders can be cancelled and refunded
            if ($lockedOrder->status !== 'pending') {
                abort(400, 'สามารถยกเลิกได้เฉพาะออเดอร์ที่ยังรอดำเนินการเท่านั้น');
            }

            $lockedOrder->status = 'cancelled';
            $lockedOrder->cancelled_at = now();
            $lockedOrder->save();

            // Load items to return stock
            $lockedOrder->load('items');

            foreach ($lockedOrder->items as $oi) {
                $it = Item::query()->whereKey($oi->item_id)->lockForUpdate()->first();
                $it->increment('stock', $oi->quantity);
            }

            // --- INSTANT REFUND ---
            // Find the buyer and lock their row
            $buyer = User::query()->whereKey($lockedOrder->buyer_id)->lockForUpdate()->first();
            $buyer->increment('balance', $lockedOrder->total_price);
        });

        return back()->with('success', 'ยกเลิกออเดอร์และคืนเงินเรียบร้อยแล้ว');
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
