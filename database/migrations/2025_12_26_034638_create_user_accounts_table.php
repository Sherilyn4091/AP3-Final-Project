<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('user_account')) {
        Schema::create('user_account', function (Blueprint $table) {
            $table->id('user_id');
            $table->string('user_email', 255)->unique();
            $table->string('user_password', 255);
            $table->boolean('is_super_admin')->default(false);
            $table->timestamp('last_login')->nullable();
            $table->timestamps();
            
            $table->index('user_email');
            $table->index('is_super_admin');
        });
    }
}

    public function down(): void
    {
        Schema::dropIfExists('user_account');
    }
};