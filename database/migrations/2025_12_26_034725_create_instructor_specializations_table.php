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
        if (!Schema::hasTable('instructor_specialization')) {
        Schema::create('instructor_specialization', function (Blueprint $table) {
            $table->id('instructor_specialization_id');
            $table->foreignId('instructor_id')->constrained('instructor', 'instructor_id')->onDelete('cascade');
            $table->foreignId('specialization_id')->constrained('specialization', 'specialization_id')->onDelete('cascade');
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
            
            $table->unique(['instructor_id', 'specialization_id'], 'unique_instructor_specialization');
            $table->index('instructor_id');
            $table->index('specialization_id');
        });
    }
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('instructor_specialization');
    }
};