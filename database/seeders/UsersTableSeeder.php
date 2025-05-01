<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    public function run()
    {
        User::create([
            'name' => 'Admin User',
            'phone' => '09123456789',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        User::create([
            'name' => 'Astrology User',
            'phone' => '09234567890',
            'password' => Hash::make('password'),
            'role' => 'astrology',
        ]);

        User::create([
            'name' => 'Customer User',
            'phone' => '09345678901',
            'password' => Hash::make('password'),
            'role' => 'customer',
        ]);
    }
}