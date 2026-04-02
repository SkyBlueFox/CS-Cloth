<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('item_id')->constrained()->cascadeOnDelete();

            $table->unsignedInteger('quantity');
            $table->decimal('price_at_purchase', 10, 2);

            // --- ส่วนของระบบ Refund ที่ต้องเพิ่มให้ตรงกับ Model ---
            $table->unsignedInteger('refund_requested_quantity')->default(0);
            $table->unsignedInteger('refunded_quantity')->default(0);
            $table->string('refund_reason_code')->nullable();
            $table->string('refund_reason_detail')->nullable();
            $table->text('refund_issue_description')->nullable();
            $table->string('refund_evidence_image_path')->nullable();
            $table->timestamp('refund_requested_at')->nullable();
            $table->timestamp('refund_approved_at')->nullable();
            // ---------------------------------------------

            $table->timestamps();

            $table->index(['order_id']);
            $table->index(['item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};