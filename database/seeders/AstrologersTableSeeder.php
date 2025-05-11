<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AstrologersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $astrologers = [
            [
                'name' => 'John Smith',
                'dob' => '1985-05-15',
                'description' => 'Experienced astrologer with 20 years of experience. Specializes in Vedic astrology.',
                'type' => 'vipp',
                'status' => 'active',
                'profile_image' => 'images/astrologers/john-smith.jpg',
            ],
            [
                'name' => 'Sarah Johnson',
                'dob' => '1978-03-22',
                'description' => 'Renowned astrologer and author of several astrology books.',
                'type' => 'vipp',
                'status' => 'active',
                'profile_image' => 'images/astrologers/sarah-johnson.jpg',
            ],
            [
                'name' => 'Michael Brown',
                'dob' => '1990-11-10',
                'description' => 'Young and talented astrologer with modern approach to astrology.',
                'type' => 'normal',
                'status' => 'active',
                'profile_image' => 'images/astrologers/michael-brown.jpg',
            ],
            [
                'name' => 'Emily Davis',
                'dob' => '1982-08-28',
                'description' => 'Expert in Western astrology and relationship compatibility.',
                'type' => 'normal',
                'status' => 'active',
                'profile_image' => 'images/astrologers/emily-davis.jpg',
            ],
        ];

        foreach ($astrologers as $astrologer) {
            DB::table('astrologers')->insert([
                'name' => $astrologer['name'],
                'dob' => $astrologer['dob'],
                'description' => $astrologer['description'],
                'type' => $astrologer['type'],
                'status' => $astrologer['status'],
                'profile_image' => $astrologer['profile_image'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
