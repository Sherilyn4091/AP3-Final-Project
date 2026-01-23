<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * ============================================================================
 * ATTENDANCE SEEDER (INTELLIGENT DATE FILTERING)
 * database/seeders/AttendanceSeeder.php
 * ============================================================================
 * Generates 40 lesson attendance records with:
 * - Only lesson-type attendance (student attendance for schedules)
 * - ONLY PAST & TODAY schedules (no future dates)
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
        // Get today's date for filtering
        $today = Carbon::today();

        // Fetch ONLY past and today schedules (exclude future) WITH enrollment data
        $schedules = DB::table('schedule as s')
            ->join('student as st', 's.student_id', '=', 'st.student_id')
            ->join('enrollment as e', 's.enrollment_id', '=', 'e.enrollment_id')
            ->select(
                's.schedule_id', 
                's.student_id', 
                's.schedule_date', 
                's.start_time', 
                's.end_time', 
                's.status', 
                'st.user_id',
                'e.enrollment_id',
                'e.completed_sessions',
                'e.total_sessions'
            )
            ->whereNotNull('s.schedule_id')
            ->where('s.schedule_date', '<=', $today) // ONLY past & today
            ->orderBy('s.schedule_date') // Oldest first to match session order
            ->get();

        if ($schedules->isEmpty()) {
            $this->command->error('No past/today schedules found. Run ScheduleSeeder first or adjust schedule dates.');
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
            $this->command->warn('All past/today schedules already have attendance records. No new records created.');
            return;
        }

        $this->command->info("✓ Seeding attendance records (matching enrollment.completed_sessions)...");

        // Group schedules by enrollment_id to track per-enrollment progress
        $schedulesByEnrollment = $availableSchedules->groupBy('enrollment_id');

        $created = 0;
        foreach ($schedulesByEnrollment as $enrollmentId => $enrollmentSchedules) {
            $enrollment = $enrollmentSchedules->first();
            $completedSessions = (int)$enrollment->completed_sessions;

            // Only create attendance for the FIRST N schedules matching completed_sessions
            $schedulesToMark = $enrollmentSchedules->take($completedSessions);

            foreach ($schedulesToMark as $schedule) {
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

        $this->command->info("✓ Successfully seeded {$created} lesson attendance records (past & today only)!");
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