<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    protected $fillable = [
        'name',
        'created_by_id',
        'created_by_name',
        'description',
        'price',
        'stock',
        'image_path',
    ];

    public function questions()
    {
        return $this->hasMany(Question::class);
    }
}
