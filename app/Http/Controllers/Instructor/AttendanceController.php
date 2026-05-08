<?php
// app/Http/Controllers/Instructor/AttendanceController.php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
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
     * Attendance list.
     * Uses schedule.instructor_id so older seeded attendance with null instructor_id can still be connected correctly.
     */
    public function index(Request $request)
    {
        $instructorId = $this->instructorIdOrAbort();
        $q = trim((string) $request->query('q', ''));

        $attendance = DB::table('attendance as a')
            ->join('schedule as s', 's.schedule_id', '=', 'a.schedule_id')
            ->join('student as st', 'st.student_id', '=', 'a.student_id')
            ->leftJoin('enrollment as e', 'e.enrollment_id', '=', 's.enrollment_id')
            ->leftJoin('instrument as ins', 'ins.instrument_id', '=', 'e.instrument_id')
            ->where('s.instructor_id', $instructorId)
            ->where('a.attendance_type', 'lesson')
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($qq) use ($q) {
                    $qq->where('st.first_name', 'ilike', "%{$q}%")
                        ->orWhere('st.last_name', 'ilike', "%{$q}%")
                        ->orWhere('a.attendance_status', 'ilike', "%{$q}%")
                        ->orWhere('ins.instrument_name', 'ilike', "%{$q}%");
                });
            })
            ->select([
                'a.attendance_id',
                'a.attendance_date',
                'a.attendance_status',
                'a.student_id',
                's.schedule_id',
                's.start_time',
                's.end_time',
                's.room_number',
                's.lesson_topic',
                'ins.instrument_name',
                DB::raw("TRIM(st.first_name || ' ' || st.last_name) as student_name"),
            ])
            ->orderByDesc('a.attendance_date')
            ->orderByDesc('a.attendance_id')
            ->paginate(15)
            ->withQueryString();

        return view('instructor.attendance.index', compact('attendance', 'q'));
    }

    /**
     * Edit attendance for all schedules of one assigned student.
     */
    public function edit($studentId)
    {
        $instructorId = $this->instructorIdOrAbort();
        $studentId = (int) $studentId;

        $student = DB::table('student as st')
            ->where('st.student_id', $studentId)
            ->select([
                'st.student_id',
                DB::raw("TRIM(st.first_name || ' ' || st.last_name) as student_name"),
                'st.email',
                'st.phone',
            ])
            ->first();

        if (!$student) {
            abort(404, 'Student not found.');
        }

        $assigned = DB::table('enrollment')
            ->where('student_id', $studentId)
            ->where('instructor_id', $instructorId)
            ->exists();

        if (!$assigned) {
            abort(403, 'You are not assigned to this student.');
        }

        $schedules = DB::table('schedule as s')
            ->leftJoin('attendance as a', function ($join) {
                $join->on('a.schedule_id', '=', 's.schedule_id')
                    ->where('a.attendance_type', '=', 'lesson');
            })
            ->leftJoin('enrollment as e', 'e.enrollment_id', '=', 's.enrollment_id')
            ->leftJoin('instrument as ins', 'ins.instrument_id', '=', 'e.instrument_id')
            ->where('s.student_id', $studentId)
            ->where('s.instructor_id', $instructorId)
            ->select([
                's.schedule_id',
                's.enrollment_id',
                's.schedule_date',
                's.start_time',
                's.end_time',
                's.room_number',
                's.lesson_topic',
                's.status as schedule_status',
                'a.attendance_status',
                'e.total_sessions',
                'e.completed_sessions',
                'e.remaining_sessions',
                'ins.instrument_name',
            ])
            ->orderByDesc('s.schedule_date')
            ->orderByDesc('s.start_time')
            ->get();

        return view('instructor.attendance.edit', compact('student', 'schedules'));
    }

    /**
     * Update attendance and adjust completed/remaining sessions only when status changes.
     */
    public function update(Request $request, $studentId)
    {
        $instructorId = $this->instructorIdOrAbort();
        $studentId = (int) $studentId;
        $userId = Auth::user()->user_id;

        $data = $request->validate([
            'attendance' => ['required', 'array'],
            'attendance.*.schedule_id' => ['required', 'integer'],
            'attendance.*.status' => ['required', 'in:present,absent,late,excused,half_day,on_leave'],
        ]);

        DB::transaction(function () use ($data, $studentId, $instructorId, $userId) {
            foreach ($data['attendance'] as $row) {
                $scheduleId = (int) $row['schedule_id'];
                $newStatus = $row['status'];

                $schedule = DB::table('schedule')
                    ->where('schedule_id', $scheduleId)
                    ->where('student_id', $studentId)
                    ->where('instructor_id', $instructorId)
                    ->first();

                if (!$schedule) {
                    continue;
                }

                $previousStatus = DB::table('attendance')
                    ->where('attendance_type', 'lesson')
                    ->where('schedule_id', $scheduleId)
                    ->where('student_id', $studentId)
                    ->value('attendance_status');

                $wasConsumed = $this->consumesSession($previousStatus);
                $isConsumed = $this->consumesSession($newStatus);

                if ($wasConsumed !== $isConsumed && !empty($schedule->enrollment_id)) {
                    $this->adjustEnrollmentSession((string) $schedule->enrollment_id, $isConsumed);
                }

                DB::table('attendance')->updateOrInsert(
                    [
                        'attendance_type' => 'lesson',
                        'schedule_id' => $scheduleId,
                        'student_id' => $studentId,
                    ],
                    [
                        'user_id' => $userId,
                        'instructor_id' => $instructorId,
                        'attendance_date' => $schedule->schedule_date,
                        'attendance_status' => $newStatus,
                        'check_in_time' => in_array($newStatus, ['present', 'late'], true) ? Carbon::now() : null,
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );

                if (in_array($newStatus, ['present', 'late'], true)) {
                    DB::table('schedule')
                        ->where('schedule_id', $scheduleId)
                        ->update(['status' => 'completed', 'updated_at' => now()]);
                }
            }
        });

        return redirect()
            ->route('instructor.attendance.edit', $studentId)
            ->with('success', 'Attendance and session counts updated successfully.');
    }

    /**
     * Only present and late should consume paid lesson sessions.
     */
    private function consumesSession(?string $status): bool
    {
        return in_array($status, ['present', 'late'], true);
    }

    /**
     * Keep enrollment_session_count_check valid: completed + remaining = total.
     */
    private function adjustEnrollmentSession(string $enrollmentId, bool $consume): void
    {
        if ($consume) {
            DB::table('enrollment')
                ->where('enrollment_id', $enrollmentId)
                ->update([
                    'completed_sessions' => DB::raw('LEAST(completed_sessions + 1, total_sessions)'),
                    'remaining_sessions' => DB::raw('GREATEST(remaining_sessions - 1, 0)'),
                    'updated_at' => now(),
                ]);

            return;
        }

        DB::table('enrollment')
            ->where('enrollment_id', $enrollmentId)
            ->update([
                'completed_sessions' => DB::raw('GREATEST(completed_sessions - 1, 0)'),
                'remaining_sessions' => DB::raw('LEAST(remaining_sessions + 1, total_sessions)'),
                'updated_at' => now(),
            ]);
    }
}