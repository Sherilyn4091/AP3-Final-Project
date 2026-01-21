<?php

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
 * - Unique emails and employee IDs
 * - Filipino names using Faker
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
        $faker = Faker::create('en_PH'); // Filipino locale
        
        // Fetch all specializations from database
        $specializations = DB::table('specialization')
            ->where('is_active', true)
            ->pluck('specialization_name', 'specialization_id')
            ->toArray();
        
        if (empty($specializations)) {
            $this->command->error('❌ No specializations found! Please run migrations first.');
            return;
        }
        
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
            
            // Generate unique email with timestamp to avoid duplicates
            $timestamp = time() + $i;
            $email = strtolower($faker->firstName) . '.' . strtolower($faker->lastName) . $timestamp . '@musiclab.com';
            
            // Create user account
            $userId = DB::table('user_account')->insertGetId([
                'user_email' => $email,
                'user_password' => Hash::make('instructor123'),
                'is_super_admin' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ], 'user_id'); // ← Specify PK column name
            
            // Random experience and ratings
            $yearsExp = rand(1, 20);
            $rating = round(rand(35, 50) / 10, 1); // 3.5 to 5.0
            $totalStudents = rand(10, 150);
            
            // Random availability
            $isAvailable = rand(1, 10) > 2; // 80% available
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
                '4:00 PM - 10:00 PM'
            ];
            $preferredTime = $timeSlots[array_rand($timeSlots)];
            
            // Create instructor record
            $instructorId = DB::table('instructor')->insertGetId([
                'user_id' => $userId,
                'first_name' => $faker->firstName,
                'middle_name' => $faker->lastName,
                'last_name' => $faker->lastName,
                'phone' => '09' . rand(100000000, 999999999),
                'email' => $email,
                'address_line1' => $faker->streetAddress,
                'city' => $faker->randomElement(['Cebu City', 'Mandaue City', 'Lapu-Lapu City', 'Talisay City']),
                'province' => 'Cebu',
                'postal_code' => rand(6000, 6050),
                'country' => 'Philippines',
                'gender' => $faker->randomElement(['Male', 'Female']),
                'nationality' => 'Filipino',
                'emergency_contact_name' => $faker->name,
                'emergency_contact_relationship' => $faker->randomElement(['Spouse', 'Parent', 'Sibling', 'Friend']),
                'emergency_contact_phone' => '09' . rand(100000000, 999999999),
                'employee_id' => 'INST-2024-' . str_pad($employeeNumber, 3, '0', STR_PAD_LEFT),
                'hire_date' => now()->subMonths(rand(1, 48)),
                'employment_status' => 'active',
                'contract_type' => $contractType,
                'hourly_rate' => rand(600, 1200) + (rand(0, 1) * 50), // 600-1250 in 50 increments
                'monthly_salary' => null,
                'education_level' => $faker->randomElement([
                    'Bachelor of Music',
                    'Bachelor of Music Education',
                    'Bachelor of Fine Arts - Music',
                    'Master of Music',
                    'Diploma in Music Performance'
                ]),
                'music_degree' => $faker->randomElement([
                    'Bachelor of Music in Performance',
                    'BM Music Education',
                    'BFA Music Performance',
                    'Diploma in Contemporary Music',
                    'Bachelor of Arts in Music'
                ]),
                'certifications' => $faker->randomElement([
                    'ABRSM Grade 8, Certified Music Educator',
                    'Trinity College London Grade 8',
                    'RGT Guitar Grade 8',
                    'Certified Vocal Coach',
                    'Suzuki Method Certified',
                    'Yamaha Grade Examination System'
                ]),
                'years_of_experience' => $yearsExp,
                'teaching_style' => $faker->randomElement([
                    'Patient and encouraging, focuses on building fundamentals',
                    'Interactive and fun approach with creative expression',
                    'Technique-focused with emphasis on discipline',
                    'Modern and contemporary teaching methods',
                    'Classical foundation with modern repertoire',
                    'Performance-oriented with stage confidence training'
                ]),
                'bio' => $faker->sentence(15),
                'languages_spoken' => 'English, Tagalog, Cebuano',
                'is_available' => $isAvailable,
                'available_days' => $availableDays,
                'preferred_time_slots' => $preferredTime,
                'max_students_per_day' => rand(4, 8),
                'total_students_taught' => $totalStudents,
                'average_rating' => $rating,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ], 'instructor_id'); // ← Specify PK column name
            
            // Assign 1-2 random specializations
            $numSpecs = rand(1, 2);
            $chosenSpecs = $faker->randomElements(array_keys($specializations), $numSpecs);
            
            foreach ($chosenSpecs as $index => $specId) {
                DB::table('instructor_specialization')->insert([
                    'instructor_id' => $instructorId,
                    'specialization_id' => $specId,
                    'is_primary' => ($index === 0), // First one is primary
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            
            // Progress indicator every 10 instructors
            if (($i + 1) % 10 === 0) {
                $this->command->info("✓ Created " . ($i + 1) . " instructors...");
            }
        }
        
        $this->command->info('Successfully seeded 50 instructors with specializations!');
    }
}