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
        if (!Schema::hasTable('student')) {
        Schema::create('student', function (Blueprint $table) {
            $table->id('student_id');
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
            
            // Guardian/Parent Information
            $table->string('parent_guardian_name', 200)->nullable();
            $table->string('parent_guardian_relationship', 50)->nullable();
            $table->string('parent_guardian_phone', 11)->nullable();
            $table->string('parent_guardian_email', 255)->nullable();
            $table->text('parent_guardian_address')->nullable();
            
            // Musical Background
            $table->foreignId('instrument_id')->nullable()->constrained('instrument', 'instrument_id')->onDelete('set null');
            $table->text('secondary_instruments')->nullable();
            $table->text('previous_music_experience')->nullable();
            $table->string('skill_level', 50)->nullable();
            $table->text('music_goals')->nullable();
            
            // Educational Background
            $table->string('school_name', 200)->nullable();
            $table->string('grade_level', 50)->nullable();
            
            // Medical Information
            $table->text('medical_conditions')->nullable();
            $table->text('allergies')->nullable();
            $table->text('special_needs')->nullable();
            
            // Enrollment Information
            $table->date('enrollment_date')->default(DB::raw('CURRENT_DATE'));
            $table->date('expected_completion_date')->nullable();
            $table->foreignId('student_status_id')->constrained('student_status', 'status_id')->onDelete('restrict');
            $table->foreignId('preferred_genre_id')->nullable()->constrained('genre', 'genre_id')->onDelete('set null');
            
            // Preferences
            $table->text('preferred_lesson_days')->nullable();
            $table->text('preferred_lesson_time')->nullable();
            
            // System Fields
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index('user_id');
            $table->index('instrument_id');
            $table->index('student_status_id');
            $table->index('preferred_genre_id');
            $table->index(['last_name', 'first_name']);
        });
    }
}

    public function down(): void
    {
        Schema::dropIfExists('student');
    }
};