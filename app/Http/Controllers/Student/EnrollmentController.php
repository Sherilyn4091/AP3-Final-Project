<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * app/Http/Controllers/Student/EnrollmentController.php
 *
 * Handles student enrollment actions:
 * - Browse lesson packages
 * - View enrollment history
 * - View enrollment details
 * - Submit a new enrollment
 * - Filter instructors by selected instrument
 *
 * Important Music Lab rule:
 * - One student can have many enrollments.
 * - Each enrollment has one instrument.
 * - Each enrollment has one assigned instructor.
 * - The assigned instructor must be qualified for the selected instrument.
 */
class EnrollmentController extends Controller
{
    /**
     * Display all active lesson packages.
     *
     * Example:
     * - 5 Sessions Package
     * - 10 Sessions Package
     * - 20 Sessions Package
     */
    public function packages()
    {
        $packages = DB::table('lesson_session')
            ->where('is_active', true)
            ->orderBy('session_count')
            ->get();

        return view('student.packages', compact('packages'));
    }

    /**
     * Display all enrollments of the authenticated student.
     *
     * This includes:
     * - package details
     * - enrolled instrument
     * - assigned instructor
     */
    public function index()
    {
        $student = DB::table('student')
            ->where('user_id', Auth::id())
            ->first();

        if (!$student) {
            abort(404, 'Student record not found.');
        }

        $enrollments = Enrollment::where('student_id', $student->student_id)
            ->with([
                'lessonSession',
                'instrument',
                'instructor' => function ($query) {
                    $query->select('instructor_id', 'first_name', 'last_name');
                },
            ])
            ->orderByDesc('enrollment_date')
            ->get();

        return view('student.enrollments', compact('enrollments'));
    }

    /**
     * Show details of one enrollment.
     *
     * Security:
     * - The enrollment must belong to the authenticated student.
     */
    public function show($enrollmentId)
    {
        $student = DB::table('student')
            ->where('user_id', Auth::id())
            ->first();

        if (!$student) {
            abort(404, 'Student record not found.');
        }

        $enrollment = Enrollment::where('enrollment_id', $enrollmentId)
            ->where('student_id', $student->student_id)
            ->with([
                'lessonSession',
                'instrument',
                'instructor',
                'progress' => function ($query) use ($student) {
                    $query->where('student_id', $student->student_id);
                },
            ])
            ->firstOrFail();

        return view('student.enrollments-show', compact('enrollment'));
    }

    /**
     * Show enrollment form for the selected lesson package.
     *
     * The form needs:
     * - selected package
     * - official Music Lab instruments
     * - available instructors
     * - payment methods
     */
    public function enrollmentForm($sessionId)
    {
        $student = DB::table('student')
            ->where('user_id', Auth::id())
            ->first();

        if (!$student) {
            abort(404, 'Student record not found.');
        }

        $package = DB::table('lesson_session')
            ->where('session_id', $sessionId)
            ->where('is_active', true)
            ->first();

        if (!$package) {
            abort(404, 'Package not found or inactive.');
        }

        $instruments = DB::table('instrument')
            ->where('is_active', true)
            ->orderBy('instrument_name')
            ->get();

        $instructors = DB::table('instructor')
            ->where('is_active', true)
            ->where('is_available', true)
            ->select('instructor_id', 'first_name', 'last_name')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        $paymentMethods = DB::table('payment_methods')
            ->where('is_active', true)
            ->orderBy('method_name')
            ->get();

        return view('student.enroll-form', compact(
            'student',
            'package',
            'instruments',
            'instructors',
            'paymentMethods'
        ));
    }

