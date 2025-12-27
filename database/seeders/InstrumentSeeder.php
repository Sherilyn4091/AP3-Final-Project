<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InstrumentSeeder extends Seeder
{
    public function run(): void
    {
        $instruments = [
            ['instrument_name' => 'Guitar', 'category' => 'String', 'is_active' => true],
            ['instrument_name' => 'Piano', 'category' => 'Keyboard', 'is_active' => true],
            ['instrument_name' => 'Drums', 'category' => 'Percussion', 'is_active' => true],
            ['instrument_name' => 'Bass Guitar', 'category' => 'String', 'is_active' => true],
            ['instrument_name' => 'Violin', 'category' => 'String', 'is_active' => true],
            ['instrument_name' => 'Keyboard', 'category' => 'Keyboard', 'is_active' => true],
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