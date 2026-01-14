<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * database/migrations/2025_12_26_030639_create_instruments_table.php
     */
    public function up(): void
    {
        if (!Schema::hasTable('instrument')) {
            Schema::create('instrument', function (Blueprint $table) {
                $table->id('instrument_id');
                $table->string('instrument_name', 100)->unique();
                $table->string('category', 100)->nullable();
                $table->text('description')->nullable();
                $table->boolean('is_system')->default(false);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                
                $table->index('instrument_name');
                $table->index('is_active');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('instrument');
    }
};