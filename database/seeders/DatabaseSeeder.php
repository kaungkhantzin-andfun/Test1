<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\FreeBaydin;
use App\Models\Astrologer;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CategoriesTableSeeder::class,
            AstrologersTableSeeder::class,
            UsersTableSeeder::class,
            TypeSeeder::class,
        ]);

        User::factory()->create([
            'name' => 'Test User',
            'phone' => '09785220691',
            'password' => Hash::make('password'),
        ]);

        // Create a test user if not exists
        $user = User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => bcrypt('password'),
                'role' => 'astrologer'
            ]
        );

        // Create a test astrologer
        Astrologer::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'user_id' => $user->id,
                'name' => 'Test Astrologer',
                'phone' => '1234567890',
                'bio' => 'Test astrologer bio',
                'specialization' => 'General',
                'experience' => '5 years',
                'status' => 'active'
            ]
        );
    }
}
