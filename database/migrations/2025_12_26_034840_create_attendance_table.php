<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('attendance')) {
        Schema::create('attendance', function (Blueprint $table) {
            $table->id('attendance_id');
            
            // What is being tracked
            $table->string('attendance_type', 20);
            
            // References (only one will be populated based on type)
            $table->foreignId('schedule_id')->nullable()->constrained('schedule', 'schedule_id')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('user_account', 'user_id')->onDelete('cascade');
            $table->foreignId('student_id')->nullable()->constrained('student', 'student_id')->onDelete('cascade');
            $table->foreignId('instructor_id')->nullable()->constrained('instructor', 'instructor_id')->onDelete('cascade');
            $table->foreignId('sales_staff_id')->nullable()->constrained('sales_staff', 'sales_staff_id')->onDelete('cascade');
            $table->foreignId('all_around_staff_id')->nullable()->constrained('all_around_staff', 'all_around_staff_id')->onDelete('cascade');
            
            // Attendance Details
            $table->date('attendance_date')->default(DB::raw('CURRENT_DATE'));
            $table->string('attendance_status', 20);
            $table->timestamp('check_in_time')->nullable();
            $table->timestamp('check_out_time')->nullable();
            
            // System Fields
            $table->timestamps();
            
            // Indexes
            $table->index('attendance_type');
            $table->index('schedule_id');
            $table->index('user_id');
            $table->index('student_id');
            $table->index('instructor_id');
            $table->index('sales_staff_id');
            $table->index('all_around_staff_id');
            $table->index('attendance_status');
            $table->index('attendance_date');
            
            // Unique constraints
            $table->unique(['schedule_id', 'student_id'], 'unique_lesson_attendance');
            $table->unique(['user_id', 'attendance_date', 'attendance_type'], 'unique_work_attendance');
        });
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