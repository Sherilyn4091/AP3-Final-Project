<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('genre')) {
        Schema::create('genre', function (Blueprint $table) {
            $table->id('genre_id');
            $table->string('genre_name', 100)->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index('genre_name');
            $table->index('is_active');
        });
    }
}

    public function down(): void
    {
        Schema::dropIfExists('genre');
    }
};