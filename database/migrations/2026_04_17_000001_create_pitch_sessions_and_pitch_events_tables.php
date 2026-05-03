<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| Create Pitch Monitor Tables
|--------------------------------------------------------------------------
|
| This migration creates a brand-new database structure for the
| real-time pitch extraction module so it remains separate from
| the existing Sound Check / guitar-specific tables.
|
*/

return new class extends Migration
{
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | pitch_monitor_sessions
        |--------------------------------------------------------------------------
        |
        | Stores one microphone session per user.
        |
        */
        Schema::create('pitch_monitor_sessions', function (Blueprint $table) {
            $table->bigIncrements('session_id');

            // Matches your existing authenticated user ID
            $table->unsignedBigInteger('user_id');
            $table->index('user_id');

            // Optional source label if you want future flexibility
            $table->string('source_type')->default('microphone');

            // Start and end time of one monitoring session
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();

            $table->timestamps();

            $table->foreign('user_id')
                ->references('user_id')
                ->on('user_account')
                ->onDelete('cascade');
        });

        /*
        |--------------------------------------------------------------------------
        | pitch_monitor_events
        |--------------------------------------------------------------------------
        |
        | Stores the detected pitch events inside one session.
        | This is intentionally general-purpose and not guitar-specific.
        |
        */
        Schema::create('pitch_monitor_events', function (Blueprint $table) {
            $table->bigIncrements('event_id');

            $table->unsignedBigInteger('session_id');
            $table->index('session_id');

            // Example: A4, C#5, G3
            $table->string('note_name', 8);

            // Pitch frequency in Hz
            $table->decimal('frequency', 10, 2);

            // Difference from nearest tempered note
            $table->decimal('cents_deviation', 8, 2)->nullable();

            // Confidence from Essentia / processor
            $table->decimal('confidence', 8, 4)->nullable();

            // RMS / signal strength
            $table->decimal('rms', 10, 5)->nullable();

            // General status for UI badges
            $table->string('tuning_status', 20)->default('detected');

            // Exact time this event was captured
            $table->timestamp('detected_at');

            $table->timestamps();

            $table->foreign('session_id')
                ->references('session_id')
                ->on('pitch_monitor_sessions')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pitch_monitor_events');
        Schema::dropIfExists('pitch_monitor_sessions');
    }
};