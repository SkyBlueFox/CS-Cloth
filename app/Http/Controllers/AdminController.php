<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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
        // 1. Ensure the order is actually in the 'refunding' state
        if ($order->status !== 'refunding') {
            return back()->with('error', 'Order is not in a refundable state.');
        }

        DB::transaction(function () use ($order) {
            // 2. Lock the order and the buyer
            $lockedOrder = Order::query()->whereKey($order->id)->lockForUpdate()->first();
            $buyer = User::query()->whereKey($order->buyer_id)->lockForUpdate()->first();

            // 3. Update Order Status
            $lockedOrder->update([
                'status' => 'refunded',
                'refunded_at' => now(),
            ]);

            // 4. Return Money to Wallet
            $buyer->increment('balance', $lockedOrder->total_price);

            // 5. OPTIONAL: Return items to stock
            $lockedOrder->load('items');
            foreach ($lockedOrder->items as $orderItem) {
                Item::whereKey($orderItem->item_id)->increment('stock', $orderItem->quantity);
            }
        });

        return back()->with('success', 'Refund approved. ฿' . number_format($order->total_price) . ' has been returned to ' . $order->buyer->name);
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

        return redirect()->route('admin.items.index')->with('success', 'Item posted successfully!');
    }

    public function indexItems()
    {
        // Fetch items, latest first, 10 per page
        $items = Item::latest()->paginate(10);
        return view('admin.items.index', compact('items'));
    }

    public function editItem(Item $item)
    {
        return view('admin.items.edit', compact('item'));
    }

    public function updateItem(Request $request, Item $item)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'image' => 'nullable|image|max:10240', // 10MB Max
        ]);

        // Handle Image Replacement
        if ($request->hasFile('image')) {
            // Optional: Delete old image to save space
            if ($item->image_path) {
                Storage::disk('public')->delete($item->image_path);
            }
            $item->image_path = $request->file('image')->store('items', 'public');
        }

        $item->update([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'price' => $validated['price'],
            'stock' => $validated['stock'],
            // image_path is updated directly above if needed
        ]);

        return redirect()->route('admin.items.index')->with('success', 'Item updated successfully.');
    }

    public function toggleItem(Item $item)
    {
        // Flip the boolean
        $item->is_active = !$item->is_active;
        $item->save();

        $status = $item->is_active ? 'Active (Selling)' : 'Inactive (Hidden)';
        return back()->with('success', "Item is now $status.");
    }

    public function destroyItem(Item $item)
    {
        $item->delete();
        return back()->with('success', 'Item deleted.');
    }
}
