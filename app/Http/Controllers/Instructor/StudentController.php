<?php
// app/Http/Controllers/Instructor/StudentController.php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StudentController extends Controller
{
    private function instructorIdOrAbort(): int
    {
        $instructorId = DB::table('instructor')
            ->where('user_id', Auth::user()->user_id)
            ->value('instructor_id');

        if (!$instructorId) {
            abort(403, 'Instructor profile not found.');
        }

        return (int) $instructorId;
    }

    /**
     * Student monitoring list.
     * Uses enrollment as the true relationship between instructor and student.
     */
    public function index(Request $request)
    {
        $instructorId = $this->instructorIdOrAbort();
        $q = trim((string) $request->query('q', ''));

        $students = DB::table('enrollment as e')
            ->join('student as st', 'st.student_id', '=', 'e.student_id')
            ->join('instrument as ins', 'ins.instrument_id', '=', 'e.instrument_id')
            ->leftJoin('student_status as ss', 'ss.status_id', '=', 'st.student_status_id')
            ->where('e.instructor_id', $instructorId)
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($qq) use ($q) {
                    $qq->where('st.first_name', 'ilike', "%{$q}%")
                        ->orWhere('st.last_name', 'ilike', "%{$q}%")
                        ->orWhere('st.email', 'ilike', "%{$q}%")
                        ->orWhere('st.phone', 'ilike', "%{$q}%")
                        ->orWhere('ins.instrument_name', 'ilike', "%{$q}%");
                });
            })
            ->select([
                'st.student_id',
                DB::raw("TRIM(st.first_name || ' ' || COALESCE(st.middle_name || ' ', '') || st.last_name) as student_name"),
                'st.email',
                'st.phone',
                'st.skill_level',
                'ss.status_name',
                'ins.instrument_name',
                'e.enrollment_id',
                'e.status as enrollment_status',
                'e.payment_status',
                'e.total_sessions',
                'e.completed_sessions',
                'e.remaining_sessions',
                'e.enrollment_date',
                DB::raw("(
                    SELECT MAX(a.attendance_date)
                    FROM attendance a
                    JOIN schedule s2 ON s2.schedule_id = a.schedule_id
                    WHERE a.student_id = st.student_id
                      AND s2.instructor_id = {$instructorId}
                      AND a.attendance_type = 'lesson'
                ) as last_lesson_date"),
                DB::raw("(
                    SELECT COUNT(*)
                    FROM progress p
                    WHERE p.student_id = st.student_id
                      AND p.instructor_id = {$instructorId}
                ) as progress_count"),
            ])
            ->orderBy('st.last_name')
            ->orderBy('st.first_name')
            ->paginate(12)
            ->withQueryString();

        return view('instructor.students.index', compact('students', 'q'));
    }

    /**
     * Detailed student monitoring page.
     */
    public function show($studentId)
    {
        $instructorId = $this->instructorIdOrAbort();
        $studentId = (int) $studentId;

        $student = DB::table('student as st')
            ->leftJoin('student_status as ss', 'ss.status_id', '=', 'st.student_status_id')
            ->where('st.student_id', $studentId)
            ->select([
                'st.*',
                'ss.status_name',
                DB::raw("TRIM(st.first_name || ' ' || COALESCE(st.middle_name || ' ', '') || st.last_name) as student_name"),
            ])
            ->first();

        if (!$student) {
            abort(404, 'Student not found.');
        }

        $enrollment = DB::table('enrollment as e')
            ->join('instrument as ins', 'ins.instrument_id', '=', 'e.instrument_id')
            ->join('lesson_session as ls', 'ls.session_id', '=', 'e.session_id')
            ->where('e.student_id', $studentId)
            ->where('e.instructor_id', $instructorId)
            ->select([
                'e.*',
                'ins.instrument_name',
                'ls.session_name',
                'ls.session_count',
                'ls.duration_minutes',
            ])
            ->orderByDesc('e.enrollment_date')
            ->orderByDesc('e.enrollment_id')
            ->first();

        if (!$enrollment) {
            abort(403, 'You are not assigned to this student.');
        }

        $today = Carbon::today()->toDateString();

        $nextClass = DB::table('schedule')
            ->where('student_id', $studentId)
            ->where('instructor_id', $instructorId)
            ->whereDate('schedule_date', '>=', $today)
            ->whereNotIn('status', ['cancelled', 'no_class', 'rescheduled'])
            ->orderBy('schedule_date')
            ->orderBy('start_time')
            ->first();

        $recentSchedules = DB::table('schedule as s')
            ->leftJoin('attendance as a', function ($join) {
                $join->on('a.schedule_id', '=', 's.schedule_id')
                    ->where('a.attendance_type', '=', 'lesson');
            })
            ->where('s.student_id', $studentId)
            ->where('s.instructor_id', $instructorId)
            ->select('s.*', 'a.attendance_status')
            ->orderByDesc('s.schedule_date')
            ->orderByDesc('s.start_time')
            ->limit(10)
            ->get();

        $attendanceStats = [
            'present' => DB::table('attendance as a')
                ->join('schedule as s', 's.schedule_id', '=', 'a.schedule_id')
                ->where('a.student_id', $studentId)
                ->where('s.instructor_id', $instructorId)
                ->whereIn('a.attendance_status', ['present', 'late'])
                ->count(),
            'absent' => DB::table('attendance as a')
                ->join('schedule as s', 's.schedule_id', '=', 'a.schedule_id')
                ->where('a.student_id', $studentId)
                ->where('s.instructor_id', $instructorId)
                ->where('a.attendance_status', 'absent')
                ->count(),
        ];

        $progressRecords = DB::table('progress')
            ->where('student_id', $studentId)
            ->where('instructor_id', $instructorId)
            ->orderByDesc('progress_date')
            ->orderByDesc('progress_id')
            ->limit(8)
            ->get();

        $latestHomework = DB::table('progress')
            ->where('student_id', $studentId)
            ->where('instructor_id', $instructorId)
            ->whereNotNull('homework')
            ->whereRaw("NULLIF(TRIM(homework), '') IS NOT NULL")
            ->orderByDesc('progress_date')
            ->orderByDesc('progress_id')
            ->first();

        return view('instructor.students.show', compact(
            'student',
            'enrollment',
            'nextClass',
            'recentSchedules',
            'attendanceStats',
            'progressRecords',
            'latestHomework'
        ));
    }
}