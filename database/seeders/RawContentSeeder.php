<?php

namespace Database\Seeders;

use App\Models\Blueprint;
use App\Models\RawContent;
use App\Models\User;
use Illuminate\Database\Seeder;

class RawContentSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first();
        $blueprints = Blueprint::all();

        RawContent::create([
            'body' => 'Laravel Collections are one of the most underutilized features of the framework. Most developers only use collect() and map(), but there is a whole world of lazy collections, pipe() for method chaining, and higher-order messages. Once you master these, your code becomes both more readable and more performant.',
            'status' => 'en_attente',
            'blueprint_id' => $blueprints[0]->id,
            'user_id' => $user->id,
        ]);

        RawContent::create([
            'body' => "I have been thinking about why junior developers struggle with service containers. It's not that DI is hard—it's that the mental model of 'binding an interface to an implementation' is abstract until you see it in action. Let me break it down with a real example: a payment gateway that switches from Stripe to Paddle without touching your controller.",
            'status' => 'en_attente',
            'blueprint_id' => $blueprints[0]->id,
            'user_id' => $user->id,
        ]);

        RawContent::create([
            'body' => 'Hot take: Most coding tutorials are too long. If I search "how to sort an array in PHP" I want the answer in 10 seconds, not a 15-minute video with an intro, sponsor, and life story. Write the shortest possible answer first, then add context. Respect your reader\'s time.',
            'status' => 'en_attente',
            'blueprint_id' => $blueprints[2]->id,
            'user_id' => $user->id,
        ]);

        RawContent::create([
            'body' => "Just spent 2 hours debugging a 'Headers already sent' error in PHP. Turns out there was a stray whitespace after the closing PHP tag in a config file. If you use Laravel, you don't need closing tags—remove them everywhere and save yourself this headache forever.",
            'status' => 'en_attente',
            'blueprint_id' => $blueprints[1]->id,
            'user_id' => $user->id,
        ]);
    }
}
