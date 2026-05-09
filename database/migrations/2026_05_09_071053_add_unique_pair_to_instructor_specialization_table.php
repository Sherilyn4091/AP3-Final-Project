<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/*
|--------------------------------------------------------------------------
| Add Unique Instructor-Specialization Pair
|--------------------------------------------------------------------------
|
| This prevents duplicate records like:
| - instructor_id = 1
| - specialization_id = 2
|
| The migration is written safely because the unique constraint/index already
| exists in your Supabase database. This prevents Laravel from failing again
| when php artisan migrate is executed.
|
*/

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            DO $$
            BEGIN
                IF to_regclass('public.instructor_specialization_unique_pair') IS NULL THEN
                    ALTER TABLE instructor_specialization
                    ADD CONSTRAINT instructor_specialization_unique_pair
                    UNIQUE (instructor_id, specialization_id);
                END IF;
            END
            $$;
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE instructor_specialization
            DROP CONSTRAINT IF EXISTS instructor_specialization_unique_pair;
        ");
    }
};