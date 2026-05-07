<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * database/migrations/2025_12_26_034840_create_attendance_table.php
     *
     * Run the migrations.
     *
     * Purpose:
     * - Creates the attendance table for lesson, work, practice, and event attendance.
     * - This version only uses the actual Music Lab roles/modules:
     *   schedule, user_account, student, and instructor.
     *
     * Important:
     * - Lesson attendance and work attendance have different uniqueness rules.
     */
    public function up(): void
    {
        if (!Schema::hasTable('attendance')) {
            Schema::create('attendance', function (Blueprint $table) {
                $table->id('attendance_id');

                /*
                |--------------------------------------------------------------------------
                | Attendance Type
                |--------------------------------------------------------------------------
                |
                | Defines what kind of attendance is being recorded.
                | Allowed values are enforced below using a PostgreSQL CHECK constraint.
                |
                */
                $table->string('attendance_type', 20);

                /*
                |--------------------------------------------------------------------------
                | Related Records
                |--------------------------------------------------------------------------
                |
                | schedule_id is nullable because not all attendance records are tied
                | to a lesson schedule. For example, work/practice/event attendance
                | may only need the user_id.
                |
                */
                $table->foreignId('schedule_id')
                    ->nullable()
                    ->constrained('schedule', 'schedule_id')
                    ->onDelete('cascade');

                $table->foreignId('user_id')
                    ->constrained('user_account', 'user_id')
                    ->onDelete('cascade');

                $table->foreignId('student_id')
                    ->nullable()
                    ->constrained('student', 'student_id')
                    ->onDelete('cascade');

                $table->foreignId('instructor_id')
                    ->nullable()
                    ->constrained('instructor', 'instructor_id')
                    ->onDelete('cascade');

                /*
                |--------------------------------------------------------------------------
                | Attendance Details
                |--------------------------------------------------------------------------
                */
                $table->date('attendance_date')->default(DB::raw('CURRENT_DATE'));
                $table->string('attendance_status', 20);
                $table->timestamp('check_in_time')->nullable();
                $table->timestamp('check_out_time')->nullable();

                /*
                |--------------------------------------------------------------------------
                | System Fields
                |--------------------------------------------------------------------------
                */
                $table->timestamps();

                /*
                |--------------------------------------------------------------------------
                | Indexes
                |--------------------------------------------------------------------------
                |
                | These make filtering and searching faster in admin/instructor views.
                |
                */
                $table->index('attendance_type');
                $table->index('schedule_id');
                $table->index('user_id');
                $table->index('student_id');
                $table->index('instructor_id');
                $table->index('attendance_status');
                $table->index('attendance_date');

                /*
                |--------------------------------------------------------------------------
                | Lesson Attendance Unique Rule
                |--------------------------------------------------------------------------
                |
                | Prevents duplicate attendance records for the same student
                | in the same lesson schedule.
                |
                | This is the correct uniqueness rule for lesson attendance.
                |
                */
                $table->unique(['schedule_id', 'student_id'], 'unique_lesson_attendance');
            });

            /*
            |--------------------------------------------------------------------------
            | Work/Practice/Event Attendance Unique Rule
            |--------------------------------------------------------------------------
            |
            | This is a PostgreSQL partial unique index.
            |
            | Why partial?
            | - A student may have more than one lesson schedule on the same day.
            | - This rule only prevents duplicate non-lesson attendance for the
            |   same user, date, and type.
            |
            */
            DB::statement("
                CREATE UNIQUE INDEX unique_work_attendance
                ON attendance(user_id, attendance_date, attendance_type)
                WHERE attendance_type IN ('work', 'practice', 'event')
            ");

            /*
            |--------------------------------------------------------------------------
            | PostgreSQL CHECK Constraints
            |--------------------------------------------------------------------------
            |
            | These enforce valid attendance type and status values.
            |
            */
            DB::statement("
                ALTER TABLE attendance
                ADD CONSTRAINT attendance_type_check
                CHECK (attendance_type IN ('lesson', 'work', 'practice', 'event'))
            ");

            DB::statement("
                ALTER TABLE attendance
                ADD CONSTRAINT attendance_status_check
                CHECK (attendance_status IN ('present', 'absent', 'late', 'excused', 'half_day', 'on_leave'))
            ");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance');
    }
};