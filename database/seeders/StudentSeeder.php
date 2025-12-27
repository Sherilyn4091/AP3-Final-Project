<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        // CRITICAL: Use ONLY en_US for number/date generation and random selection
        // NEVER use paragraph(), sentence(), or words() methods - they generate Lorem Ipsum Latin text
        $faker = fake('en_US');

        // Get existing lookup IDs
        $studentStatusIds = DB::table('student_status')->pluck('status_id')->toArray();
        $instrumentIds = DB::table('instrument')->pluck('instrument_id')->toArray();
        $genreIds = DB::table('genre')->pluck('genre_id')->toArray();

        // Realistic Philippine streets (manual array for consistency)
        $streets = [
            'Rizal Avenue', 'EDSA', 'Ayala Avenue', 'Quezon Avenue', 'Roxas Boulevard', 'Taft Avenue',
            'Magsaysay Boulevard', 'España Boulevard', 'Commonwealth Avenue', 'Katipunan Avenue',
            'Ortigas Avenue', 'Shaw Boulevard', 'Aurora Boulevard', 'Marcos Highway', 'C5 Road',
            'MacArthur Highway', 'Bonifacio Street', 'Luna Street', 'Mabini Street', 'Del Pilar Street',
            'Legarda Street', 'Recto Avenue', 'Blumentritt Street', 'Dimasalang Street', 'Lacson Avenue',
            'Timog Avenue', 'Kamuning Road', 'Tomas Morato Avenue', 'West Avenue', 'Visayas Avenue',
            'Mindanao Avenue', 'Congressional Avenue', 'Banawe Street', 'Retiro Street', 'Araneta Avenue',
            'Cubao', 'Alabang-Zapote Road', 'Alabang', 'SLEX', 'NLEX', 'Skyway',
            'Osmeña Highway', 'Quirino Avenue', 'Pedro Gil Street', 'Padre Faura Street', 'Kalaw Avenue',
        ];

        // Realistic Philippine schools (manual array)
        $schools = [
            'Ateneo de Manila University', 'University of the Philippines', 'De La Salle University',
            'University of Santo Tomas', 'Far Eastern University', 'Adamson University',
            'Mapua University', 'Polytechnic University of the Philippines', 'National University',
            'Philippine Normal University', 'Manila Central University', 'Jose Rizal University',
            'Arellano University', 'Centro Escolar University', 'Lyceum of the Philippines University',
            'San Beda University', 'University of the East', 'Technological Institute of the Philippines',
            'Rizal High School', 'Quezon City Science High School', 'Manila Science High School',
            'Pasig City Science High School', 'Makati Science High School', 'Valenzuela City Science High School',
            'Marikina Science High School', 'Taguig Science High School', 'Philippine Science High School',
            'St. Paul College', 'St. Scholastica\'s College', 'Assumption College', 'Miriam College',
            'La Salle Greenhills', 'Xavier School', 'Immaculate Conception Academy', 'St. Jude Catholic School',
            'Colegio de San Juan de Letran', 'San Sebastian College', 'Holy Angel University',
            'Angeles University Foundation', 'University of Cebu', 'Silliman University', 'University of San Carlos',
        ];

        // Philippine cities (manually curated for authentic data)
        $cities = [
            'Manila', 'Quezon City', 'Makati', 'Pasig', 'Taguig', 'Mandaluyong', 'San Juan', 'Marikina',
            'Parañaque', 'Las Piñas', 'Muntinlupa', 'Valenzuela', 'Malabon', 'Navotas', 'Caloocan',
            'Pasay', 'Pateros', 'Cebu City', 'Davao City', 'Cagayan de Oro', 'Iloilo City', 'Bacolod',
            'Baguio', 'Antipolo', 'Cavite City', 'Bacoor', 'Imus', 'Dasmariñas', 'Tagaytay'
        ];

        // Philippine provinces (manually curated)
        $provinces = [
            'Metro Manila', 'Rizal', 'Cavite', 'Laguna', 'Bulacan', 'Pampanga', 'Batangas',
            'Cebu', 'Davao del Sur', 'Misamis Oriental', 'Iloilo', 'Negros Occidental',
            'Benguet', 'Pangasinan', 'Nueva Ecija', 'Tarlac', 'Zambales'
        ];

        // Common Filipino first names (manually curated for authenticity)
        $firstNames = [
            'Juan', 'Jose', 'Miguel', 'Angelo', 'Gabriel', 'Joshua', 'Christian', 'John', 'Mark', 'James',
            'Andres', 'Alden', 'Jacob', 'Nathaniel', 'Emmanuel', 'Daniel', 'Michael', 'Rafael', 'Luis', 'Carlos',
            'Maria', 'Angela', 'Althea', 'Andrea', 'Samantha', 'Angel', 'Princess', 'Cristina', 'Victoria', 'Isabella',
            'Sofia', 'Camilla', 'Nathalie', 'Jasmine', 'Mariel', 'Clarissa', 'Lourdes', 'Theresa', 'Rosa', 'Elena'
        ];

        // Common Filipino last names (manually curated for authenticity)
        $lastNames = [
            'Dela Cruz', 'Reyes', 'Santos', 'Garcia', 'Cruz', 'Ramos', 'Mendoza', 'Torres', 'Flores', 'Rivera',
            'Gonzales', 'Bautista', 'Fernandez', 'Castro', 'Villanueva', 'Domingo', 'Aquino', 'Perez', 'Lopez', 'Salvador',
            'Hernandez', 'Martinez', 'Silva', 'De Guzman', 'Cabrera', 'Lim', 'Tan', 'Ong', 'Chua', 'Sy',
            'Go', 'Lee', 'Uy', 'Co', 'Yap', 'King', 'Chan', 'Young', 'Tee', 'Wee'
        ];

        // PRE-DEFINED PURE ENGLISH TEXT ARRAYS (NO FAKER TEXT GENERATION)
        
        // Previous Music Experience - 15 realistic English samples
        $musicExperiences = [
            'Started learning piano at age 8 through school music program. Participated in several school recitals and local competitions. Comfortable reading sheet music and playing basic classical pieces.',
            'Self-taught guitarist with 2 years of experience. Learned primarily through online tutorials and can play basic chords and simple songs. Looking to improve technique and expand repertoire.',
            'Complete beginner with no prior musical experience. Very interested in learning and dedicated to practice. Attended several music appreciation workshops at school.',
            'Played drums in church band for 3 years. Familiar with basic rhythms and beats. Can keep steady tempo and play along with other musicians.',
            'Learned violin through private lessons for 1 year. Can read basic sheet music and play simple melodies. Stopped lessons but now ready to continue learning.',
            'Former member of school choir. Good sense of pitch and rhythm. No instrumental experience but strong musical foundation and sight-reading ability.',
            'Played keyboard as a hobby for 2 years. Familiar with major and minor scales. Can play by ear and improvise simple melodies.',
            'Participated in school band playing clarinet for 2 years. Understands basic music theory and can sight-read. Transitioning to learn a new instrument.',
            'No formal training but loves music and practices singing regularly. Strong interest in learning proper vocal techniques and music fundamentals.',
            'Took guitar lessons for 6 months several years ago. Remembers basic chords but needs refresher on technique. Eager to continue learning and improve skills.',
            'Played piano recreationally for family gatherings. Can read basic sheet music but wants formal training to improve technique and learn proper methods.',
            'Studied music theory in school and participated in ensemble performances. Good understanding of rhythm and melody but limited instrumental practice.',
            'Learned bass guitar by watching videos and playing along with favorite songs. Can follow chord progressions and wants to learn proper technique.',
            'Took group music classes for one year. Familiar with basic concepts but wants one-on-one instruction to develop skills more effectively.',
            'Has natural musical talent and good ear for music. Learns quickly by listening but wants to understand music theory and proper technique.'
        ];

        // Music Goals - 15 realistic English samples
        $musicGoals = [
            'Want to perform confidently at school events and family gatherings. Goal is to build a repertoire of popular songs and improve sight-reading ability. Also interested in learning music composition basics.',
            'Aspire to join a band and perform with other musicians. Want to develop strong technical skills and learn various music genres including rock, pop, and jazz.',
            'Looking to pursue music as a serious hobby and possibly perform at local venues. Goal is to achieve intermediate level proficiency and understand music theory deeply.',
            'Want to learn enough to teach music to others someday. Interested in developing well-rounded musical skills including performance, theory, and ear training.',
            'Aim to play favorite songs confidently and maybe start writing original music. Want to improve improvisation skills and learn to play by ear more effectively.',
            'Goal is to pass music grade examinations and build strong foundation for potential music career. Want to master both classical and contemporary styles.',
            'Want to develop music skills for personal enjoyment and stress relief. Interested in learning relaxing pieces and improving overall musicality.',
            'Aspire to perform at church services and community events. Want to use musical talents to serve others while continuously improving technique and expression.',
            'Looking to join school music ensembles and participate in competitions. Goal is to achieve advanced level and possibly pursue music scholarship for college.',
            'Want to record cover songs and potentially create YouTube content. Interested in learning music production basics alongside instrumental skills.',
            'Planning to audition for school talent shows and local performance opportunities. Want to build confidence and stage presence through regular practice and lessons.',
            'Interested in exploring different music genres and finding personal style. Goal is to become versatile musician who can adapt to various musical contexts.',
            'Want to play music professionally at weddings and events someday. Need to build extensive repertoire and master different performance techniques.',
            'Looking to collaborate with other musicians and form a band. Want to understand ensemble playing and develop skills for group musical performances.',
            'Aspire to compose original music and understand music production. Want solid foundation in theory and technique to support creative musical endeavors.'
        ];

        // Secondary Instruments - realistic English instrument combinations
        $secondaryInstruments = [
            'Piano, Vocals', 'Drums, Percussion', 'Guitar, Ukulele', 'Bass, Guitar', 'Keyboard, Synthesizer',
            'Violin, Viola', 'Vocals, Piano', 'Percussion, Drums', 'Ukulele, Guitar', 'Flute, Recorder',
            'Saxophone, Clarinet', 'Trumpet, Cornet', 'Guitar, Bass', 'Piano, Organ', 'Vocals, Guitar'
        ];

        // Medical Conditions - realistic, non-sensitive samples (70% null chance)
        $medicalConditions = [
            null, null, null, null, null, null, null, // 70% null
            'Mild asthma, controlled with medication',
            'Seasonal allergies, no major impact',
            'Wears eyeglasses for nearsightedness',
            'History of minor ear infections, fully resolved',
            'Takes daily vitamins for general health',
            'Occasional headaches, managed with rest',
            'Minor back pain, uses proper posture support'
        ];

        // Allergies - realistic samples (80% null chance)
        $allergies = [
            null, null, null, null, null, null, null, null, // 80% null
            'Mild pollen allergies during summer',
            'Dust sensitivity, manageable',
            'Food allergy to shellfish',
            'Pet dander sensitivity',
            'Mild lactose intolerance'
        ];

        // Special Needs - realistic samples (90% null chance)
        $specialNeeds = [
            null, null, null, null, null, null, null, null, null, // 90% null
            'Prefers written instructions along with verbal',
            'Benefits from extra practice time',
            'Needs frequent breaks during lessons',
            'Learns best with visual demonstrations'
        ];

        // Generate 50 students
        foreach (range(1, 50) as $i) {
            // Create user account for the student
            $userId = DB::table('user_account')->insertGetId([
                'user_email' => $faker->unique()->safeEmail,
                'user_password' => Hash::make('password'),
                'is_super_admin' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ], 'user_id');

            // Generate exactly 11-digit Philippine mobile numbers (09xxxxxxxxx)
            $phone = '09' . $faker->numerify('#########');
            $emergencyPhone = '09' . $faker->numerify('#########');
            $parentPhone = '09' . $faker->numerify('#########');

            // Philippine location data from manual arrays
            $city = $faker->randomElement($cities);
            $province = $faker->randomElement($provinces);
            $postalCode = $faker->numerify('####'); // Simple 4-digit postal

            // Streets and schools from manual arrays
            $street = $faker->randomElement($streets);
            $school = $faker->randomElement($schools);

            // Build realistic address
            $buildingNumber = $faker->numberBetween(1, 999);
            $addressLine1 = $buildingNumber . ' ' . $street;
            $addressLine2 = $faker->optional(0.3)->secondaryAddress;

            // Date of birth: student age 10-25 years, format "Month day, Year"
            $dob = $faker->dateTimeBetween('-25 years', '-10 years')->format('F j, Y');

            // Preferred lesson days: comma-separated
            $preferredDaysArray = $faker->randomElements(
                ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'], 
                $faker->numberBetween(2, 5)
            );
            $preferredDays = implode(', ', $preferredDaysArray);

            // Preferred lesson time: realistic 1-2 hour slot
            $startHour = $faker->numberBetween(8, 17);
            $duration = $faker->randomElement([1, 1.5, 2]);
            $endHour = $startHour + $duration;
            $startTime = sprintf('%02d:00', $startHour);
            $endTime = $duration == 1.5 ? sprintf('%02d:30', $startHour + 1) : sprintf('%02d:00', (int)$endHour);
            $preferredTime = Carbon::createFromFormat('H:i', $startTime)->format('g:i A') . ' - ' .
                             Carbon::createFromFormat('H:i', $endTime)->format('g:i A');

            // Student name: authentic Filipino
            $firstName = $faker->randomElement($firstNames);
            $middleName = $faker->optional(0.7)->randomElement($firstNames);
            $lastName = $faker->randomElement($lastNames);

            // Guardian/Parent: authentic Filipino name + realistic email
            $parentFirstName = $faker->randomElement($firstNames);
            $parentLastName = $faker->randomElement($lastNames);
            $parentFullName = $parentFirstName . ' ' . $parentLastName;
            $parentEmail = strtolower(
                str_replace(' ', '.', $parentFirstName) . '.' . 
                str_replace(' ', '.', $parentLastName) . '@example.com'
            );

            // Emergency contact: authentic Filipino name
            $emergencyFirstName = $faker->randomElement($firstNames);
            $emergencyLastName = $faker->randomElement($lastNames);
            $emergencyFullName = $emergencyFirstName . ' ' . $emergencyLastName;

            // Generate realistic enrollment timeline (2023 onwards)
            $enrollmentDateObj = Carbon::instance($faker->dateTimeBetween('2023-01-01', 'now'));

            // Simulate short completion: 4-12 weeks after enrollment
            $weeksToComplete = $faker->numberBetween(4, 12);
            $completionDateObj = $enrollmentDateObj->copy()->addWeeks($weeksToComplete);

            // Display dates in "Month day, Year" format
            $enrollmentDateDisplay = $enrollmentDateObj->format('F j, Y');
            $expectedCompletionDateDisplay = $completionDateObj->format('F j, Y');

            // CRITICAL: Select from pre-defined PURE ENGLISH arrays (NO FAKER METHODS)
            $selectedExperience = $faker->randomElement($musicExperiences);
            $selectedGoals = $faker->randomElement($musicGoals);
            $selectedSecondaryInstruments = $faker->optional(0.4)->randomElement($secondaryInstruments);
            $selectedMedical = $faker->randomElement($medicalConditions);
            $selectedAllergies = $faker->randomElement($allergies);
            $selectedSpecialNeeds = $faker->randomElement($specialNeeds);

            // Insert student record
            DB::table('student')->insert([
                'user_id' => $userId,

                // Personal Information
                'first_name' => $firstName,
                'middle_name' => $middleName,
                'last_name' => $lastName,
                'suffix' => $faker->optional(0.05)->suffix,

                // Contact
                'phone' => $phone,
                'email' => $faker->safeEmail,

                // Address
                'address_line1' => $addressLine1,
                'address_line2' => $addressLine2,
                'city' => $city,
                'province' => $province,
                'postal_code' => $postalCode,
                'country' => 'Philippines',

                // Personal Details
                'date_of_birth' => $dob,
                'gender' => $faker->randomElement(['Male', 'Female', 'Other', 'Prefer not to say']),
                'nationality' => 'Filipino',

                // Emergency Contact - authentic Filipino name
                'emergency_contact_name' => $emergencyFullName,
                'emergency_contact_relationship' => $faker->randomElement(['Parent', 'Guardian', 'Sibling', 'Relative']),
                'emergency_contact_phone' => $emergencyPhone,

                // Guardian/Parent Information - authentic Filipino name + realistic email
                'parent_guardian_name' => $parentFullName,
                'parent_guardian_relationship' => $faker->randomElement(['Father', 'Mother', 'Guardian']),
                'parent_guardian_phone' => $parentPhone,
                'parent_guardian_email' => $parentEmail,
                'parent_guardian_address' => $addressLine1 . ', ' . $city . ', ' . $province . ', Philippines',

                // Musical Background - PURE ENGLISH from pre-defined arrays
                'instrument_id' => $faker->randomElement($instrumentIds),
                'secondary_instruments' => $selectedSecondaryInstruments,
                'previous_music_experience' => $selectedExperience,
                'skill_level' => $faker->randomElement(['beginner', 'intermediate', 'advanced', 'expert']),
                'music_goals' => $selectedGoals,

                // Educational Background
                'school_name' => $school,
                'grade_level' => $faker->randomElement(['Grade 7', 'Grade 8', 'Grade 9', 'Grade 10', 'Grade 11', 'Grade 12', 'College']),

                // Medical Information - PURE ENGLISH from pre-defined arrays
                'medical_conditions' => $selectedMedical,
                'allergies' => $selectedAllergies,
                'special_needs' => $selectedSpecialNeeds,

                // Enrollment Information
                'enrollment_date' => $enrollmentDateDisplay,
                'expected_completion_date' => $expectedCompletionDateDisplay,
                'student_status_id' => $faker->randomElement($studentStatusIds),
                'preferred_genre_id' => $faker->randomElement($genreIds),

                // Preferences
                'preferred_lesson_days' => $preferredDays,
                'preferred_lesson_time' => $preferredTime,

                // System Fields
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}