<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use SoftDeletes, Notifiable;

    const ROLE_SUPERADMIN = 'superadmin';
    const ROLE_ADMIN = 'admin';
    const ROLE_USER = 'user';

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'balance',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'balance' => 'decimal:2',
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

    public function walletTransactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class, 'user_id');
    }

    public function getWalletBalanceAttribute(): ?string
    {
        return $this->getAttribute('balance');
    }

    public function setWalletBalanceAttribute($value): void
    {
        $this->attributes['balance'] = $value;
    }
}
