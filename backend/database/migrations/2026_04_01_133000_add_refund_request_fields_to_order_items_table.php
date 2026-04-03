<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (
            Schema::hasColumn('order_items', 'refund_requested_quantity')
            && Schema::hasColumn('order_items', 'refunded_quantity')
            && Schema::hasColumn('order_items', 'refund_reason_code')
            && Schema::hasColumn('order_items', 'refund_reason_detail')
            && Schema::hasColumn('order_items', 'refund_issue_description')
            && Schema::hasColumn('order_items', 'refund_requested_at')
            && Schema::hasColumn('order_items', 'refund_approved_at')
        ) {
            return;
        }

        Schema::table('order_items', function (Blueprint $table) {
            if (!Schema::hasColumn('order_items', 'refund_requested_quantity')) {
                $table->unsignedInteger('refund_requested_quantity')->default(0)->after('quantity');
            }
            if (!Schema::hasColumn('order_items', 'refunded_quantity')) {
                $table->unsignedInteger('refunded_quantity')->default(0)->after('refund_requested_quantity');
            }
            if (!Schema::hasColumn('order_items', 'refund_reason_code')) {
                $table->string('refund_reason_code', 50)->nullable()->after('refunded_quantity');
            }
            if (!Schema::hasColumn('order_items', 'refund_reason_detail')) {
                $table->text('refund_reason_detail')->nullable()->after('refund_reason_code');
            }
            if (!Schema::hasColumn('order_items', 'refund_issue_description')) {
                $table->text('refund_issue_description')->nullable()->after('refund_reason_detail');
            }
            if (!Schema::hasColumn('order_items', 'refund_requested_at')) {
                $table->timestamp('refund_requested_at')->nullable()->after('refund_issue_description');
            }
            if (!Schema::hasColumn('order_items', 'refund_approved_at')) {
                $table->timestamp('refund_approved_at')->nullable()->after('refund_requested_at');
            }
        });
    }

    public function down(): void
    {
        // These columns may belong to the base order_items migration, so don't drop them here.
    }
};
