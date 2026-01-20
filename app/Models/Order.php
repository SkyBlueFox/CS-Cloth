<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'buyer_id', 'status', 'total_price', 'shipping_address',
        'admin_shipped_id', 'admin_refunded_id',
        'shipped_at', 'cancelled_at', 'refund_requested_at', 'refunded_at',
    ];

    protected $casts = [
        'shipped_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'refund_requested_at' => 'datetime',
        'refunded_at' => 'datetime',
    ];

    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
}
