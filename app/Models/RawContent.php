<?php

namespace App\Models;

use App\Models\Blueprint;
use App\Models\Post;
use App\Models\User;
use Database\Factories\RawContentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property-read int $id
 * @property-read int $user_id
 * @property-read int $blueprint_id
 * @property-read string $body
 * @property-read string $status
 * @property-read \Carbon\CarbonInterface $created_at
 * @property-read \Carbon\CarbonInterface $updated_at
 */
class RawContent extends Model
{
    /** @use HasFactory<RawContentFactory> */
    use HasFactory;

    protected $fillable = [
        'body',
        'status',
        'blueprint_id',
        'user_id',
    ];

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<Blueprint, $this> */
    public function blueprint(): BelongsTo
    {
        return $this->belongsTo(Blueprint::class);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }
}
