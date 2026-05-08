<?php
// app/Http/Controllers/Instructor/InstructorDashboardController.php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InstructorDashboardController extends Controller
{
    /**
     * Show the instructor dashboard using only records owned by the logged-in instructor.
     *
     * Data source summary:
     * - instructor table identifies the logged-in instructor.
     * - enrollment connects instructor + student + instrument + package.
     * - schedule stores lesson dates and times.
     * - attendance stores lesson attendance per schedule.
     * - progress stores notes, ratings, homework, and practice recommendations.
     */
    public function index()
    {
        $userId = Auth::user()->user_id;
        $today = Carbon::today()->toDateString();

        $instructor = DB::table('instructor')
            ->where('user_id', $userId)
            ->first();

        if (!$instructor) {
            return view('instructor.dashboard', [
                'instructor' => null,
                'stats' => $this->emptyStats(),
                'todayClasses' => collect(),
                'nextClasses' => collect(),
                'recentStudents' => collect(),
                'recentProgress' => collect(),
                'homeworkList' => collect(),
            ]);
        }

        $instructorId = (int) $instructor->instructor_id;

        $stats = [
            'total_students' => DB::table('enrollment')
                ->where('instructor_id', $instructorId)
                ->distinct('student_id')
                ->count('student_id'),

            'active_enrollments' => DB::table('enrollment')
                ->where('instructor_id', $instructorId)
                ->where('status', 'active')
                ->count(),

            'remaining_sessions' => (int) DB::table('enrollment')
                ->where('instructor_id', $instructorId)
                ->where('status', 'active')
                ->sum('remaining_sessions'),

            'today_classes' => DB::table('schedule')
                ->where('instructor_id', $instructorId)
                ->whereDate('schedule_date', $today)
                ->whereNotIn('status', ['cancelled', 'no_class', 'rescheduled'])
                ->count(),

            'upcoming_classes' => DB::table('schedule')
                ->where('instructor_id', $instructorId)
                ->whereDate('schedule_date', '>=', $today)
                ->whereNotIn('status', ['cancelled', 'no_class', 'rescheduled'])
                ->count(),

            'completed_classes' => DB::table('attendance as a')
                ->join('schedule as s', 's.schedule_id', '=', 'a.schedule_id')
                ->where('s.instructor_id', $instructorId)
                ->where('a.attendance_type', 'lesson')
                ->whereIn('a.attendance_status', ['present', 'late'])
                ->count(),

            'pending_attendance' => DB::table('schedule as s')
                ->leftJoin('attendance as a', function ($join) {
                    $join->on('a.schedule_id', '=', 's.schedule_id')
                        ->where('a.attendance_type', '=', 'lesson');
                })
                ->where('s.instructor_id', $instructorId)
                ->whereDate('s.schedule_date', '<=', $today)
                ->whereNotIn('s.status', ['cancelled', 'no_class', 'rescheduled'])
                ->whereNull('a.attendance_id')
                ->count(),

            'progress_records' => DB::table('progress')
                ->where('instructor_id', $instructorId)
                ->count(),

            'homework_given' => DB::table('progress')
                ->where('instructor_id', $instructorId)
                ->whereNotNull('homework')
                ->whereRaw("NULLIF(TRIM(homework), '') IS NOT NULL")
                ->count(),

            'average_rating' => round((float) DB::table('progress')
                ->where('instructor_id', $instructorId)
                ->whereNotNull('performance_rating')
                ->avg('performance_rating'), 1),
        ];

        $todayClasses = $this->scheduleQuery($instructorId)
            ->whereDate('s.schedule_date', $today)
            ->orderBy('s.start_time')
            ->get();

        $nextClasses = $this->scheduleQuery($instructorId)
            ->whereDate('s.schedule_date', '>=', $today)
            ->orderBy('s.schedule_date')
            ->orderBy('s.start_time')
            ->limit(6)
            ->get();

        $recentStudents = DB::table('enrollment as e')
            ->join('student as st', 'st.student_id', '=', 'e.student_id')
            ->join('instrument as ins', 'ins.instrument_id', '=', 'e.instrument_id')
            ->leftJoin('student_status as ss', 'ss.status_id', '=', 'st.student_status_id')
            ->where('e.instructor_id', $instructorId)
            ->select([
                'st.student_id',
                DB::raw("TRIM(st.first_name || ' ' || COALESCE(st.middle_name || ' ', '') || st.last_name) as student_name"),
                'st.email',
                'st.phone',
                'ss.status_name',
                'ins.instrument_name',
                'e.enrollment_id',
                'e.total_sessions',
                'e.completed_sessions',
                'e.remaining_sessions',
                'e.status as enrollment_status',
                'e.enrollment_date',
            ])
            ->orderByDesc('e.enrollment_date')
            ->orderBy('st.last_name')
            ->limit(6)
            ->get();

        $recentProgress = DB::table('progress as p')
            ->join('student as st', 'st.student_id', '=', 'p.student_id')
            ->leftJoin('enrollment as e', 'e.enrollment_id', '=', 'p.enrollment_id')
            ->leftJoin('instrument as ins', 'ins.instrument_id', '=', 'e.instrument_id')
            ->where('p.instructor_id', $instructorId)
            ->select([
                'p.progress_id',
                'p.progress_date',
                'p.lesson_topic',
                'p.performance_rating',
                'p.homework',
                'p.next_lesson_focus',
                'ins.instrument_name',
                DB::raw("TRIM(st.first_name || ' ' || st.last_name) as student_name"),
            ])
            ->orderByDesc('p.progress_date')
            ->orderByDesc('p.progress_id')
            ->limit(5)
            ->get();

        $homeworkList = DB::table('progress as p')
            ->join('student as st', 'st.student_id', '=', 'p.student_id')
            ->where('p.instructor_id', $instructorId)
            ->whereNotNull('p.homework')
            ->whereRaw("NULLIF(TRIM(p.homework), '') IS NOT NULL")
            ->select([
                'p.progress_id',
                'p.progress_date',
                'p.homework',
                'p.practice_recommendations',
                DB::raw("TRIM(st.first_name || ' ' || st.last_name) as student_name"),
            ])
            ->orderByDesc('p.progress_date')
            ->orderByDesc('p.progress_id')
            ->limit(5)
            ->get();

        return view('instructor.dashboard', compact(
            'instructor',
            'stats',
            'todayClasses',
            'nextClasses',
            'recentStudents',
            'recentProgress',
            'homeworkList'
        ));
    }

    /**
     * Shared schedule query for dashboard lists.
     */
    private function scheduleQuery(int $instructorId)
    {
        return DB::table('schedule as s')
            ->join('student as st', 'st.student_id', '=', 's.student_id')
            ->leftJoin('enrollment as e', 'e.enrollment_id', '=', 's.enrollment_id')
            ->leftJoin('instrument as ins', 'ins.instrument_id', '=', 'e.instrument_id')
            ->leftJoin('attendance as a', function ($join) {
                $join->on('a.schedule_id', '=', 's.schedule_id')
                    ->where('a.attendance_type', '=', 'lesson');
            })
            ->where('s.instructor_id', $instructorId)
            ->whereNotIn('s.status', ['cancelled', 'no_class', 'rescheduled'])
            ->select([
                's.schedule_id',
                's.schedule_date',
                's.start_time',
                's.end_time',
                's.room_number',
                's.lesson_topic',
                's.status',
                'e.enrollment_id',
                'e.remaining_sessions',
                'ins.instrument_name',
                'a.attendance_status',
                DB::raw("TRIM(st.first_name || ' ' || st.last_name) as student_name"),
            ]);
    }

    /**
     * Safe defaults if the logged-in account has no instructor profile.
     */
    private function emptyStats(): array
    {
        return [
            'total_students' => 0,
            'active_enrollments' => 0,
            'remaining_sessions' => 0,
            'today_classes' => 0,
            'upcoming_classes' => 0,
            'completed_classes' => 0,
            'pending_attendance' => 0,
            'progress_records' => 0,
            'homework_given' => 0,
            'average_rating' => 0,
        ];
    }
}