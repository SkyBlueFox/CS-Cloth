<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('orders', 'delivery_method') && Schema::hasColumn('orders', 'delivery_method_label')) {
            return;
        }

        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'delivery_method')) {
                $table->string('delivery_method', 50)->nullable()->after('shipping_address_snapshot');
            }

            if (! Schema::hasColumn('orders', 'delivery_method_label')) {
                $table->string('delivery_method_label', 100)->nullable()->after('delivery_method');
            }
        });
    }

    public function down(): void
    {
        // Intentionally left as a no-op because these columns may already exist in drifted local schemas.
    }
};
