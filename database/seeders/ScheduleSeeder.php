<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * ============================================================================
 * SCHEDULE SEEDER
 * ============================================================================
 * Generates 50 lesson schedules with:
 * - Proper FK relationships (enrollment, student, instructor)
 * - Realistic time slots (10 AM - 7 PM)
 * - Room assignments
 * - Various statuses (scheduled, completed, cancelled)
 * - Re-runnable without errors
 * ============================================================================
 */
class ScheduleSeeder extends Seeder
{
    public function run(): void
    {
        // Fetch required foreign keys
        $enrollments = DB::table('enrollment')
            ->select('enrollment_id', 'student_id', 'instructor_id')
            ->get();

        if ($enrollments->isEmpty()) {
            $this->command->error('No enrollments found. Run EnrollmentSeeder first.');
            return;
        }

        // Get or create rooms
        $rooms = DB::table('room')->pluck('room_number')->toArray();
        if (empty($rooms)) {
            // Create default rooms if none exist
            for ($i = 1; $i <= 5; $i++) {
                DB::table('room')->insert([
                    'room_number' => 'R' . str_pad($i, 2, '0', STR_PAD_LEFT),
                    'room_name' => 'Practice Room ' . $i,
                    'capacity' => rand(2, 6),
                    'hourly_rate' => rand(200, 500),
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            $rooms = DB::table('room')->pluck('room_number')->toArray();
        }

        // Number of schedules to create
        $count = 50;

        $this->command->info("📅 Seeding {$count} lesson schedules...");

        // Time slots (studio hours: 10 AM - 7 PM)
        $timeSlots = [
            ['10:00', '11:00'],
            ['11:00', '12:00'],
            ['13:00', '14:00'],
            ['14:00', '15:00'],
            ['15:00', '16:00'],
            ['16:00', '17:00'],
            ['17:00', '18:00'],
            ['18:00', '19:00'],
        ];

        // Statuses with weights
        $statuses = [
            'scheduled' => 40,  // 40%
            'completed' => 45,  // 45%
            'cancelled' => 10,  // 10%
            'no_show' => 5,     // 5%
        ];

        for ($i = 0; $i < $count; $i++) {
            $enrollment = $enrollments->random();
            $room = $rooms[array_rand($rooms)];
            $timeSlot = $timeSlots[array_rand($timeSlots)];

            // Random date (past 30 days to future 30 days)
            $scheduleDate = Carbon::now()
                ->addDays(rand(-30, 30))
                ->toDateString();

            // Determine status based on date
            $isPast = Carbon::parse($scheduleDate)->isPast();
            if ($isPast) {
                $status = $this->weightedRandom([
                    'completed' => 70,
                    'cancelled' => 20,
                    'no_show' => 10,
                ]);
            } else {
                $status = 'scheduled';
            }

            DB::table('schedule')->insert([
                'enrollment_id' => $enrollment->enrollment_id,
                'student_id' => $enrollment->student_id,
                'instructor_id' => $enrollment->instructor_id,
                'room_number' => $room,
                'schedule_date' => $scheduleDate,
                'start_time' => $timeSlot[0],
                'end_time' => $timeSlot[1],
                'duration_minutes' => 60,
                'status' => $status,
                'lesson_topic' => $this->randomTopic(),
                'lesson_content' => $this->randomContent(),
                'notes' => rand(1, 10) > 7 ? 'Student requested extra practice time' : null,
                'cancellation_reason' => $status === 'cancelled' ? 'Student unavailable' : null,
                'cancelled_at' => $status === 'cancelled' ? now() : null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Progress indicator
            if (($i + 1) % 10 === 0) {
                $this->command->info("✓ Created " . ($i + 1) . " schedules...");
            }
        }

        $this->command->info("Successfully seeded {$count} schedules!");
    }

    /**
     * Get random lesson topic
     */
    private function randomTopic(): string
    {
        $topics = [
            'Scales and Arpeggios',
            'Chord Progressions',
            'Rhythm and Timing',
            'Song Performance Practice',
            'Music Theory Fundamentals',
            'Sight Reading',
            'Improvisation Techniques',
            'Fingerstyle Techniques',
            'Vocal Warm-ups and Breathing',
            'Classical Pieces',
        ];

        return $topics[array_rand($topics)];
    }

    /**
     * Get random lesson content
     */
    private function randomContent(): ?string
    {
        $contents = [
            'Focused on major and minor scales in all keys',
            'Practiced chord transitions and progressions',
            'Worked on timing with metronome exercises',
            'Prepared for upcoming recital performance',
            'Introduced basic music notation and key signatures',
            null, // Some lessons don't have detailed content
        ];

        return $contents[array_rand($contents)];
    }

    /**
     * Weighted random selection
     */
    private function weightedRandom(array $weights): string
    {
        $total = array_sum($weights);
        $rand = rand(1, $total);
        $cumulative = 0;

        foreach ($weights as $key => $weight) {
            $cumulative += $weight;
            if ($rand <= $cumulative) {
                return $key;
            }
        }

        return array_key_first($weights);
    }
}