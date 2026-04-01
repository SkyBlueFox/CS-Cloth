<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Item extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'name',
        'created_by_id',
        'description',
        'price',
        'stock',
        'image_path',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'stock' => 'integer',
        'price' => 'decimal:2',
    ];

    protected $appends = ['image_url'];
    public function getImageUrlAttribute()
    {
        if (!$this->image_path) {
            return null;
        }

        // This forces the URL to use the APP_URL from .env
        // rather than the internal Docker hostname
        return asset('storage/' . $this->image_path);
    }

    public function questions()
    {
        return $this->hasMany(\App\Models\Question::class, 'item_id');
    }
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}
