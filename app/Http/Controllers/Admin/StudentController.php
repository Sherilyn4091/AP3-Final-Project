<?php
/**
 * ============================================================================
 * STUDENT MANAGEMENT CONTROLLER
 * app/Http/Controllers/Admin/StudentController.php
 * ============================================================================
 * Handles all student management operations including:
 * - List view with comprehensive filters
 * - Student details with multiple tabs (personal, musical, enrollment, etc.)
 * - Edit student information
 * - View progress, attendance, payments
 * - Bulk actions
 * ============================================================================
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class StudentController extends Controller
{
    /**
     * Display student management page with filters and pagination.
     * 
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // ====================================================================
        // Base query with all necessary joins
        // ====================================================================
        $query = DB::table('student as s')
            ->leftJoin('user_account as ua', 's.user_id', '=', 'ua.user_id')
            ->leftJoin('instrument as i', 's.instrument_id', '=', 'i.instrument_id')
            ->leftJoin('genre as g', 's.preferred_genre_id', '=', 'g.genre_id')
            ->leftJoin('student_status as ss', 's.student_status_id', '=', 'ss.status_id')
            ->select(
                's.student_id',
                's.user_id',
                's.first_name',
                's.last_name',
                's.email',
                's.phone',
                's.is_active',
                's.created_at',
                'i.instrument_name',
                'g.genre_name',
                'ss.status_name',
                'ua.last_login',
                // Count active enrollments
                DB::raw('(SELECT COUNT(*) FROM enrollment WHERE student_id = s.student_id AND status = \'active\') as active_enrollments'),
                // Sum remaining sessions
                DB::raw('(SELECT COALESCE(SUM(remaining_sessions), 0) FROM enrollment WHERE student_id = s.student_id AND status = \'active\') as total_remaining_sessions'),
                // Get last lesson date
                DB::raw('(SELECT MAX(schedule_date) FROM schedule WHERE student_id = s.student_id) as last_lesson_date'),
                // Get payment status from most recent active enrollment
                DB::raw("(SELECT payment_status FROM enrollment WHERE student_id = s.student_id AND status = 'active' ORDER BY enrollment_date DESC LIMIT 1) as payment_status")
            );

        // ====================================================================
        // FILTERS
        // ====================================================================
        
        // Filter by student status
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('s.student_status_id', $request->status);
        }

        // Filter by instrument
        if ($request->filled('instrument') && $request->instrument !== 'all') {
            $query->where('s.instrument_id', $request->instrument);
        }

        // Filter by genre
        if ($request->filled('genre') && $request->genre !== 'all') {
            $query->where('s.preferred_genre_id', $request->genre);
        }

        // Filter by enrollment status
        if ($request->filled('enrollment_status') && $request->enrollment_status !== 'all') {
            $enrollmentStatus = $request->enrollment_status;
            $query->whereExists(function($q) use ($enrollmentStatus) {
                $q->select(DB::raw(1))
                  ->from('enrollment')
                  ->whereRaw('enrollment.student_id = s.student_id')
                  ->where('enrollment.status', $enrollmentStatus);
            });
        }

        // Filter by payment status
        if ($request->filled('payment_status') && $request->payment_status !== 'all') {
            $paymentStatus = $request->payment_status;
            $query->whereExists(function($q) use ($paymentStatus) {
                $q->select(DB::raw(1))
                  ->from('enrollment')
                  ->whereRaw('enrollment.student_id = s.student_id')
                  ->where('enrollment.payment_status', $paymentStatus)
                  ->where('enrollment.status', 'active');
            });
        }

        // Search functionality (name, email, phone)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where(DB::raw("CONCAT(s.first_name, ' ', s.last_name)"), 'ILIKE', "%{$search}%")
                  ->orWhere('s.email', 'ILIKE', "%{$search}%")
                  ->orWhere('s.phone', 'ILIKE', "%{$search}%")
                  ->orWhere(DB::raw('CAST(s.student_id AS TEXT)'), 'ILIKE', "%{$search}%");
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('s.created_at', '>=', $request->date_from);  // Use created_at
        }
        if ($request->filled('date_to')) {
            $query->whereDate('s.created_at', '<=', $request->date_to);  // Use created_at
        }

        // ====================================================================
        // SORTING
        // ====================================================================
        $sortBy = $request->input('sort_by', 'name');
        $sortOrder = $request->input('sort_order', 'asc');
        
        switch ($sortBy) {
            case 'last_lesson':
                $query->orderByRaw('(SELECT MAX(schedule_date) FROM schedule WHERE student_id = s.student_id) ' . $sortOrder . ' NULLS LAST');
                break;
            case 'enrollments':
                $query->orderByRaw('(SELECT COUNT(*) FROM enrollment WHERE student_id = s.student_id AND status = \'active\') ' . $sortOrder);
                break;
            case 'status':
                $query->orderBy('ss.status_name', $sortOrder);
                break;
            default:
                $query->orderBy(DB::raw("CONCAT(s.first_name, ' ', s.last_name)"), $sortOrder);
        }

        // Paginate results
        $students = $query->paginate(20);

        // ====================================================================
        // Get filter dropdown data
        // ====================================================================
        $statuses = DB::table('student_status')
            ->where('is_active', true)
            ->orderBy('status_name')
            ->get();

        $instruments = DB::table('instrument')
            ->where('is_active', true)
            ->orderBy('instrument_name')
            ->get();

        $genres = DB::table('genre')
            ->where('is_active', true)
            ->orderBy('genre_name')
            ->get();

        return view('admin.students.index', compact('students', 'statuses', 'instruments', 'genres'));
    }

    /**
     * ========================================================================
     * Get student details for modal view with all tabs data
     * ========================================================================
     * @param int $id - student_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        // Get student basic info
        $student = DB::table('student as s')
            ->leftJoin('instrument as i', 's.instrument_id', '=', 'i.instrument_id')
            ->leftJoin('genre as g', 's.preferred_genre_id', '=', 'g.genre_id')
            ->leftJoin('student_status as ss', 's.student_status_id', '=', 'ss.status_id')
            ->where('s.student_id', $id)
            ->select('s.*', 'i.instrument_name', 'g.genre_name', 'ss.status_name')
            ->first();
        
        if (!$student) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        // ====================================================================
        // TAB 1: Personal Information (already in $student)
        // ====================================================================

        // ====================================================================
        // TAB 2: Musical Background (already in $student)
        // ====================================================================

        // ====================================================================
        // TAB 3: Enrollment History
        // ====================================================================
        $enrollments = DB::table('enrollment as e')
            ->leftJoin('lesson_session as ls', 'e.session_id', '=', 'ls.session_id')
            ->leftJoin('instructor as i', 'e.instructor_id', '=', 'i.instructor_id')
            ->where('e.student_id', $id)
            ->select(
                'e.enrollment_id',
                'e.enrollment_date',
                'e.start_date',
                'e.end_date',
                'e.total_sessions',
                'e.completed_sessions',
                'e.remaining_sessions',
                'e.status',
                'e.payment_status',
                'e.amount_paid',
                'e.total_amount',
                'ls.session_name',
                'ls.session_count',
                DB::raw("CONCAT(i.first_name, ' ', i.last_name) as instructor_name")
            )
            ->orderBy('e.enrollment_date', 'desc')
            ->get();

        // ====================================================================
        // TAB 4: Schedule (Upcoming and recent lessons)
        // ====================================================================
        $upcomingSchedule = DB::table('schedule as sch')
            ->leftJoin('instructor as i', 'sch.instructor_id', '=', 'i.instructor_id')
            ->where('sch.student_id', $id)
            ->where('sch.schedule_date', '>=', now()->subDays(30))
            ->select(
                'sch.schedule_id',
                'sch.schedule_date',
                'sch.start_time',
                'sch.end_time',
                'sch.room_number',
                'sch.status',
                'sch.lesson_topic',
                DB::raw("CONCAT(i.first_name, ' ', i.last_name) as instructor_name")
            )
            ->orderBy('sch.schedule_date', 'desc')
            ->orderBy('sch.start_time', 'desc')
            ->limit(20)
            ->get();

        // ====================================================================
        // TAB 5: Attendance
        // ====================================================================
        $attendance = DB::table('attendance as a')
            ->join('schedule as sch', 'a.schedule_id', '=', 'sch.schedule_id')
            ->leftJoin('instructor as i', 'sch.instructor_id', '=', 'i.instructor_id')
            ->where('a.student_id', $id)
            ->where('a.attendance_type', 'lesson')
            ->select(
                'a.attendance_date',
                'a.attendance_status',
                'a.check_in_time',
                'sch.start_time',
                'sch.end_time',
                DB::raw("CONCAT(i.first_name, ' ', i.last_name) as instructor_name")
            )
            ->orderBy('a.attendance_date', 'desc')
            ->limit(20)
            ->get();

        // Attendance statistics
        $attendanceStats = DB::table('attendance')
            ->where('student_id', $id)
            ->where('attendance_type', 'lesson')
            ->selectRaw("
                COUNT(*) as total_lessons,
                SUM(CASE WHEN attendance_status = 'present' THEN 1 ELSE 0 END) as present_count,
                SUM(CASE WHEN attendance_status = 'absent' THEN 1 ELSE 0 END) as absent_count,
                ROUND((SUM(CASE WHEN attendance_status = 'present' THEN 1 ELSE 0 END)::numeric / NULLIF(COUNT(*), 0) * 100), 2) as attendance_rate
            ")
            ->first();

        // ====================================================================
        // TAB 6: Progress
        // ====================================================================
        $progress = DB::table('progress as p')
            ->leftJoin('instructor as i', 'p.instructor_id', '=', 'i.instructor_id')
            ->where('p.student_id', $id)
            ->select(
                'p.progress_id',
                'p.progress_date',
                'p.lesson_topic',
                'p.skills_covered',
                'p.performance_rating',
                'p.technical_skills_rating',
                'p.musicality_rating',
                'p.effort_rating',
                'p.strengths',
                'p.areas_for_improvement',
                'p.instructor_notes',
                'p.homework',
                DB::raw("CONCAT(i.first_name, ' ', i.last_name) as instructor_name")
            )
            ->orderBy('p.progress_date', 'desc')
            ->limit(15)
            ->get();

        // Calculate average ratings over time
        $progressTrend = DB::table('progress')
            ->where('student_id', $id)
            ->whereNotNull('performance_rating')
            ->select(
                DB::raw("TO_CHAR(progress_date, 'YYYY-MM') as month"),
                DB::raw('AVG(performance_rating) as avg_performance'),
                DB::raw('AVG(technical_skills_rating) as avg_technical'),
                DB::raw('AVG(musicality_rating) as avg_musicality'),
                DB::raw('AVG(effort_rating) as avg_effort')
            )
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->limit(6)
            ->get();

        // ====================================================================
        // TAB 7: Payment History
        // ====================================================================
        $payments = DB::table('payment as p')
            ->leftJoin('payment_method as pm', 'p.payment_method_id', '=', 'pm.method_id')
            ->leftJoin('payment_status as ps', 'p.payment_status_id', '=', 'ps.status_id')
            ->leftJoin('enrollment as e', 'p.enrollment_id', '=', 'e.enrollment_id')
            ->where('p.student_id', $id)
            ->select(
                'p.payment_id',
                'p.payment_date',
                'p.receipt_number',
                'p.amount',
                'pm.method_name',
                'ps.status_name as payment_status',
                'e.enrollment_id',
                'p.booking_id'
            )
            ->orderBy('p.payment_date', 'desc')
            ->get();

        // Payment summary
        $paymentSummary = DB::table('payment')
            ->where('student_id', $id)
            ->selectRaw('
                SUM(amount) as total_paid
            ')
            ->first();

        // Outstanding balance from enrollments
        $outstandingBalance = DB::table('enrollment')
            ->where('student_id', $id)
            ->whereIn('status', ['active', 'on_hold'])
            ->selectRaw('SUM(total_amount - amount_paid) as outstanding')
            ->first();

        return response()->json([
            'student' => $student,
            'enrollments' => $enrollments,
            'upcomingSchedule' => $upcomingSchedule,
            'attendance' => $attendance,
            'attendanceStats' => $attendanceStats,
            'progress' => $progress,
            'progressTrend' => $progressTrend,
            'payments' => $payments,
            'paymentSummary' => $paymentSummary,
            'outstandingBalance' => $outstandingBalance->outstanding ?? 0
        ]);
    }

    /**
     * ========================================================================
     * Update student information
     * ========================================================================
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|regex:/^\d{11}$/',
            'instrument_id' => 'nullable|exists:instrument,instrument_id',
            'preferred_genre_id' => 'nullable|exists:genre,genre_id',
            'student_status_id' => 'required|exists:student_status,status_id',
            'skill_level' => 'nullable|in:beginner,intermediate,advanced,expert',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::table('student')
            ->where('student_id', $id)
            ->update([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'instrument_id' => $request->instrument_id,
                'preferred_genre_id' => $request->preferred_genre_id,
                'student_status_id' => $request->student_status_id,
                'skill_level' => $request->skill_level,
                'address_line1' => $request->address_line1,
                'city' => $request->city,
                'province' => $request->province,
                'emergency_contact_name' => $request->emergency_contact_name,
                'emergency_contact_phone' => $request->emergency_contact_phone,
                'emergency_contact_relationship' => $request->emergency_contact_relationship,
                'updated_at' => now(),
            ]);

        return response()->json(['success' => true, 'message' => 'Student updated successfully.']);
    }

    /**
     * ========================================================================
     * Bulk status update
     * ========================================================================
     */
    public function bulkUpdateStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_ids' => 'required|array',
            'student_ids.*' => 'exists:student,student_id',
            'status_id' => 'required|exists:student_status,status_id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::table('student')
            ->whereIn('student_id', $request->student_ids)
            ->update([
                'student_status_id' => $request->status_id,
                'updated_at' => now()
            ]);

        return response()->json([
            'success' => true,
            'message' => count($request->student_ids) . ' student(s) status updated successfully.'
        ]);
    }
}