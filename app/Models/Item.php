<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    protected $fillable = [
        'name',
        'created_by_id',
        'description',
        'price',
        'stock',
        'image_path',
        'is_active',
    ];

    public function questions()
    {
        return $this->hasMany(Question::class);
    }
}
