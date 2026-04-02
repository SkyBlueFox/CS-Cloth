<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Question;
use App\Models\User;
use App\Support\ApiData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    public function items(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $sort = (string) $request->query('sort', 'newest');

        $items = Item::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            });

        match ($sort) {
            'oldest' => $items->oldest(),
            'name_asc' => $items->orderBy('name'),
            'name_desc' => $items->orderByDesc('name'),
            'price_low' => $items->orderBy('price'),
            'price_high' => $items->orderByDesc('price'),
            'stock_low' => $items->orderBy('stock'),
            'stock_high' => $items->orderByDesc('stock'),
            default => $items->latest(),
        };

        $items = $items->paginate(20);

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

    public function orders(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $sort = (string) $request->query('sort', 'newest');
        $queue = (string) $request->query('queue', 'shipping');
        $refundReasons = collect($request->query('refund_reasons', []))
            ->flatMap(function (array|string $reason) {
                if (is_array($reason)) {
                    return $reason;
                }

                return explode(',', $reason);
            })
            ->map(fn (mixed $reason) => trim((string) $reason))
            ->filter()
            ->values();

        $orders = Order::query()
            ->with(['buyer', 'items.item', 'items.refundEvents'])
            ->when($queue === 'shipping', function ($query) {
                $query->where('status', 'pending');
            })
            ->when($queue === 'refund', function ($query) use ($refundReasons) {
                $query->whereIn('status', ['refunding', 'partially_refunded'])
                    ->whereHas('items', function ($itemQuery) use ($refundReasons) {
                        $itemQuery->where('refund_requested_quantity', '>', 0);

                        if ($refundReasons->isNotEmpty()) {
                            $itemQuery->whereIn('refund_reason_code', $refundReasons->all());
                        }
                    });
            })
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery
                        ->where('order_number', 'like', "%{$search}%")
                        ->orWhere('status', 'like', "%{$search}%")
                        ->orWhereHas('buyer', function ($buyerQuery) use ($search) {
                            $buyerQuery
                                ->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        })
                        ->orWhereHas('items.item', function ($itemQuery) use ($search) {
                            $itemQuery->where('name', 'like', "%{$search}%");
                        });
                });
            });

        $usesRefundQueueSort = $queue === 'refund';

        if ($usesRefundQueueSort && in_array($sort, ['newest', 'oldest'], true)) {
            $latestRefundRequestAt = OrderItem::query()
                ->selectRaw('MAX(refund_requested_at)')
                ->whereColumn('order_id', 'orders.id')
                ->where('refund_requested_quantity', '>', 0);

            if ($refundReasons->isNotEmpty()) {
                $latestRefundRequestAt->whereIn('refund_reason_code', $refundReasons->all());
            }

            $orders->addSelect([
                'latest_refund_request_at' => $latestRefundRequestAt,
            ]);
        }

        match ($sort) {
            'oldest' => $usesRefundQueueSort
                ? $orders->orderBy('latest_refund_request_at')->orderBy('created_at')
                : $orders->oldest(),
            'total_low' => $orders->orderBy('total_price'),
            'total_high' => $orders->orderByDesc('total_price'),
            default => $usesRefundQueueSort
                ? $orders->orderByDesc('latest_refund_request_at')->orderByDesc('created_at')
                : $orders->latest(),
        };

        $orders = $orders->paginate(20);

        return response()->json(ApiData::pagination($orders, fn (Order $order) => ApiData::order($order)));
    }

    public function showAdminOrder(Order $order)
    {
        $order->load(['buyer', 'items.item', 'items.refundEvents', 'shippingAddress']);

        return response()->json([
            'order' => ApiData::order($order),
        ]);
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

    public function approveRefund(Request $request, Order $order)
    {
        if (!in_array($order->status, ['refunding', 'partially_refunded'], true)) {
            return response()->json(['message' => 'Order is not in a refundable state.'], 422);
        }

        $validated = $request->validate([
            'order_item_id' => ['required', 'integer'],
        ]);

        DB::transaction(function () use ($order, $validated, $request) {
            $lockedOrder = Order::query()->whereKey($order->id)->with('items')->lockForUpdate()->first();
            $buyer = User::query()->whereKey($order->buyer_id)->lockForUpdate()->first();

            $orderItem = $lockedOrder->items->firstWhere('id', (int) $validated['order_item_id']);
            abort_unless($orderItem, 404, 'Order item not found.');
            abort_unless($orderItem->refund_requested_quantity > 0, 422, 'This order item has no pending refund request.');

            $refundQty = $orderItem->refund_requested_quantity;
            $refundTotal = $refundQty * $orderItem->price_at_purchase;

            $orderItem->update([
                'refunded_quantity' => $orderItem->refunded_quantity + $refundQty,
                'refund_requested_quantity' => 0,
                'refund_approved_at' => now(),
            ]);

            $orderItem->refundEvents()->create([
                'event_type' => 'approved',
                'quantity' => $refundQty,
                'reason_code' => $orderItem->refund_reason_code,
                'reason_detail' => $orderItem->refund_reason_detail,
                'issue_description' => $orderItem->refund_issue_description,
                'evidence_image_path' => $orderItem->refund_evidence_image_path,
                'acted_by_user_id' => $request->user()->id,
                'happened_at' => now(),
            ]);

            Item::query()->whereKey($orderItem->item_id)->increment('stock', $refundQty);

            $hasPendingRefunds = $lockedOrder->items->contains(fn ($line) => $line->id !== $orderItem->id && $line->refund_requested_quantity > 0);
            $allItemsRefunded = $lockedOrder->items->every(
                fn ($line) => $line->refunded_quantity >= $line->quantity
            );

            $lockedOrder->update([
                'status' => $hasPendingRefunds ? 'refunding' : ($allItemsRefunded ? 'refunded' : 'partially_refunded'),
                'refunded_at' => now(),
                'admin_refunded_id' => $request->user()->id,
            ]);

            $buyer->increment('balance', $refundTotal);
        });

        return response()->json(['message' => 'Refund approved for the selected item request.']);
    }

    public function dismissRefund(Request $request, Order $order)
    {
        if (!in_array($order->status, ['refunding', 'partially_refunded'], true)) {
            return response()->json(['message' => 'Order is not in a refundable state.'], 422);
        }

        $validated = $request->validate([
            'order_item_id' => ['required', 'integer'],
        ]);

        DB::transaction(function () use ($order, $validated, $request) {
            $lockedOrder = Order::query()->whereKey($order->id)->with('items')->lockForUpdate()->first();

            $orderItem = $lockedOrder->items->firstWhere('id', (int) $validated['order_item_id']);
            abort_unless($orderItem, 404, 'Order item not found.');
            abort_unless($orderItem->refund_requested_quantity > 0, 422, 'This order item has no pending refund request.');

            $dismissedQuantity = $orderItem->refund_requested_quantity;

            $orderItem->update([
                'refund_requested_quantity' => 0,
                'refund_dismissed_quantity' => $dismissedQuantity,
                'refund_dismissed_at' => now(),
            ]);

            $orderItem->refundEvents()->create([
                'event_type' => 'dismissed',
                'quantity' => $dismissedQuantity,
                'reason_code' => $orderItem->refund_reason_code,
                'reason_detail' => $orderItem->refund_reason_detail,
                'issue_description' => $orderItem->refund_issue_description,
                'evidence_image_path' => $orderItem->refund_evidence_image_path,
                'acted_by_user_id' => $request->user()->id,
                'happened_at' => now(),
            ]);

            $hasPendingRefunds = $lockedOrder->items->contains(fn ($line) => $line->id !== $orderItem->id && $line->refund_requested_quantity > 0);
            $allItemsRefunded = $lockedOrder->items->every(fn ($line) => $line->refunded_quantity >= $line->quantity);

            $lockedOrder->update([
                'status' => $hasPendingRefunds ? 'refunding' : ($allItemsRefunded ? 'refunded' : 'shipped'),
            ]);
        });

        return response()->json(['message' => 'Refund request dismissed for the selected item.']);
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
