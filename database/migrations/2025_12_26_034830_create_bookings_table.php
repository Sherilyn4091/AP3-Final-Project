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
        if (!Schema::hasTable('booking')) {
        Schema::create('booking', function (Blueprint $table) {
            $table->string('booking_id', 20)->primary();
            $table->foreignId('user_id')->constrained('user_account', 'user_id')->onDelete('cascade');
            
            // Booking Details
            $table->string('room_number', 50);
            $table->date('booking_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->decimal('duration_hours', 4, 2)->nullable();
            
            // Pricing
            $table->decimal('hourly_rate', 10, 2)->nullable();
            $table->decimal('total_amount', 10, 2)->nullable();
            
            // Booking Information
            $table->string('booking_status', 20)->default('pending');
            $table->text('purpose')->nullable();
            $table->integer('number_of_people')->nullable();
            $table->string('band_name', 200)->nullable();
            
            // Equipment Needs
            $table->text('equipment_needed')->nullable();
            $table->text('special_requests')->nullable();
            
            // Contact for Booking
            $table->string('contact_name', 200)->nullable();
            $table->string('contact_phone', 11)->nullable();
            $table->string('contact_email', 255)->nullable();
            
            // Processing
            $table->foreignId('confirmed_by')->nullable()->constrained('user_account', 'user_id')->onDelete('set null');
            $table->timestamp('confirmed_at')->nullable();
            
            // Cancellation
            $table->foreignId('cancelled_by')->nullable()->constrained('user_account', 'user_id')->onDelete('set null');
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            
            // System Fields
            $table->timestamps();
            
            $table->index('user_id');
            $table->index(['room_number', 'booking_date']);
            $table->index('booking_date');
            $table->index('booking_status');
        });
        
        // Unique constraint to prevent double booking
        DB::statement("
            CREATE UNIQUE INDEX idx_booking_conflict ON booking(room_number, booking_date, start_time)
            WHERE booking_status NOT IN ('cancelled')
        ");
    }
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking');
    }
};