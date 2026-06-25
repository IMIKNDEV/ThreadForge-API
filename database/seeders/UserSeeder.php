<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Creator',
            'email' => 'creator@threadforge.com',
            'password' => bcrypt('password'),
        ]);

        User::create([
            'name' => 'Ayoub',
            'email' => 'ayoub@threadforge.com',
            'password' => bcrypt('password'),
        ]);
    }
}
