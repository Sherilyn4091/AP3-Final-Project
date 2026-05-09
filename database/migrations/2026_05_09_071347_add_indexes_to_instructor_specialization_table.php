<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/*
|--------------------------------------------------------------------------
| Add Instructor Specialization Lookup Indexes
|--------------------------------------------------------------------------
|
| These indexes help when filtering instructors by specialization and when
| opening specialization details that list assigned instructors.
|
| This migration is safe to rerun because it uses CREATE INDEX IF NOT EXISTS.
|
*/

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            CREATE INDEX IF NOT EXISTS instructor_specialization_instructor_id_index
            ON instructor_specialization (instructor_id);
        ");

        DB::statement("
            CREATE INDEX IF NOT EXISTS instructor_specialization_specialization_id_index
            ON instructor_specialization (specialization_id);
        ");
    }

    public function down(): void
    {
        DB::statement("
            DROP INDEX IF EXISTS instructor_specialization_instructor_id_index;
        ");

        DB::statement("
            DROP INDEX IF EXISTS instructor_specialization_specialization_id_index;
        ");
    }
};