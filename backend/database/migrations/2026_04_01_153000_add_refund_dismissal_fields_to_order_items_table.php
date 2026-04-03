<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->unsignedInteger('refund_dismissed_quantity')->default(0)->after('refunded_quantity');
            $table->timestamp('refund_dismissed_at')->nullable()->after('refund_approved_at');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn([
                'refund_dismissed_quantity',
                'refund_dismissed_at',
            ]);
        });
    }
};
