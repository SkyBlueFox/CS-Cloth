<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('orders', 'shipping_address_id')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->foreignId('shipping_address_id')
                    ->nullable()
                    ->after('buyer_id')
                    ->constrained('user_addresses')
                    ->nullOnDelete();
            });
        }

        if (!Schema::hasColumn('orders', 'shipping_address_snapshot')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->json('shipping_address_snapshot')->nullable()->after('shipping_address');
            });
        }
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'shipping_address_id')) {
                $table->dropConstrainedForeignId('shipping_address_id');
            }

            if (Schema::hasColumn('orders', 'shipping_address_snapshot')) {
                $table->dropColumn('shipping_address_snapshot');
            }
        });
    }
};