    /**
     * Process student enrollment.
     *
     * Important:
     * - instrument_id is saved in enrollment, not only in student profile.
     * - This supports one student enrolling in different instruments.
     */
    public function processEnrollment(Request $request)
    {
        $validated = $request->validate([
            'session_id' => 'required|exists:lesson_session,session_id',
            'instrument_id' => 'required|exists:instrument,instrument_id',
            'preferred_genre_id' => 'nullable|exists:genre,genre_id',
            'instructor_id' => 'required|exists:instructor,instructor_id',
            'payment_method_id' => 'nullable|exists:payment_methods,method_id',
            'start_date' => 'required|date|after_or_equal:today',
            'notes' => 'nullable|string|max:1000',
        ]);

        $student = DB::table('student')
            ->where('user_id', Auth::id())
            ->first();

        if (!$student) {
            return back()
                ->withInput()
                ->with('error', 'Student record not found.');
        }

        $package = DB::table('lesson_session')
            ->where('session_id', $validated['session_id'])
            ->where('is_active', true)
            ->first();

        if (!$package) {
            return back()
                ->withInput()
                ->with('error', 'Selected package not found or inactive.');
        }

        /*
        |--------------------------------------------------------------------------
        | Validate Instructor Qualification
        |--------------------------------------------------------------------------
        |
        | This prevents wrong pairings such as:
        | - Guitar enrollment assigned to a Voice instructor
        | - Keyboard enrollment assigned to a Drums instructor
        */
        if (!$this->instructorCanTeachInstrument(
            (int) $validated['instructor_id'],
            (int) $validated['instrument_id']
        )) {
            return back()
                ->withInput()
                ->with('error', 'The selected instructor does not teach this instrument. Please choose a qualified instructor.');
        }

        /*
        |--------------------------------------------------------------------------
        | Prevent Duplicate Active Enrollment
        |--------------------------------------------------------------------------
        |
        | A student can enroll in different instruments at the same time.
        | But the student should not have two active enrollments for the same
        | instrument at the same time.
        */
        $hasSameActiveEnrollment = DB::table('enrollment')
            ->where('student_id', $student->student_id)
            ->where('instrument_id', $validated['instrument_id'])
            ->where('status', 'active')
            ->exists();

        if ($hasSameActiveEnrollment) {
            return back()
                ->withInput()
                ->with('error', 'You already have an active enrollment for this instrument.');
        }

        try {
            DB::beginTransaction();

            $enrollmentId = $this->generateEnrollmentId();

            /*
            |--------------------------------------------------------------------------
            | Update Student Profile Preference
            |--------------------------------------------------------------------------
            |
            | This is only for profile/preferred display.
            | The real enrolled instrument is saved below in enrollment.instrument_id.
            */
            DB::table('student')
                ->where('student_id', $student->student_id)
                ->update([
                    'instrument_id' => $validated['instrument_id'],
                    'preferred_genre_id' => $validated['preferred_genre_id'] ?? $student->preferred_genre_id,
                    'updated_at' => now(),
                ]);

            /*
            |--------------------------------------------------------------------------
            | Insert Enrollment
            |--------------------------------------------------------------------------
            |
            | This row connects:
            | student + instrument + instructor + lesson package
            */
            DB::table('enrollment')->insert([
                'enrollment_id' => $enrollmentId,
                'student_id' => $student->student_id,
                'instrument_id' => $validated['instrument_id'],
                'session_id' => $validated['session_id'],
                'instructor_id' => $validated['instructor_id'],
                'payment_method_id' => $validated['payment_method_id'] ?? null,

                'enrollment_date' => now()->toDateString(),
                'start_date' => $validated['start_date'],
                'end_date' => Carbon::parse($validated['start_date'])
                    ->addWeeks((int) $package->session_count)
                    ->toDateString(),

                'total_sessions' => $package->session_count,
                'completed_sessions' => 0,
                'remaining_sessions' => $package->session_count,

                'status' => 'active',
                'payment_status' => 'pending',

                'total_amount' => $package->price,
                'amount_paid' => 0,

                'notes' => $validated['notes'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();

            return redirect()
                ->route('student.enrollments')
                ->with('success', 'Enrollment successful. Your package is now active.');

        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Student enrollment failed', [
                'message' => $e->getMessage(),
                'student_id' => $student->student_id,
            ]);

            return back()
                ->withInput()
                ->with('error', 'Enrollment failed. Please try again.');
        }
    }

    /**
     * API: Get instructors filtered by instrument specialization.
     *
     * Used when the student selects an instrument in the enrollment form.
     */
    public function getInstructorsByInstrument($instrumentId)
    {
        $instrument = DB::table('instrument')
            ->where('instrument_id', $instrumentId)
            ->where('is_active', true)
            ->first();

        if (!$instrument) {
            return response()->json([]);
        }

        $instructors = DB::table('instructor as i')
            ->join('instructor_specialization as isp', 'i.instructor_id', '=', 'isp.instructor_id')
            ->join('specialization as sp', 'isp.specialization_id', '=', 'sp.specialization_id')
            ->where('i.is_active', true)
            ->where('i.is_available', true)
            ->whereRaw('LOWER(sp.specialization_name) = ?', [strtolower($instrument->instrument_name)])
            ->select('i.instructor_id', 'i.first_name', 'i.last_name')
            ->distinct()
            ->orderBy('i.first_name')
            ->orderBy('i.last_name')
            ->get();

        return response()->json($instructors);
    }

    /**
     * Check if instructor has a specialization that matches the selected instrument.
     */
    private function instructorCanTeachInstrument(int $instructorId, int $instrumentId): bool
    {
        $instrument = DB::table('instrument')
            ->where('instrument_id', $instrumentId)
            ->where('is_active', true)
            ->first();

        if (!$instrument) {
            return false;
        }

        return DB::table('instructor_specialization as isp')
            ->join('specialization as sp', 'isp.specialization_id', '=', 'sp.specialization_id')
            ->where('isp.instructor_id', $instructorId)
            ->whereRaw('LOWER(sp.specialization_name) = ?', [strtolower($instrument->instrument_name)])
            ->exists();
    }

    /**
     * Generate a custom enrollment ID.
     *
     * Format:
     * YYYY-MM-0000001
     *
     * Example:
     * 2026-05-0000001
     */
    private function generateEnrollmentId(): string
    {
        $prefix = now()->format('Y-m');

        $maxSequence = DB::table('enrollment')
            ->where('enrollment_id', 'LIKE', $prefix . '-%')
            ->selectRaw("MAX(CAST(SUBSTRING(enrollment_id FROM 9) AS BIGINT)) AS max_sequence")
            ->value('max_sequence');

        $nextSequence = $maxSequence ? ((int) $maxSequence + 1) : 1;

        return $prefix . '-' . str_pad((string) $nextSequence, 7, '0', STR_PAD_LEFT);
    }
}