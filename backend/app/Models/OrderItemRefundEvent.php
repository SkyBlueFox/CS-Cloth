<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItemRefundEvent extends Model
{
    protected $fillable = [
        'order_item_id',
        'event_type',
        'quantity',
        'reason_code',
        'reason_detail',
        'issue_description',
        'evidence_image_path',
        'acted_by_user_id',
        'happened_at',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'happened_at' => 'datetime',
    ];

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'acted_by_user_id')->withTrashed();
    }
}
