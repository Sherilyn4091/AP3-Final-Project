<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InstrumentSeeder extends Seeder
{
    /**
     * Seed official Music Lab instruments.
     *
     * Official lessons:
     * - Guitar
     * - Bass
     * - Keyboard
     * - Drums
     * - Ukulele
     * - Violin
     * - Voice
     *
     */
    public function run(): void
    {
        $instruments = [
            [
                'instrument_name' => 'Guitar',
                'category' => 'String',
                'description' => 'Guitar lesson for acoustic or electric guitar.',
                'is_system' => true,
                'is_active' => true,
            ],
            [
                'instrument_name' => 'Bass',
                'category' => 'String',
                'description' => 'Bass guitar lesson.',
                'is_system' => true,
                'is_active' => true,
            ],
            [
                'instrument_name' => 'Keyboard',
                'category' => 'Keyboard',
                'description' => 'Keyboard lesson.',
                'is_system' => true,
                'is_active' => true,
            ],
            [
                'instrument_name' => 'Drums',
                'category' => 'Percussion',
                'description' => 'Drum lesson.',
                'is_system' => true,
                'is_active' => true,
            ],
            [
                'instrument_name' => 'Ukulele',
                'category' => 'String',
                'description' => 'Ukulele lesson.',
                'is_system' => true,
                'is_active' => true,
            ],
            [
                'instrument_name' => 'Violin',
                'category' => 'String',
                'description' => 'Violin lesson.',
                'is_system' => true,
                'is_active' => true,
            ],
            [
                'instrument_name' => 'Voice',
                'category' => 'Voice/Vocal',
                'description' => 'Voice and vocal training lesson.',
                'is_system' => true,
                'is_active' => true,
            ],
        ];

        foreach ($instruments as $instrument) {
            DB::table('instrument')->updateOrInsert(
                ['instrument_name' => $instrument['instrument_name']],
                array_merge($instrument, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }

        $this->command->info('Successfully seeded official Music Lab instruments.');
    }
}