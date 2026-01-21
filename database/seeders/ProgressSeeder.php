<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * ============================================================================
 * PROGRESS SEEDER
 * ============================================================================
 * Generates 40 student progress records with:
 * - Proper FK relationships (student, enrollment, instructor, schedule)
 * - Realistic ratings (1-10) and feedback
 * - Homework assignments and practice recommendations
 * - Only for completed schedules
 * - Re-runnable without errors or duplicates
 * ============================================================================
 */
class ProgressSeeder extends Seeder
{
    public function run(): void
    {
        // Fetch completed schedules with all required relationships
        $schedules = DB::table('schedule as s')
            ->join('enrollment as e', 's.enrollment_id', '=', 'e.enrollment_id')
            ->select('s.schedule_id', 's.student_id', 'e.enrollment_id', 's.instructor_id', 's.schedule_date', 's.lesson_topic', 's.lesson_content')
            ->where('s.status', 'completed')
            ->whereNotNull('s.instructor_id')
            ->get();

        if ($schedules->isEmpty()) {
            $this->command->error('No completed schedules found. Run ScheduleSeeder first.');
            return;
        }

        // Get existing progress schedule IDs to avoid duplicates
        $existingScheduleIds = DB::table('progress')
            ->whereNotNull('schedule_id')
            ->pluck('schedule_id')
            ->toArray();

        // Filter out schedules that already have progress records
        $availableSchedules = $schedules->filter(function ($schedule) use ($existingScheduleIds) {
            return !in_array($schedule->schedule_id, $existingScheduleIds);
        });

        if ($availableSchedules->isEmpty()) {
            $this->command->warn('All completed schedules already have progress records. No new records created.');
            return;
        }

        // Number of progress records to create
        $count = min(40, $availableSchedules->count());

        $this->command->info("📊 Seeding {$count} student progress records...");

        $created = 0;
        foreach ($availableSchedules->take($count) as $schedule) {
            // Generate realistic ratings (bias toward 6-9)
            $performanceRating = rand(5, 10);
            $technicalRating = rand(5, 10);
            $musicalityRating = rand(5, 10);
            $effortRating = rand(6, 10); // Students usually show good effort

            DB::table('progress')->insert([
                'student_id' => $schedule->student_id,
                'enrollment_id' => $schedule->enrollment_id,
                'instructor_id' => $schedule->instructor_id,
                'schedule_id' => $schedule->schedule_id,
                'progress_date' => $schedule->schedule_date,
                'lesson_topic' => $schedule->lesson_topic ?? $this->randomTopic(),
                'skills_covered' => $this->randomSkills(),
                'techniques_learned' => $this->randomTechniques(),
                'songs_practiced' => $this->randomSongs(),
                'performance_rating' => $performanceRating,
                'technical_skills_rating' => $technicalRating,
                'musicality_rating' => $musicalityRating,
                'effort_rating' => $effortRating,
                'strengths' => $this->randomStrengths(),
                'areas_for_improvement' => $this->randomImprovements(),
                'instructor_notes' => $this->randomNotes(),
                'homework' => $this->randomHomework(),
                'practice_recommendations' => $this->randomPracticeRecs(),
                'next_lesson_focus' => $this->randomNextFocus(),
                'student_comments' => rand(1, 10) > 7 ? $this->randomStudentComment() : null,
                'student_satisfaction' => rand(3, 5), // 3-5 stars
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $created++;

            // Progress indicator
            if ($created % 10 === 0) {
                $this->command->info("✓ Created {$created} progress records...");
            }
        }

        $this->command->info("Successfully seeded {$created} student progress records!");
    }

    private function randomTopic(): string
    {
        $topics = ['Scales', 'Chords', 'Rhythm', 'Performance', 'Theory', 'Technique'];
        return $topics[array_rand($topics)];
    }

    private function randomSkills(): string
    {
        $skills = [
            'Major and minor scales, finger positioning',
            'Chord transitions, strumming patterns',
            'Rhythm exercises, tempo control',
            'Performance confidence, stage presence',
        ];
        return $skills[array_rand($skills)];
    }

    private function randomTechniques(): string
    {
        $techniques = [
            'Fingerstyle picking, palm muting',
            'Legato playing, vibrato control',
            'Breath control, vocal projection',
            'Dynamic control, articulation',
        ];
        return $techniques[array_rand($techniques)];
    }

    private function randomSongs(): ?string
    {
        $songs = [
            'Wonderwall - Oasis',
            'Let It Be - The Beatles',
            'Hallelujah - Leonard Cohen',
            'Thinking Out Loud - Ed Sheeran',
            null,
        ];
        return $songs[array_rand($songs)];
    }

    private function randomStrengths(): string
    {
        $strengths = [
            'Good sense of rhythm and timing',
            'Quick learner, excellent hand coordination',
            'Strong musical ear, good pitch recognition',
            'Dedicated practice routine, shows improvement',
        ];
        return $strengths[array_rand($strengths)];
    }

    private function randomImprovements(): string
    {
        $improvements = [
            'Work on finger strength and dexterity',
            'Practice chord transitions more smoothly',
            'Focus on tempo consistency with metronome',
            'Improve breath support and vocal control',
        ];
        return $improvements[array_rand($improvements)];
    }

    private function randomNotes(): string
    {
        $notes = [
            'Student is progressing well. Keep up the practice!',
            'Needs more focus on fundamentals before advancing.',
            'Excellent lesson! Ready to move to next level.',
            'Student showed great improvement this session.',
        ];
        return $notes[array_rand($notes)];
    }

    private function randomHomework(): string
    {
        $homework = [
            'Practice scales for 15 minutes daily',
            'Work on assigned chord progressions',
            'Memorize first verse and chorus of song',
            'Complete rhythm exercises in workbook',
        ];
        return $homework[array_rand($homework)];
    }

    private function randomPracticeRecs(): string
    {
        $recs = [
            'Practice with metronome at 60 BPM, gradually increase',
            'Focus on slow, deliberate practice for accuracy',
            'Record yourself and listen for areas to improve',
            'Practice in short, focused 20-minute sessions',
        ];
        return $recs[array_rand($recs)];
    }

    private function randomNextFocus(): string
    {
        $focus = [
            'Moving to intermediate-level techniques',
            'Introduction to new song repertoire',
            'Advanced rhythm patterns',
            'Performance preparation and stage confidence',
        ];
        return $focus[array_rand($focus)];
    }

    private function randomStudentComment(): string
    {
        $comments = [
            'Really enjoyed this lesson!',
            'Found the exercises challenging but helpful.',
            'Looking forward to next session.',
            'Appreciate the detailed feedback.',
        ];
        return $comments[array_rand($comments)];
    }
}