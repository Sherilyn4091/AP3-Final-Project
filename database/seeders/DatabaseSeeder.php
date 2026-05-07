<?php

// database/seeders/DatabaseSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * ============================================================================
     * DATABASE SEEDER - FINAL MUSIC LAB ORDER
     * ============================================================================
     *
     * Purpose:
     * - Runs seeders in the correct foreign-key order.
     * - Prevents errors where a child table is seeded before its parent table.
     *
     * Important relationship flow:
     * 1. Instruments and specializations must exist before instructors/enrollments.
     * 2. Lesson packages must exist before enrollments.
     * 3. Instructors and students must exist before enrollments.
     * 4. Enrollments must exist before schedules, payments, and progress.
     * 5. Schedules must exist before attendance and progress.
     *
     * ============================================================================
     */
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Level 1: Lookup / reference tables
        |--------------------------------------------------------------------------
        |
        | These tables have no major parent dependencies.
        | Other tables will reference these values.
        |
        */
        $this->call([
            InstrumentSeeder::class,        // Official lessons: Guitar, Bass, Keyboard, Drums, Ukulele, Violin, Voice
            SpecializationSeeder::class,    // Must match instruments for instructor filtering
            GenreSeeder::class,             // Used by student profile/preference
            StudentStatusSeeder::class,     // Used by student records
            PaymentMethodSeeder::class,     // Used by enrollment/payment
            PaymentStatusSeeder::class,     // Used by payment records
            RoomSeeder::class,              // Used by schedule and booking
        ]);

        /*
        |--------------------------------------------------------------------------
        | Level 2: Authentication account
        |--------------------------------------------------------------------------
        |
        | Creates the super admin account.
        | Student and instructor accounts are created by their own seeders.
        |
        */
        $this->call([
            UserAccountSeeder::class,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Level 3: Independent business data
        |--------------------------------------------------------------------------
        |
        | Suppliers are used by inventory.
        | Lesson sessions/packages are used by enrollment.
        |
        */
        $this->call([
            SupplierSeeder::class,
            LessonSessionSeeder::class,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Level 4: User role tables
        |--------------------------------------------------------------------------
        |
        | InstructorSeeder:
        | - creates user_account rows for instructors
        | - creates instructor rows
        | - assigns instructor specializations
        |
        | StudentSeeder:
        | - creates user_account rows for students
        | - creates student profile rows
        |
        */
        $this->call([
            InstructorSeeder::class,
            StudentSeeder::class,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Level 5: Inventory
        |--------------------------------------------------------------------------
        |
        | Inventory depends on supplier data.
        |
        */
        $this->call([
            InventorySeeder::class,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Level 6: Enrollments
        |--------------------------------------------------------------------------
        |
        | Enrollment depends on:
        | - student
        | - instructor
        | - instrument
        | - lesson_session
        | - payment_methods
        |
        | This is the table that connects:
        | student + instrument + instructor + package
        |
        */
        $this->call([
            EnrollmentSeeder::class,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Level 7: Schedules
        |--------------------------------------------------------------------------
        |
        | Schedule depends on enrollment.
        | Each schedule must match the enrollment's student and instructor.
        |
        */
        $this->call([
            ScheduleSeeder::class,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Level 8: Transactions and tracking
        |--------------------------------------------------------------------------
        |
        | Payment depends on enrollment and student.
        | Attendance depends on schedule.
        | Progress depends on schedule and enrollment.
        |
        */
        $this->call([
            PaymentSeeder::class,
            BookingSeeder::class,
            AttendanceSeeder::class,
            ProgressSeeder::class,
            ReviewSeeder::class,
        ]);

        $this->command->info('All Music Lab seeders completed successfully.');
    }
}