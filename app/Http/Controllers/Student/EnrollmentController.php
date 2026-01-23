<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Enrollment;

class EnrollmentController extends Controller
{
    /**
     * app/Http/Controllers/Student/EnrollmentController.php
     * Display available lesson packages for enrollment
     * Purpose: Browse lesson packages TO PURCHASE (Requirement #7)
     * Data Source: lesson_session table WHERE is_active = TRUE
     */
    public function packages()
    {
        // Fetch all active lesson packages from lesson_session table
        $packages = DB::table('lesson_session')
            ->where('is_active', true)
            ->orderBy('session_count')  // Order: 5, 10, 20 sessions
            ->get();

        return view('student.packages', compact('packages'));
    }

    /**
     * Display all the student's enrollments/packages (PAST PURCHASES)
     * Purpose: View enrollment history and current packages
     * Shows current and past lesson packages with progress tracking
     */
    public function index()
    {
        // Get student record using authenticated user ID
        $student = DB::table('student')->where('user_id', Auth::id())->first();
        
        // Validate student exists
        if (!$student) {
            abort(404, 'Student record not found');
        }

        // Fetch all enrollments with related lesson session and instructor data
        $enrollments = Enrollment::where('student_id', $student->student_id)
            ->with([
                'lessonSession',  // Get package details (5, 10, 20 sessions)
                'instructor' => function ($q) {
                    $q->select('instructor_id', 'first_name', 'last_name');
                }
            ])
            ->orderByDesc('enrollment_date')  // Most recent first
            ->get();

        return view('student.enrollments', compact('enrollments'));
    }

    /**
     * Show details of a specific enrollment/package
     * Includes progress history for that enrollment
     */
    public function show($enrollmentId)
    {
        // Get student record using authenticated user ID
        $student = DB::table('student')->where('user_id', Auth::id())->first();
        
        // Validate student exists
        if (!$student) {
            abort(404, 'Student record not found');
        }

        // Fetch specific enrollment with security check (student_id match)
        $enrollment = Enrollment::where('enrollment_id', $enrollmentId)
            ->where('student_id', $student->student_id)  // Security: only own enrollments
            ->with([
                'lessonSession',  // Package details
                'instructor',     // Instructor info
                'progress' => function ($q) use ($student) {
                    $q->where('student_id', $student->student_id);  // Only this student's progress
                }
            ])
            ->firstOrFail();

        return view('student.enrollments-show', compact('enrollment'));
    }

    /**
     * Show enrollment form for a specific package
     * Purpose: Allow student to enroll in a selected package
     */
    public function enrollmentForm($sessionId)
    {
        // Get student record
        $student = DB::table('student')->where('user_id', Auth::id())->first();
        
        if (!$student) {
            abort(404, 'Student record not found');
        }

        // Get the selected package
        $package = DB::table('lesson_session')
            ->where('session_id', $sessionId)
            ->where('is_active', true)
            ->first();

        if (!$package) {
            abort(404, 'Package not found or inactive');
        }

        // Get available instructors
        $instructors = DB::table('instructor')
            ->where('is_active', true)
            ->where('is_available', true)
            ->select('instructor_id', 'first_name', 'last_name')
            ->get();

        // Get payment methods
        $paymentMethods = DB::table('payment_methods')
            ->where('is_active', true)
            ->get();

        return view('student.enroll-form', compact('student', 'package', 'instructors', 'paymentMethods'));
    }

    /**
     * Process enrollment form submission
     * Inserts into enrollment table with auto-generated enrollment_id
     */
    public function processEnrollment(Request $request)
    {
        // Validate input
        $validated = $request->validate([
            'session_id' => 'required|exists:lesson_session,session_id',
            'instrument_id' => 'required|exists:instrument,instrument_id',
            'preferred_genre_id' => 'nullable|exists:genre,genre_id',
            'instructor_id' => 'required|exists:instructor,instructor_id',
            'start_date' => 'required|date|after_or_equal:today',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Get student record
        $student = DB::table('student')->where('user_id', Auth::id())->first();
        
        if (!$student) {
            return redirect()->back()->with('error', 'Student record not found');
        }

        // Get package details
        $package = DB::table('lesson_session')->where('session_id', $validated['session_id'])->first();
        
        if (!$package) {
            return redirect()->back()->with('error', 'Package not found');
        }

        try {
            DB::beginTransaction();

            // Update student's instrument and genre if provided
            DB::table('student')
                ->where('student_id', $student->student_id)
                ->update([
                    'instrument_id' => $validated['instrument_id'],
                    'preferred_genre_id' => $validated['preferred_genre_id'],
                    'updated_at' => now(),
                ]);

            // Insert enrollment (enrollment_id auto-generated by trigger)
            DB::table('enrollment')->insert([
                'student_id' => $student->student_id,
                'session_id' => $validated['session_id'],
                'instructor_id' => $validated['instructor_id'],
                'enrollment_date' => now(),
                'start_date' => $validated['start_date'],
                'total_sessions' => $package->session_count,
                'completed_sessions' => 0,
                'remaining_sessions' => $package->session_count,
                'status' => 'active',
                'payment_status' => 'pending',
                'total_amount' => $package->price,
                'amount_paid' => 0,
                'notes' => $validated['notes'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();

            return redirect()->route('student.enrollments')
                ->with('success', 'Enrollment successful! Your package is now active.');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Enrollment error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Enrollment failed. Please try again.');
        }
    }

    /**
     * API: Get instructors filtered by instrument specialization
     */
    public function getInstructorsByInstrument($instrumentId)
    {
        // First get specialization_id that matches the instrument name
        $instrument = DB::table('instrument')->where('instrument_id', $instrumentId)->first();
        
        if (!$instrument) {
            return response()->json([]);
        }

        // Find matching specialization
        $specialization = DB::table('specialization')
            ->where('specialization_name', 'LIKE', '%' . $instrument->instrument_name . '%')
            ->first();

        if (!$specialization) {
            // If no match, return all available instructors
            $instructors = DB::table('instructor')
                ->where('is_active', true)
                ->where('is_available', true)
                ->select('instructor_id', 'first_name', 'last_name')
                ->get();
            
            return response()->json($instructors);
        }

        // Get instructors with this specialization
        $instructors = DB::table('instructor as i')
            ->join('instructor_specialization as is', 'i.instructor_id', '=', 'is.instructor_id')
            ->where('is.specialization_id', $specialization->specialization_id)
            ->where('i.is_active', true)
            ->where('i.is_available', true)
            ->select('i.instructor_id', 'i.first_name', 'i.last_name')
            ->distinct()
            ->get();

        return response()->json($instructors);
    }
}