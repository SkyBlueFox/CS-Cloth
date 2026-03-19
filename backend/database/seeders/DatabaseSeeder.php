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
        ]);

        $admin1 = User::updateOrCreate(['email' => 'admin@cloth.com'], [
            'name' => 'Admin 1',
            'password' => Hash::make('asd123'),
            'role' => User::ROLE_ADMIN,
        ]);

        $customer1 = User::updateOrCreate(['email' => 'user@cloth.com'], [
            'name' => 'Test Customer 1',
            'password' => Hash::make('asd123'),
            'role' => User::ROLE_USER,
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

        $item1 = Item::updateOrCreate(['name' => 'Item 1'], [
            'name' => 'Item 1',
            'created_by_id' => $admin1->id,
            'description' => 'Item 1 description',
            'price' => 100.00,
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

        $shirt = Item::updateOrCreate(['name' => 'KU CS T-Shirt'], [
            'name' => 'KU CS T-Shirt',
            'created_by_id' => $admin1->id,
            'description' => 'Standard issue CS shirt. 100% Cotton.',
            'price' => 250.00,
            'stock' => 50,
            'is_active' => true,
        ]);

        $hoodie = Item::updateOrCreate(['name' => 'Tech Faculty Hoodie'], [
            'name' => 'Tech Faculty Hoodie',
            'created_by_id' => $admin1->id,
            'description' => 'Warm polyester hoodie for the cold labs.',
            'price' => 600.00,
            'stock' => 20,
            'is_active' => true,
        ]);

        $sticker = Item::updateOrCreate(['name' => 'Laptop Sticker Pack'], [
            'name' => 'Laptop Sticker Pack',
            'created_by_id' => $admin1->id,
            'description' => 'Vinyl stickers for your ThinkPad.',
            'price' => 50.00,
            'stock' => 100,
            'is_active' => true,
        ]);

        $homeSnapshot = $primaryAddress->toSnapshot();

        $order1 = Order::updateOrCreate(['buyer_id' => $customer1->id, 'status' => 'pending', 'total_price' => 500.00], [
            'buyer_id' => $customer1->id,
            'shipping_address_id' => $primaryAddress->id,
            'status' => 'pending',
            'total_price' => 500.00,
            'shipping_address' => '123 Ngamwongwan Rd, Chatuchak, Bangkok',
            'shipping_address_snapshot' => $homeSnapshot,
        ]);

        OrderItem::updateOrCreate(['order_id' => $order1->id, 'item_id' => $shirt->id], [
            'quantity' => 2,
            'price_at_purchase' => 250.00,
        ]);

        $order2 = Order::updateOrCreate(['buyer_id' => $customer1->id, 'status' => 'refunding', 'total_price' => 600.00], [
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
        ]);
    }
}
