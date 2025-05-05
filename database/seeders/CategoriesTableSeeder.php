<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class CategoriesTableSeeder extends Seeder
{
    public function run(): void
    {
        \DB::table('categories')->insert([
            ['name' => 'Electronics', 'image' => 'electronics.jpg', 'status' => true],
            ['name' => 'Fashion', 'image' => 'fashion.jpg', 'status' => true],
            ['name' => 'Home & Living', 'image' => 'home.jpg', 'status' => true],
            ['name' => 'Sports', 'image' => 'sports.jpg', 'status' => true],
            ['name' => 'Books', 'image' => 'books.jpg', 'status' => true]
        ]);
    }
}
