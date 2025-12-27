<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('instructor')) {
        Schema::create('instructor', function (Blueprint $table) {
            $table->id('instructor_id');
            $table->foreignId('user_id')->unique()->constrained('user_account', 'user_id')->onDelete('cascade');
            
            // Personal Information
            $table->string('first_name', 100);
            $table->string('middle_name', 100)->nullable();
            $table->string('last_name', 100);
            $table->string('suffix', 20)->nullable();
            
            // Contact Information
            $table->string('phone', 11)->nullable();
            $table->string('email', 255)->nullable();
            
            // Address Information
            $table->string('address_line1', 255)->nullable();
            $table->string('address_line2', 255)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('province', 100)->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->string('country', 100)->default('Philippines');
            
            // Personal Details
            $table->date('date_of_birth')->nullable();
            $table->string('gender', 20)->nullable();
            $table->string('nationality', 100)->nullable();
            
            // Emergency Contact
            $table->string('emergency_contact_name', 200)->nullable();
            $table->string('emergency_contact_relationship', 100)->nullable();
            $table->string('emergency_contact_phone', 11)->nullable();
            
            // Professional Information
            $table->string('employee_id', 50)->unique()->nullable();
            
            // Employment Details
            $table->date('hire_date')->default(DB::raw('CURRENT_DATE'));
            $table->string('employment_status', 50)->default('active');
            $table->string('contract_type', 50)->nullable();
            
            // Compensation
            $table->decimal('hourly_rate', 10, 2)->nullable();
            $table->decimal('monthly_salary', 10, 2)->nullable();
            
            // Professional Qualifications
            $table->string('education_level', 100)->nullable();
            $table->string('music_degree', 200)->nullable();
            $table->text('certifications')->nullable();
            $table->integer('years_of_experience')->nullable();
            
            // Teaching Details
            $table->text('teaching_style')->nullable();
            $table->text('bio')->nullable();
            $table->text('languages_spoken')->nullable();
            
            // Availability
            $table->boolean('is_available')->default(true);
            $table->text('available_days')->nullable();
            $table->text('preferred_time_slots')->nullable();
            $table->integer('max_students_per_day')->default(8);
            
            // Performance Metrics
            $table->integer('total_students_taught')->default(0);
            $table->decimal('average_rating', 3, 2)->nullable();
            
            // System Fields
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index('user_id');
            $table->index('employee_id');
            $table->index(['last_name', 'first_name']);
        });
    }
}

    public function down(): void
    {
        Schema::dropIfExists('instructor');
    }
};