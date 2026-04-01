<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class Order extends Model
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'order_number',
        'buyer_id',
        'shipping_address_id',
        'status',
        'total_price',
        'shipping_address',
        'shipping_address_snapshot',
        'delivery_method',
        'delivery_method_label',
        'admin_shipped_id',
        'admin_refunded_id',
        'shipped_at',
        'cancelled_at',
        'refund_requested_at',
        'refunded_at',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'total_price' => 'decimal:2',
        'shipping_address_snapshot' => 'array',
        'shipped_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'refund_requested_at' => 'datetime',
        'refunded_at' => 'datetime',
    ];

    /**
     * Relationship: The user who placed the order.
     */
    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id')->withTrashed();
    }

    /**
     * Relationship: The items within this order.
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Alias for items relationship.
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Relationship: The fixed shipping address linked to the order.
     */
    public function shippingAddress(): BelongsTo
    {
        return $this->belongsTo(UserAddress::class, 'shipping_address_id');
    }

    /**
     * Generate a unique order number.
     * Format: YYMMDD + 2 chars + 2 digits + 4 chars (e.g., 260402AB99XYZW)
     */
    public static function generateOrderNumber(?Carbon $date = null): string
    {
        $date ??= now();

        do {
            $value = $date->format('ymd')
                . Str::upper(Str::random(2))
                . str_pad((string) random_int(0, 99), 2, '0', STR_PAD_LEFT)
                . Str::upper(Str::random(4));
        } while (self::query()->where('order_number', $value)->exists());

        return $value;
    }
}