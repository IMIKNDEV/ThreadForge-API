<?php

namespace Database\Factories;

use App\Models\Blueprint;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BlueprintFactory extends Factory
{
    protected $model = Blueprint::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->word(),
            'tone' => fake()->randomElement(['Professional', 'Casual', 'Bold', 'Educational']),
            'max_hashtag' => fake()->numberBetween(1, 10),
            'max_characters' => fake()->numberBetween(100, 280),
            'banned_word' => fake()->boolean() ? fake()->word() : null,
            'extra_rules' => fake()->boolean() ? fake()->sentence() : null,
        ];
    }
}
