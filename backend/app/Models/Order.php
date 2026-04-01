<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'order_number',
        'buyer_id', 'shipping_address_id', 'status', 'total_price', 'shipping_address', 'shipping_address_snapshot',
        'admin_shipped_id', 'admin_refunded_id',
        'shipped_at', 'cancelled_at', 'refund_requested_at', 'refunded_at',
    ];

    protected $casts = [
        'total_price' => 'decimal:2',
        'shipping_address_snapshot' => 'array',
        'shipped_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'refund_requested_at' => 'datetime',
        'refunded_at' => 'datetime',
    ];

    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id')->withTrashed();
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function shippingAddress()
    {
        return $this->belongsTo(UserAddress::class, 'shipping_address_id');
    }
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public static function generateOrderNumber(?\Illuminate\Support\Carbon $date = null): string
    {
        $date ??= now();

        do {
            $value = $date->format('ymd')
                . \Illuminate\Support\Str::upper(\Illuminate\Support\Str::random(2))
                . str_pad((string) random_int(0, 99), 2, '0', STR_PAD_LEFT)
                . \Illuminate\Support\Str::upper(\Illuminate\Support\Str::random(4));
        } while (self::query()->where('order_number', $value)->exists());

        return $value;
    }
}
