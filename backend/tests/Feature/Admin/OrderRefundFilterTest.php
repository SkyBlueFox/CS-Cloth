<?php

use App\Models\ApiToken;
use App\Models\Item;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

it('includes orders that match any selected refund reason in the refund queue', function () {
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
        'name' => 'T-Shirt',
        'created_by_id' => $admin->id,
        'description' => 'Cotton shirt',
        'price' => 20.00,
        'stock' => 20,
        'is_active' => true,
    ]);

    $damagedOrder = Order::query()->create([
        'order_number' => Order::generateOrderNumber(),
        'buyer_id' => $buyer->id,
        'status' => 'refunding',
        'total_price' => 20.00,
    ]);

    OrderItem::query()->create([
        'order_id' => $damagedOrder->id,
        'item_id' => $item->id,
        'quantity' => 1,
        'price_at_purchase' => 20.00,
        'refund_requested_quantity' => 1,
        'refund_reason_code' => 'damaged_item',
        'refund_requested_at' => now()->subHour(),
    ]);

    $wrongItemOrder = Order::query()->create([
        'order_number' => Order::generateOrderNumber(),
        'buyer_id' => $buyer->id,
        'status' => 'partially_refunded',
        'total_price' => 20.00,
    ]);

    OrderItem::query()->create([
        'order_id' => $wrongItemOrder->id,
        'item_id' => $item->id,
        'quantity' => 1,
        'price_at_purchase' => 20.00,
        'refund_requested_quantity' => 1,
        'refund_reason_code' => 'wrong_item',
        'refund_requested_at' => now(),
    ]);

    $qualityIssueOrder = Order::query()->create([
        'order_number' => Order::generateOrderNumber(),
        'buyer_id' => $buyer->id,
        'status' => 'refunding',
        'total_price' => 20.00,
    ]);

    OrderItem::query()->create([
        'order_id' => $qualityIssueOrder->id,
        'item_id' => $item->id,
        'quantity' => 1,
        'price_at_purchase' => 20.00,
        'refund_requested_quantity' => 1,
        'refund_reason_code' => 'quality_issue',
        'refund_requested_at' => now()->addHour(),
    ]);

    $plainToken = Str::random(40);

    ApiToken::query()->create([
        'user_id' => $admin->id,
        'name' => 'test-token',
        'token_hash' => hash('sha256', $plainToken),
    ]);

    $response = $this
        ->withHeader('Authorization', 'Bearer ' . $plainToken)
        ->getJson('/api/admin/orders?queue=refund&refund_reasons=damaged_item,wrong_item');

    $response->assertOk();
    $response->assertJsonPath('meta.total', 2);

    expect(collect($response->json('data'))->pluck('id')->all())
        ->toBe([$wrongItemOrder->id, $damagedOrder->id]);
});
