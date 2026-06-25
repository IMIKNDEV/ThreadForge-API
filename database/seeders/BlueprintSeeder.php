<?php

namespace Database\Seeders;

use App\Models\Blueprint;
use App\Models\User;
use Illuminate\Database\Seeder;

class BlueprintSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first();

        Blueprint::create([
            'name' => 'Tech LinkedIn Posts',
            'tone' => 'Professional & Educational',
            'max_hashtag' => 5,
            'max_characters' => 280,
            'banned_word' => 'clickbait, scam',
            'extra_rules' => 'Focus on PHP and Laravel ecosystem. Use technical terms but explain them.',
            'user_id' => $user->id,
        ]);

        Blueprint::create([
            'name' => 'Casual Twitter Threads',
            'tone' => 'Casual & Engaging',
            'max_hashtag' => 3,
            'max_characters' => 240,
            'banned_word' => null,
            'extra_rules' => 'Use emojis sparingly. End with a question to drive engagement.',
            'user_id' => $user->id,
        ]);

        Blueprint::create([
            'name' => 'Viral Dev Tips',
            'tone' => 'Bold & Opinionated',
            'max_hashtag' => 8,
            'max_characters' => 200,
            'banned_word' => 'easy, simple',
            'extra_rules' => 'Challenge common practices. Back opinions with facts.',
            'user_id' => $user->id,
        ]);
    }
}
