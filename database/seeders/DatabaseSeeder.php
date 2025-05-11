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
            AstrologersTableSeeder::class,
        ]);

        User::factory()->create([
            'name' => 'Test User',
            'phone' => '09785220691',
            'password' => Hash::make('password'),
        ]);
    }
}
