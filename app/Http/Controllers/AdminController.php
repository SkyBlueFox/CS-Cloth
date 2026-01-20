<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function index()
    {
        $orders = Order::with('buyer')->latest()->paginate(10);
        return view('admin.orders', compact('orders'));
    }

    public function ship(Order $order)
    {
        if ($order->status !== 'pending') {
            return back()->with('error', 'Only pending orders can be shipped.');
        }

        $order->update(['status' => 'shipped']);

        return back()->with('success', 'Order marked as shipped.');
    }

    public function approveRefund(Order $order)
    {
        if ($order->status !== 'refunding') {
            return back()->with('error', 'This order has not requested a refund.');
        }

        DB::transaction(function () use ($order) {
            // 1. Lock the order
            $lockedOrder = Order::where('id', $order->id)->lockForUpdate()->first();

            // 2. Update Status
            $lockedOrder->status = 'refunded';
            $lockedOrder->save();

            // 3. Restore Stock
            foreach ($lockedOrder->items as $orderItem) {
                $item = Item::where('id', $orderItem->item_id)->lockForUpdate()->first();
                if ($item) {
                    $item->increment('stock', $orderItem->quantity);
                }
            }

            // 4. Restore Money (Check if wallet exists first)
            $user = User::where('id', $lockedOrder->buyer_id)->lockForUpdate()->first();
            // Note: checking for the column existence in the model attribute or schema is safer
            // but for now we follow the friend's pattern of checking property access
            if ($user && isset($user->wallet_balance)) {
                $user->increment('wallet_balance', $lockedOrder->total_price);
            }
        });

        return back()->with('success', 'Refund approved. Stock and Wallet restored.');
    }
}
