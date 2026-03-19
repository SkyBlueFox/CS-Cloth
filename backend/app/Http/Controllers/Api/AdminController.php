<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Order;
use App\Models\Question;
use App\Models\User;
use App\Support\ApiData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    public function items()
    {
        $items = Item::query()->latest()->paginate(20);

        return response()->json(ApiData::pagination($items, fn (Item $item) => ApiData::item($item)));
    }

    public function showItem(Item $item)
    {
        return response()->json([
            'item' => ApiData::item($item),
        ]);
    }

    public function storeItem(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'image' => ['nullable', 'image', 'max:10240'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $path = $request->hasFile('image')
            ? $request->file('image')->store('items', 'public')
            : null;

        $item = Item::create([
            'name' => $validated['name'],
            'created_by_id' => $request->user()->id,
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'],
            'stock' => $validated['stock'],
            'image_path' => $path,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return response()->json(['item' => ApiData::item($item)], 201);
    }

    public function updateItem(Request $request, Item $item)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'image' => ['nullable', 'image', 'max:10240'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if ($request->hasFile('image')) {
            if ($item->image_path) {
                Storage::disk('public')->delete($item->image_path);
            }

            $item->image_path = $request->file('image')->store('items', 'public');
        }

        $item->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'],
            'stock' => $validated['stock'],
            'is_active' => $validated['is_active'] ?? $item->is_active,
            'image_path' => $item->image_path,
        ]);

        return response()->json(['item' => ApiData::item($item->fresh())]);
    }

    public function destroyItem(Item $item)
    {
        $item->delete();

        return response()->json(['message' => 'Item deleted.']);
    }

    public function toggleItem(Item $item)
    {
        $item->update(['is_active' => !$item->is_active]);

        return response()->json(['item' => ApiData::item($item->fresh())]);
    }

    public function orders()
    {
        $orders = Order::query()->with(['buyer', 'items.item'])->latest()->paginate(20);

        return response()->json(ApiData::pagination($orders, fn (Order $order) => ApiData::order($order)));
    }

    public function ship(Order $order)
    {
        if ($order->status !== 'pending') {
            return response()->json(['message' => 'Only pending orders can be shipped.'], 422);
        }

        $order->update([
            'status' => 'shipped',
            'shipped_at' => now(),
        ]);

        return response()->json(['message' => 'Order marked as shipped.']);
    }

    public function approveRefund(Order $order)
    {
        if ($order->status !== 'refunding') {
            return response()->json(['message' => 'Order is not in a refundable state.'], 422);
        }

        DB::transaction(function () use ($order) {
            $lockedOrder = Order::query()->whereKey($order->id)->lockForUpdate()->first();
            $buyer = User::query()->whereKey($order->buyer_id)->lockForUpdate()->first();

            $lockedOrder->update([
                'status' => 'refunded',
                'refunded_at' => now(),
            ]);

            $buyer->increment('balance', $lockedOrder->total_price);

            $lockedOrder->load('items');
            foreach ($lockedOrder->items as $orderItem) {
                Item::query()->whereKey($orderItem->item_id)->increment('stock', $orderItem->quantity);
            }
        });

        return response()->json(['message' => 'Refund approved.']);
    }

    public function questions(Request $request)
    {
        $pending = Question::query()
            ->with(['item', 'asker'])
            ->whereNull('answer_text')
            ->latest()
            ->paginate(10, ['*'], 'pending_page', (int) $request->integer('pending_page', 1));

        $answered = Question::query()
            ->with(['item', 'asker'])
            ->whereNotNull('answer_text')
            ->where('admin_id', $request->user()->id)
            ->latest()
            ->paginate(10, ['*'], 'answered_page', (int) $request->integer('answered_page', 1));

        return response()->json([
            'pending' => ApiData::pagination($pending, fn (Question $question) => ApiData::question($question)),
            'answered' => ApiData::pagination($answered, fn (Question $question) => ApiData::question($question)),
        ]);
    }

    public function answerQuestion(Request $request, Question $question)
    {
        $validated = $request->validate([
            'answer_text' => ['required', 'string', 'max:5000'],
        ]);

        $question->update([
            'answer_text' => $validated['answer_text'],
            'admin_id' => $request->user()->id,
            'admin_name' => $request->user()->name,
        ]);

        return response()->json(['question' => ApiData::question($question->fresh())]);
    }

    public function deleteAnswer(Request $request, Question $question)
    {
        if ($question->admin_id !== $request->user()->id) {
            return response()->json(['message' => 'You cannot delete another admin\'s answer.'], 403);
        }

        $question->update([
            'answer_text' => null,
            'admin_id' => null,
            'admin_name' => null,
            'score_cached' => 0,
        ]);

        return response()->json(['message' => 'Answer deleted.']);
    }
}
