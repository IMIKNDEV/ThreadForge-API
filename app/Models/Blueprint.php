<?php

namespace App\Models;

use App\Models\RawContent;
use App\Models\User;
use Database\Factories\BlueprintFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property-read int $id
 * @property-read int $user_id
 * @property-read string $name
 * @property-read string $tone
 * @property-read int $max_hashtag
 * @property-read int $max_characters
 * @property-read string|null $banned_word
 * @property-read string|null $extra_rules
 * @property-read \Carbon\CarbonInterface $created_at
 * @property-read \Carbon\CarbonInterface $updated_at
 */
class Blueprint extends Model
{
    /** @use HasFactory<BlueprintFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'tone',
        'max_hashtag',
        'max_characters',
        'banned_word',
        'extra_rules',
        'user_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function rawContents(): HasMany
    {
        return $this->hasMany(RawContent::class);
    }
}
