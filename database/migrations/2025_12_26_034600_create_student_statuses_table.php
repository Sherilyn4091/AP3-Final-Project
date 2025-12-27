<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('student_status')) {
        Schema::create('student_status', function (Blueprint $table) {
            $table->id('status_id');
            $table->string('status_name', 50);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index('status_name');
        });
    }
}

    public function down(): void
    {
        Schema::dropIfExists('student_status');
    }
};