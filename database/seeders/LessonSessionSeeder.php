<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LessonSessionSeeder extends Seeder
{
    public function run(): void
    {
        // Idempotent: delete existing to avoid duplicates during repeated seeding
        DB::table('lesson_session')->truncate();

        $rows = [
            [
                'session_count' => 5,
                'duration_minutes' => 60,
                'price' => 1500.00,
                'session_name' => '5-Session Package (1 hour each)',
                'description' => 'Intro package for beginners. Includes 5 one-hour sessions.',
                'is_active' => true,
            ],
            [
                'session_count' => 10,
                'duration_minutes' => 60,
                'price' => 2500.00,
                'session_name' => '10-Session Package (1 hour each)',
                'description' => 'Standard package. Includes 10 one-hour sessions.',
                'is_active' => true,
            ],
            [
                'session_count' => 20,
                'duration_minutes' => 60,
                'price' => 4500.00,
                'session_name' => '20-Session Package (1 hour each)',
                'description' => 'Best value package. Includes 20 one-hour sessions.',
                'is_active' => true,
            ],
        ];

        foreach ($rows as $row) {
            DB::table('lesson_session')->insert(array_merge($row, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}