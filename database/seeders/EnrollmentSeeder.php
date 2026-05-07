<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * ============================================================================
 * ENROLLMENT SEEDER
 * ============================================================================
 *
 * Creates demo enrollments that follow Music Lab's real rule:
 * - One student can have many enrollments.
 * - Each enrollment has one instrument.
 * - Each enrollment has one instructor.
 * - The instructor should be connected to the instrument specialization.
 *
 * Example:
 * Angel Flores -> Guitar -> Instructor A
 * Angel Flores -> Keyboard -> Instructor B
 * ============================================================================
 */
class EnrollmentSeeder extends Seeder
{
    public function run(): void
    {
        $students = DB::table('student')
            ->select('student_id', 'enrollment_date')
            ->orderBy('student_id')
            ->get();

        $packages = DB::table('lesson_session')
            ->select('session_id', 'session_count', 'price')
            ->where('is_active', true)
            ->orderBy('session_count')
            ->get();

        $instruments = DB::table('instrument')
            ->select('instrument_id', 'instrument_name')
            ->where('is_active', true)
            ->orderBy('instrument_name')
            ->get();

        $paymentMethodIds = DB::table('payment_methods')
            ->pluck('method_id')
            ->toArray();

        /*
        |--------------------------------------------------------------------------
        | Required Data Checks
        |--------------------------------------------------------------------------
        |
        | This prevents confusing errors if another seeder was not run first.
        |
        */
        if ($students->isEmpty()) {
            $this->command->error('No students found. Run StudentSeeder first.');
            return;
        }

        if ($packages->isEmpty()) {
            $this->command->error('No lesson packages found. Run LessonSessionSeeder first.');
            return;
        }

        if ($instruments->isEmpty()) {
            $this->command->error('No instruments found. Run InstrumentSeeder first.');
            return;
        }

        if (empty($paymentMethodIds)) {
            $this->command->error('No payment methods found. Run PaymentMethodSeeder first.');
            return;
        }

        if (DB::table('enrollment')->exists()) {
            $this->command->warn('Enrollments already exist. EnrollmentSeeder skipped to avoid duplicates.');
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Safe Target Count
        |--------------------------------------------------------------------------
        |
        | We cannot create more unique student + instrument pairs than possible.
        | This prevents an infinite loop.
        |
        */
        $requestedCount = 30;
        $maxPossiblePairs = $students->count() * $instruments->count();
        $targetCount = min($requestedCount, $maxPossiblePairs);

        $created = 0;
        $attempts = 0;
        $maxAttempts = $targetCount * 20;

        $usedStudentInstrumentPairs = [];

        $this->command->info("Seeding {$targetCount} Music Lab enrollments...");

        while ($created < $targetCount && $attempts < $maxAttempts) {
            $attempts++;

            $student = $students->random();
            $instrument = $instruments->random();

            /*
            |--------------------------------------------------------------------------
            | Avoid Duplicate Student + Instrument Pair
            |--------------------------------------------------------------------------
            */
            $pairKey = $student->student_id . '|' . $instrument->instrument_id;

            if (isset($usedStudentInstrumentPairs[$pairKey])) {
                continue;
            }

            $qualifiedInstructorIds = $this->getQualifiedInstructorIds($instrument->instrument_name);

            if (empty($qualifiedInstructorIds)) {
                $this->command->warn("No available instructor found for {$instrument->instrument_name}. Skipped.");
                continue;
            }

            $package = $packages->random();
            $instructorId = $qualifiedInstructorIds[array_rand($qualifiedInstructorIds)];
            $paymentMethodId = $paymentMethodIds[array_rand($paymentMethodIds)];

            $totalSessions = (int) $package->session_count;
            $completedSessions = random_int(0, min(3, $totalSessions));
            $remainingSessions = $totalSessions - $completedSessions;

            /*
            |--------------------------------------------------------------------------
            | Demo Payment Status
            |--------------------------------------------------------------------------
            */
            $randomPayment = random_int(1, 100);

            if ($randomPayment <= 75) {
                $paymentStatus = 'paid';
                $amountPaid = (float) $package->price;
            } elseif ($randomPayment <= 90) {
                $paymentStatus = 'partial';
                $amountPaid = (float) $package->price * 0.5;
            } else {
                $paymentStatus = 'pending';
                $amountPaid = 0.00;
            }

            /*
            |--------------------------------------------------------------------------
            | Demo Dates
            |--------------------------------------------------------------------------
            */
            $enrollmentDate = Carbon::parse($student->enrollment_date ?? now())->toDateString();
            $startDate = Carbon::parse($enrollmentDate)->addDays(random_int(1, 5))->toDateString();
            $endDate = Carbon::parse($startDate)->addWeeks($totalSessions)->toDateString();

            $enrollmentId = $this->generateEnrollmentId($created + 1);

            DB::table('enrollment')->insert([
                'enrollment_id' => $enrollmentId,
                'student_id' => $student->student_id,
                'instrument_id' => $instrument->instrument_id,
                'session_id' => $package->session_id,
                'instructor_id' => $instructorId,
                'payment_method_id' => $paymentMethodId,
                'enrollment_date' => $enrollmentDate,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'total_sessions' => $totalSessions,
                'completed_sessions' => $completedSessions,
                'remaining_sessions' => $remainingSessions,
                'status' => $remainingSessions > 0 ? 'active' : 'completed',
                'payment_status' => $paymentStatus,
                'total_amount' => $package->price,
                'amount_paid' => $amountPaid,
                'notes' => "Demo {$instrument->instrument_name} enrollment generated by EnrollmentSeeder.",
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            /*
            |--------------------------------------------------------------------------
            | Optional Student Profile Sync
            |--------------------------------------------------------------------------
            |
            | The actual lesson instrument is saved in enrollment.instrument_id.
            | This update only keeps student.instrument_id useful as the latest or
            | preferred display value.
            |
            */
            DB::table('student')
                ->where('student_id', $student->student_id)
                ->update([
                    'instrument_id' => $instrument->instrument_id,
                    'updated_at' => now(),
                ]);

            $usedStudentInstrumentPairs[$pairKey] = true;
            $created++;

            if ($created % 10 === 0) {
                $this->command->info("Created {$created} enrollments...");
            }
        }

        $this->command->info("Successfully seeded {$created} Music Lab enrollments.");

        if ($created < $targetCount) {
            $this->command->warn("Only {$created} enrollments were created because the available valid data was limited.");
        }
    }

    /**
     * Find instructors whose specialization matches the selected instrument.
     */
    private function getQualifiedInstructorIds(string $instrumentName): array
    {
        $ids = DB::table('instructor as i')
            ->join('instructor_specialization as isp', 'i.instructor_id', '=', 'isp.instructor_id')
            ->join('specialization as sp', 'isp.specialization_id', '=', 'sp.specialization_id')
            ->where('i.is_active', true)
            ->where('i.is_available', true)
            ->whereRaw('LOWER(sp.specialization_name) = ?', [strtolower($instrumentName)])
            ->pluck('i.instructor_id')
            ->unique()
            ->values()
            ->toArray();

        /*
        |--------------------------------------------------------------------------
        | Fallback
        |--------------------------------------------------------------------------
        |
        | This prevents the seeder from failing if demo instructor specialization
        | data is incomplete. In real enrollment, the controller still validates
        | the correct specialization.
        |
        */
        if (empty($ids)) {
            $ids = DB::table('instructor')
                ->where('is_active', true)
                ->where('is_available', true)
                ->pluck('instructor_id')
                ->toArray();
        }

        return $ids;
    }

    /**
     * Generate readable enrollment IDs.
     *
     * Example:
     * 2026-05-0000001
     */
    private function generateEnrollmentId(int $sequence): string
    {
        return now()->format('Y-m') . '-' . str_pad((string) $sequence, 7, '0', STR_PAD_LEFT);
    }
}