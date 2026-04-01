<?php

use App\Models\ApiToken;
use App\Models\Item;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

it('keeps the order partially refunded when only part of a line item is approved', function () {
    $admin = User::query()->create([
        'name' => 'Admin',
        'email' => 'admin@example.com',
        'password' => Hash::make('password'),
        'role' => User::ROLE_ADMIN,
    ]);

    $buyer = User::query()->create([
        'name' => 'Buyer',
        'email' => 'buyer@example.com',
        'password' => Hash::make('password'),
        'role' => User::ROLE_USER,
        'balance' => 100.00,
    ]);

    $item = Item::query()->create([
        'name' => 'Apple',
        'created_by_id' => $admin->id,
        'description' => 'Fresh apple',
        'price' => 10.00,
        'stock' => 20,
        'is_active' => true,
    ]);

    $order = Order::query()->create([
        'order_number' => Order::generateOrderNumber(),
        'buyer_id' => $buyer->id,
        'status' => 'refunding',
        'total_price' => 50.00,
        'refund_requested_at' => now(),
    ]);

    $orderItem = OrderItem::query()->create([
        'order_id' => $order->id,
        'item_id' => $item->id,
        'quantity' => 5,
        'price_at_purchase' => 10.00,
        'refund_requested_quantity' => 3,
        'refund_reason_code' => 'damaged',
        'refund_issue_description' => 'Three apples were damaged.',
        'refund_requested_at' => now(),
    ]);

    $plainToken = Str::random(40);

    ApiToken::query()->create([
        'user_id' => $admin->id,
        'name' => 'test-token',
        'token_hash' => hash('sha256', $plainToken),
    ]);

    $response = $this
        ->withHeader('Authorization', 'Bearer ' . $plainToken)
        ->patchJson("/api/admin/orders/{$order->id}/approve-refund", [
            'order_item_id' => $orderItem->id,
        ]);

    $response->assertOk();

    expect($order->fresh()->status)->toBe('partially_refunded')
        ->and($orderItem->fresh()->refunded_quantity)->toBe(3)
        ->and((float) $buyer->fresh()->balance)->toBe(130.0)
        ->and($item->fresh()->stock)->toBe(23);
});
