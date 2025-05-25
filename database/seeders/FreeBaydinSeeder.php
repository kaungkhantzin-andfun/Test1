<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\FreeBaydin;
use Carbon\Carbon;

class FreeBaydinSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'date' => Carbon::now(),
                'title' => 'First Task',
                'description' => 'This is the description for the first task',
                'status' => 'pending'
            ],
            [
                'date' => Carbon::now()->addDays(1),
                'title' => 'Second Task',
                'description' => 'This is the description for the second task',
                'status' => 'in progress'
            ],
            [
                'date' => Carbon::now()->addDays(2),
                'title' => 'Third Task',
                'description' => 'This is the description for the third task',
                'status' => 'completed'
            ]
        ];

        foreach ($data as $item) {
            FreeBaydin::create($item);
        }
    }
}
