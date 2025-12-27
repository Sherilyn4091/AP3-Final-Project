<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('payment_status')) {
        Schema::create('payment_status', function (Blueprint $table) {
            $table->id('status_id');
            $table->string('status_name', 50);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }
}

    public function down(): void
    {
        Schema::dropIfExists('payment_status');
    }
};