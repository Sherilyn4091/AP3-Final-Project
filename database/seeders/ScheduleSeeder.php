<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * ============================================================================
 * SCHEDULE SEEDER
 * database/seeders/ScheduleSeeder.php
 * ============================================================================
 *
 * Generates lesson schedules based on existing enrollments.
 *
 * Foreign key alignment:
 * - enrollment_id comes from enrollment.enrollment_id
 * - student_id comes from enrollment.student_id
 * - instructor_id comes from enrollment.instructor_id
 * - room_number comes from room.room_number
 *
 * Conflict alignment:
 * - Avoids duplicate room schedules:
 *   room_number + schedule_date + start_time
 * - Avoids duplicate instructor schedules:
 *   instructor_id + schedule_date + start_time
 *
 * Performance note:
 * - This version checks conflicts in memory instead of repeatedly querying
 *   Supabase inside nested loops. This makes seeding much faster on remote DB.
 *
 * Important:
 * - This seeder must run AFTER EnrollmentSeeder and RoomSeeder.
 * - This seeder must run BEFORE AttendanceSeeder and ProgressSeeder.
 * ============================================================================
 */
class ScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $enrollments = DB::table('enrollment')
            ->select(
                'enrollment_id',
                'student_id',
                'instructor_id',
                'total_sessions',
                'completed_sessions',
                'start_date'
            )
            ->whereNotNull('student_id')
            ->whereNotNull('instructor_id')
            ->orderBy('enrollment_id')
            ->get();

        $roomNumbers = DB::table('room')
            ->where('is_active', true)
            ->orderBy('room_number')
            ->pluck('room_number')
            ->toArray();

        if ($enrollments->isEmpty()) {
            $this->command->error('No enrollments found. Run EnrollmentSeeder first.');
            return;
        }

        if (empty($roomNumbers)) {
            $this->command->error('No active rooms found. Run RoomSeeder first.');
            return;
        }

        if (DB::table('schedule')->exists()) {
            $this->command->warn('Schedules already exist. ScheduleSeeder skipped to avoid duplicate lesson schedules.');
            return;
        }

        $lessonTopics = [
            'Basic Music Theory',
            'Finger Positioning and Warm-up',
            'Major and Minor Scales',
            'Chord Progressions',
            'Rhythm and Timing Practice',
            'Song Repertoire Practice',
            'Ear Training and Pitch Recognition',
            'Performance Technique',
            'Sight Reading Practice',
            'Final Performance Preparation',
        ];

        $lessonContents = [
            'Review of previous lesson, guided practice, and assigned exercises.',
            'Technique drills, rhythm exercises, and instructor feedback.',
            'Practical application using selected songs and performance activities.',
            'Focused practice on weak areas and preparation for the next lesson.',
            'Student performance check, corrections, and homework assignment.',
        ];

        /*
        |--------------------------------------------------------------------------
        | Fixed Time Slots
        |--------------------------------------------------------------------------
        |
        | Fixed slots make conflict checking predictable and avoid random duplicate
        | room/instructor schedules.
        |
        */
        $timeSlots = [
            '09:00:00',
            '10:00:00',
            '11:00:00',
            '13:00:00',
            '14:00:00',
            '15:00:00',
            '16:00:00',
            '17:00:00',
            '18:00:00',
        ];

        /*
        |--------------------------------------------------------------------------
        | In-Memory Conflict Trackers
        |--------------------------------------------------------------------------
        |
        | These arrays prevent repeated remote database checks.
        |
        | roomConflict key format:
        | room_number|schedule_date|start_time
        |
        | instructorConflict key format:
        | instructor_id|schedule_date|start_time
        |
        */
        $roomConflicts = [];
        $instructorConflicts = [];

        $rows = [];
        $created = 0;
        $skipped = 0;

        foreach ($enrollments as $enrollment) {
            $totalSessions = max(1, (int) $enrollment->total_sessions);
            $completedSessions = max(0, min((int) $enrollment->completed_sessions, $totalSessions));

            for ($sessionNumber = 1; $sessionNumber <= $totalSessions; $sessionNumber++) {
                $isCompleted = $sessionNumber <= $completedSessions;

                if ($isCompleted) {
                    $preferredDate = Carbon::today()->subDays(($completedSessions - $sessionNumber + 1) * 7);
                    $status = 'completed';
                } else {
                    $daysAhead = ($sessionNumber - $completedSessions) * 7;
                    $preferredDate = Carbon::today()->addDays($daysAhead);
                    $status = 'scheduled';
                }

                $slot = $this->findAvailableSlot(
                    $roomNumbers,
                    $timeSlots,
                    $preferredDate,
                    (int) $enrollment->instructor_id,
                    $roomConflicts,
                    $instructorConflicts
                );

                if ($slot === null) {
                    $skipped++;
                    continue;
                }

                $startTime = Carbon::createFromFormat('H:i:s', $slot['start_time']);
                $endTime = $startTime->copy()->addHour();

                $roomKey = $this->makeRoomKey(
                    $slot['room_number'],
                    $slot['schedule_date'],
                    $slot['start_time']
                );

                $instructorKey = $this->makeInstructorKey(
                    (int) $enrollment->instructor_id,
                    $slot['schedule_date'],
                    $slot['start_time']
                );

                $roomConflicts[$roomKey] = true;
                $instructorConflicts[$instructorKey] = true;

                $rows[] = [
                    'enrollment_id' => $enrollment->enrollment_id,
                    'student_id' => $enrollment->student_id,
                    'instructor_id' => $enrollment->instructor_id,
                    'room_number' => $slot['room_number'],
                    'schedule_date' => $slot['schedule_date'],
                    'start_time' => $startTime->format('H:i:s'),
                    'end_time' => $endTime->format('H:i:s'),
                    'duration_minutes' => 60,
                    'status' => $status,
                    'lesson_topic' => $lessonTopics[array_rand($lessonTopics)],
                    'lesson_content' => $lessonContents[array_rand($lessonContents)],
                    'notes' => 'Demo lesson schedule generated by ScheduleSeeder.',
                    'cancellation_reason' => null,
                    'cancelled_at' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                $created++;

                /*
                |--------------------------------------------------------------------------
                | Batch Insert
                |--------------------------------------------------------------------------
                |
                | Insert every 100 rows to reduce database round trips while avoiding
                | a very large single insert statement.
                |
                */
                if (count($rows) >= 100) {
                    DB::table('schedule')->insert($rows);
                    $rows = [];

                    $this->command->info("✓ Created {$created} schedules...");
                }
            }
        }

        if (!empty($rows)) {
            DB::table('schedule')->insert($rows);
        }

        $this->command->info("✓ Successfully seeded {$created} lesson schedules.");

        if ($skipped > 0) {
            $this->command->warn("Skipped {$skipped} schedules because no conflict-free slot was available.");
        }
    }

    /**
     * Find an available room and time slot using in-memory conflict checking.
     */
    private function findAvailableSlot(
        array $roomNumbers,
        array $timeSlots,
        Carbon $preferredDate,
        int $instructorId,
        array $roomConflicts,
        array $instructorConflicts
    ): ?array {
        for ($dayOffset = 0; $dayOffset <= 180; $dayOffset++) {
            $candidateDate = $preferredDate->copy()->addDays($dayOffset)->toDateString();

            foreach ($timeSlots as $startTime) {
                foreach ($roomNumbers as $roomNumber) {
                    $roomKey = $this->makeRoomKey($roomNumber, $candidateDate, $startTime);
                    $instructorKey = $this->makeInstructorKey($instructorId, $candidateDate, $startTime);

                    if (isset($roomConflicts[$roomKey])) {
                        continue;
                    }

                    if (isset($instructorConflicts[$instructorKey])) {
                        continue;
                    }

                    return [
                        'room_number' => $roomNumber,
                        'schedule_date' => $candidateDate,
                        'start_time' => $startTime,
                    ];
                }
            }
        }

        return null;
    }

    /**
     * Build a unique key for room conflict checking.
     */
    private function makeRoomKey(string $roomNumber, string $scheduleDate, string $startTime): string
    {
        return $roomNumber . '|' . $scheduleDate . '|' . $startTime;
    }

    /**
     * Build a unique key for instructor conflict checking.
     */
    private function makeInstructorKey(int $instructorId, string $scheduleDate, string $startTime): string
    {
        return $instructorId . '|' . $scheduleDate . '|' . $startTime;
    }
}