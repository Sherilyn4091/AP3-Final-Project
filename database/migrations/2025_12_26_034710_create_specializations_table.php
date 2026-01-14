<?php
/*
* database/migrations/2025_12_26_034437_create_specializations_table.php
*/

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
        if (!Schema::hasTable('specialization')) {
        Schema::create('specialization', function (Blueprint $table) {
            $table->id('specialization_id');
            $table->string('specialization_name', 100)->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index('specialization_name');
            $table->index('is_active');
            });
        }

        Schema::create('instructor_specialization', function (Blueprint $table) {
            $table->id('instructor_specialization_id');
            $table->foreignId('instructor_id')->constrained('instructor', 'instructor_id')->onDelete('cascade');
            $table->foreignId('specialization_id')->constrained('specialization', 'specialization_id')->onDelete('cascade');
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('instructor_specialization');
        Schema::dropIfExists('specialization');
    }
};

