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
        if (!Schema::hasTable('payment')) {
        Schema::create('payment', function (Blueprint $table) {
            $table->id('payment_id');
            $table->foreignId('student_id')->constrained('student', 'student_id')->onDelete('cascade');
            $table->string('enrollment_id', 20)->nullable();
            $table->string('booking_id', 20)->nullable();
            
            // Payment Details
            $table->decimal('amount', 10, 2);
            $table->foreignId('payment_method_id')->constrained('payment_method', 'method_id')->onDelete('restrict');
            $table->foreignId('payment_status_id')->constrained('payment_status', 'status_id')->onDelete('restrict');
            $table->date('payment_date')->default(DB::raw('CURRENT_DATE'));
            
            // Transaction Information
            $table->string('transaction_reference', 100)->nullable();
            $table->string('receipt_number', 100)->nullable();
            $table->string('bank_name', 100)->nullable();
            $table->string('account_number', 100)->nullable();
            $table->string('check_number', 100)->nullable();
            
            // Breakdown
            $table->decimal('subtotal', 10, 2)->nullable();
            $table->decimal('discount', 10, 2)->default(0);
            
            // Processing
            $table->foreignId('processed_by')->nullable()->constrained('user_account', 'user_id')->onDelete('set null');
            $table->foreignId('approved_by')->nullable()->constrained('user_account', 'user_id')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            
            // System Fields
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('enrollment_id')->references('enrollment_id')->on('enrollment')->onDelete('set null');
            $table->foreign('booking_id')->references('booking_id')->on('booking')->onDelete('set null');
            
            $table->index('student_id');
            $table->index('enrollment_id');
            $table->index('booking_id');
            $table->index('payment_method_id');
            $table->index('payment_status_id');
            $table->index('payment_date');
            $table->index('receipt_number');
        });
    }
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment');
    }
};