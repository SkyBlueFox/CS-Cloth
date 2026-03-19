<?php

namespace App\Support;

use App\Models\Item;
use App\Models\Order;
use App\Models\Question;
use App\Models\Report;
use App\Models\User;
use App\Models\UserAddress;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ApiData
{
    public static function user(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'role' => strtolower($user->role),
            'balance' => (float) $user->balance,
            'created_at' => $user->created_at?->toIso8601String(),
        ];
    }

    public static function address(UserAddress $address): array
    {
        return [
            'id' => $address->id,
            'label' => $address->label,
            'recipient_name' => $address->recipient_name,
            'phone' => $address->phone,
            'line_1' => $address->line_1,
            'line_2' => $address->line_2,
            'district' => $address->district,
            'province' => $address->province,
            'postal_code' => $address->postal_code,
            'country' => $address->country,
            'is_default' => (bool) $address->is_default,
            'formatted' => self::formatAddress($address->toSnapshot()),
        ];
    }

    public static function item(Item $item): array
    {
        return [
            'id' => $item->id,
            'name' => $item->name,
            'description' => $item->description,
            'price' => (float) $item->price,
            'stock' => (int) $item->stock,
            'image_path' => $item->image_path,
            'image_url' => $item->image_path ? url('/storage/'.$item->image_path) : null,
            'is_active' => (bool) $item->is_active,
            'created_at' => $item->created_at?->toIso8601String(),
            'updated_at' => $item->updated_at?->toIso8601String(),
        ];
    }

    public static function question(Question $question): array
    {
        return [
            'id' => $question->id,
            'item_id' => $question->item_id,
            'asker_id' => $question->asker_id,
            'asker_name' => $question->asker_name,
            'admin_id' => $question->admin_id,
            'admin_name' => $question->admin_name,
            'question_text' => $question->question_text,
            'answer_text' => $question->answer_text,
            'score_cached' => (int) $question->score_cached,
            'is_reported_by_current_user' => (bool) ($question->is_reported_by_current_user ?? false),
            'item' => $question->relationLoaded('item') && $question->item ? self::item($question->item) : null,
            'asker' => $question->relationLoaded('asker') && $question->asker ? self::user($question->asker) : null,
            'created_at' => $question->created_at?->toIso8601String(),
            'updated_at' => $question->updated_at?->toIso8601String(),
        ];
    }

    public static function report(Report $report): array
    {
        return [
            'id' => $report->id,
            'reporter_id' => $report->reporter_id,
            'reporter_name' => $report->reporter_name,
            'admin_id' => $report->admin_id,
            'admin_name' => $report->admin_name,
            'question_id' => $report->question_id,
            'question_text_snapshot' => $report->question_text_snapshot,
            'answer_text_snapshot' => $report->answer_text_snapshot,
            'reason' => $report->reason,
            'status' => $report->status,
            'created_at' => $report->created_at?->toIso8601String(),
        ];
    }

    public static function order(Order $order): array
    {
        return [
            'id' => $order->id,
            'buyer_id' => $order->buyer_id,
            'status' => $order->status,
            'total_price' => (float) $order->total_price,
            'shipping_address' => $order->shipping_address,
            'shipping_address_id' => $order->shipping_address_id,
            'shipping_address_snapshot' => $order->shipping_address_snapshot,
            'shipping_address_formatted' => self::formatAddress($order->shipping_address_snapshot, $order->shipping_address),
            'buyer' => $order->relationLoaded('buyer') && $order->buyer ? self::user($order->buyer) : null,
            'items' => $order->relationLoaded('items')
                ? $order->items->map(fn ($orderItem) => [
                    'id' => $orderItem->id,
                    'item_id' => $orderItem->item_id,
                    'quantity' => (int) $orderItem->quantity,
                    'price_at_purchase' => (float) $orderItem->price_at_purchase,
                    'item' => $orderItem->relationLoaded('item') && $orderItem->item ? self::item($orderItem->item) : null,
                ])->values()->all()
                : [],
            'shipped_at' => $order->shipped_at?->toIso8601String(),
            'cancelled_at' => $order->cancelled_at?->toIso8601String(),
            'refund_requested_at' => $order->refund_requested_at?->toIso8601String(),
            'refunded_at' => $order->refunded_at?->toIso8601String(),
            'created_at' => $order->created_at?->toIso8601String(),
        ];
    }

    public static function pagination(LengthAwarePaginator $paginator, callable $map): array
    {
        return [
            'data' => collect($paginator->items())->map($map)->values()->all(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ];
    }

    public static function formatAddress(?array $snapshot, ?string $fallback = null): ?string
    {
        if (!$snapshot) {
            return $fallback;
        }

        $parts = array_filter([
            $snapshot['recipient_name'] ?? null,
            $snapshot['phone'] ?? null,
            $snapshot['line_1'] ?? null,
            $snapshot['line_2'] ?? null,
            $snapshot['district'] ?? null,
            $snapshot['province'] ?? null,
            $snapshot['postal_code'] ?? null,
            $snapshot['country'] ?? null,
        ]);

        return $parts ? implode(', ', $parts) : $fallback;
    }
}
