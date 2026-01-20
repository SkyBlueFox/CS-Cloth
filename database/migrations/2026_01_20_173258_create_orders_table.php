<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            $table->foreignId('buyer_id')->constrained('users')->cascadeOnDelete();

            // pending | shipped | refunding | refunded | cancelled
            $table->string('status', 20)->default('pending');

            // เก็บเป็นจำนวนเต็ม (บาท)
            $table->integer('total_price');

            $table->text('shipping_address')->nullable();

            // ฝั่ง Tan ใช้ approve ship/refund
            $table->foreignId('admin_shipped_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('admin_refunded_id')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('refund_requested_at')->nullable();
            $table->timestamp('refunded_at')->nullable();

            $table->timestamps();

            $table->index(['buyer_id', 'created_at']);
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
