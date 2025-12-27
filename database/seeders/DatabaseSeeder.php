<?php

//database/seeders/DatabaseSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
     public function run(): void
    {
        // REQUIRED: Lookup/Reference tables (must be seeded first)
        $this->call([
            InstrumentSeeder::class, // Seed instruments
            SpecializationSeeder::class, // Seed specializations
            GenreSeeder::class, // Seed genres
            StudentStatusSeeder::class, // Seed student statuses
            PaymentMethodSeeder::class, // Seed payment methods
            PaymentStatusSeeder::class, // Seed payment statuses
            ]);

        // OPTIONAL: Test/Sample data (uncomment if you want sample data)
        $this->call([
            UserAccountSeeder::class,        // Sample admin/users
        //     InstructorSeeder::class,         // Sample instructors
        //     SalesStaffSeeder::class,         // Sample sales staff
        //     AllAroundStaffSeeder::class,     // Sample all-around staff
            StudentSeeder::class,            // Sample students
        //     SupplierSeeder::class,           // Sample suppliers
        //     InventorySeeder::class,          // Sample inventory items
        //     LessonSessionSeeder::class,      // Sample lesson packages (5, 10, 20 sessions)
        //     EnrollmentSeeder::class,         // Sample enrollments
        //     ScheduleSeeder::class,           // Sample schedules
        //     BookingSeeder::class,            // Sample room bookings
        ]);
    }
}