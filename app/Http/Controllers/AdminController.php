<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;


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

    public function createItem()
    {
        return view('admin.items.create');
    }

    public function storeItem(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'image' => 'nullable|image|max:10240', // 10MB Max
        ]);

        $path = null;
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('items', 'public');
        }

        Item::create([
            'name' => $validated['name'],
            'created_by_id' => Auth::id(),


            'description' => $validated['description'],
            'price' => $validated['price'],
            'stock' => $validated['stock'],
            'image_path' => $path,
            'is_active' => true,
        ]);

        return redirect()->route('admin.items.create')->with('success', 'Item posted successfully!');
    }
}
