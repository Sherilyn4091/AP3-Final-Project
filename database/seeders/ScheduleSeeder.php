<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * ============================================================================
 * SCHEDULE SEEDER
 * ============================================================================
 * - Generates dates that EXACTLY match student's preferred_lesson_days
 * - Uses EXACT times from student's preferred_lesson_time
 * - Works with database trigger validation
 * ============================================================================
 */
class ScheduleSeeder extends Seeder
{
    public function run(): void
    {
        // Fetch enrollments with student preferences
        $enrollments = DB::table('enrollment')
            ->join('student', 'enrollment.student_id', '=', 'student.student_id')
            ->select(
                'enrollment.enrollment_id',
                'enrollment.student_id',
                'enrollment.instructor_id',
                'student.preferred_lesson_days',
                'student.preferred_lesson_time'
            )
            ->get();

        if ($enrollments->isEmpty()) {
            $this->command->error('❌ No enrollments found. Run EnrollmentSeeder first.');
            return;
        }

        // Get or create rooms
        $rooms = DB::table('room')->pluck('room_number')->toArray();
        if (empty($rooms)) {
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

        $count = 50;
        $this->command->info("📅 Seeding {$count} schedules with STRICT preference enforcement...");

        // Day mapping
        $dayMap = [
            'Monday' => Carbon::MONDAY,
            'Tuesday' => Carbon::TUESDAY,
            'Wednesday' => Carbon::WEDNESDAY,
            'Thursday' => Carbon::THURSDAY,
            'Friday' => Carbon::FRIDAY,
            'Saturday' => Carbon::SATURDAY,
            'Sunday' => Carbon::SUNDAY,
        ];

        $created = 0;
        $attempts = 0;
        $maxAttempts = $count * 3; // Allow 3x attempts to handle conflicts

        while ($created < $count && $attempts < $maxAttempts) {
            $attempts++;
            
            $enrollment = $enrollments->random();
            $room = $rooms[array_rand($rooms)];

            // Parse preferred days
            $preferredDaysRaw = $enrollment->preferred_lesson_days 
                ? array_map('trim', explode(',', $enrollment->preferred_lesson_days))
                : ['Monday', 'Wednesday', 'Friday'];

            // Get random preferred day
            $randomDay = $preferredDaysRaw[array_rand($preferredDaysRaw)];
            $carbonDay = $dayMap[$randomDay] ?? Carbon::MONDAY;

            // Generate EXACT date matching preferred day (mix of past/future for progress tracking)
            $weeksAhead = rand(-4, 8); // ← CHANGED: -4 to +8 weeks (mix past/future)
            $baseDate = Carbon::now()->next($carbonDay)->addWeeks($weeksAhead);
            $scheduleDate = $baseDate->toDateString();

            // Parse EXACT preferred time
            $timeSlot = $this->parsePreferredTime($enrollment->preferred_lesson_time);

            // Determine status
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

            // Check for conflicts before inserting
            $conflict = DB::table('schedule')
                ->where('room_number', $room)
                ->where('schedule_date', $scheduleDate)
                ->where('start_time', $timeSlot['start'])
                ->whereNotIn('status', ['cancelled', 'no_class', 'rescheduled'])
                ->exists();

            if ($conflict) {
                continue; // Skip this iteration, try again
            }

            try {
                DB::table('schedule')->insert([
                    'enrollment_id' => $enrollment->enrollment_id,
                    'student_id' => $enrollment->student_id,
                    'instructor_id' => $enrollment->instructor_id,
                    'room_number' => $room,
                    'schedule_date' => $scheduleDate,
                    'start_time' => $timeSlot['start'],
                    'end_time' => $timeSlot['end'],
                    'duration_minutes' => $timeSlot['duration'],
                    'status' => $status,
                    'lesson_topic' => $this->randomTopic(),
                    'lesson_content' => $this->randomContent(),
                    'notes' => rand(1, 10) > 7 ? 'Student requested extra practice time' : null,
                    'cancellation_reason' => $status === 'cancelled' ? 'Student unavailable' : null,
                    'cancelled_at' => $status === 'cancelled' ? now() : null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $created++;

                if ($created % 10 === 0) {
                    $this->command->info("✓ Created {$created} schedules...");
                }
            } catch (\Exception $e) {
                // Trigger validation failed or other error - skip
                $this->command->warn("⚠ Skipped schedule due to: " . $e->getMessage());
                continue;
            }
        }

        if ($created < $count) {
            $this->command->warn("⚠ Created only {$created}/{$count} schedules due to preference constraints");
        } else {
            $this->command->info("✅ Successfully seeded {$created} schedules with strict preferences!");
        }
    }

    /**
     * Parse student's preferred_lesson_time (e.g., "2:00 PM - 3:30 PM")
     */
    private function parsePreferredTime(?string $preferredTime): array
    {
        if (!$preferredTime || !str_contains($preferredTime, '-')) {
            return [
                'start' => '14:00:00',
                'end' => '15:00:00',
                'duration' => 60,
            ];
        }

        $parts = explode('-', $preferredTime);
        $startStr = trim($parts[0]);
        $endStr = trim($parts[1]);

        try {
            $start = Carbon::createFromFormat('g:i A', $startStr);
            $end = Carbon::createFromFormat('g:i A', $endStr);

            return [
                'start' => $start->format('H:i:s'),
                'end' => $end->format('H:i:s'),
                'duration' => $start->diffInMinutes($end),
            ];
        } catch (\Exception $e) {
            return [
                'start' => '14:00:00',
                'end' => '15:00:00',
                'duration' => 60,
            ];
        }
    }

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

    private function randomContent(): ?string
    {
        $contents = [
            'Focused on major and minor scales in all keys',
            'Practiced chord transitions and progressions',
            'Worked on timing with metronome exercises',
            'Prepared for upcoming recital performance',
            'Introduced basic music notation and key signatures',
            null,
        ];
        return $contents[array_rand($contents)];
    }

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