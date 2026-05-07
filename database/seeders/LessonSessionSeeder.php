<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LessonSessionSeeder extends Seeder
{
    /**
     * Seed official Music Lab lesson packages.
     *
     * Official rates:
     * - 5 sessions  = PHP 3,500
     * - 10 sessions = PHP 6,500
     * - 20 sessions = PHP 10,500
     *
     * Each lesson session is 1 hour.
     */
    public function run(): void
    {
        $packages = [
            [
                'session_count' => 5,
                'duration_minutes' => 60,
                'price' => 3500.00,
                'session_name' => '5 Sessions Package',
                'description' => 'Includes 5 one-on-one lesson sessions. Each session is 1 hour.',
                'is_active' => true,
            ],
            [
                'session_count' => 10,
                'duration_minutes' => 60,
                'price' => 6500.00,
                'session_name' => '10 Sessions Package',
                'description' => 'Includes 10 one-on-one lesson sessions. Each session is 1 hour.',
                'is_active' => true,
            ],
            [
                'session_count' => 20,
                'duration_minutes' => 60,
                'price' => 10500.00,
                'session_name' => '20 Sessions Package',
                'description' => 'Includes 20 one-on-one lesson sessions. Each session is 1 hour.',
                'is_active' => true,
            ],
        ];

        foreach ($packages as $package) {
            DB::table('lesson_session')->updateOrInsert(
                ['session_count' => $package['session_count']],
                array_merge($package, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }

        $this->command->info('Successfully seeded official Music Lab lesson packages.');
    }
}