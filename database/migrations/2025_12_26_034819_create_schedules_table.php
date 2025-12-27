<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('schedule')) {
        Schema::create('schedule', function (Blueprint $table) {
            $table->id('schedule_id');
            
            // Relationships
            $table->string('enrollment_id', 20)->nullable();
            $table->foreignId('student_id')->constrained('student', 'student_id')->onDelete('cascade');
            $table->foreignId('instructor_id')->nullable()->constrained('instructor', 'instructor_id')->onDelete('set null');
            
            // Schedule Details
            $table->string('room_number', 50)->nullable();
            $table->date('schedule_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('duration_minutes')->nullable();
            
            // Status
            $table->string('status', 20)->default('scheduled');
            
            // Lesson Information
            $table->string('lesson_topic', 200)->nullable();
            $table->text('lesson_content')->nullable();
            $table->text('notes')->nullable();
            
            // Cancellation
            $table->text('cancellation_reason')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            
            // System Fields
            $table->timestamps();
            
            // Foreign key
            $table->foreign('enrollment_id')->references('enrollment_id')->on('enrollment')->onDelete('cascade');
            
            $table->index('schedule_date');
            $table->index('student_id');
            $table->index('instructor_id');
            $table->index('enrollment_id');
        });
        
        // Unique constraint to prevent double booking of rooms
        DB::statement("
            CREATE UNIQUE INDEX idx_schedule_conflict_room ON schedule(room_number, schedule_date, start_time)
            WHERE status NOT IN ('cancelled', 'no_class', 'rescheduled')
        ");
        
        // Unique constraint to prevent instructor double booking
        DB::statement("
            CREATE UNIQUE INDEX idx_schedule_conflict_instructor ON schedule(instructor_id, schedule_date, start_time)
            WHERE status NOT IN ('cancelled', 'no_class', 'rescheduled') AND instructor_id IS NOT NULL
        ");
    }
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedule');
    }
};