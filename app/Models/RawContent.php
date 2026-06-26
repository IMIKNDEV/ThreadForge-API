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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function blueprint(): BelongsTo
    {
        return $this->belongsTo(Blueprint::class);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }
}
