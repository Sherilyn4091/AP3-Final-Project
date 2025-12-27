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
        if (!Schema::hasTable('enrollment')) {
        Schema::create('enrollment', function (Blueprint $table) {
            $table->string('enrollment_id', 20)->primary();
            $table->foreignId('student_id')->constrained('student', 'student_id')->onDelete('cascade');
            $table->foreignId('session_id')->constrained('lesson_session', 'session_id')->onDelete('restrict');
            $table->foreignId('instructor_id')->nullable()->constrained('instructor', 'instructor_id')->onDelete('set null');
            
            // Enrollment Details
            $table->date('enrollment_date')->default(DB::raw('CURRENT_DATE'));
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            
            // Session Tracking
            $table->integer('total_sessions');
            $table->integer('completed_sessions')->default(0);
            $table->integer('remaining_sessions');
            
            // Status
            $table->string('status', 20)->default('active');
            $table->string('payment_status', 20)->default('pending');
            
            // Pricing
            $table->decimal('total_amount', 10, 2)->nullable();
            $table->decimal('amount_paid', 10, 2)->default(0);
            
            // Notes
            $table->text('notes')->nullable();
            
            // System Fields
            $table->timestamps();
            
            $table->index('student_id');
            $table->index('session_id');
            $table->index('instructor_id');
            $table->index('status');
            $table->index('payment_status');
        });
    }
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enrollment');
    }
};