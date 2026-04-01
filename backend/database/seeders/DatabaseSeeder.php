<?php

namespace Database\Seeders;

use App\Models\Item;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Question;
use App\Models\Report;
use App\Models\User;
use App\Models\UserAddress;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $superAdmin = User::updateOrCreate(['email' => 'tan@cloth.com'], [
            'name' => 'Tan SuperAdmin',
            'password' => Hash::make('asd123'),
            'role' => User::ROLE_SUPERADMIN,
            'email_verified_at' => now(),
        ]);

        $admin1 = User::updateOrCreate(['email' => 'admin@cloth.com'], [
            'name' => 'Admin 1',
            'password' => Hash::make('asd123'),
            'role' => User::ROLE_ADMIN,
            'email_verified_at' => now(),
        ]);

        $customer1 = User::updateOrCreate(['email' => 'user@cloth.com'], [
            'name' => 'Test Customer 1',
            'password' => Hash::make('asd123'),
            'role' => User::ROLE_USER,
            'email_verified_at' => now(),
            'balance' => 5000,
        ]);

        $customer2 = User::updateOrCreate(['email' => 'may@cloth.com'], [
            'name' => 'May Customer',
            'password' => Hash::make('asd123'),
            'role' => User::ROLE_USER,
            'email_verified_at' => now(),
            'balance' => 4200,
        ]);

        $primaryAddress = UserAddress::updateOrCreate(
            ['user_id' => $customer1->id, 'label' => 'Home'],
            [
                'recipient_name' => $customer1->name,
                'phone' => '0812345678',
                'line_1' => '123 Ngamwongwan Rd',
                'line_2' => 'Chatuchak',
                'district' => 'Chatuchak',
                'province' => 'Bangkok',
                'postal_code' => '10900',
                'country' => 'Thailand',
                'is_default' => true,
            ]
        );

        $secondaryAddress = UserAddress::updateOrCreate(
            ['user_id' => $customer2->id, 'label' => 'Dorm'],
            [
                'recipient_name' => $customer2->name,
                'phone' => '0898765432',
                'line_1' => '77 Dormitory Road',
                'line_2' => 'Room 908',
                'district' => 'Bang Khen',
                'province' => 'Bangkok',
                'postal_code' => '10220',
                'country' => 'Thailand',
                'is_default' => true,
            ]
        );

        $item1 = Item::updateOrCreate(['name' => 'Fresh Apple'], [
            'name' => 'Fresh Apple',
            'created_by_id' => $admin1->id,
            'description' => 'It is very fresh and sweet',
            'price' => 40.00,
            'image_path' => "items/apple.jpg",
            'stock' => 100,
        ]);

        $question1 = Question::updateOrCreate(['item_id' => $item1->id, 'question_text' => 'Is this cotton?'], [
            'item_id' => $item1->id,
            'asker_id' => $customer1->id,
            'asker_name' => $customer1->name,
            'admin_id' => $admin1->id,
            'admin_name' => $admin1->name,
            'answer_text' => 'idk',
        ]);

        $question2 = Question::updateOrCreate(['item_id' => $item1->id, 'question_text' => 'Is this big?'], [
            'item_id' => $item1->id,
            'asker_id' => $customer1->id,
            'asker_name' => $customer1->name,
            'admin_id' => $admin1->id,
            'admin_name' => $admin1->name,
            'answer_text' => "yes like yo mama",
        ]);

        Report::updateOrCreate(['question_id' => $question1->id, 'reason' => "Doesn't answer the question"], [
            'reporter_id' => $customer1->id,
            'reporter_name' => $customer1->name,
            'admin_id' => $admin1->id,
            'admin_name' => $admin1->name,
            'question_text_snapshot' => $question1->question_text,
            'answer_text_snapshot' => $question1->answer_text,
            'status' => Report::STATUS_PENDING,
        ]);

        Report::updateOrCreate(['question_id' => $question2->id, 'reason' => 'Rude answer'], [
            'reporter_id' => $customer1->id,
            'reporter_name' => $customer1->name,
            'admin_id' => $admin1->id,
            'admin_name' => $admin1->name,
            'question_text_snapshot' => $question2->question_text,
            'answer_text_snapshot' => $question2->answer_text,
            'status' => Report::STATUS_PENDING,
        ]);

        $shirt = Item::updateOrCreate(['name' => 'XL KU CS T-Shirt'], [
            'name' => 'XL KU CS T-Shirt',
            'created_by_id' => $admin1->id,
            'description' => 'Standard issue CS shirt. 100% Cotton.',
            'price' => 250.00,
            'stock' => 50,
            'image_path' => "items/black_cs_shirt.png",
            'is_active' => true,
        ]);

        $hoodie = Item::updateOrCreate(['name' => 'XS Tech Faculty Hoodie'], [
            'name' => 'XS Tech Faculty Hoodie',
            'created_by_id' => $admin1->id,
            'description' => 'Warm polyester hoodie for the cold labs.',
            'price' => 600.00,
            'stock' => 20,
            'image_path' => "items/black_cs_hoodie.png",
            'is_active' => true,
        ]);

        $sticker = Item::updateOrCreate(['name' => 'Laptop Sticker Pack'], [
            'name' => 'Laptop Sticker Pack',
            'created_by_id' => $admin1->id,
            'description' => 'Vinyl stickers for your ThinkPad.',
            'price' => 50.00,
            'stock' => 100,
            'image_path' => "items/cute_sticker.png",
            'is_active' => true,
        ]);

        $cap = Item::updateOrCreate(['name' => 'CS Baseball Cap'], [
            'name' => 'CS Baseball Cap',
            'created_by_id' => $admin1->id,
            'description' => 'Lightweight cap with embroidered CS logo.',
            'price' => 320.00,
            'stock' => 40,
            'image_path' => "items/green_csku_cap.png",
            'is_active' => true,
        ]);

        $homeSnapshot = $primaryAddress->toSnapshot();
        $dormSnapshot = $secondaryAddress->toSnapshot();

        $order1 = Order::updateOrCreate(['buyer_id' => $customer1->id, 'status' => 'pending', 'total_price' => 500.00], [
            'order_number' => '260401PN10TSH1',
            'buyer_id' => $customer1->id,
            'shipping_address_id' => $primaryAddress->id,
            'status' => 'pending',
            'total_price' => 500.00,
            'shipping_address' => '123 Ngamwongwan Rd, Chatuchak, Bangkok, 10900, Thailand',
            'shipping_address_snapshot' => $homeSnapshot,
        ]);

        OrderItem::updateOrCreate(['order_id' => $order1->id, 'item_id' => $shirt->id], [
            'quantity' => 2,
            'price_at_purchase' => 250.00,
            'refund_requested_quantity' => 0,
            'refunded_quantity' => 0,
        ]);

        $order2 = Order::updateOrCreate(['buyer_id' => $customer1->id, 'status' => 'refunding', 'total_price' => 600.00], [
            'order_number' => '260401RF21HOD9',
            'buyer_id' => $customer1->id,
            'shipping_address_id' => $primaryAddress->id,
            'status' => 'refunding',
            'total_price' => 600.00,
            'shipping_address' => 'Kasetsart University, Lab 404',
            'shipping_address_snapshot' => [
                'label' => 'Campus',
                'recipient_name' => $customer1->name,
                'phone' => '0812345678',
                'line_1' => 'Kasetsart University, Lab 404',
                'line_2' => null,
                'district' => 'Chatuchak',
                'province' => 'Bangkok',
                'postal_code' => '10900',
                'country' => 'Thailand',
            ],
            'refund_requested_at' => now(),
        ]);

        OrderItem::updateOrCreate(['order_id' => $order2->id, 'item_id' => $hoodie->id], [
            'quantity' => 1,
            'price_at_purchase' => 600.00,
            'refund_requested_quantity' => 1,
            'refunded_quantity' => 0,
            'refund_reason_code' => 'damaged_item',
            'refund_reason_detail' => null,
            'refund_issue_description' => 'The hoodie zipper arrived broken and the front pocket seam is torn.',
            'refund_requested_at' => now(),
        ]);

        $order3 = Order::updateOrCreate(['buyer_id' => $customer1->id, 'status' => 'partially_refunded', 'total_price' => 750.00], [
            'order_number' => '260401PR32APP7',
            'buyer_id' => $customer1->id,
            'shipping_address_id' => $primaryAddress->id,
            'status' => 'partially_refunded',
            'total_price' => 750.00,
            'shipping_address' => '123 Ngamwongwan Rd, Chatuchak, Bangkok, 10900, Thailand',
            'shipping_address_snapshot' => $homeSnapshot,
            'shipped_at' => now()->subDays(6),
            'refund_requested_at' => now()->subDays(3),
            'refunded_at' => now()->subDays(2),
        ]);

        OrderItem::updateOrCreate(['order_id' => $order3->id, 'item_id' => $shirt->id], [
            'quantity' => 3,
            'price_at_purchase' => 250.00,
            'refund_requested_quantity' => 0,
            'refunded_quantity' => 1,
            'refund_reason_code' => 'quality_issue',
            'refund_reason_detail' => null,
            'refund_issue_description' => 'One shirt had a badly misprinted logo while the other two were fine.',
            'refund_requested_at' => now()->subDays(3),
            'refund_approved_at' => now()->subDays(2),
        ]);

        $order4 = Order::updateOrCreate(['buyer_id' => $customer2->id, 'status' => 'shipped', 'total_price' => 640.00], [
            'order_number' => '260401SH43STK8',
            'buyer_id' => $customer2->id,
            'shipping_address_id' => $secondaryAddress->id,
            'status' => 'shipped',
            'total_price' => 640.00,
            'shipping_address' => '77 Dormitory Road, Room 908, Bang Khen, Bangkok, 10220, Thailand',
            'shipping_address_snapshot' => $dormSnapshot,
            'shipped_at' => now()->subDay(),
        ]);

        OrderItem::updateOrCreate(['order_id' => $order4->id, 'item_id' => $cap->id], [
            'quantity' => 2,
            'price_at_purchase' => 320.00,
            'refund_requested_quantity' => 0,
            'refunded_quantity' => 0,
        ]);

        $order5 = Order::updateOrCreate(['buyer_id' => $customer2->id, 'status' => 'refunding', 'total_price' => 150.00], [
            'order_number' => '260401RF54STK2',
            'buyer_id' => $customer2->id,
            'shipping_address_id' => $secondaryAddress->id,
            'status' => 'refunding',
            'total_price' => 150.00,
            'shipping_address' => '77 Dormitory Road, Room 908, Bang Khen, Bangkok, 10220, Thailand',
            'shipping_address_snapshot' => $dormSnapshot,
            'shipped_at' => now()->subDays(4),
            'refund_requested_at' => now()->subHours(8),
        ]);

        OrderItem::updateOrCreate(['order_id' => $order5->id, 'item_id' => $sticker->id], [
            'quantity' => 3,
            'price_at_purchase' => 50.00,
            'refund_requested_quantity' => 1,
            'refunded_quantity' => 0,
            'refund_reason_code' => 'other',
            'refund_reason_detail' => 'Wrong design',
            'refund_issue_description' => 'One of the sticker sheets has the wrong artwork printed on it.',
            'refund_requested_at' => now()->subHours(8),
        ]);

        $order6 = Order::updateOrCreate(['buyer_id' => $customer2->id, 'status' => 'refunded', 'total_price' => 320.00], [
            'order_number' => '260401RF65CAP4',
            'buyer_id' => $customer2->id,
            'shipping_address_id' => $secondaryAddress->id,
            'status' => 'refunded',
            'total_price' => 320.00,
            'shipping_address' => '77 Dormitory Road, Room 908, Bang Khen, Bangkok, 10220, Thailand',
            'shipping_address_snapshot' => $dormSnapshot,
            'shipped_at' => now()->subDays(10),
            'refund_requested_at' => now()->subDays(8),
            'refunded_at' => now()->subDays(7),
        ]);

        OrderItem::updateOrCreate(['order_id' => $order6->id, 'item_id' => $cap->id], [
            'quantity' => 1,
            'price_at_purchase' => 320.00,
            'refund_requested_quantity' => 0,
            'refunded_quantity' => 1,
            'refund_reason_code' => 'wrong_item',
            'refund_reason_detail' => null,
            'refund_issue_description' => 'Received the wrong hat color and support approved the refund.',
            'refund_requested_at' => now()->subDays(8),
            'refund_approved_at' => now()->subDays(7),
        ]);
    }
}
