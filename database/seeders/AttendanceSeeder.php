<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * ============================================================================
 * ATTENDANCE SEEDER
 * ============================================================================
 * Generates 40 lesson attendance records with:
 * - Only lesson-type attendance (student attendance for schedules)
 * - Proper FK relationships (schedule, student, user_account)
 * - Realistic attendance statuses based on schedule status
 * - Auto check-in/check-out times
 * - Re-runnable without errors or duplicates
 * ============================================================================
 */
class AttendanceSeeder extends Seeder
{
    public function run(): void
    {
        // Fetch schedules with student and user info
        $schedules = DB::table('schedule as s')
            ->join('student as st', 's.student_id', '=', 'st.student_id')
            ->select('s.schedule_id', 's.student_id', 's.schedule_date', 's.start_time', 's.end_time', 's.status', 'st.user_id')
            ->whereNotNull('s.schedule_id')
            ->get();

        if ($schedules->isEmpty()) {
            $this->command->error('No schedules found. Run ScheduleSeeder first.');
            return;
        }

        // Get existing attendance schedule IDs to avoid duplicates
        $existingScheduleIds = DB::table('attendance')
            ->whereNotNull('schedule_id')
            ->pluck('schedule_id')
            ->toArray();

        // Filter out schedules that already have attendance
        $availableSchedules = $schedules->filter(function ($schedule) use ($existingScheduleIds) {
            return !in_array($schedule->schedule_id, $existingScheduleIds);
        });

        if ($availableSchedules->isEmpty()) {
            $this->command->warn('All schedules already have attendance records. No new records created.');
            return;
        }

        // Number of attendance records to create
        $count = min(40, $availableSchedules->count());

        $this->command->info("✓ Seeding {$count} lesson attendance records...");

        $created = 0;
        foreach ($availableSchedules->take($count) as $schedule) {
            // Determine attendance status based on schedule status
            $attendanceStatus = $this->mapScheduleToAttendance($schedule->status);

            // Generate check-in/check-out times only if present
            $checkInTime = null;
            $checkOutTime = null;

            if ($attendanceStatus === 'present') {
                $checkInTime = Carbon::parse($schedule->schedule_date . ' ' . $schedule->start_time)
                    ->subMinutes(rand(0, 10)); // 0-10 mins early
                $checkOutTime = Carbon::parse($schedule->schedule_date . ' ' . $schedule->end_time)
                    ->addMinutes(rand(0, 5)); // 0-5 mins late
            } elseif ($attendanceStatus === 'late') {
                $checkInTime = Carbon::parse($schedule->schedule_date . ' ' . $schedule->start_time)
                    ->addMinutes(rand(5, 20)); // 5-20 mins late
                $checkOutTime = Carbon::parse($schedule->schedule_date . ' ' . $schedule->end_time);
            }

            DB::table('attendance')->insert([
                'attendance_type' => 'lesson',
                'schedule_id' => $schedule->schedule_id,
                'user_id' => $schedule->user_id,
                'student_id' => $schedule->student_id,
                'instructor_id' => null, // Not tracking instructor attendance
                'attendance_date' => $schedule->schedule_date,
                'attendance_status' => $attendanceStatus,
                'check_in_time' => $checkInTime,
                'check_out_time' => $checkOutTime,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $created++;

            // Progress indicator
            if ($created % 10 === 0) {
                $this->command->info("✓ Created {$created} attendance records...");
            }
        }

        $this->command->info("Successfully seeded {$created} lesson attendance records!");
    }

    /**
     * Map schedule status to attendance status
     */
    private function mapScheduleToAttendance(string $scheduleStatus): string
    {
        return match ($scheduleStatus) {
            'completed' => rand(1, 10) > 2 ? 'present' : 'late', // 80% present, 20% late
            'no_show' => 'absent',
            'cancelled' => 'excused',
            default => 'present',
        };
    }
}