<?php

namespace Database\Factories;

use App\Enums\PostStatusEnum;
use App\Models\Post;
use App\Models\RawContent;
use Illuminate\Database\Eloquent\Factories\Factory;

class PostFactory extends Factory
{
    protected $model = Post::class;

    public function definition(): array
    {
        return [
            'raw_content_id' => RawContent::factory(),
            'hook' => fake()->sentence(8),
            'body_points' => fake()->sentences(3),
            'technical_readability_score' => fake()->numberBetween(0, 100),
            'suggested_hashtags' => fake()->words(3),
            'tone_compliance_justification' => fake()->sentence(),
            'payload_brut' => null,
            'statut_publication' => fake()->randomElement(PostStatusEnum::cases()),
        ];
    }
}
