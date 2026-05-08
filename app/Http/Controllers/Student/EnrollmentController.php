<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

/**
 * app/Http/Controllers/Student/EnrollmentController.php
 *
 * Handles student enrollment actions:
 * - Browse lesson packages
 * - View enrollment history
 * - View enrollment details
 * - Submit a new enrollment
 * - Edit enrollment before lessons start
 * - Cancel enrollment before lessons start
 * - Request withdrawal for ongoing lessons
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
    private const VALID_DAYS = [
        'Monday',
        'Tuesday',
        'Wednesday',
        'Thursday',
        'Friday',
        'Saturday',
        'Sunday',
    ];

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
     * - progress counters
     * - student scheduling preferences
     */
    public function index()
    {
        $student = $this->getAuthenticatedStudent();

        $enrollments = Enrollment::where('student_id', $student->student_id)
            ->with([
                'lessonSession',
                'instrument',
                'instructor' => function ($query) {
                    /*
                    |--------------------------------------------------------------------------
                    | Instructor Contact Details
                    |--------------------------------------------------------------------------
                    |
                    | These fields are needed by the student enrollment details modal.
                    | user_id is included so Laravel can also load the linked user account
                    | email if needed.
                    |
                    */
                    $query->select(
                        'instructor_id',
                        'user_id',
                        'first_name',
                        'middle_name',
                        'last_name',
                        'suffix',
                        'phone',
                        'email'
                    );
                },
                'instructor.userAccount:user_id,user_email',
                'schedules' => function ($query) {
                    $query->select('schedule_id', 'enrollment_id', 'schedule_date', 'start_time', 'status')
                        ->orderBy('schedule_date')
                        ->orderBy('start_time');
                },
            ])
            ->orderByDesc('enrollment_date')
            ->orderByDesc('created_at')
            ->get();

        $stats = $this->buildEnrollmentStats($enrollments);
        $formOptions = $this->getEnrollmentFormOptions();

        return view('student.enrollments', compact('enrollments', 'stats', 'formOptions'));
    }

    /**
     * app/Http/Controllers/Student/EnrollmentController.php
     * 
     * Show details of one enrollment.
     *
     * Security:
     * - The enrollment must belong to the authenticated student.
     */
    public function show($enrollmentId)
    {
        $student = $this->getAuthenticatedStudent();

        $enrollment = Enrollment::where('enrollment_id', $enrollmentId)
            ->where('student_id', $student->student_id)
            ->with([
                'lessonSession',
                'instrument',
                'instructor',
                'schedules',
                'progress' => function ($query) use ($student) {
                    $query->where('student_id', $student->student_id)
                        ->orderByDesc('progress_date');
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
     * - preferred schedule fields
     */
    public function enrollmentForm($sessionId)
    {
        $student = $this->getAuthenticatedStudent();

        $package = DB::table('lesson_session')
            ->where('session_id', $sessionId)
            ->where('is_active', true)
            ->first();

        if (!$package) {
            abort(404, 'Package not found or inactive.');
        }

        $formOptions = $this->getEnrollmentFormOptions();

        return view('student.enroll-form', array_merge($formOptions, [
            'student' => $student,
            'package' => $package,
            'validDays' => self::VALID_DAYS,
            'timeSlots' => $this->getPreferredTimeSlots(),
        ]));
    }

    /**
     * Process student enrollment.
     *
     * Important:
     * - instrument_id is saved in enrollment, not only in student profile.
     * - preferred_lesson_days and preferred_lesson_time are preferences only.
     * - This does NOT automatically create final schedule rows.
     */
    public function processEnrollment(Request $request)
    {
        $validated = $this->validateEnrollmentPayload($request);
        $student = $this->getAuthenticatedStudent();
        $package = $this->getActivePackageOrFail((int) $validated['session_id']);

        if (!$this->instructorCanTeachInstrument((int) $validated['instructor_id'], (int) $validated['instrument_id'])) {
            return back()
                ->withInput()
                ->with('error', 'The selected instructor does not teach this instrument. Please choose a qualified instructor.');
        }

        if ($this->hasDuplicateActiveEnrollment($student->student_id, (int) $validated['instrument_id'])) {
            return back()
                ->withInput()
                ->with('error', 'You already have an active enrollment for this instrument.');
        }

        try {
            DB::beginTransaction();

            $enrollmentId = $this->generateEnrollmentId();
            $preferredDays = $this->formatPreferredDays($validated['preferred_lesson_days']);

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
                    'preferred_lesson_days' => $preferredDays,
                    'preferred_lesson_time' => $validated['preferred_lesson_time'],
                    'updated_at' => now(),
                ]);

            /*
            |--------------------------------------------------------------------------
            | Insert Enrollment
            |--------------------------------------------------------------------------
            |
            | This row connects:
            | student + instrument + instructor + lesson package + schedule preference
            |
            | Schedules are intentionally NOT created here. Admin/Instructor confirms
            | the final schedule after checking room and instructor availability.
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
                'preferred_lesson_days' => $preferredDays,
                'preferred_lesson_time' => $validated['preferred_lesson_time'],
                'end_date' => Carbon::parse($validated['start_date'])
                    ->addWeeks((int) $package->session_count)
                    ->toDateString(),

                'total_sessions' => $package->session_count,
                'completed_sessions' => 0,
                'remaining_sessions' => $package->session_count,

                // Kept as active for compatibility with existing admin/instructor schedule flows.
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
                ->with('success', 'Enrollment submitted successfully. Your preferred schedule is saved and will be confirmed soon.');
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
     * Update an enrollment before the lesson package starts.
     *
     * Editable only when:
     * - start date is still in the future
     * - no completed sessions yet
     * - no confirmed schedule rows yet
     */
    public function update(Request $request, string $enrollmentId)
    {
        $student = $this->getAuthenticatedStudent();
        $enrollment = $this->getStudentEnrollmentOrFail($student->student_id, $enrollmentId);

        if (!$enrollment->can_be_edited) {
            return back()->with('error', 'This enrollment can no longer be edited because it has already started or has confirmed lessons.');
        }

        $validated = $this->validateEnrollmentPayload($request);
        $package = $this->getActivePackageOrFail((int) $validated['session_id']);

        if (!$this->instructorCanTeachInstrument((int) $validated['instructor_id'], (int) $validated['instrument_id'])) {
            return back()
                ->withInput()
                ->with('error', 'The selected instructor does not teach this instrument. Please choose a qualified instructor.');
        }

        if ($this->hasDuplicateActiveEnrollment($student->student_id, (int) $validated['instrument_id'], $enrollment->enrollment_id)) {
            return back()
                ->withInput()
                ->with('error', 'You already have another active enrollment for this instrument.');
        }

        $preferredDays = $this->formatPreferredDays($validated['preferred_lesson_days']);

        $enrollment->update([
            'instrument_id' => $validated['instrument_id'],
            'session_id' => $validated['session_id'],
            'instructor_id' => $validated['instructor_id'],
            'payment_method_id' => $validated['payment_method_id'] ?? null,
            'start_date' => $validated['start_date'],
            'preferred_lesson_days' => $preferredDays,
            'preferred_lesson_time' => $validated['preferred_lesson_time'],
            'end_date' => Carbon::parse($validated['start_date'])->addWeeks((int) $package->session_count)->toDateString(),
            'total_sessions' => $package->session_count,
            'completed_sessions' => 0,
            'remaining_sessions' => $package->session_count,
            'total_amount' => $package->price,
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()
            ->route('student.enrollments')
            ->with('success', 'Enrollment updated successfully.');
    }

    /**
     * Cancel an enrollment before it starts.
     *
     * Professional wording:
     * - Before start date: Cancel Enrollment
     * - Ongoing lessons: Request Withdrawal
     */
    public function cancel(Request $request, string $enrollmentId)
    {
        $student = $this->getAuthenticatedStudent();
        $enrollment = $this->getStudentEnrollmentOrFail($student->student_id, $enrollmentId);

        if (!$enrollment->can_be_cancelled) {
            return back()->with('error', 'This enrollment can no longer be cancelled. You may request withdrawal instead.');
        }

        $validated = $request->validate([
            'cancellation_reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $enrollment->update([
            'status' => 'cancelled',
            'cancellation_reason' => $validated['cancellation_reason'] ?? null,
            'cancelled_at' => now(),
        ]);

        return redirect()
            ->route('student.enrollments')
            ->with('success', 'Enrollment cancelled successfully.');
    }

    /**
     * Request withdrawal for an ongoing enrollment.
     *
     * This does not instantly delete or cancel the enrollment. It marks the
     * enrollment for admin/instructor review.
     */
    public function requestWithdrawal(Request $request, string $enrollmentId)
    {
        $student = $this->getAuthenticatedStudent();
        $enrollment = $this->getStudentEnrollmentOrFail($student->student_id, $enrollmentId);

        if (!$enrollment->can_request_withdrawal) {
            return back()->with('error', 'Withdrawal can only be requested for an ongoing enrollment.');
        }

        $validated = $request->validate([
            'withdrawal_reason' => ['required', 'string', 'max:1000'],
        ]);

        $enrollment->update([
            'status' => 'withdrawal_requested',
            'withdrawal_reason' => $validated['withdrawal_reason'],
            'withdrawal_requested_at' => now(),
        ]);

        return redirect()
            ->route('student.enrollments')
            ->with('success', 'Withdrawal request submitted. Please wait for admin review.');
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
            ->select(
                'i.instructor_id',
                'i.first_name',
                'i.middle_name',
                'i.last_name',
                'i.suffix',
                'i.available_days',
                'i.preferred_time_slots',
                DB::raw("CONCAT(i.first_name, ' ', COALESCE(i.middle_name || ' ', ''), i.last_name, COALESCE(' ' || i.suffix, '')) AS full_name")
            )
            ->distinct()
            ->orderBy('i.first_name')
            ->orderBy('i.last_name')
            ->get();

        return response()->json($instructors);
    }

    /**
     * Shared validation for create and edit enrollment.
     *
     * Extracted to avoid duplicated validation rules and to prevent Long Method.
     */
    private function validateEnrollmentPayload(Request $request): array
    {
        return $request->validate([
            'session_id' => ['required', 'exists:lesson_session,session_id'],
            'instrument_id' => ['required', 'exists:instrument,instrument_id'],
            'preferred_genre_id' => ['nullable', 'exists:genre,genre_id'],
            'instructor_id' => ['required', 'exists:instructor,instructor_id'],
            'payment_method_id' => ['nullable', 'exists:payment_methods,method_id'],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'preferred_lesson_days' => ['required', 'array', 'min:1'],
            'preferred_lesson_days.*' => ['required', 'string', Rule::in(self::VALID_DAYS)],
            'preferred_lesson_time' => ['required', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);
    }

    /**
     * Get authenticated student record safely.
     */
    private function getAuthenticatedStudent(): object
    {
        $student = DB::table('student')
            ->where('user_id', Auth::id())
            ->first();

        if (!$student) {
            abort(404, 'Student record not found.');
        }

        return $student;
    }

    /**
     * Find one enrollment owned by the current student.
     */
    private function getStudentEnrollmentOrFail(int $studentId, string $enrollmentId): Enrollment
    {
        return Enrollment::where('student_id', $studentId)
            ->where('enrollment_id', $enrollmentId)
            ->with(['schedules'])
            ->firstOrFail();
    }

    /**
     * Fetch active package or show 404.
     */
    private function getActivePackageOrFail(int $sessionId): object
    {
        $package = DB::table('lesson_session')
            ->where('session_id', $sessionId)
            ->where('is_active', true)
            ->first();

        if (!$package) {
            abort(404, 'Selected package not found or inactive.');
        }

        return $package;
    }

    /**
     * Options used by create and edit enrollment forms.
     */
    private function getEnrollmentFormOptions(): array
    {
        return [
            'packages' => DB::table('lesson_session')
                ->where('is_active', true)
                ->orderBy('session_count')
                ->get(),
            'instruments' => DB::table('instrument')
                ->where('is_active', true)
                ->orderBy('instrument_name')
                ->get(),
            'genres' => DB::table('genre')
                ->where('is_active', true)
                ->orderBy('genre_name')
                ->get(),
            'instructors' => DB::table('instructor')
                ->where('is_active', true)
                ->where('is_available', true)
                ->select(
                    'instructor_id',
                    'first_name',
                    'middle_name',
                    'last_name',
                    'suffix',
                    DB::raw("CONCAT(first_name, ' ', COALESCE(middle_name || ' ', ''), last_name, COALESCE(' ' || suffix, '')) AS full_name")
                )
                ->orderBy('first_name')
                ->orderBy('last_name')
                ->get(),
            'paymentMethods' => DB::table('payment_methods')
                ->where('is_active', true)
                ->orderBy('method_name')
                ->get(),
            'validDays' => self::VALID_DAYS,
            'timeSlots' => $this->getPreferredTimeSlots(),
        ];
    }

    /**
     * Time slots offered in student-facing forms.
     */
    private function getPreferredTimeSlots(): array
    {
        return [
            'Morning (9:00 AM - 12:00 PM)',
            'Afternoon (12:00 PM - 3:00 PM)',
            'Late Afternoon (3:00 PM - 6:00 PM)',
            'Evening (6:00 PM - 8:00 PM)',
            'Sunday Window (10:00 AM - 6:00 PM)',
        ];
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
     * Prevent duplicate active enrollment for the same instrument.
     */
    private function hasDuplicateActiveEnrollment(int $studentId, int $instrumentId, ?string $exceptEnrollmentId = null): bool
    {
        $query = DB::table('enrollment')
            ->where('student_id', $studentId)
            ->where('instrument_id', $instrumentId)
            ->whereIn('status', ['active', 'withdrawal_requested']);

        if ($exceptEnrollmentId) {
            $query->where('enrollment_id', '!=', $exceptEnrollmentId);
        }

        return $query->exists();
    }

    /**
     * Convert selected days array into a clean string for storage.
     */
    private function formatPreferredDays(array $days): string
    {
        $orderedDays = collect(self::VALID_DAYS)
            ->filter(fn ($day) => in_array($day, $days, true))
            ->values();

        return $orderedDays->implode(', ');
    }

    /**
     * Build small stat cards for My Enrollments.
     */
    private function buildEnrollmentStats($enrollments): array
    {
        return [
            'total' => $enrollments->count(),
            'active' => $enrollments->where('status', 'active')->count(),
            'remaining_sessions' => $enrollments->where('status', 'active')->sum('remaining_sessions'),
            'completed_sessions' => $enrollments->sum('completed_sessions'),
            'withdrawal_requests' => $enrollments->where('status', 'withdrawal_requested')->count(),
        ];
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
