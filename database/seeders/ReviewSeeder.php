<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * database/seeders/ReviewSeeder.php
 *
 * Purpose:
 * - Creates 100 safe demo reviews for Music Lab.
 * - Uses Filipino-style names.
 * - Review table has no foreign keys, so this seeder is independent.
 */
class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Safety Check
        |--------------------------------------------------------------------------
        |
        | Avoid duplicate demo reviews if this seeder is accidentally run again.
        */
        if (DB::table('review')->exists()) {
            $this->command->warn('Reviews already exist. ReviewSeeder skipped to avoid duplicates.');
            return;
        }

        $firstNames = [
            'Juan', 'Jose', 'Miguel', 'Angelo', 'Gabriel', 'Joshua', 'Christian', 'John', 'Mark', 'James',
            'Andres', 'Alden', 'Jacob', 'Nathaniel', 'Emmanuel', 'Daniel', 'Michael', 'Rafael', 'Luis', 'Carlos',
            'Maria', 'Angela', 'Althea', 'Andrea', 'Samantha', 'Angel', 'Princess', 'Cristina', 'Victoria', 'Isabella',
            'Sofia', 'Camilla', 'Nathalie', 'Jasmine', 'Mariel', 'Clarissa', 'Lourdes', 'Theresa', 'Rosa', 'Elena',
            'Sherilyn', 'Jessa', 'Katrina', 'Christine', 'Mary Grace', 'Nicole', 'Janine', 'Rhea', 'Mae', 'Joy',
        ];

        $lastNames = [
            'Dela Cruz', 'Reyes', 'Santos', 'Garcia', 'Cruz', 'Ramos', 'Mendoza', 'Torres', 'Flores', 'Rivera',
            'Gonzales', 'Bautista', 'Fernandez', 'Castro', 'Villanueva', 'Domingo', 'Aquino', 'Perez', 'Lopez', 'Salvador',
            'Hernandez', 'Martinez', 'Silva', 'De Guzman', 'Cabrera', 'Lim', 'Tan', 'Ong', 'Chua', 'Sy',
            'Go', 'Lee', 'Uy', 'Co', 'Yap', 'King', 'Chan', 'Young', 'Tee', 'Wee',
            'Sanchez', 'Navarro', 'Mercado', 'Rosales', 'Padilla', 'Villamor', 'Abella', 'Canete', 'Caballero', 'Labrador',
        ];

        $fiveStarReviews = [
            'Music Lab has a very friendly learning environment. The instructors are patient and explain lessons clearly.',
            'The one-on-one lessons helped me improve faster. I became more confident with my instrument.',
            'The lesson packages are worth it because each session is focused and organized.',
            'The instructors are professional and approachable. I enjoyed every practice session.',
            'Music Lab is a good place for beginners. The lessons are easy to follow and very helpful.',
            'The rooms are comfortable for practice. The staff also assisted us well.',
            'I learned proper technique and became more confident performing in front of others.',
            'The instructor gave clear feedback and realistic practice tasks after every session.',
            'The schedule was organized and the lesson flow was easy to understand.',
            'Music Lab helped me build discipline in practicing my instrument.',
        ];

        $fourStarReviews = [
            'The lessons are very helpful and the instructors are kind. I hope there will be more schedule options.',
            'Good learning experience overall. The instructor gave useful corrections during practice.',
            'The package is affordable for the quality of teaching. The room was also clean.',
            'I improved my timing and confidence. The lessons were practical and student-friendly.',
            'The staff and instructors were approachable. The learning pace was comfortable.',
            'Good place to learn music. I liked the one-on-one setup because I could ask questions easily.',
            'The lessons were organized and easy to understand. I would recommend Music Lab to other students.',
            'The instructor helped me understand the basics better. I am satisfied with the experience.',
            'The practice room and lesson setup were good. The booking process can still improve a little.',
            'Music Lab gave me a good foundation in my chosen instrument.',
        ];

        $threeStarReviews = [
            'The lesson was helpful, but I think I need more practice time to improve.',
            'The instructor was kind and patient. Some parts were challenging but manageable.',
            'Good experience, but I hope there will be more available time slots.',
            'The lesson was okay and the staff were approachable.',
            'I learned the basics, but I still need more sessions to become confident.',
        ];

        $rows = [];

        for ($i = 1; $i <= 100; $i++) {
            $firstName = $this->randomElement($firstNames);
            $lastName = $this->randomElement($lastNames);

            /*
            |--------------------------------------------------------------------------
            | Rating Distribution
            |--------------------------------------------------------------------------
            |
            | Most reviews are positive to match a demo/homepage testimonial section.
            */
            $ratingRoll = rand(1, 100);

            if ($ratingRoll <= 65) {
                $rating = 5;
                $reviewText = $this->randomElement($fiveStarReviews);
            } elseif ($ratingRoll <= 92) {
                $rating = 4;
                $reviewText = $this->randomElement($fourStarReviews);
            } else {
                $rating = 3;
                $reviewText = $this->randomElement($threeStarReviews);
            }

            $rows[] = [
                'reviewer_name' => $firstName . ' ' . $lastName,
                'rating' => $rating,
                'review_text' => $reviewText,
                'is_approved' => rand(1, 100) <= 95,
                'created_at' => now()->subDays(rand(0, 180)),
            ];
        }

        DB::table('review')->insert($rows);

        $this->command->info('Successfully seeded 100 Filipino-name-based reviews.');
    }

    /**
     * Small helper to keep random selection readable.
     */
    private function randomElement(array $items)
    {
        return $items[array_rand($items)];
    }
}