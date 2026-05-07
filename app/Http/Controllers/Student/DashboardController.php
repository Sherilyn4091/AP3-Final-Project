<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Show the student dashboard.
     *
     * Important:
     * - The current enrollment, next lesson, instructor, and progress must match.
     * - If a student has many enrollments, the dashboard uses the nearest upcoming
     *   schedule first, then falls back to the latest active enrollment.
     */
    public function index()
    {
        $userId = Auth::id();

        $student = DB::table('student')
            ->where('user_id', $userId)
            ->first();

        if (!$student) {
            abort(404, 'Student record not found.');
        }

        /*
        |--------------------------------------------------------------------------
        | Active Enrollments
        |--------------------------------------------------------------------------
        */
        $activeEnrollments = DB::table('enrollment as e')
            ->leftJoin('lesson_session as ls', 'e.session_id', '=', 'ls.session_id')
            ->leftJoin('instrument as inst', 'e.instrument_id', '=', 'inst.instrument_id')
            ->leftJoin('instructor as i', 'e.instructor_id', '=', 'i.instructor_id')
            ->where('e.student_id', $student->student_id)
            ->where('e.status', 'active')
            ->select(
                'e.*',
                'ls.session_count',
                'ls.session_name',
                'ls.price',
                'ls.duration_minutes',
                'inst.instrument_name',
                'i.first_name as instructor_first_name',
                'i.last_name as instructor_last_name'
            )
            ->orderByDesc('e.enrollment_date')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Next Lesson
        |--------------------------------------------------------------------------
        |
        | Joined through enrollment so the displayed instrument and instructor
        | match the exact enrollment.
        |
        */
        $nextLesson = DB::table('schedule as s')
            ->join('enrollment as e', 's.enrollment_id', '=', 'e.enrollment_id')
            ->leftJoin('instrument as inst', 'e.instrument_id', '=', 'inst.instrument_id')
            ->leftJoin('instructor as i', 's.instructor_id', '=', 'i.instructor_id')
            ->where('s.student_id', $student->student_id)
            ->where('s.schedule_date', '>=', now()->toDateString())
            ->where('s.status', 'scheduled')
            ->select(
                's.*',
                'e.instrument_id',
                'inst.instrument_name',
                'i.first_name as instructor_first_name',
                'i.last_name as instructor_last_name'
            )
            ->orderBy('s.schedule_date')
            ->orderBy('s.start_time')
            ->first();

        /*
        |--------------------------------------------------------------------------
        | Current Enrollment
        |--------------------------------------------------------------------------
        */
        $currentEnrollment = null;

        if ($nextLesson) {
            $currentEnrollment = $activeEnrollments
                ->firstWhere('enrollment_id', $nextLesson->enrollment_id);
        }

        if (!$currentEnrollment && $activeEnrollments->isNotEmpty()) {
            $currentEnrollment = $activeEnrollments->first();
        }

        /*
        |--------------------------------------------------------------------------
        | Progress Percentage
        |--------------------------------------------------------------------------
        */
        $progressPercentage = 0;

        if ($currentEnrollment && (int) $currentEnrollment->total_sessions > 0) {
            $progressPercentage = round(
                ((int) $currentEnrollment->completed_sessions / (int) $currentEnrollment->total_sessions) * 100
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Recent Progress
        |--------------------------------------------------------------------------
        */
        $recentProgressQuery = DB::table('progress as p')
            ->leftJoin('instructor as i', 'p.instructor_id', '=', 'i.instructor_id')
            ->where('p.student_id', $student->student_id)
            ->select(
                'p.*',
                'i.first_name as instructor_first_name',
                'i.last_name as instructor_last_name'
            )
            ->orderByDesc('p.progress_date')
            ->limit(6);

        if ($currentEnrollment) {
            $recentProgressQuery->where('p.enrollment_id', $currentEnrollment->enrollment_id);
        }

        $recentProgress = $recentProgressQuery->get();

        /*
        |--------------------------------------------------------------------------
        | Blade Compatibility Objects
        |--------------------------------------------------------------------------
        |
        | These objects keep the existing dashboard Blade safe if it expects
        | relationship-like properties such as $currentEnrollment->lessonSession.
        |
        */
        if ($currentEnrollment) {
            $currentEnrollment->enrollment_date = $currentEnrollment->enrollment_date
                ? Carbon::parse($currentEnrollment->enrollment_date)
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
                'last_name' => $currentEnrollment->instructor_last_name,
            ];
        }

        if ($nextLesson) {
            $nextLesson->schedule_date = Carbon::parse($nextLesson->schedule_date);
            $nextLesson->start_time = Carbon::parse($nextLesson->start_time);
            $nextLesson->end_time = Carbon::parse($nextLesson->end_time);

            $nextLesson->instructor = (object) [
                'first_name' => $nextLesson->instructor_first_name,
                'last_name' => $nextLesson->instructor_last_name,
            ];

            $nextLesson->instrument = (object) [
                'instrument_name' => $nextLesson->instrument_name,
            ];
        }

        $recentProgress = $recentProgress->map(function ($progress) {
            $progress->progress_date = Carbon::parse($progress->progress_date);

            return $progress;
        });

        return view('dashboards.student', compact(
            'student',
            'currentEnrollment',
            'progressPercentage',
            'nextLesson',
            'recentProgress'
        ));
    }
}