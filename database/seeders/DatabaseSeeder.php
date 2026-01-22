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
     * DATABASE SEEDER - PROPER EXECUTION ORDER
     * ============================================================================
     * Seeds are organized by dependency level to avoid foreign key constraint errors.
     * Each level depends on the previous levels being completed first.
     * ============================================================================
     */
    public function run(): void
    {
        // ========================================================================
        // LEVEL 1: LOOKUP/REFERENCE TABLES (No dependencies)
        // These tables have no foreign keys and are referenced by other tables
        // ========================================================================
        $this->call([
            InstrumentSeeder::class,        // Referenced by: student
            SpecializationSeeder::class,    // Referenced by: instructor_specialization
            GenreSeeder::class,             // Referenced by: student
            StudentStatusSeeder::class,     // Referenced by: student
            PaymentMethodSeeder::class,     // Referenced by: payment
            PaymentStatusSeeder::class,     // Referenced by: payment
            RoomSeeder::class,              // Referenced by: schedule, booking
        ]);

        // ========================================================================
        // LEVEL 2: USER ACCOUNTS
        // Core authentication table that all user roles depend on
        // ========================================================================
        $this->call([
            UserAccountSeeder::class,       // Creates super admin and test users
        ]);

        // ========================================================================
        // LEVEL 3: INDEPENDENT TABLES
        // No foreign key dependencies (except possibly suppliers)
        // ========================================================================
        $this->call([
            SupplierSeeder::class,          // Referenced by: inventory
            LessonSessionSeeder::class,     // Referenced by: enrollment
        ]);

        // ========================================================================
        // LEVEL 4: USER ROLE TABLES
        // Depend on: user_account, and some lookup tables
        // ========================================================================
        $this->call([
            InstructorSeeder::class,        // Depends on: user_account
                                            // Referenced by: instructor_specialization, enrollment, schedule
            
            StudentSeeder::class,           // Depends on: user_account, student_status, instrument, genre
                                            // Referenced by: enrollment, schedule, payment, attendance
        ]);


        // ========================================================================
        // LEVEL 5: INVENTORY
        // Depends on: supplier
        // ========================================================================
        $this->call([
            InventorySeeder::class,         // Depends on: supplier
        ]);

        // ========================================================================
        // LEVEL 7: ENROLLMENTS
        // Depend on: student, instructor, lesson_session
        // ========================================================================
        $this->call([
            EnrollmentSeeder::class,        // Depends on: student, instructor, lesson_session
                                            // Referenced by: schedule, payment, progress
        ]);

        // ========================================================================
        // LEVEL 8: SCHEDULES
        // Depend on: enrollment, student, instructor
        // ========================================================================
        $this->call([
            ScheduleSeeder::class,          // Depends on: enrollment, student, instructor
                                            // Referenced by: attendance, progress
        ]);

        // ========================================================================
        // LEVEL 9: TRANSACTIONS & TRACKING
        // Depend on: enrollment, schedule, student
        // ========================================================================
        $this->call([
            PaymentSeeder::class,        // Depends on: student, enrollment, payment_method, payment_status
            AttendanceSeeder::class,     // Depends on: schedule, student, instructor, user_account
            ProgressSeeder::class,       // Depends on: student, enrollment, instructor, schedule
        ]);

        $this->command->info('✓ All seeders completed successfully!');
    }
}