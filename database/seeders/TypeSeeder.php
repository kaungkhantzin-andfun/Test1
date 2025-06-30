<?php

namespace Database\Seeders;

use App\Models\Type;
use Illuminate\Database\Seeder;

class TypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            [
                'name' => 'KBZ Pay',
                'number' => '09785220691',
                'photo' => 'https://mp.kbzpay.com:21006/myanmar-bank-h5/assets/logo.2e441aa2.png',
                'status' => 'active'
            ],
            [
                'name' => 'AYA Pay',
                'number' => '31231231241231',
                'photo' => 'https://cdn6.aptoide.com/imgs/2/7/d/27d01734b51a0acb345a6117246c9a2e_icon.png',
                'status' => 'active'
            ],
            [
                'name' => 'CB Pay',
                'number' => '1312314235346345',
                'photo' => 'https://www.temenos.com/wp-content/uploads/2019/03/CBBank-logo.jpg',
                'status' => 'active'
            ],
            
        ];

        foreach ($types as $type) {
            Type::create($type);
        }
    }
}
