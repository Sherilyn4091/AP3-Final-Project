<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * database/migrations/xxxx_xx_xx_xxxxxx_add_final_safety_constraints_to_music_lab_tables.php
     *
     * Purpose:
     * - Adds extra database-level safety rules after the main Music Lab schema is clean.
     * - These constraints help prevent invalid records even if a controller or seeder has a bug.
     *
     * Important:
     * - This migration is safe to run with php artisan migrate.
     * - Do NOT run migrate:fresh just for this.
     * - The helper functions below prevent duplicate constraint errors if the migration is retried.
     */
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Room Foreign Keys
        |--------------------------------------------------------------------------
        |
        | These make sure schedule.room_number and booking.room_number must exist
        | in the room table.
        |
        | Why this is good:
        | - Prevents schedules from using fake/non-existing rooms.
        | - Prevents room bookings from using invalid rooms.
        */
        $this->addConstraintIfMissing(
            'schedule',
            'fk_schedule_room_number',
            "ALTER TABLE schedule
             ADD CONSTRAINT fk_schedule_room_number
             FOREIGN KEY (room_number)
             REFERENCES room(room_number)
             ON UPDATE CASCADE
             ON DELETE SET NULL"
        );

        $this->addConstraintIfMissing(
            'booking',
            'fk_booking_room_number',
            "ALTER TABLE booking
             ADD CONSTRAINT fk_booking_room_number
             FOREIGN KEY (room_number)
             REFERENCES room(room_number)
             ON UPDATE CASCADE
             ON DELETE RESTRICT"
        );

        /*
        |--------------------------------------------------------------------------
        | Payment Safety
        |--------------------------------------------------------------------------
        |
        | Payments should not have zero or negative amounts.
        | Discounts should not be negative.
        */
        $this->addConstraintIfMissing(
            'payment',
            'payment_amount_positive_check',
            "ALTER TABLE payment
             ADD CONSTRAINT payment_amount_positive_check
             CHECK (amount > 0)"
        );

        $this->addConstraintIfMissing(
            'payment',
            'payment_discount_non_negative_check',
            "ALTER TABLE payment
             ADD CONSTRAINT payment_discount_non_negative_check
             CHECK (discount >= 0)"
        );

        /*
        |--------------------------------------------------------------------------
        | Enrollment Session Count Safety
        |--------------------------------------------------------------------------
        |
        | Ensures completed + remaining sessions always equals total sessions.
        |
        | Example:
        | total_sessions = 10
        | completed_sessions = 2
        | remaining_sessions = 8
        */
        $this->addConstraintIfMissing(
            'enrollment',
            'enrollment_session_count_check',
            "ALTER TABLE enrollment
             ADD CONSTRAINT enrollment_session_count_check
             CHECK (
                total_sessions > 0
                AND completed_sessions >= 0
                AND remaining_sessions >= 0
                AND completed_sessions + remaining_sessions = total_sessions
             )"
        );

        /*
        |--------------------------------------------------------------------------
        | Schedule and Booking Time Safety
        |--------------------------------------------------------------------------
        |
        | End time must be later than start time.
        */
        $this->addConstraintIfMissing(
            'schedule',
            'schedule_time_order_check',
            "ALTER TABLE schedule
             ADD CONSTRAINT schedule_time_order_check
             CHECK (end_time > start_time)"
        );

        $this->addConstraintIfMissing(
            'booking',
            'booking_time_order_check',
            "ALTER TABLE booking
             ADD CONSTRAINT booking_time_order_check
             CHECK (end_time > start_time)"
        );

        /*
        |--------------------------------------------------------------------------
        | Progress Rating Safety
        |--------------------------------------------------------------------------
        |
        | Instructor ratings are 1 to 10.
        | Student satisfaction is 1 to 5.
        */
        $this->addConstraintIfMissing(
            'progress',
            'progress_performance_rating_range_check',
            "ALTER TABLE progress
             ADD CONSTRAINT progress_performance_rating_range_check
             CHECK (performance_rating IS NULL OR performance_rating BETWEEN 1 AND 10)"
        );

        $this->addConstraintIfMissing(
            'progress',
            'progress_technical_skills_rating_range_check',
            "ALTER TABLE progress
             ADD CONSTRAINT progress_technical_skills_rating_range_check
             CHECK (technical_skills_rating IS NULL OR technical_skills_rating BETWEEN 1 AND 10)"
        );

        $this->addConstraintIfMissing(
            'progress',
            'progress_musicality_rating_range_check',
            "ALTER TABLE progress
             ADD CONSTRAINT progress_musicality_rating_range_check
             CHECK (musicality_rating IS NULL OR musicality_rating BETWEEN 1 AND 10)"
        );

        $this->addConstraintIfMissing(
            'progress',
            'progress_effort_rating_range_check',
            "ALTER TABLE progress
             ADD CONSTRAINT progress_effort_rating_range_check
             CHECK (effort_rating IS NULL OR effort_rating BETWEEN 1 AND 10)"
        );

        $this->addConstraintIfMissing(
            'progress',
            'progress_student_satisfaction_range_check',
            "ALTER TABLE progress
             ADD CONSTRAINT progress_student_satisfaction_range_check
             CHECK (student_satisfaction IS NULL OR student_satisfaction BETWEEN 1 AND 5)"
        );

        /*
        |--------------------------------------------------------------------------
        | Report Type Safety
        |--------------------------------------------------------------------------
        |
        | Keeps report_type values consistent for admin reports and analytics.
        */
        $this->addConstraintIfMissing(
            'report',
            'report_type_allowed_check',
            "ALTER TABLE report
             ADD CONSTRAINT report_type_allowed_check
             CHECK (
                report_type IN (
                    'revenue',
                    'enrollment',
                    'inventory',
                    'attendance',
                    'analytics',
                    'instructor_performance',
                    'student_progress',
                    'sales',
                    'payment_summary'
                )
             )"
        );
    }

    /**
     * Reverse the migration.
     */
    public function down(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Drop constraints safely
        |--------------------------------------------------------------------------
        |
        | IF EXISTS prevents rollback errors if a constraint was already removed.
        */
        DB::statement('ALTER TABLE report DROP CONSTRAINT IF EXISTS report_type_allowed_check');

        DB::statement('ALTER TABLE progress DROP CONSTRAINT IF EXISTS progress_student_satisfaction_range_check');
        DB::statement('ALTER TABLE progress DROP CONSTRAINT IF EXISTS progress_effort_rating_range_check');
        DB::statement('ALTER TABLE progress DROP CONSTRAINT IF EXISTS progress_musicality_rating_range_check');
        DB::statement('ALTER TABLE progress DROP CONSTRAINT IF EXISTS progress_technical_skills_rating_range_check');
        DB::statement('ALTER TABLE progress DROP CONSTRAINT IF EXISTS progress_performance_rating_range_check');

        DB::statement('ALTER TABLE booking DROP CONSTRAINT IF EXISTS booking_time_order_check');
        DB::statement('ALTER TABLE schedule DROP CONSTRAINT IF EXISTS schedule_time_order_check');

        DB::statement('ALTER TABLE enrollment DROP CONSTRAINT IF EXISTS enrollment_session_count_check');

        DB::statement('ALTER TABLE payment DROP CONSTRAINT IF EXISTS payment_discount_non_negative_check');
        DB::statement('ALTER TABLE payment DROP CONSTRAINT IF EXISTS payment_amount_positive_check');

        DB::statement('ALTER TABLE booking DROP CONSTRAINT IF EXISTS fk_booking_room_number');
        DB::statement('ALTER TABLE schedule DROP CONSTRAINT IF EXISTS fk_schedule_room_number');
    }

    /**
     * Add a constraint only if it does not already exist.
     *
     * This keeps the migration safer during retries or interrupted runs.
     */
    private function addConstraintIfMissing(string $tableName, string $constraintName, string $sql): void
    {
        if (!$this->constraintExists($tableName, $constraintName)) {
            DB::statement($sql);
        }
    }

    /**
     * Check PostgreSQL system catalog for an existing table constraint.
     */
    private function constraintExists(string $tableName, string $constraintName): bool
    {
        $result = DB::selectOne(
            "
            SELECT EXISTS (
                SELECT 1
                FROM pg_constraint c
                JOIN pg_class t ON c.conrelid = t.oid
                JOIN pg_namespace n ON n.oid = t.relnamespace
                WHERE n.nspname = 'public'
                  AND t.relname = ?
                  AND c.conname = ?
            ) AS constraint_exists
            ",
            [$tableName, $constraintName]
        );

        return (bool) $result->constraint_exists;
    }
};