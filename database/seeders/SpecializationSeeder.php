<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SpecializationSeeder extends Seeder
{
    public function run(): void
    {
        $specializations = [
            ['specialization_name' => 'Guitar', 'is_active' => true],
            ['specialization_name' => 'Piano', 'is_active' => true],
            ['specialization_name' => 'Drums', 'is_active' => true],
            ['specialization_name' => 'Bass Guitar', 'is_active' => true],
            ['specialization_name' => 'Violin', 'is_active' => true],
            ['specialization_name' => 'Keyboard', 'is_active' => true],
            ['specialization_name' => 'Voice/Vocals', 'is_active' => true],
        ];

        foreach ($specializations as $specialization) {
            if (!DB::table('specialization')->where('specialization_name', $specialization['specialization_name'])->exists()) {
                $specialization['created_at'] = now();
                $specialization['updated_at'] = now();
                DB::table('specialization')->insert($specialization);
            }
        }
    }
}