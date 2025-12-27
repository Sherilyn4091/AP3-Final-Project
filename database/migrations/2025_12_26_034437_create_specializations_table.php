<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
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
}

    public function down(): void
    {
        Schema::dropIfExists('specialization');
    }
};