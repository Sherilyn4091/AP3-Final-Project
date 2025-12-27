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
        if (!Schema::hasTable('progress')) {
        Schema::create('progress', function (Blueprint $table) {
            $table->id('progress_id');
            $table->foreignId('student_id')->constrained('student', 'student_id')->onDelete('cascade');
            $table->string('enrollment_id', 20);
            $table->foreignId('instructor_id')->constrained('instructor', 'instructor_id')->onDelete('cascade');
            $table->foreignId('schedule_id')->nullable()->constrained('schedule', 'schedule_id')->onDelete('set null');
            
            // Progress Details
            $table->date('progress_date')->default(DB::raw('CURRENT_DATE'));
            $table->string('lesson_topic', 200)->nullable();
            $table->text('skills_covered')->nullable();
            $table->text('techniques_learned')->nullable();
            $table->text('songs_practiced')->nullable();
            
            // Assessment
            $table->integer('performance_rating')->nullable();
            $table->integer('technical_skills_rating')->nullable();
            $table->integer('musicality_rating')->nullable();
            $table->integer('effort_rating')->nullable();
            
            // Feedback
            $table->text('strengths')->nullable();
            $table->text('areas_for_improvement')->nullable();
            $table->text('instructor_notes')->nullable();
            
            // Assignments
            $table->text('homework')->nullable();
            $table->text('practice_recommendations')->nullable();
            $table->text('next_lesson_focus')->nullable();
            
            // Student Feedback
            $table->text('student_comments')->nullable();
            $table->integer('student_satisfaction')->nullable();
            
            // System Fields
            $table->timestamps();
            
            // Foreign key
            $table->foreign('enrollment_id')->references('enrollment_id')->on('enrollment')->onDelete('cascade');
            
            $table->index('student_id');
            $table->index('enrollment_id');
            $table->index('instructor_id');
            $table->index('progress_date');
        });
    }
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('progress');
    }
};