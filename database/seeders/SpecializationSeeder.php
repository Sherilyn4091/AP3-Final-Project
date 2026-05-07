<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SpecializationSeeder extends Seeder
{
    /**
     * Seed instructor specializations.
     *
     * Important:
     * Specialization names must match instrument names because the enrollment
     * form filters instructors by matching:
     *
     * instrument.instrument_name = specialization.specialization_name
     */
    public function run(): void
    {
        $specializations = [
            [
                'specialization_name' => 'Guitar',
                'description' => 'Teaches guitar lessons.',
                'is_active' => true,
            ],
            [
                'specialization_name' => 'Bass',
                'description' => 'Teaches bass lessons.',
                'is_active' => true,
            ],
            [
                'specialization_name' => 'Keyboard',
                'description' => 'Teaches keyboard lessons.',
                'is_active' => true,
            ],
            [
                'specialization_name' => 'Drums',
                'description' => 'Teaches drum lessons.',
                'is_active' => true,
            ],
            [
                'specialization_name' => 'Ukulele',
                'description' => 'Teaches ukulele lessons.',
                'is_active' => true,
            ],
            [
                'specialization_name' => 'Violin',
                'description' => 'Teaches violin lessons.',
                'is_active' => true,
            ],
            [
                'specialization_name' => 'Voice',
                'description' => 'Teaches voice lessons.',
                'is_active' => true,
            ],
        ];

        foreach ($specializations as $specialization) {
            DB::table('specialization')->updateOrInsert(
                ['specialization_name' => $specialization['specialization_name']],
                array_merge($specialization, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }

        $this->command->info('Successfully seeded official Music Lab specializations.');
    }
}