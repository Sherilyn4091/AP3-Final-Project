<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('payment_method')) {
        Schema::create('payment_method', function (Blueprint $table) {
            $table->id('method_id');
            $table->string('method_name', 50);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }
}

    public function down(): void
    {
        Schema::dropIfExists('payment_method');
    }
};