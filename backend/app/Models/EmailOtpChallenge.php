<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailOtpChallenge extends Model
{
    public const PURPOSE_REGISTER = 'register';
    public const PURPOSE_PASSWORD_RESET = 'password_reset';
    public const PURPOSE_EMAIL_CHANGE = 'email_change';

    protected $fillable = [
        'user_id',
        'email',
        'purpose',
        'code_hash',
        'payload',
        'attempts',
        'last_sent_at',
        'expires_at',
        'used_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'last_sent_at' => 'datetime',
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
