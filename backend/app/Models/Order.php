<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
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
}
