<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Model
{
    use SoftDeletes;

    const ROLE_SUPERADMIN = 'superadmin';
    const ROLE_ADMIN = 'admin';
    const ROLE_USER = 'user';

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'wallet_balance',
        'role',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'wallet_balance' => 'decimal:2',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class, 'buyer_id');
    }

    public function questions()
    {
        return $this->hasMany(Question::class, 'asker_id');
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(UserAddress::class, 'user_id');
    }
}
