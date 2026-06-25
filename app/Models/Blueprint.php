<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Blueprint extends Model
{
    protected $fillable = [
        'name',
        'tone',
        'max_hashtag',
        'max_characters',
        'banned_word',
        'extra_rules',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'banned_word' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function rawContents(): HasMany
    {
        return $this->hasMany(RawContent::class);
    }
}
