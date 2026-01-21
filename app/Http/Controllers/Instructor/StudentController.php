<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Instructor;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Schedule;
use App\Models\Attendance;
use Carbon\Carbon;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $userId = Auth::user()->user_id;

        $instructor = Instructor::where('user_id', $userId)->first();

        if (!$instructor) {
            abort(403, 'Instructor profile not found.');
        }

        $q = trim((string) $request->query('q', ''));

        $students = Student::query()
            // only students that have enrollments under this instructor
            ->whereHas('enrollments', function ($enroll) use ($instructor) {
                $enroll->where('instructor_id', $instructor->instructor_id);
            })
            // eager load relations used in blade
            ->with([
                'instrument:instrument_id,instrument_name',
                'status:status_id,status_name',
                'latestEnrollment' => function ($q) {
                    $q->select([
                        'enrollment.enrollment_id',
                        'enrollment.student_id',
                        'enrollment.total_sessions',
                        'enrollment.completed_sessions',
                        'enrollment.remaining_sessions',
                        'enrollment.enrollment_date',
                    ]);
                },
            ])
            // search
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($qq) use ($q) {
                    $qq->where('first_name', 'ilike', "%{$q}%")
                       ->orWhere('last_name', 'ilike', "%{$q}%")
                       ->orWhere('email', 'ilike', "%{$q}%")
                       ->orWhere('phone', 'ilike', "%{$q}%");
                });
            })
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->paginate(12)
            ->withQueryString();

        return view('instructor.students.index', compact('students', 'q'));
    }

  public function show($studentId)
{
    if (!is_numeric($studentId)) {
        abort(404);
    }

    $studentId = (int) $studentId;

    $userId = Auth::user()->user_id;

    $instructor = Instructor::where('user_id', $userId)->first();
    if (!$instructor) {
        abort(403, 'Instructor profile not found.');
    }

    // Student exists?
    $studentExists = Student::where('student_id', $studentId)->exists();
    if (!$studentExists) {
        abort(404, 'Student not found.');
    }

    // Assigned to this instructor?
    $assigned = Student::query()
        ->where('student_id', $studentId)
        ->whereHas('enrollments', function ($enroll) use ($instructor) {
            $enroll->where('instructor_id', $instructor->instructor_id);
        })
        ->exists();

    if (!$assigned) {
        abort(403, 'You are not assigned to this student (enrollment instructor_id mismatch).');
    }

    // Load full student data
    $student = Student::query()
        ->where('student_id', $studentId)
        ->with([
            'instrument:instrument_id,instrument_name',
            'status:status_id,status_name',
            'latestEnrollment' => function ($q) use ($instructor) {
                $q->select([
                        'enrollment.enrollment_id',
                        'enrollment.student_id',
                        'enrollment.total_sessions',
                        'enrollment.completed_sessions',
                        'enrollment.remaining_sessions',
                        'enrollment.enrollment_date',
                        'enrollment.instructor_id',
                        'enrollment.status',
                        'enrollment.payment_status',
                    ])
                    ->where('enrollment.instructor_id', $instructor->instructor_id);
            },
        ])
        ->firstOrFail();

    $attendance = Attendance::query()
        ->where('student_id', $student->student_id)
        ->where('attendance_type', 'lesson')
        ->orderByDesc('attendance_date')
        ->orderByDesc('attendance_id')
        ->limit(20)
        ->get();

    $today = Carbon::today()->toDateString();

    $nextClass = Schedule::query()
        ->where('student_id', $student->student_id)
        ->where('instructor_id', $instructor->instructor_id)
        ->where('schedule_date', '>=', $today)
        ->whereNotIn('status', ['cancelled', 'no_class'])
        ->orderBy('schedule_date')
        ->orderBy('start_time')
        ->first();

    return view('instructor.students.show', compact('student', 'attendance', 'nextClass'));
}



}
