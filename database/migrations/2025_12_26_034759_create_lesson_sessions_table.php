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
        if (!Schema::hasTable('lesson_session')) {
        Schema::create('lesson_session', function (Blueprint $table) {
            $table->id('session_id');
            
            // Session Details
            $table->integer('session_count');
            $table->integer('duration_minutes')->default(60);
            
            // Pricing
            $table->decimal('price', 10, 2);
            
            // Description
            $table->string('session_name', 200)->nullable();
            $table->text('description')->nullable();
            
            // System Fields
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index('is_active');
        });
    }
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lesson_session');
    }
};