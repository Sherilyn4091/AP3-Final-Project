<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * app/Http/Controllers/Student/DashboardController.php
     * 
     * Show the student dashboard.
     *
     * Important:
     * - The current enrollment, next lesson, instructor, and progress must match.
     * - If a student has many enrollments, the dashboard uses the nearest upcoming
     *   schedule first, then falls back to the latest active enrollment.
     * - This method is read-only. It does not create schedules, attendance, or progress.
     */
    public function index()
    {
        $student = $this->getAuthenticatedStudent();
        $activeEnrollments = $this->getActiveEnrollments($student->student_id);
        $nextLesson = $this->getNextLesson($student->student_id);
        $currentEnrollment = $this->resolveCurrentEnrollment($activeEnrollments, $nextLesson);
        $progressPercentage = $this->calculateProgressPercentage($currentEnrollment);
        $recentProgress = $this->getRecentProgress($student->student_id, $currentEnrollment?->enrollment_id);
        $dashboardStats = $this->buildDashboardStats($student->student_id, $activeEnrollments);

        $this->prepareCompatibilityObjects($currentEnrollment, $nextLesson);

        return view('dashboards.student', compact(
            'student',
            'activeEnrollments',
            'currentEnrollment',
            'progressPercentage',
            'nextLesson',
            'recentProgress',
            'dashboardStats'
        ));
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
     * Get all active enrollments with package, instrument, instructor, and preferences.
     */
    private function getActiveEnrollments(int $studentId)
    {
        return DB::table('enrollment as e')
            ->leftJoin('lesson_session as ls', 'e.session_id', '=', 'ls.session_id')
            ->leftJoin('instrument as inst', 'e.instrument_id', '=', 'inst.instrument_id')
            ->leftJoin('instructor as i', 'e.instructor_id', '=', 'i.instructor_id')
            ->where('e.student_id', $studentId)
            ->whereIn('e.status', ['active', 'withdrawal_requested'])
            ->select(
                'e.*',
                'ls.session_count',
                'ls.session_name',
                'ls.price',
                'ls.duration_minutes',
                'inst.instrument_name',
                'i.first_name as instructor_first_name',
                'i.middle_name as instructor_middle_name',
                'i.last_name as instructor_last_name',
                'i.suffix as instructor_suffix',
                DB::raw("CONCAT(i.first_name, ' ', COALESCE(i.middle_name || ' ', ''), i.last_name, COALESCE(' ' || i.suffix, '')) AS instructor_full_name")
            )
            ->orderByDesc('e.enrollment_date')
            ->orderByDesc('e.created_at')
            ->get();
    }

    /**
     * Get next confirmed schedule row.
     */
    private function getNextLesson(int $studentId): ?object
    {
        return DB::table('schedule as s')
            ->join('enrollment as e', 's.enrollment_id', '=', 'e.enrollment_id')
            ->leftJoin('instrument as inst', 'e.instrument_id', '=', 'inst.instrument_id')
            ->leftJoin('instructor as i', 's.instructor_id', '=', 'i.instructor_id')
            ->where('s.student_id', $studentId)
            ->where('s.schedule_date', '>=', now()->toDateString())
            ->whereIn('s.status', ['scheduled', 'in_progress'])
            ->select(
                's.*',
                'e.instrument_id',
                'e.preferred_lesson_days',
                'e.preferred_lesson_time',
                'inst.instrument_name',
                'i.first_name as instructor_first_name',
                'i.middle_name as instructor_middle_name',
                'i.last_name as instructor_last_name',
                'i.suffix as instructor_suffix',
                DB::raw("CONCAT(i.first_name, ' ', COALESCE(i.middle_name || ' ', ''), i.last_name, COALESCE(' ' || i.suffix, '')) AS instructor_full_name")
            )
            ->orderBy('s.schedule_date')
            ->orderBy('s.start_time')
            ->first();
    }

    /**
     * Pick the enrollment connected to next lesson; fallback to latest active enrollment.
     */
    private function resolveCurrentEnrollment($activeEnrollments, ?object $nextLesson): ?object
    {
        if ($nextLesson) {
            $matched = $activeEnrollments->firstWhere('enrollment_id', $nextLesson->enrollment_id);

            if ($matched) {
                return $matched;
            }
        }

        return $activeEnrollments->first();
    }

    /**
     * Calculate progress percentage safely.
     */
    private function calculateProgressPercentage(?object $currentEnrollment): int
    {
        if (!$currentEnrollment || (int) $currentEnrollment->total_sessions <= 0) {
            return 0;
        }

        return (int) round(((int) $currentEnrollment->completed_sessions / (int) $currentEnrollment->total_sessions) * 100);
    }

    /**
     * Get recent progress records for the current package when available.
     */
    private function getRecentProgress(int $studentId, ?string $enrollmentId)
    {
        $query = DB::table('progress as p')
            ->leftJoin('instructor as i', 'p.instructor_id', '=', 'i.instructor_id')
            ->where('p.student_id', $studentId)
            ->select(
                'p.*',
                'i.first_name as instructor_first_name',
                'i.middle_name as instructor_middle_name',
                'i.last_name as instructor_last_name',
                'i.suffix as instructor_suffix',
                DB::raw("CONCAT(i.first_name, ' ', COALESCE(i.middle_name || ' ', ''), i.last_name, COALESCE(' ' || i.suffix, '')) AS instructor_full_name")
            )
            ->orderByDesc('p.progress_date')
            ->limit(6);

        if ($enrollmentId) {
            $query->where('p.enrollment_id', $enrollmentId);
        }

        return $query->get()->map(function ($progress) {
            $progress->progress_date = Carbon::parse($progress->progress_date);
            return $progress;
        });
    }

    /**
     * Build compact dashboard stat cards.
     */
    private function buildDashboardStats(int $studentId, $activeEnrollments): array
    {
        $upcomingLessons = DB::table('schedule')
            ->where('student_id', $studentId)
            ->whereDate('schedule_date', '>=', now()->toDateString())
            ->whereIn('status', ['scheduled', 'in_progress'])
            ->count();

        return [
            'active_packages' => $activeEnrollments->where('status', 'active')->count(),
            'remaining_sessions' => (int) $activeEnrollments->sum('remaining_sessions'),
            'completed_sessions' => (int) $activeEnrollments->sum('completed_sessions'),
            'upcoming_lessons' => $upcomingLessons,
            'withdrawal_requests' => $activeEnrollments->where('status', 'withdrawal_requested')->count(),
        ];
    }

    /**
     * Keep existing dashboard Blade safe if it expects relationship-like properties.
     */
    private function prepareCompatibilityObjects(?object $currentEnrollment, ?object $nextLesson): void
    {
        if ($currentEnrollment) {
            $currentEnrollment->enrollment_date = $currentEnrollment->enrollment_date
                ? Carbon::parse($currentEnrollment->enrollment_date)
                : null;

            $currentEnrollment->start_date = $currentEnrollment->start_date
                ? Carbon::parse($currentEnrollment->start_date)
                : null;

            $currentEnrollment->lessonSession = (object) [
                'session_count' => $currentEnrollment->session_count,
                'session_name' => $currentEnrollment->session_name,
                'price' => $currentEnrollment->price,
                'duration_minutes' => $currentEnrollment->duration_minutes,
            ];

            $currentEnrollment->instrument = (object) [
                'instrument_name' => $currentEnrollment->instrument_name,
            ];

            $currentEnrollment->instructor = (object) [
                'first_name' => $currentEnrollment->instructor_first_name,
                'middle_name' => $currentEnrollment->instructor_middle_name,
                'last_name' => $currentEnrollment->instructor_last_name,
                'suffix' => $currentEnrollment->instructor_suffix,
                'full_name' => $currentEnrollment->instructor_full_name,
            ];
        }

        if ($nextLesson) {
            $nextLesson->schedule_date = Carbon::parse($nextLesson->schedule_date);
            $nextLesson->start_time = Carbon::parse($nextLesson->start_time);
            $nextLesson->end_time = Carbon::parse($nextLesson->end_time);

            $nextLesson->instructor = (object) [
                'first_name' => $nextLesson->instructor_first_name,
                'middle_name' => $nextLesson->instructor_middle_name,
                'last_name' => $nextLesson->instructor_last_name,
                'suffix' => $nextLesson->instructor_suffix,
                'full_name' => $nextLesson->instructor_full_name,
            ];

            $nextLesson->instrument = (object) [
                'instrument_name' => $nextLesson->instrument_name,
            ];
        }
    }
}
