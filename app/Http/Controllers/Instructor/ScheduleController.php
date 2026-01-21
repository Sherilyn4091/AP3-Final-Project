<?php

namespace App\Http\Controllers\Instructor;


use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\Instructor;
use App\Models\Schedule;
use App\Models\Student;
class ScheduleController extends Controller
{
    public function index(Request $request)
    {
        $userId = Auth::user()->user_id;

        // Get instructor_id of the logged-in user
        $instructorId = DB::table('instructor')
            ->where('user_id', $userId)
            ->value('instructor_id');

        if (!$instructorId) {
            abort(403, 'Instructor profile not found.');
        }

        $filter = $request->query('filter', 'upcoming');

        $today = Carbon::today();
        $startOfWeek = Carbon::now()->startOfWeek(Carbon::MONDAY)->toDateString();
        $endOfWeek   = Carbon::now()->endOfWeek(Carbon::SUNDAY)->toDateString();

        // Base query
        $query = DB::table('schedule')
            ->where('instructor_id', $instructorId)
            ->orderBy('schedule_date')
            ->orderBy('start_time');

        // Filters
        if ($filter === 'today') {
            $query->whereDate('schedule_date', $today->toDateString());
        } elseif ($filter === 'week') {
            $query->whereBetween('schedule_date', [$startOfWeek, $endOfWeek]);
        } elseif ($filter === 'upcoming') {
            $query->whereDate('schedule_date', '>=', $today->toDateString());
        } elseif ($filter === 'past') {
            $query->whereDate('schedule_date', '<', $today->toDateString());
        }

        $schedules = $query->get();

        if ($schedules->isEmpty()) {
            return view('instructor.schedule.index', [
                'schedules' => $schedules,
                'filter' => $filter,
            ]);
        }

        // Collect student IDs from schedule rows
        $studentIds = $schedules->pluck('student_id')->unique()->values();

        // Students keyed by student_id
        $students = DB::table('student')
            ->select('student_id', 'first_name', 'last_name')
            ->whereIn('student_id', $studentIds)
            ->get()
            ->keyBy('student_id');

        /*
         * Enrollment mapping (important):
         * We do NOT rely on schedule.enrollment_id, because it may be null.
         * Instead, we fetch the latest enrollment per (student_id, instructor_id).
         */
        $latestEnrollmentsByStudent = DB::table('enrollment as e')
            ->select('e.enrollment_id', 'e.student_id', 'e.total_sessions', 'e.completed_sessions', 'e.remaining_sessions', 'e.status', 'e.enrollment_date')
            ->where('e.instructor_id', $instructorId)
            ->whereIn('e.student_id', $studentIds)
            ->whereNotNull('e.enrollment_id')
            ->orderByDesc('e.enrollment_date')
            ->orderByDesc('e.enrollment_id')
            ->get()
            ->groupBy('student_id')
            ->map(fn ($rows) => $rows->first()); // take latest row per student

        // Attach student + enrollment + Carbon time objects
        $schedules = $schedules->map(function ($sch) use ($students, $latestEnrollmentsByStudent) {
            $sch->student = $students[$sch->student_id] ?? null;

            // Attach latest enrollment for this student (under this instructor)
            $sch->enrollment = $latestEnrollmentsByStudent[$sch->student_id] ?? null;

            // Make times Carbon instances so ->format() works in Blade
            if (!empty($sch->start_time)) {
                $sch->start_time = Carbon::createFromFormat('H:i:s', $sch->start_time);
            }
            if (!empty($sch->end_time)) {
                $sch->end_time = Carbon::createFromFormat('H:i:s', $sch->end_time);
            }

            return $sch;
        });

        return view('instructor.schedule.index', [
            'schedules' => $schedules,
            'filter' => $filter,
        ]);
    }

    public function create()
    {
        $userId = Auth::user()->user_id;

        $instructor = Instructor::where('user_id', $userId)->firstOrFail();

        // Only students with active enrollments under this instructor
        $students = Student::whereHas('enrollments', function ($q) use ($instructor) {
            $q->where('instructor_id', $instructor->instructor_id)
              ->where('remaining_sessions', '>', 0);
        })
        ->select('student_id', 'first_name', 'last_name')
        ->orderBy('first_name')
        ->get();

        return view('instructor.schedule.create', compact('students'));
    }

    /**
     * Store new schedule
     */
    public function store(Request $request)
    {
        $userId = Auth::user()->user_id;
        $instructor = Instructor::where('user_id', $userId)->firstOrFail();

        $data = $request->validate([
            'student_id'   => ['required', 'exists:student,student_id'],
            'schedule_date'=> ['required', 'date'],
            'start_time'   => ['required'],
            'end_time'     => ['required'],
            'room_number'  => ['nullable', 'string', 'max:50'],
            'lesson_topic' => ['nullable', 'string', 'max:200'],
            'notes'        => ['nullable', 'string'],
        ]);

        // Get latest active enrollment
        $enrollment = Enrollment::where('student_id', $data['student_id'])
            ->where('instructor_id', $instructor->instructor_id)
            ->where('remaining_sessions', '>', 0)
            ->orderByDesc('enrollment_date')
            ->first();

        if (!$enrollment) {
            return back()->with('error', 'Student has no active enrollment.');
        }

        Schedule::create([
            'student_id'     => $data['student_id'],
            'instructor_id'  => $instructor->instructor_id,
            'enrollment_id'  => $enrollment->enrollment_id,
            'schedule_date'  => $data['schedule_date'],
            'start_time'     => $data['start_time'],
            'end_time'       => $data['end_time'],
            'room_number'    => $data['room_number'] ?? null,
            'lesson_topic'   => $data['lesson_topic'] ?? null,
            'notes'          => $data['notes'] ?? null,
            'status'         => 'scheduled',
        ]);

        return redirect()
            ->route('instructor.schedule.index')
            ->with('success', 'Schedule created successfully.');
    }

}