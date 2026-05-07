<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * database/migrations/2025_12_26_034812_create_rooms_table.php
     *
     * Run the migrations.
     *
     * Purpose:
     * - Creates the room table used by lesson schedules and room bookings.
     * - This table is required because:
     *   1. schedule.room_number references room.room_number
     *   2. booking.room_number references room.room_number
     *
     * Important:
     * - The table name is singular: room.
     * - This migration must run before schedules and bookings.
     */
    public function up(): void
    {
        DB::statement("
            CREATE TABLE public.room (
                room_id SERIAL PRIMARY KEY,

                room_number VARCHAR NOT NULL UNIQUE,
                room_name VARCHAR NULL,
                capacity INTEGER NULL,
                hourly_rate NUMERIC NULL,

                is_active BOOLEAN DEFAULT TRUE,

                created_at TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP
            );
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP TABLE IF EXISTS public.room;');
    }
};