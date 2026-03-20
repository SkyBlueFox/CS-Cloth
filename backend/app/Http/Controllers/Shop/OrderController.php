<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Jobs\ProcessOrderJob;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = Order::where('buyer_id', $request->user()->id)
            ->with('items.item')
            ->latest()
            ->get();

        return response()->json([
            'data' => $orders
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'shipping_address' => 'required|string|max:2000',
        ]);

        try {
            $order = DB::transaction(function () use ($request, $validated) {

                $user = User::whereKey($request->user()->id)
                    ->lockForUpdate()
                    ->first();

                $total = 0;
                $orderItems = [];

                foreach ($validated['items'] as $i) {
                    $item = Item::whereKey($i['item_id'])
                        ->lockForUpdate()
                        ->first();

                    if (!$item->is_active) {
                        throw new \Exception("Item {$item->name} not available");
                    }

                    if ($item->stock < $i['quantity']) {
                        throw new \Exception("Stock not enough for {$item->name}");
                    }

                    $subtotal = $item->price * $i['quantity'];
                    $total += $subtotal;

                    $orderItems[] = [
                        'item' => $item,
                        'quantity' => $i['quantity'],
                        'price' => $item->price
                    ];
                }

                if ($user->wallet_balance < $total) {
                    throw new \Exception("Insufficient balance");
                }

                $order = Order::create([
                    'buyer_id' => $user->id,
                    'status' => 'pending',
                    'total_price' => $total,
                    'shipping_address' => $validated['shipping_address'],
                ]);

                foreach ($orderItems as $oi) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'item_id' => $oi['item']->id,
                        'quantity' => $oi['quantity'],
                        'price_at_purchase' => $oi['price'],
                    ]);

                    $oi['item']->decrement('stock', $oi['quantity']);
                }

                $user->decrement('wallet_balance', $total);

                // เพิ่ม Queue ตรงนี้
                ProcessOrderJob::dispatch($order);

                return $order->load('items.item');
            });

            return response()->json([
                'message' => 'Order placed',
                'data' => $order
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function cancel(Request $request, Order $order)
    {
        abort_unless($order->buyer_id === $request->user()->id, 403);

        DB::transaction(function () use ($order) {

            // lock order
            $order = Order::whereKey($order->id)->lockForUpdate()->first();

            if ($order->status !== 'pending') {
                abort(400, 'Only pending orders can be cancelled');
            }

            $order->update([
                'status' => 'cancelled',
                'cancelled_at' => now()
            ]);

            // คืน stock
            $order->load('items');

            foreach ($order->items as $oi) {
                $item = Item::whereKey($oi->item_id)->lockForUpdate()->first();
                $item->increment('stock', $oi->quantity);
            }

            // คืนเงิน
            $user = User::whereKey($order->buyer_id)->lockForUpdate()->first();
            $user->increment('wallet_balance', $order->total_price);
        });

        return response()->json([
            'message' => 'Order cancelled and refunded'
        ]);
    }

    public function requestRefund(Request $request, Order $order)
    {
        abort_unless($order->buyer_id === $request->user()->id, 403);

        DB::transaction(function () use ($order) {

            $order = Order::whereKey($order->id)->lockForUpdate()->first();

            if ($order->status !== 'shipped') {
                abort(400, 'Only shipped orders can be refunded');
            }

            $order->update([
                'status' => 'refunding',
                'refund_requested_at' => now()
            ]);
        });

        return response()->json([
            'message' => 'Refund requested'
        ]);
    }
}