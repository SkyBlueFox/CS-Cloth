<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'item_id',
        'quantity',
        'price_at_purchase',
        'refund_requested_quantity',
        'refunded_quantity',
        'refund_dismissed_quantity',
        'refund_reason_code',
        'refund_reason_detail',
        'refund_issue_description',
        'refund_evidence_image_path',
        'refund_requested_at',
        'refund_approved_at',
        'refund_dismissed_at',
    ];

    protected $casts = [
        'price_at_purchase' => 'decimal:2',
        'quantity' => 'integer',
        'refund_requested_quantity' => 'integer',
        'refunded_quantity' => 'integer',
        'refund_dismissed_quantity' => 'integer',
        'refund_requested_at' => 'datetime',
        'refund_approved_at' => 'datetime',
        'refund_dismissed_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function refundEvents()
    {
        return $this->hasMany(OrderItemRefundEvent::class)->orderBy('happened_at')->orderBy('id');
    }

    public function getRefundableQuantityAttribute(): int
    {
        return max(0, $this->quantity - $this->refunded_quantity - $this->refund_requested_quantity);
    }
}
