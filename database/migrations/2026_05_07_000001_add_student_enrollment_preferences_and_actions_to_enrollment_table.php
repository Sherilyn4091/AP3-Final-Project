<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| Add Student Enrollment Preferences To Enrollment Table
|--------------------------------------------------------------------------
|
| These fields store the student's preferred lesson days and preferred
| lesson time for EACH specific enrollment/package.
|
| Important:
| - The student profile preferences are general preferences.
| - These enrollment preferences are package-specific.
| - This does NOT auto-create schedules.
| - Admin/Instructor still confirms the actual schedule later.
|
*/

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('enrollment', function (Blueprint $table) {
            if (!Schema::hasColumn('enrollment', 'preferred_lesson_days')) {
                $table->text('preferred_lesson_days')->nullable()->after('start_date');
            }

            if (!Schema::hasColumn('enrollment', 'preferred_lesson_time')) {
                $table->string('preferred_lesson_time', 255)->nullable()->after('preferred_lesson_days');
            }

            if (!Schema::hasColumn('enrollment', 'cancellation_reason')) {
                $table->text('cancellation_reason')->nullable()->after('notes');
            }

            if (!Schema::hasColumn('enrollment', 'cancelled_at')) {
                $table->timestamp('cancelled_at')->nullable()->after('cancellation_reason');
            }

            if (!Schema::hasColumn('enrollment', 'withdrawal_reason')) {
                $table->text('withdrawal_reason')->nullable()->after('cancelled_at');
            }

            if (!Schema::hasColumn('enrollment', 'withdrawal_requested_at')) {
                $table->timestamp('withdrawal_requested_at')->nullable()->after('withdrawal_reason');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enrollment', function (Blueprint $table) {
            if (Schema::hasColumn('enrollment', 'withdrawal_requested_at')) {
                $table->dropColumn('withdrawal_requested_at');
            }

            if (Schema::hasColumn('enrollment', 'withdrawal_reason')) {
                $table->dropColumn('withdrawal_reason');
            }

            if (Schema::hasColumn('enrollment', 'cancelled_at')) {
                $table->dropColumn('cancelled_at');
            }

            if (Schema::hasColumn('enrollment', 'cancellation_reason')) {
                $table->dropColumn('cancellation_reason');
            }

            if (Schema::hasColumn('enrollment', 'preferred_lesson_time')) {
                $table->dropColumn('preferred_lesson_time');
            }

            if (Schema::hasColumn('enrollment', 'preferred_lesson_days')) {
                $table->dropColumn('preferred_lesson_days');
            }
        });
    }
};