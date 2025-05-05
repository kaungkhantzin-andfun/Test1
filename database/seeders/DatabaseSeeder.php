<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CategoriesTableSeeder::class,
        ]);

        User::factory()->create([
            'name' => 'Test User',
            'phone' => '1234567890',
            'password' => Hash::make('password'),
        ]);
    }
}
