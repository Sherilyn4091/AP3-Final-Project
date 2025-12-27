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
        if (!Schema::hasTable('password_reset_tokens')) {
        Schema::create('report', function (Blueprint $table) {
            $table->id('report_id');
            
            // Report Details
            $table->string('report_type', 50);
            $table->string('report_title', 200);
            $table->date('report_date_from')->nullable();
            $table->date('report_date_to')->nullable();
            
            // Data (stored as JSON for flexibility)
            $table->jsonb('report_data')->nullable();
            
            // Audit
            $table->foreignId('generated_by')->constrained('user_account', 'user_id')->onDelete('cascade');
            $table->timestamp('generated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            
            // System Fields
            $table->timestamps();
            
            $table->index('report_type');
            $table->index('generated_by');
            $table->index(['report_date_from', 'report_date_to']);
        });
    }
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report');
    }
};