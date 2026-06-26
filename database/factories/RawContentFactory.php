<?php

namespace Database\Factories;

use App\Models\Blueprint;
use App\Models\RawContent;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class RawContentFactory extends Factory
{
    protected $model = RawContent::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'blueprint_id' => Blueprint::factory(),
            'body' => fake()->paragraphs(3, true),
            'status' => 'en_attente',
        ];
    }
}
