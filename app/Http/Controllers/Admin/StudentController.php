<?php

/**
 * ============================================================================
 * STUDENT MANAGEMENT CONTROLLER
 * app/Http/Controllers/Admin/StudentController.php
 * ============================================================================
 *
 * Handles all student management operations including:
 * - List view with filters
 * - Student details modal
 * - Student update
 * - Attendance endpoint
 * - Progress endpoint
 * - Bulk status update
 *
 * Notes:
 * - The separate getAttendance() and getProgress() methods are included because
 *   your routes already point to them.
 * - The show() method still returns the full student modal data.
 * - Queries are kept grouped by responsibility to avoid code smell.
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
     */
    public function index(Request $request)
    {
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

                // Count active enrollments.
                DB::raw("(SELECT COUNT(*) FROM enrollment WHERE student_id = s.student_id AND status = 'active') as active_enrollments"),

                // Sum remaining sessions from active enrollments.
                DB::raw("(SELECT COALESCE(SUM(remaining_sessions), 0) FROM enrollment WHERE student_id = s.student_id AND status = 'active') as total_remaining_sessions"),

                // Latest lesson date.
                DB::raw('(SELECT MAX(schedule_date) FROM schedule WHERE student_id = s.student_id) as last_lesson_date'),

                // Payment status from the latest active enrollment.
                DB::raw("(SELECT payment_status FROM enrollment WHERE student_id = s.student_id AND status = 'active' ORDER BY enrollment_date DESC LIMIT 1) as payment_status")
            );

        // Filter by student status.
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('s.student_status_id', $request->status);
        }

        // Filter by instrument.
        if ($request->filled('instrument') && $request->instrument !== 'all') {
            $query->where('s.instrument_id', $request->instrument);
        }

        // Filter by genre.
        if ($request->filled('genre') && $request->genre !== 'all') {
            $query->where('s.preferred_genre_id', $request->genre);
        }

        // Filter by enrollment status.
        if ($request->filled('enrollment_status') && $request->enrollment_status !== 'all') {
            $enrollmentStatus = $request->enrollment_status;

            $query->whereExists(function ($q) use ($enrollmentStatus) {
                $q->select(DB::raw(1))
                    ->from('enrollment')
                    ->whereRaw('enrollment.student_id = s.student_id')
                    ->where('enrollment.status', $enrollmentStatus);
            });
        }

        // Filter by payment status.
        if ($request->filled('payment_status') && $request->payment_status !== 'all') {
            $paymentStatus = $request->payment_status;

            $query->whereExists(function ($q) use ($paymentStatus) {
                $q->select(DB::raw(1))
                    ->from('enrollment')
                    ->whereRaw('enrollment.student_id = s.student_id')
                    ->where('enrollment.payment_status', $paymentStatus)
                    ->where('enrollment.status', 'active');
            });
        }

        // Search by name, email, phone, or student ID.
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where(DB::raw("CONCAT(s.first_name, ' ', s.last_name)"), 'ILIKE', "%{$search}%")
                    ->orWhere('s.email', 'ILIKE', "%{$search}%")
                    ->orWhere('s.phone', 'ILIKE', "%{$search}%")
                    ->orWhere(DB::raw('CAST(s.student_id AS TEXT)'), 'ILIKE', "%{$search}%");
            });
        }

        // Date filters.
        if ($request->filled('date_from')) {
            $query->whereDate('s.created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('s.created_at', '<=', $request->date_to);
        }

        // Sorting.
        $sortBy = $request->input('sort_by', 'name');
        $sortOrder = $request->input('sort_order', 'asc');

        switch ($sortBy) {
            case 'last_lesson':
                $query->orderByRaw("(SELECT MAX(schedule_date) FROM schedule WHERE student_id = s.student_id) {$sortOrder} NULLS LAST");
                break;

            case 'enrollments':
                $query->orderByRaw("(SELECT COUNT(*) FROM enrollment WHERE student_id = s.student_id AND status = 'active') {$sortOrder}");
                break;

            case 'status':
                $query->orderBy('ss.status_name', $sortOrder);
                break;

            default:
                $query->orderBy(DB::raw("CONCAT(s.first_name, ' ', s.last_name)"), $sortOrder);
                break;
        }

        $students = $query->paginate(20)->withQueryString();

        $statuses = DB::table('student_status')
            ->whereRaw('is_active = TRUE')
            ->orderBy('status_name')
            ->get();

        $instruments = DB::table('instrument')
            ->whereRaw('is_active = TRUE')
            ->orderBy('instrument_name')
            ->get();

        $genres = DB::table('genre')
            ->whereRaw('is_active = TRUE')
            ->orderBy('genre_name')
            ->get();

        return view('admin.students.index', compact('students', 'statuses', 'instruments', 'genres'));
    }

    /**
     * Get student details for modal view with all tabs data.
     */
    public function show($id)
    {
        $student = DB::table('student as s')
            ->leftJoin('instrument as i', 's.instrument_id', '=', 'i.instrument_id')
            ->leftJoin('genre as g', 's.preferred_genre_id', '=', 'g.genre_id')
            ->leftJoin('student_status as ss', 's.student_status_id', '=', 'ss.status_id')
            ->where('s.student_id', $id)
            ->select('s.*', 'i.instrument_name', 'g.genre_name', 'ss.status_name')
            ->first();

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student not found.',
            ], 404);
        }

        $enrollments = $this->getEnrollmentHistory($id);
        $upcomingSchedule = $this->getScheduleHistory($id);
        $attendance = $this->getAttendanceRows($id, 20);
        $attendanceStats = $this->getAttendanceStats($id);
        $progress = $this->getProgressRows($id, 15);
        $progressTrend = $this->getProgressTrend($id);
        $payments = $this->getPaymentRows($id);
        $paymentSummary = $this->getPaymentSummary($id);
        $outstandingBalance = $this->getOutstandingBalance($id);

        return response()->json([
            'success' => true,
            'student' => $student,
            'enrollments' => $enrollments,
            'upcomingSchedule' => $upcomingSchedule,
            'attendance' => $attendance,
            'attendanceStats' => $attendanceStats,
            'progress' => $progress,
            'progressTrend' => $progressTrend,
            'payments' => $payments,
            'paymentSummary' => $paymentSummary,
            'outstandingBalance' => $outstandingBalance,
        ]);
    }

    /**
     * Update student information.
     */
    public function update(Request $request, $id)
    {
        $studentExists = DB::table('student')
            ->where('student_id', $id)
            ->exists();

        if (!$studentExists) {
            return response()->json([
                'success' => false,
                'message' => 'Student not found.',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|regex:/^\d{11}$/',
            'instrument_id' => 'nullable|exists:instrument,instrument_id',
            'preferred_genre_id' => 'nullable|exists:genre,genre_id',
            'student_status_id' => 'required|exists:student_status,status_id',
            'skill_level' => 'nullable|in:beginner,intermediate,advanced,expert',
            'address_line1' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'province' => 'nullable|string|max:100',
            'emergency_contact_name' => 'nullable|string|max:200',
            'emergency_contact_phone' => 'nullable|string|max:11',
            'emergency_contact_relationship' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::table('student')
            ->where('student_id', $id)
            ->update([
                'first_name' => trim($request->first_name),
                'last_name' => trim($request->last_name),
                'email' => strtolower(trim($request->email)),
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

        return response()->json([
            'success' => true,
            'message' => 'Student updated successfully.',
        ]);
    }

    /**
     * Get student attendance records.
     *
     * This fixes the route:
     * GET /admin/students/{id}/attendance
     */
    public function getAttendance($id)
    {
        if (!$this->studentExists($id)) {
            return response()->json([
                'success' => false,
                'message' => 'Student not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'attendance' => $this->getAttendanceRows($id, 50),
            'stats' => $this->getAttendanceStats($id),
        ]);
    }

    /**
     * Get student progress records.
     *
     * This fixes the route:
     * GET /admin/students/{id}/progress
     */
    public function getProgress($id)
    {
        if (!$this->studentExists($id)) {
            return response()->json([
                'success' => false,
                'message' => 'Student not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'progress' => $this->getProgressRows($id, 50),
            'trend' => $this->getProgressTrend($id),
        ]);
    }

    /**
     * Bulk update student status.
     */
    public function bulkUpdateStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_ids' => 'required|array',
            'student_ids.*' => 'exists:student,student_id',
            'status_id' => 'required|exists:student_status,status_id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::table('student')
            ->whereIn('student_id', $request->student_ids)
            ->update([
                'student_status_id' => $request->status_id,
                'updated_at' => now(),
            ]);

        return response()->json([
            'success' => true,
            'message' => count($request->student_ids) . ' student(s) status updated successfully.',
        ]);
    }

    /**
     * Check if a student exists.
     */
    private function studentExists($id): bool
    {
        return DB::table('student')
            ->where('student_id', $id)
            ->exists();
    }

    /**
     * Fetch enrollment history.
     */
    private function getEnrollmentHistory($studentId)
    {
        return DB::table('enrollment as e')
            ->leftJoin('lesson_session as ls', 'e.session_id', '=', 'ls.session_id')
            ->leftJoin('instructor as i', 'e.instructor_id', '=', 'i.instructor_id')
            ->where('e.student_id', $studentId)
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
    }

    /**
     * Fetch recent and upcoming schedule records.
     */
    private function getScheduleHistory($studentId)
    {
        return DB::table('schedule as sch')
            ->leftJoin('instructor as i', 'sch.instructor_id', '=', 'i.instructor_id')
            ->where('sch.student_id', $studentId)
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
    }

    /**
     * Fetch attendance rows.
     */
    private function getAttendanceRows($studentId, int $limit)
    {
        return DB::table('attendance as a')
            ->join('schedule as sch', 'a.schedule_id', '=', 'sch.schedule_id')
            ->leftJoin('instructor as i', 'sch.instructor_id', '=', 'i.instructor_id')
            ->where('a.student_id', $studentId)
            ->where('a.attendance_type', 'lesson')
            ->select(
                'a.attendance_id',
                'a.attendance_date',
                'a.attendance_status',
                'a.check_in_time',
                'a.check_out_time',
                'sch.schedule_id',
                'sch.schedule_date',
                'sch.start_time',
                'sch.end_time',
                'sch.room_number',
                'sch.lesson_topic',
                DB::raw("CONCAT(i.first_name, ' ', i.last_name) as instructor_name")
            )
            ->orderBy('a.attendance_date', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Fetch attendance statistics.
     */
    private function getAttendanceStats($studentId)
    {
        return DB::table('attendance')
            ->where('student_id', $studentId)
            ->where('attendance_type', 'lesson')
            ->selectRaw("
                COUNT(*) as total_lessons,
                SUM(CASE WHEN attendance_status = 'present' THEN 1 ELSE 0 END) as present_count,
                SUM(CASE WHEN attendance_status = 'absent' THEN 1 ELSE 0 END) as absent_count,
                SUM(CASE WHEN attendance_status = 'late' THEN 1 ELSE 0 END) as late_count,
                ROUND(
                    (
                        SUM(CASE WHEN attendance_status = 'present' THEN 1 ELSE 0 END)::numeric
                        / NULLIF(COUNT(*), 0)
                    ) * 100,
                    2
                ) as attendance_rate
            ")
            ->first();
    }

    /**
     * Fetch progress rows.
     */
    private function getProgressRows($studentId, int $limit)
    {
        return DB::table('progress as p')
            ->leftJoin('instructor as i', 'p.instructor_id', '=', 'i.instructor_id')
            ->leftJoin('schedule as sch', 'p.schedule_id', '=', 'sch.schedule_id')
            ->where('p.student_id', $studentId)
            ->select(
                'p.progress_id',
                'p.enrollment_id',
                'p.schedule_id',
                'p.progress_date',
                'p.lesson_topic',
                'p.skills_covered',
                'p.techniques_learned',
                'p.songs_practiced',
                'p.performance_rating',
                'p.technical_skills_rating',
                'p.musicality_rating',
                'p.effort_rating',
                'p.strengths',
                'p.areas_for_improvement',
                'p.instructor_notes',
                'p.homework',
                'p.practice_recommendations',
                'p.next_lesson_focus',
                'p.student_comments',
                'p.student_satisfaction',
                DB::raw("CONCAT(i.first_name, ' ', i.last_name) as instructor_name"),
                'sch.schedule_date',
                'sch.start_time',
                'sch.end_time'
            )
            ->orderBy('p.progress_date', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Fetch progress trend by month.
     */
    private function getProgressTrend($studentId)
    {
        return DB::table('progress')
            ->where('student_id', $studentId)
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
    }

    /**
     * Fetch payment rows based on enrollment payment data.
     */
    private function getPaymentRows($studentId)
    {
        return DB::table('enrollment as e')
            ->leftJoin('payment_methods as pm', 'e.payment_method_id', '=', 'pm.method_id')
            ->where('e.student_id', $studentId)
            ->where('e.amount_paid', '>', 0)
            ->select(
                'e.enrollment_id',
                'e.enrollment_date as payment_date',
                'e.enrollment_id as receipt_number',
                'e.amount_paid as amount',
                'pm.method_name',
                'e.payment_status'
            )
            ->orderBy('e.enrollment_date', 'desc')
            ->get();
    }

    /**
     * Fetch payment summary from payment table.
     */
    private function getPaymentSummary($studentId)
    {
        return DB::table('payment')
            ->where('student_id', $studentId)
            ->selectRaw('COALESCE(SUM(amount), 0) as total_paid')
            ->first();
    }

    /**
     * Calculate outstanding balance from active/on-hold enrollments.
     */
    private function getOutstandingBalance($studentId)
    {
        $balance = DB::table('enrollment')
            ->where('student_id', $studentId)
            ->whereIn('status', ['active', 'on_hold'])
            ->selectRaw('COALESCE(SUM(total_amount - amount_paid), 0) as outstanding')
            ->first();

        return $balance->outstanding ?? 0;
    }
}