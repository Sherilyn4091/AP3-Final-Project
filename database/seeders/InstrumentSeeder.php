<?php

// database/seeders/InstrumentSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InstrumentSeeder extends Seeder
{
    public function run(): void
    {
        $instruments = [
            [
                'instrument_name' => 'Guitar',
                'category' => 'String',
                'description' => '6-string acoustic or electric guitar',
                'is_system' => true,
                'is_active' => true
            ],
            [
                'instrument_name' => 'Bass',
                'category' => 'String',
                'description' => '4-string bass guitar',
                'is_system' => true,
                'is_active' => true
            ],
            [
                'instrument_name' => 'Piano',
                'category' => 'Keyboard',
                'description' => 'Acoustic or digital piano',
                'is_system' => true,
                'is_active' => true
            ],
            [
                'instrument_name' => 'Keyboard',
                'category' => 'Keyboard',
                'description' => 'Electronic keyboard or synthesizer',
                'is_system' => true,
                'is_active' => true
            ],
            [
                'instrument_name' => 'Drums',
                'category' => 'Percussion',
                'description' => 'Drum set with cymbals',
                'is_system' => true,
                'is_active' => true
            ],
            [
                'instrument_name' => 'Ukulele',
                'category' => 'String',
                'description' => '4-string ukulele',
                'is_system' => true,
                'is_active' => true
            ],
            [
                'instrument_name' => 'Violin',
                'category' => 'String',
                'description' => 'Classical violin',
                'is_system' => true,
                'is_active' => true
            ],
            [
                'instrument_name' => 'Voice',
                'category' => 'Voice/Vocal',
                'description' => 'Vocal training and singing lessons',
                'is_system' => true,
                'is_active' => true
            ],
        ];

        foreach ($instruments as $instrument) {
            if (!DB::table('instrument')->where('instrument_name', $instrument['instrument_name'])->exists()) {
                $instrument['created_at'] = now();
                $instrument['updated_at'] = now();
                DB::table('instrument')->insert($instrument);
            }
        }
    }
}