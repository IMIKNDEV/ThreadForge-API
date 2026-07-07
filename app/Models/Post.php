<?php

namespace App\Models;

use App\Enums\PostStatusEnum;
use App\Models\RawContent;
use Database\Factories\PostFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read int $id
 * @property-read int $raw_content_id
 * @property-read string $hook
 * @property-read array $body_points
 * @property-read int|null $technical_readability_score
 * @property-read array|null $suggested_hashtags
 * @property-read string|null $tone_compliance_justification
 * @property-read array|null $payload_brut
 * @property-read \App\Enums\PostStatusEnum $statut_publication
 * @property-read \Carbon\CarbonInterface $created_at
 * @property-read \Carbon\CarbonInterface $updated_at
 */
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

    /** @return BelongsTo<RawContent, $this> */
    public function rawContent(): BelongsTo
    {
        return $this->belongsTo(RawContent::class);
    }
}
