<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("CREATE UNIQUE INDEX IF NOT EXISTS idx_schedule_conflict_student ON schedule(student_id, schedule_date, start_time) WHERE status NOT IN ('cancelled', 'no_class', 'rescheduled')");
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS idx_schedule_conflict_student');
    }
};
