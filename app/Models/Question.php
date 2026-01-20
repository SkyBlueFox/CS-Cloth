<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    protected $fillable = [
        'question_id',
        'item_id',
        'asker_id', 'asker_name',
        'admin_id', 'admin_name',
        'question_text',
        'answer_text',
        'score_cached',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_id');
    }
    public function asker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'asker_id');
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class, 'question_id');
    }

}
