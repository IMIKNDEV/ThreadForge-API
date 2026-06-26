<?php

namespace App\Models;

use App\Enums\PostStatusEnum;
use App\Models\RawContent;
use Database\Factories\PostFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Post extends Model
{
    /** @use HasFactory<PostFactory> */
    use HasFactory;

    protected $fillable = [
        'raw_content_id',
        'hook',
        'body_points',
        'technical_readability_score',
        'suggested_hashtags',
        'tone_compliance_justification',
        'payload_brut',
        'statut_publication',
    ];

    protected function casts(): array
    {
        return [
            'body_points' => 'array',
            'suggested_hashtags' => 'array',
            'payload_brut' => 'array',
            'statut_publication' => PostStatusEnum::class,
        ];
    }

    public function rawContent(): BelongsTo
    {
        return $this->belongsTo(RawContent::class);
    }
}
