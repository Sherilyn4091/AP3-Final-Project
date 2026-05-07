<?php

// database/seeders/InstructorSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;

/**
 * ============================================================================
 * INSTRUCTOR SEEDER - 50 INSTRUCTORS
 * ============================================================================
 * Generates 50 instructors with:
 * - Filipino-name-based login/profile emails
 * - Matching user_account.user_email and instructor.email
 * - Unique employee IDs
 * - Proper FK relationships (user_account -> instructor -> instructor_specialization)
 * - Random specializations (1-2 per instructor)
 * - Varied availability, experience, and ratings
 * ============================================================================
 */
class InstructorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('en_PH');

        // Fetch all specializations from database
        $specializations = DB::table('specialization')
            ->where('is_active', true)
            ->pluck('specialization_name', 'specialization_id')
            ->toArray();

        if (empty($specializations)) {
            $this->command->error('No specializations found. Please run SpecializationSeeder first.');
            return;
        }

        // Common Filipino first names
        $firstNames = [
            'Juan', 'Jose', 'Miguel', 'Angelo', 'Gabriel', 'Joshua', 'Christian', 'John', 'Mark', 'James',
            'Andres', 'Alden', 'Jacob', 'Nathaniel', 'Emmanuel', 'Daniel', 'Michael', 'Rafael', 'Luis', 'Carlos',
            'Maria', 'Angela', 'Althea', 'Andrea', 'Samantha', 'Angel', 'Princess', 'Cristina', 'Victoria', 'Isabella',
            'Sofia', 'Camilla', 'Nathalie', 'Jasmine', 'Mariel', 'Clarissa', 'Lourdes', 'Theresa', 'Rosa', 'Elena',
            'Sherilyn', 'Jessa', 'Katrina', 'Christine', 'Mary Grace', 'Nicole', 'Janine', 'Rhea', 'Mae', 'Joy',
        ];

        // Common Filipino last names
        $lastNames = [
            'Dela Cruz', 'Reyes', 'Santos', 'Garcia', 'Cruz', 'Ramos', 'Mendoza', 'Torres', 'Flores', 'Rivera',
            'Gonzales', 'Bautista', 'Fernandez', 'Castro', 'Villanueva', 'Domingo', 'Aquino', 'Perez', 'Lopez', 'Salvador',
            'Hernandez', 'Martinez', 'Silva', 'De Guzman', 'Cabrera', 'Lim', 'Tan', 'Ong', 'Chua', 'Sy',
            'Go', 'Lee', 'Uy', 'Co', 'Yap', 'King', 'Chan', 'Young', 'Tee', 'Wee',
            'Sanchez', 'Navarro', 'Mercado', 'Rosales', 'Padilla', 'Villamor', 'Abella', 'Cañete', 'Caballero', 'Labrador',
        ];

        // Get the highest existing employee_id number to continue from there
        $lastEmployeeId = DB::table('instructor')
            ->where('employee_id', 'LIKE', 'INST-2024-%')
            ->orderByRaw("CAST(SUBSTRING(employee_id FROM 11) AS INTEGER) DESC")
            ->value('employee_id');

        $startNumber = 1;

        if ($lastEmployeeId) {
            // Extract number from "INST-2024-XXX" format
            $startNumber = intval(substr($lastEmployeeId, -3)) + 1;
        }

        $this->command->info("🎵 Seeding 50 instructors starting from INST-2024-" . str_pad($startNumber, 3, '0', STR_PAD_LEFT) . "...");

        for ($i = 0; $i < 50; $i++) {
            $employeeNumber = $startNumber + $i;

            /*
            |--------------------------------------------------------------------------
            | Filipino Instructor Name
            |--------------------------------------------------------------------------
            |
            | Names are generated from Filipino-style name arrays.
            | The same name is used to create the login/profile email.
            |
            */
            $firstName = $faker->randomElement($firstNames);
            $middleName = $faker->optional(0.7)->randomElement($lastNames);
            $lastName = $faker->randomElement($lastNames);

            /*
            |--------------------------------------------------------------------------
            | Instructor Login Email
            |--------------------------------------------------------------------------
            |
            | IMPORTANT:
            | user_account.user_email and instructor.email must be the same.
            |
            | Example:
            | maria.santos.instructor1@musiclab.com
            |
            */
            $email = $this->makeFilipinoEmail($firstName, $lastName, $employeeNumber, 'instructor');

            // Create user account
            $userId = DB::table('user_account')->insertGetId([
                'user_email' => $email,
                'user_password' => Hash::make('instructor123'),
                'is_super_admin' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ], 'user_id');

            // Random experience and ratings
            $yearsExp = rand(1, 20);
            $rating = round(rand(35, 50) / 10, 1);
            $totalStudents = rand(10, 150);

            // Random availability
            $isAvailable = rand(1, 10) > 2;
            $contractTypes = ['part-time', 'full-time', 'contract', 'freelance'];
            $contractType = $contractTypes[array_rand($contractTypes)];

            // Days of week
            $allDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
            $numDays = rand(2, 5);
            $availableDays = implode(', ', $faker->randomElements($allDays, $numDays));

            // Time slots
            $timeSlots = [
                '9:00 AM - 3:00 PM',
                '1:00 PM - 7:00 PM',
                '2:00 PM - 8:00 PM',
                '3:00 PM - 9:00 PM',
                '10:00 AM - 4:00 PM',
                '4:00 PM - 10:00 PM',
            ];
            $preferredTime = $timeSlots[array_rand($timeSlots)];

            // Create instructor record
            $instructorId = DB::table('instructor')->insertGetId([
                'user_id' => $userId,

                // Personal Information
                'first_name' => $firstName,
                'middle_name' => $middleName,
                'last_name' => $lastName,
                'suffix' => null,

                // Contact Information
                'phone' => '09' . rand(100000000, 999999999),
                'email' => $email,

                // Address
                'address_line1' => $faker->streetAddress,
                'address_line2' => null,
                'city' => $faker->randomElement(['Cebu City', 'Mandaue City', 'Lapu-Lapu City', 'Talisay City']),
                'province' => 'Cebu',
                'postal_code' => rand(6000, 6050),
                'country' => 'Philippines',

                // Personal Details
                'date_of_birth' => $faker->dateTimeBetween('-55 years', '-24 years')->format('Y-m-d'),
                'gender' => $faker->randomElement(['Male', 'Female']),
                'nationality' => 'Filipino',

                // Emergency Contact
                'emergency_contact_name' => $faker->randomElement($firstNames) . ' ' . $faker->randomElement($lastNames),
                'emergency_contact_relationship' => $faker->randomElement(['Spouse', 'Parent', 'Sibling', 'Friend']),
                'emergency_contact_phone' => '09' . rand(100000000, 999999999),

                // Employment Information
                'employee_id' => 'INST-2024-' . str_pad($employeeNumber, 3, '0', STR_PAD_LEFT),
                'hire_date' => now()->subMonths(rand(1, 48))->toDateString(),
                'employment_status' => 'active',
                'contract_type' => $contractType,
                'hourly_rate' => rand(600, 1200) + (rand(0, 1) * 50),
                'monthly_salary' => null,

                // Professional Background
                'education_level' => $faker->randomElement([
                    'Bachelor of Music',
                    'Bachelor of Music Education',
                    'Bachelor of Fine Arts - Music',
                    'Master of Music',
                    'Diploma in Music Performance',
                ]),
                'music_degree' => $faker->randomElement([
                    'Bachelor of Music in Performance',
                    'BM Music Education',
                    'BFA Music Performance',
                    'Diploma in Contemporary Music',
                    'Bachelor of Arts in Music',
                ]),
                'certifications' => $faker->randomElement([
                    'ABRSM Grade 8, Certified Music Educator',
                    'Trinity College London Grade 8',
                    'RGT Guitar Grade 8',
                    'Certified Vocal Coach',
                    'Suzuki Method Certified',
                    'Yamaha Grade Examination System',
                ]),
                'years_of_experience' => $yearsExp,
                'teaching_style' => $faker->randomElement([
                    'Patient and encouraging, focuses on building fundamentals',
                    'Interactive and fun approach with creative expression',
                    'Technique-focused with emphasis on discipline',
                    'Modern and contemporary teaching methods',
                    'Classical foundation with modern repertoire',
                    'Performance-oriented with stage confidence training',
                ]),
                'bio' => $faker->randomElement([
                    'Experienced music instructor with strong passion for helping students build confidence and skill.',
                    'Dedicated teacher focused on proper technique, consistent practice, and musical expression.',
                    'Patient and supportive instructor who helps students learn at their own pace.',
                    'Performance-oriented music mentor with experience in guiding beginners and intermediate students.',
                    'Creative instructor who combines music theory, practical exercises, and enjoyable song practice.',
                ]),
                'languages_spoken' => 'English, Tagalog, Cebuano',

                // Availability and Metrics
                'is_available' => $isAvailable,
                'available_days' => $availableDays,
                'preferred_time_slots' => $preferredTime,
                'max_students_per_day' => rand(4, 8),
                'total_students_taught' => $totalStudents,
                'average_rating' => $rating,
                'is_active' => true,
                'notes' => null,

                // System Fields
                'created_at' => now(),
                'updated_at' => now(),
            ], 'instructor_id');

            // Assign 1-2 random specializations
            $numSpecs = rand(1, 2);
            $chosenSpecs = $faker->randomElements(array_keys($specializations), $numSpecs);

            foreach ($chosenSpecs as $index => $specId) {
                DB::table('instructor_specialization')->insert([
                    'instructor_id' => $instructorId,
                    'specialization_id' => $specId,
                    'is_primary' => ($index === 0),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Progress indicator every 10 instructors
            if (($i + 1) % 10 === 0) {
                $this->command->info("✓ Created " . ($i + 1) . " instructors...");
            }
        }

        $this->command->info('✓ Successfully seeded 50 instructors with matching Filipino-name-based login/profile emails.');
    }

    /**
     * Create a clean Filipino-name-based email.
     *
     * Example:
     * maria.santos.instructor1@musiclab.com
     */
    private function makeFilipinoEmail(string $firstName, string $lastName, int $number, string $role): string
    {
        $cleanFirstName = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $firstName));
        $cleanLastName = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $lastName));

        return $cleanFirstName . '.' . $cleanLastName . '.' . $role . $number . '@musiclab.com';
    }
}