<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\PythonAnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

/*
|--------------------------------------------------------------------------
| StudentRiskAnalyticsController
|--------------------------------------------------------------------------
|
| New admin data mining module for Music Lab.
|
| Technique: Classification
| Algorithm: Decision Tree Classification
| Purpose: Identify students at low, medium, or high risk of not continuing
| lessons based on attendance, progress, enrollment, payment, and schedule data.
|
*/

class StudentRiskAnalyticsController extends Controller
{
    /**
     * Python analytics service used to run the student risk analytics engine.
     */
    private PythonAnalyticsService $analyticsService;

    /**
     * Inject the Python analytics service.
     */
    public function __construct(PythonAnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    /**
     * Show the main Student Risk Analytics page.
     */
    public function index()
    {
        $this->authorizeAdmin();

        return view('admin.student-risk-analytics.index');
    }

    /**
     * Return full analytics for the Student Risk Analytics page.
     */
    public function data(Request $request): JsonResponse
    {
        $this->authorizeAdmin();

        return $this->runAnalytics('full');
    }

    /**
     * Return compact analytics for the Admin Dashboard widget.
     */
    public function dashboardData(): JsonResponse
    {
        $this->authorizeAdmin();

        return $this->runAnalytics('dashboard');
    }

    /**
     * Return report-oriented analytics for the Reports page.
     */
    public function reportData(): JsonResponse
    {
        $this->authorizeAdmin();

        return $this->runAnalytics('reports');
    }

    /**
     * Export the current risk result as CSV.
     */
    public function exportCsv(): StreamedResponse
    {
        $this->authorizeAdmin();

        $result = $this->analyticsService->runStudentRiskAnalysis(
            $this->buildStudentDataset(),
            'full'
        );

        $fileName = 'student-risk-analytics-' . now()->format('Y-m-d_His') . '.csv';

        return response()->streamDownload(function () use ($result) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Student ID',
                'Student Name',
                'Instrument',
                'Instructor',
                'Risk Level',
                'Risk Score',
                'Attendance Rate',
                'Absences',
                'Late Count',
                'Average Progress Rating',
                'Enrollment Status',
                'Payment Status',
                'Remaining Sessions',
                'Days Since Last Lesson',
                'Primary Reason',
                'Recommended Action',
            ]);

            foreach ($result['students'] ?? [] as $student) {
                fputcsv($handle, [
                    $student['student_id'] ?? '',
                    $student['student_name'] ?? '',
                    $student['instrument_name'] ?? '',
                    $student['instructor_name'] ?? '',
                    $student['risk_level'] ?? '',
                    $student['risk_score'] ?? '',
                    $student['attendance_rate'] ?? '',
                    $student['absence_count'] ?? '',
                    $student['late_count'] ?? '',
                    $student['average_progress_rating'] ?? '',
                    $student['enrollment_status'] ?? '',
                    $student['payment_status'] ?? '',
                    $student['remaining_sessions'] ?? '',
                    $student['days_since_last_lesson'] ?? '',
                    $student['primary_reason'] ?? '',
                    $student['recommended_action'] ?? '',
                ]);
            }

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv',
        ]);
    }

    /**
     * Execute Python analytics and return JSON response.
     */
    private function runAnalytics(string $mode): JsonResponse
    {
        try {
            $result = $this->analyticsService->runStudentRiskAnalysis(
                $this->buildStudentDataset(),
                $mode
            );

            return response()->json($result);
        } catch (\Throwable $exception) {
            Log::error('Student risk analytics failed.', [
                'mode' => $mode,
                'message' => $exception->getMessage(),
            ]);

            return response()->json([
                'ok' => false,
                'message' => 'Student Risk Analytics is temporarily unavailable.',
                'detail' => config('app.debug') ? $exception->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Admin authorization check that matches the current Music Lab role design.
     */
    private function authorizeAdmin(): void
    {
        $isSuperAdmin = DB::table('user_account')
            ->where('user_id', Auth::id())
            ->value('is_super_admin');

        if (!$isSuperAdmin) {
            abort(403, 'Unauthorized access.');
        }
    }

    /**
     * Build the student feature dataset that Python will classify.
     */
    private function buildStudentDataset(): array
    {
        $students = $this->baseStudentRows();
        $latestEnrollments = $this->latestEnrollmentRows();
        $attendanceStats = $this->attendanceStatsByStudent();
        $progressStats = $this->progressStatsByStudent();
        $scheduleStats = $this->scheduleStatsByStudent();

        return $students->map(function ($student) use ($latestEnrollments, $attendanceStats, $progressStats, $scheduleStats) {
            $studentId = (int) $student->student_id;
            $enrollment = $latestEnrollments[$studentId] ?? null;
            $attendance = $attendanceStats[$studentId] ?? null;
            $progress = $progressStats[$studentId] ?? null;
            $schedule = $scheduleStats[$studentId] ?? null;

            $totalAttendance = (int) ($attendance->total_attendance ?? 0);
            $presentCount = (int) ($attendance->present_count ?? 0);
            $attendanceRate = $totalAttendance > 0
                ? round(($presentCount / $totalAttendance) * 100, 2)
                : 100.0;

            return [
                'student_id' => $studentId,
                'student_name' => trim($student->student_name),
                'email' => $student->email,
                'phone' => $student->phone,
                'is_active' => (bool) $student->is_active,
                'student_status' => $student->status_name,
                'instrument_name' => $student->instrument_name,
                'instructor_name' => $enrollment->instructor_name ?? 'Unassigned',
                'enrollment_status' => $enrollment->enrollment_status ?? 'none',
                'payment_status' => $enrollment->payment_status ?? 'none',
                'total_sessions' => (int) ($enrollment->total_sessions ?? 0),
                'completed_sessions' => (int) ($enrollment->completed_sessions ?? 0),
                'remaining_sessions' => (int) ($enrollment->remaining_sessions ?? 0),
                'attendance_rate' => $attendanceRate,
                'absence_count' => (int) ($attendance->absence_count ?? 0),
                'late_count' => (int) ($attendance->late_count ?? 0),
                'average_progress_rating' => round((float) ($progress->average_progress_rating ?? 8.0), 2),
                'last_lesson_date' => $schedule->last_lesson_date ?? null,
                'days_since_last_lesson' => (int) ($schedule->days_since_last_lesson ?? 0),
            ];
        })->values()->all();
    }

    /**
     * Main student records for analytics.
     */
    private function baseStudentRows()
    {
        return DB::table('student as s')
            ->leftJoin('student_status as ss', 's.student_status_id', '=', 'ss.status_id')
            ->leftJoin('instrument as inst', 's.instrument_id', '=', 'inst.instrument_id')
            ->select(
                's.student_id',
                DB::raw("CONCAT(s.first_name, ' ', s.last_name) as student_name"),
                's.email',
                's.phone',
                's.is_active',
                'ss.status_name',
                'inst.instrument_name'
            )
            ->orderBy('s.last_name')
            ->orderBy('s.first_name')
            ->get();
    }

    /**
     * Latest enrollment row per student using PostgreSQL DISTINCT ON.
     */
    private function latestEnrollmentRows(): array
    {
        return DB::table(DB::raw('(
                SELECT DISTINCT ON (e.student_id)
                    e.student_id,
                    e.enrollment_id,
                    e.status as enrollment_status,
                    e.payment_status,
                    e.total_sessions,
                    e.completed_sessions,
                    e.remaining_sessions,
                    e.enrollment_date,
                    e.created_at,
                    CONCAT(i.first_name, \' \', i.last_name) as instructor_name
                FROM enrollment e
                LEFT JOIN instructor i ON e.instructor_id = i.instructor_id
                ORDER BY e.student_id, e.enrollment_date DESC NULLS LAST, e.created_at DESC NULLS LAST
            ) as latest_enrollment'))
            ->get()
            ->keyBy('student_id')
            ->all();
    }

    /**
     * Attendance metrics grouped by student.
     */
    private function attendanceStatsByStudent(): array
    {
        return DB::table('attendance')
            ->where('attendance_type', 'lesson')
            ->select(
                'student_id',
                DB::raw('COUNT(*) as total_attendance'),
                DB::raw("SUM(CASE WHEN attendance_status = 'present' THEN 1 ELSE 0 END) as present_count"),
                DB::raw("SUM(CASE WHEN attendance_status = 'absent' THEN 1 ELSE 0 END) as absence_count"),
                DB::raw("SUM(CASE WHEN attendance_status = 'late' THEN 1 ELSE 0 END) as late_count")
            )
            ->whereNotNull('student_id')
            ->groupBy('student_id')
            ->get()
            ->keyBy('student_id')
            ->all();
    }

    /**
     * Progress rating metrics grouped by student.
     */
    private function progressStatsByStudent(): array
    {
        return DB::table('progress')
            ->select(
                'student_id',
                DB::raw('AVG(COALESCE(performance_rating, technical_skills_rating, musicality_rating, effort_rating)) as average_progress_rating')
            )
            ->whereNotNull('student_id')
            ->groupBy('student_id')
            ->get()
            ->keyBy('student_id')
            ->all();
    }

    /**
     * Last lesson schedule metrics grouped by student.
     */
    private function scheduleStatsByStudent(): array
    {
        return DB::table('schedule')
            ->select(
                'student_id',
                DB::raw('MAX(schedule_date) as last_lesson_date'),
                DB::raw('COALESCE((CURRENT_DATE - MAX(schedule_date))::int, 0) as days_since_last_lesson')
            )
            ->whereNotNull('student_id')
            ->where('status', '!=', 'cancelled')
            ->groupBy('student_id')
            ->get()
            ->keyBy('student_id')
            ->all();
    }
}

