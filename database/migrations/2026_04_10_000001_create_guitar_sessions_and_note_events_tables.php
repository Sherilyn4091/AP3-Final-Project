<?php
# database/migrations/2026_04_10_000001_create_guitar_sessions_and_note_events_tables.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guitar_sessions', function (Blueprint $table) {
            $table->bigIncrements('session_id');
            $table->unsignedBigInteger('user_id');
            $table->index('user_id');
            $table->string('target_string', 4)->nullable();
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();

            $table->foreign('user_id')
                ->references('user_id')
                ->on('user_account')
                ->cascadeOnDelete();

            $table->index(['user_id', 'started_at']);
            $table->index('target_string');
        });

        Schema::create('guitar_note_events', function (Blueprint $table) {
            $table->bigIncrements('event_id');
            $table->unsignedBigInteger('session_id');
            $table->index('session_id');
            $table->string('note_name', 4);
            $table->decimal('frequency', 8, 2);
            $table->decimal('cents_deviation', 8, 2);
            $table->string('tuning_status', 10);
            $table->timestamp('detected_at');
            $table->timestamps();

            $table->foreign('session_id')
                ->references('session_id')
                ->on('guitar_sessions')
                ->cascadeOnDelete();

            $table->index(['session_id', 'detected_at']);
            $table->index(['session_id', 'tuning_status']);
            $table->index('note_name');
        });

        DB::statement("
            ALTER TABLE guitar_note_events
            ADD CONSTRAINT guitar_note_events_tuning_status_check
            CHECK (tuning_status IN ('flat', 'in_tune', 'sharp'))
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('guitar_note_events');
        Schema::dropIfExists('guitar_sessions');
    }
};