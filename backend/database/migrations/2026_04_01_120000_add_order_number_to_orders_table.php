<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('orders', 'order_number')) {
            return;
        }

        Schema::table('orders', function (Blueprint $table) {
            $table->string('order_number', 14)->nullable()->after('id');
        });

        DB::table('orders')
            ->select(['id', 'created_at'])
            ->orderBy('id')
            ->chunkById(100, function ($orders) {
                foreach ($orders as $order) {
                    $createdAt = $order->created_at ? \Illuminate\Support\Carbon::parse($order->created_at) : now();
                    $orderNumber = $this->generateOrderNumber($createdAt);

                    DB::table('orders')
                        ->where('id', $order->id)
                        ->update(['order_number' => $orderNumber]);
                }
            });

        Schema::table('orders', function (Blueprint $table) {
            $table->unique('order_number');
        });
    }

    public function down(): void
    {
        // The base orders migration may already own this column, so don't drop it here.
    }

    private function generateOrderNumber(\Illuminate\Support\Carbon $date): string
    {
        do {
            $value = $date->format('ymd')
                . Str::upper(Str::random(2))
                . str_pad((string) random_int(0, 99), 2, '0', STR_PAD_LEFT)
                . Str::upper(Str::random(4));
        } while (DB::table('orders')->where('order_number', $value)->exists());

        return $value;
    }
};
