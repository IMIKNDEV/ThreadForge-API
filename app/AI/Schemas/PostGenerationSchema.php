<?php

namespace App\AI\Schemas;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;

class PostGenerationSchema
{
    public static function definition(JsonSchema $schema): array
    {
        return [
            'hook' => $schema->string()
                ->required()
                ->max(280)
                ->description('The main hook/title of the post'),
            'body_points' => $schema->array()
                ->required()
                ->items($schema->string())
                ->description('Key points of the post body'),
            'technical_readability_score' => $schema->integer()
                ->required()
                ->min(0)
                ->max(100)
                ->description('Technical readability score from 0 to 100'),
            'suggested_hashtags' => $schema->array()
                ->required()
                ->items($schema->string())
                ->description('Suggested hashtags for the post'),
            'tone_compliance_justification' => $schema->string()
                ->required()
                ->description('Justification of how the post complies with the specified tone'),
        ];
    }
}
