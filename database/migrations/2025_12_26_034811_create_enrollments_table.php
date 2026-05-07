<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create the enrollment table.
     *
     * Purpose:
     * - Stores each lesson enrollment/package of a student.
     * - Allows one student to enroll in different instruments.
     * - Allows one student to have different instructors per instrument.
     *
     * Correct relationship:
     * student -> enrollment -> instrument
     * student -> enrollment -> instructor
     * student -> enrollment -> lesson_session package
     */
    public function up(): void
    {
        Schema::create('enrollment', function (Blueprint $table) {
            /*
            |--------------------------------------------------------------------------
            | Primary Key
            |--------------------------------------------------------------------------
            */
            $table->string('enrollment_id', 20)->primary();

            /*
            |--------------------------------------------------------------------------
            | Foreign Keys
            |--------------------------------------------------------------------------
            |
            | instrument_id belongs here because the chosen instrument must be saved
            | per enrollment, not only in the student profile.
            |
            | Example:
            | - Student can enroll in Guitar with Instructor A.
            | - The same student can also enroll in Keyboard with Instructor B.
            |
            */
            $table->foreignId('student_id')
                ->constrained('student', 'student_id')
                ->onDelete('cascade');

            $table->foreignId('instrument_id')
                ->constrained('instrument', 'instrument_id')
                ->onDelete('restrict');

            $table->foreignId('session_id')
                ->constrained('lesson_session', 'session_id')
                ->onDelete('restrict');

            $table->foreignId('instructor_id')
                ->nullable()
                ->constrained('instructor', 'instructor_id')
                ->onDelete('set null');

            $table->foreignId('payment_method_id')
                ->nullable()
                ->constrained('payment_methods', 'method_id')
                ->onDelete('set null');

            /*
            |--------------------------------------------------------------------------
            | Enrollment Dates
            |--------------------------------------------------------------------------
            */
            $table->date('enrollment_date')->default(DB::raw('CURRENT_DATE'));
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Session Tracking
            |--------------------------------------------------------------------------
            */
            $table->integer('total_sessions');
            $table->integer('completed_sessions')->default(0);
            $table->integer('remaining_sessions');

            /*
            |--------------------------------------------------------------------------
            | Status Fields
            |--------------------------------------------------------------------------
            */
            $table->string('status', 20)->default('active');
            $table->string('payment_status', 20)->default('pending');

            /*
            |--------------------------------------------------------------------------
            | Payment Amounts
            |--------------------------------------------------------------------------
            */
            $table->decimal('total_amount', 10, 2)->nullable();
            $table->decimal('amount_paid', 10, 2)->default(0);

            /*
            |--------------------------------------------------------------------------
            | Notes and Timestamps
            |--------------------------------------------------------------------------
            */
            $table->text('notes')->nullable();
            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | Indexes
            |--------------------------------------------------------------------------
            |
            | These make searching/filtering faster.
            |
            */
            $table->index('student_id');
            $table->index('instrument_id');
            $table->index('session_id');
            $table->index('instructor_id');
            $table->index('payment_method_id');
            $table->index('status');
            $table->index('payment_status');
        });
    }

    /**
     * Drop the enrollment table.
     */
    public function down(): void
    {
        Schema::dropIfExists('enrollment');
    }
};