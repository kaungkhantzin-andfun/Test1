<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoriesTableSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('categories')->insert([
            ['name' => 'အချစ်ရေး', 'image' => 'https://images.unsplash.com/photo-1518199266791-5375a83190b7?w=500', 'status' => true],
            ['name' => 'လက္ခဏာ', 'image' => 'https://images.unsplash.com/photo-1606766125245-6fdb97148c60?w=500', 'status' => true],
            ['name' => 'ကလေးအမည်ပေး', 'image' => 'https://images.unsplash.com/photo-1492725764893-90b379c2b6e7?w=500', 'status' => true],
            ['name' => 'အကြားအမြင်', 'image' => 'https://images.unsplash.com/photo-1518133683791-0b9de5a055f0?w=500', 'status' => true],
            ['name' => 'ဖူးစာဖက်', 'image' => 'https://images.unsplash.com/photo-1543002588-bfa74002ed7e?w=500', 'status' => true],
            ['name' => 'တစ်နှစ်စာဟောစာတမ်း', 'image' => 'https://images.unsplash.com/photo-1507925921958-8a62f3d1a50d?w=500', 'status' => true],
            ['name' => 'ဗေဒင်ရေးရာ', 'image' => 'https://images.unsplash.com/photo-1619412411985-fd777c846239?w=500', 'status' => true],
            ['name' => 'နက္ခတ်ရေးရာ', 'image' => 'https://images.unsplash.com/photo-1446776811953-b23d57bd21aa?w=500', 'status' => true],
            ['name' => 'ရက်ရာဇာ', 'image' => 'https://images.unsplash.com/photo-1528155124528-06c125d81e89?w=500', 'status' => true]
        ]);
    }
}
