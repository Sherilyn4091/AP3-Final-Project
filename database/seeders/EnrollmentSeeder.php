<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * ============================================================================
 * ENROLLMENT SEEDER
 * ============================================================================
 * Generates 30 enrollments with:
 * - Auto-incremented enrollment_id based on last existing ID
 * - Proper FK relationships (student, lesson_session, instructor, payment_method)
 * - Realistic payment statuses and dates
 * - Re-runnable without errors
 * ============================================================================
 */
class EnrollmentSeeder extends Seeder
{
    public function run(): void
    {
        // Fetch required foreign keys
        $studentIds = DB::table('student')->pluck('student_id')->toArray();
        $sessionIds = DB::table('lesson_session')->pluck('session_id')->toArray();
        $instructorIds = DB::table('instructor')->pluck('instructor_id')->toArray();
        $paymentMethodIds = DB::table('payment_method')->pluck('method_id')->toArray();

        if (empty($studentIds)) {
            $this->command->error('No students found. Run StudentSeeder first.');
            return;
        }
        if (empty($sessionIds)) {
            $this->command->error('No lesson_session found. Run LessonSessionSeeder first.');
            return;
        }
        if (empty($instructorIds)) {
            $this->command->error('No instructors found. Run InstructorSeeder first.');
            return;
        }
        if (empty($paymentMethodIds)) {
            $this->command->error('No payment_method found. Check payment_method table.');
            return;
        }

        // Number of enrollments to create
        $count = 30;

        // Get current year-month for enrollment_id prefix
        $ym = now()->format('Y-m');

        // Find the highest sequence number for this month
        $maxSeq = DB::table('enrollment')
            ->where('enrollment_id', 'LIKE', $ym . '-%')
            ->selectRaw("MAX(CAST(SUBSTRING(enrollment_id FROM 9) AS BIGINT)) AS max_seq")
            ->value('max_seq');

        $seq = $maxSeq ? (int)$maxSeq + 1 : 1;

        $this->command->info("📚 Seeding {$count} enrollments starting from {$ym}-" . str_pad($seq, 7, '0', STR_PAD_LEFT) . "...");

        for ($i = 0; $i < $count; $i++) {
            $studentId = $studentIds[array_rand($studentIds)];
            $sessionId = $sessionIds[array_rand($sessionIds)];
            $instructorId = $instructorIds[array_rand($instructorIds)];
            $paymentMethodId = $paymentMethodIds[array_rand($paymentMethodIds)]; // Random payment method

            // Get session package details
            $pkg = DB::table('lesson_session')
                ->where('session_id', $sessionId)
                ->select('session_count', 'price')
                ->first();

            $totalSessions = (int)$pkg->session_count;
            $totalAmount = (float)$pkg->price;

            // Random completion progress
            $completed = rand(0, min(3, $totalSessions)); // 0-3 sessions completed
            $remaining = $totalSessions - $completed;

            // Random payment status (75% paid, 25% pending/partial)
            $rand = rand(1, 100);
            if ($rand <= 75) {
                $paymentStatus = 'paid';
                $amountPaid = $totalAmount;
            } elseif ($rand <= 90) {
                $paymentStatus = 'partial';
                $amountPaid = $totalAmount * 0.5;
            } else {
                $paymentStatus = 'pending';
                $amountPaid = 0.00;
            }

            // Random dates
            $enrollDate = Carbon::now()->subDays(rand(0, 60))->toDateString();
            $startDate = Carbon::parse($enrollDate)->addDays(rand(1, 5))->toDateString();
            $endDate = Carbon::parse($startDate)->addDays(rand(30, 90))->toDateString();

            // Generate enrollment_id: YYYY-MM-0000001
            $enrollmentId = $ym . '-' . str_pad((string)$seq, 7, '0', STR_PAD_LEFT);
            $seq++;

            DB::table('enrollment')->insert([
                'enrollment_id' => $enrollmentId,
                'student_id' => $studentId,
                'session_id' => $sessionId,
                'instructor_id' => $instructorId,
                'enrollment_date' => $enrollDate,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'total_sessions' => $totalSessions,
                'completed_sessions' => $completed,
                'remaining_sessions' => $remaining,
                'status' => $remaining > 0 ? 'active' : 'completed',
                'payment_status' => $paymentStatus,
                'payment_method_id' => $paymentMethodId, // NEW: Added payment method
                'total_amount' => $totalAmount,
                'amount_paid' => $amountPaid,
                'notes' => 'Demo enrollment - seeded',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Progress indicator
            if (($i + 1) % 10 === 0) {
                $this->command->info("✓ Created " . ($i + 1) . " enrollments...");
            }
        }

        $this->command->info("Successfully seeded {$count} enrollments!");
    }
}