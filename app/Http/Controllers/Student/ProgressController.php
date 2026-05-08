<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProgressController extends Controller
{
    /**
     * app/Http/Controllers/Student/ProgressController.php
     * 
     * Display student's progress history.
     *
     * Shows:
     * - instructor progress feedback
     * - attendance records
     * - active packages that are still waiting for instructor updates
     *
     * Important:
     * - Progress and attendance are only shown after instructors update them.
     * - This controller is read-only and does not auto-create records.
     */
    public function index()
    {
        $student = $this->getAuthenticatedStudent();
        $progressHistory = $this->getProgressHistory($student->student_id);
        $attendanceHistory = $this->getAttendanceHistory($student->student_id);
        $activeEnrollments = $this->getActiveEnrollments($student->student_id);
        $stats = $this->buildStats($progressHistory, $attendanceHistory, $activeEnrollments);

        return view('student.progress', compact(
            'progressHistory',
            'attendanceHistory',
            'activeEnrollments',
            'stats'
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
            abort(404, 'Student record not found');
        }

        return $student;
    }

    /**
     * Fetch progress history with full instructor name.
     */
    private function getProgressHistory(int $studentId)
    {
        return DB::table('progress as p')
            ->leftJoin('instructor as i', 'p.instructor_id', '=', 'i.instructor_id')
            ->leftJoin('enrollment as e', 'p.enrollment_id', '=', 'e.enrollment_id')
            ->leftJoin('instrument as inst', 'e.instrument_id', '=', 'inst.instrument_id')
            ->where('p.student_id', $studentId)
            ->select(
                'p.*',
                'inst.instrument_name',
                'i.first_name as instructor_first_name',
                'i.middle_name as instructor_middle_name',
                'i.last_name as instructor_last_name',
                'i.suffix as instructor_suffix',
                DB::raw("CONCAT(i.first_name, ' ', COALESCE(i.middle_name || ' ', ''), i.last_name, COALESCE(' ' || i.suffix, '')) AS instructor_full_name")
            )
            ->orderBy('p.progress_date', 'desc')
            ->get()
            ->map(function ($p) {
                $p->progress_date = Carbon::parse($p->progress_date);
                return $p;
            });
    }

    /**
     * Fetch attendance history for lessons only.
     */
    private function getAttendanceHistory(int $studentId)
    {
        return DB::table('attendance as a')
            ->join('schedule as s', 'a.schedule_id', '=', 's.schedule_id')
            ->leftJoin('instructor as i', 's.instructor_id', '=', 'i.instructor_id')
            ->leftJoin('enrollment as e', 's.enrollment_id', '=', 'e.enrollment_id')
            ->leftJoin('instrument as inst', 'e.instrument_id', '=', 'inst.instrument_id')
            ->where('a.student_id', $studentId)
            ->where('a.attendance_type', 'lesson')
            ->select(
                'a.attendance_date',
                'a.attendance_status',
                's.start_time',
                's.end_time',
                's.lesson_topic',
                'inst.instrument_name',
                'i.first_name as instructor_first_name',
                'i.middle_name as instructor_middle_name',
                'i.last_name as instructor_last_name',
                'i.suffix as instructor_suffix',
                DB::raw("CONCAT(i.first_name, ' ', COALESCE(i.middle_name || ' ', ''), i.last_name, COALESCE(' ' || i.suffix, '')) AS instructor_full_name")
            )
            ->orderBy('a.attendance_date', 'desc')
            ->get()
            ->map(function ($a) {
                $a->attendance_date = Carbon::parse($a->attendance_date);
                return $a;
            });
    }

    /**
     * Active enrollments are displayed when no progress/attendance exists yet.
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
                'e.enrollment_id',
                'e.start_date',
                'e.total_sessions',
                'e.completed_sessions',
                'e.remaining_sessions',
                'e.status',
                'e.preferred_lesson_days',
                'e.preferred_lesson_time',
                'ls.session_count',
                'inst.instrument_name',
                DB::raw("CONCAT(i.first_name, ' ', COALESCE(i.middle_name || ' ', ''), i.last_name, COALESCE(' ' || i.suffix, '')) AS instructor_full_name")
            )
            ->orderByDesc('e.start_date')
            ->get();
    }

    /**
     * Build stat cards for progress page.
     */
    private function buildStats($progressHistory, $attendanceHistory, $activeEnrollments): array
    {
        return [
            'progress_records' => $progressHistory->count(),
            'attendance_records' => $attendanceHistory->count(),
            'active_packages' => $activeEnrollments->where('status', 'active')->count(),
            'completed_sessions' => (int) $activeEnrollments->sum('completed_sessions'),
            'remaining_sessions' => (int) $activeEnrollments->sum('remaining_sessions'),
        ];
    }
}
