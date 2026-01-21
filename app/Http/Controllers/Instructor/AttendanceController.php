<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Instructor;
use App\Models\Attendance;
use App\Models\Student;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    /**
     * Attendance index
     */
    public function index(Request $request)
    {
        $userId = Auth::user()->user_id;

        $instructor = Instructor::where('user_id', $userId)->first();
        if (!$instructor) {
            abort(403, 'Instructor profile not found.');
        }

        $q = trim((string) $request->query('q', ''));

        $attendance = Attendance::query()
            ->where('instructor_id', $instructor->instructor_id)
            ->where('attendance_type', 'lesson')
            ->with(['student:student_id,first_name,last_name'])
            ->when($q !== '', function ($query) use ($q) {
                $query->whereHas('student', function ($s) use ($q) {
                    $s->where('first_name', 'ilike', "%{$q}%")
                      ->orWhere('last_name', 'ilike', "%{$q}%");
                });
            })
            ->orderByDesc('attendance_date')
            ->orderByDesc('attendance_id')
            ->paginate(15)
            ->withQueryString();

        return view('instructor.attendance.index', compact('attendance', 'q'));
    }

    /**
     * Edit attendance for a student
     */
    public function edit($studentId)
    {
        $userId = Auth::user()->user_id;

        $instructor = Instructor::where('user_id', $userId)->first();
        if (!$instructor) {
            abort(403, 'Instructor profile not found.');
        }

        // Ensure instructor is assigned to this student
        $assigned = Schedule::where('student_id', $studentId)
            ->where('instructor_id', $instructor->instructor_id)
            ->exists();

        if (!$assigned) {
            abort(403, 'You are not assigned to this student.');
        }

        $student = Student::select('student_id', 'first_name', 'last_name')
            ->where('student_id', $studentId)
            ->firstOrFail();

        $schedules = Schedule::query()
            ->select('schedule_id', 'schedule_date', 'start_time', 'end_time', 'room_number')
            ->where('student_id', $studentId)
            ->where('instructor_id', $instructor->instructor_id)
            ->orderByDesc('schedule_date')
            ->orderByDesc('start_time')
            ->get();

        $attendanceBySchedule = Attendance::query()
            ->where('student_id', $studentId)
            ->where('instructor_id', $instructor->instructor_id)
            ->where('attendance_type', 'lesson')
            ->get()
            ->keyBy('schedule_id');

        return view('instructor.attendance.edit', compact(
            'student',
            'schedules',
            'attendanceBySchedule'
        ));
    }

    /**
     * Update attendance + safely adjust enrollment sessions
     */
   public function update(Request $request, $studentId)
{
    $userId = Auth::user()->user_id;

    $instructor = DB::table('instructor')
        ->where('user_id', $userId)
        ->first();

    if (!$instructor) {
        abort(403, 'Instructor profile not found.');
    }

    $allowed = DB::table('schedule')
        ->where('student_id', $studentId)
        ->where('instructor_id', $instructor->instructor_id)
        ->exists();

    if (!$allowed) {
        abort(403, 'You are not assigned to this student.');
    }

    $data = $request->validate([
        'attendance' => ['required', 'array'],
        'attendance.*.schedule_id' => ['required', 'integer'],
        'attendance.*.status' => ['required', 'in:present,absent,late,excused,half_day,on_leave'],
    ]);

    // Which statuses should consume a session?
    $consumes = function (string $status): bool {
        return in_array($status, ['present', 'late'], true);
    };

    DB::transaction(function () use ($data, $studentId, $userId, $instructor, $consumes) {

        foreach ($data['attendance'] as $row) {
            $scheduleId = (int) $row['schedule_id'];
            $newStatus = $row['status'];

            // Verify schedule belongs to this instructor + student
            $schedule = DB::table('schedule')
                ->select('schedule_id', 'schedule_date', 'student_id', 'instructor_id', 'enrollment_id')
                ->where('schedule_id', $scheduleId)
                ->where('student_id', $studentId)
                ->where('instructor_id', $instructor->instructor_id)
                ->first();

            if (!$schedule) {
                continue;
            }

            // Get previous status for THIS schedule row
            $prevStatus = DB::table('attendance')
                ->where('attendance_type', 'lesson')
                ->where('schedule_id', $scheduleId)
                ->where('student_id', $studentId)
                ->value('attendance_status');

            $wasConsumed = $prevStatus ? $consumes($prevStatus) : false;
            $isConsumed  = $consumes($newStatus);

            // Only adjust enrollment if "consumed-ness" changed
            if ($wasConsumed !== $isConsumed) {
                // Update the enrollment tied to this schedule (best/accurate)
                $enrollment = null;

                if (!empty($schedule->enrollment_id)) {
                    $enrollment = DB::table('enrollment')
                        ->where('enrollment_id', $schedule->enrollment_id)
                        ->first();
                }

                // Fallback (if schedule.enrollment_id is missing)
                if (!$enrollment) {
                    $enrollment = DB::table('enrollment')
                        ->where('student_id', $studentId)
                        ->where('instructor_id', $instructor->instructor_id)
                        ->orderByDesc('enrollment_date')
                        ->first();
                }

                if ($enrollment) {
                    if ($isConsumed) {
                        // consume 1 session: completed +1, remaining -1 (never below 0)
                        DB::table('enrollment')
                            ->where('enrollment_id', $enrollment->enrollment_id)
                            ->update([
                                'completed_sessions' => DB::raw('LEAST(completed_sessions + 1, total_sessions)'),
                                'remaining_sessions' => DB::raw('GREATEST(remaining_sessions - 1, 0)'),
                                'updated_at' => now(),
                            ]);
                    } else {
                        // refund 1 session: completed -1 (never below 0), remaining +1 (never above total_sessions)
                        DB::table('enrollment')
                            ->where('enrollment_id', $enrollment->enrollment_id)
                            ->update([
                                'completed_sessions' => DB::raw('GREATEST(completed_sessions - 1, 0)'),
                                'remaining_sessions' => DB::raw('LEAST(remaining_sessions + 1, total_sessions)'),
                                'updated_at' => now(),
                            ]);
                    }
                }
            }

            // Upsert attendance row (lesson attendance)
            DB::table('attendance')->updateOrInsert(
                [
                    'attendance_type' => 'lesson',
                    'schedule_id' => $scheduleId,
                    'student_id' => $studentId,
                ],
                [
                    'user_id' => $userId,
                    'instructor_id' => $instructor->instructor_id,
                    'attendance_date' => $schedule->schedule_date,
                    'attendance_status' => $newStatus,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    });

    return redirect()
        ->route('instructor.attendance.edit', $studentId)
        ->with('success', 'Attendance updated.');
}

}
