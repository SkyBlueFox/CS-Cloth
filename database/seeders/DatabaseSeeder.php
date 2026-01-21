<?php

namespace Database\Seeders;

use App\Models\Item;
use App\Models\Order;
use App\Models\Question;
use App\Models\Report;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Users and capture them in variables
        $superAdmin = User::create([
            'name' => 'Tan SuperAdmin',
            'email' => 'tan@cloth.com',
            'password' => Hash::make('asd123'),
            'role' => User::ROLE_SUPERADMIN,
        ]);

        $admin1 = User::create([
            'name' => 'Admin 1',
            'email' => 'admin@cloth.com',
            'password' => Hash::make('asd123'),
            'role' => User::ROLE_ADMIN,
        ]);

        // You can keep creating others if needed, but we only really need these for the logic below
        $customer1 = User::create([
            'name' => 'Test Customer 1',
            'email' => 'user@cloth.com',
            'password' => Hash::make('asd123'),
            'role' => User::ROLE_USER,
        ]);

        // 2. Create Items (Using the real Admin ID)
        $item1 = Item::create([
            'name' => 'Item 1',
            'created_by_id' => $admin1->id, // Uses real ID (likely 2)
            'description' => 'Item 1 description',
            'price' => 100,
            'stock' => 100,
        ]);

        // 3. Create Questions (Using real Customer & Admin IDs)
        $question1 = Question::create([
            'item_id' => $item1->id,
            'asker_id' => $customer1->id,      // Uses real ID (likely 3 or 4)
            'asker_name' => $customer1->name,
            'admin_id' => $admin1->id,         // The admin who answered
            'admin_name' => $admin1->name,
            'question_text' => 'Is this cotton?',
            'answer_text' => 'idk',
        ]);

        $question2 = Question::create([
            'item_id' => $item1->id,
            'asker_id' => $customer1->id,      // Uses real ID (likely 3 or 4)
            'asker_name' => $customer1->name,
            'admin_id' => $admin1->id,         // The admin who answered
            'admin_name' => $admin1->name,
            'question_text' => 'Is this big?',
            'answer_text' => "yes like yo mama",
        ]);

        // 4. Create Reports
        Report::create([
            'reporter_id' => $customer1->id,
            'reporter_name' => $customer1->name,

            'admin_id' => $admin1->id,
            'admin_name' => $admin1->name,

            'question_id' => $question1->id,
            'question_text_snapshot' => $question1->question_text,
            'answer_text_snapshot' => $question1->answer_text,

            'reason' => "Doesn't answer the question",
            'status' => Report::STATUS_PENDING,
        ]);

        Report::create([
            'reporter_id' => $customer1->id,
            'reporter_name' => $customer1->name,

            'admin_id' => $admin1->id,
            'admin_name' => $admin1->name,

            'question_id' => $question2->id,
            'question_text_snapshot' => $question2->question_text,
            'answer_text_snapshot' => $question2->answer_text,

            'reason' => "Rude answer",
            'status' => Report::STATUS_PENDING,
        ]);

        $shirt = Item::create([
            'name' => 'KU CS T-Shirt',
            'created_by_id' => $admin1->id,
            'description' => 'Standard issue CS shirt. 100% Cotton.',
            'price' => 250,
            'stock' => 50,
            'is_active' => true,
        ]);

        $hoodie = Item::create([
            'name' => 'Tech Faculty Hoodie',
            'created_by_id' => $admin1->id,
            'description' => 'Warm polyester hoodie for the cold labs.',
            'price' => 600,
            'stock' => 20,
            'is_active' => true,
        ]);

        $sticker = Item::create([
            'name' => 'Laptop Sticker Pack',
            'created_by_id' => $admin1->id,
            'description' => 'Vinyl stickers for your ThinkPad.',
            'price' => 50,
            'stock' => 100,
            'is_active' => true,
        ]);

        $order1 = Order::create([
            'buyer_id' => $customer1->id, // Assuming $customer1 variable exists
            'status' => 'pending',
            'total_price' => 500, // 2 shirts
            'shipping_address' => '123 Ngamwongwan Rd, Chatuchak, Bangkok',
        ]);

        \App\Models\OrderItem::create([
            'order_id' => $order1->id,
            'item_id' => $shirt->id,
            'quantity' => 2,
            'price_at_purchase' => 250,
        ]);

        $order2 = Order::create([
            'buyer_id' => $customer1->id,
            'status' => 'refunding', // The user requested a refund
            'total_price' => 600, // 1 hoodie
            'shipping_address' => 'Kasetsart University, Lab 404',
            'refund_requested_at' => now(),
        ]);

        \App\Models\OrderItem::create([
            'order_id' => $order2->id,
            'item_id' => $hoodie->id,
            'quantity' => 1,
            'price_at_purchase' => 600,
        ]);
    }
}
