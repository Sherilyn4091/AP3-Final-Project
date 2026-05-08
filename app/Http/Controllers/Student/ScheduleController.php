<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ScheduleController extends Controller
{
    /**
     * app/Http/Controllers/Student/ScheduleController.php
     * 
     * Display the student's personal schedule (lessons).
     *
     * Shows:
     * - confirmed schedules grouped by date
     * - active enrollments waiting for schedule confirmation
     *
     * Important:
     * - This method is read-only.
     * - It does not auto-create schedule, progress, or attendance rows.
     */
    public function index()
    {
        $student = $this->getAuthenticatedStudent();

        // Fetch all confirmed schedules with instructor, enrollment, and instrument details.
        $schedules = Schedule::where('student_id', $student->student_id)
            ->with([
                'instructor' => function ($q) {
                    $q->select('instructor_id', 'first_name', 'middle_name', 'last_name', 'suffix');
                },
                'enrollment.instrument',
                'enrollment.lessonSession',
            ])
            ->orderBy('schedule_date', 'desc')  // Most recent dates first
            ->orderBy('start_time')             // Then by time
            ->get()
            ->groupBy(function ($item) {
                // Group by date string (Y-m-d format) for Blade template
                return $item->schedule_date->format('Y-m-d');
            });

        // Active enrollments are shown as "Pending schedule confirmation" if no final schedule exists yet.
        $pendingEnrollments = $this->getPendingScheduleEnrollments($student->student_id);

        return view('student.schedule', compact('schedules', 'pendingEnrollments'));
    }

    /**
     * Show details of a specific lesson.
     * Includes instructor, enrollment, and progress data.
     */
    public function show($id)
    {
        $student = $this->getAuthenticatedStudent();

        // Fetch specific schedule
        $schedule = Schedule::findOrFail($id);

        // Security check: ensure this lesson belongs to the authenticated student
        if ((int) $schedule->student_id !== (int) $student->student_id) {
            abort(403, 'This lesson does not belong to you.');
        }

        // Load related data: instructor, enrollment package, and progress
        $schedule->load([
            'instructor',
            'enrollment.lessonSession', // Package details through enrollment
            'enrollment.instrument',
            'progress',                 // Any progress reports for this lesson
        ]);

        return view('student.schedule-show', compact('schedule'));
    }

    /**
     * API: Get lesson details for modal.
     */
    public function getDetails($id)
    {
        $student = $this->getAuthenticatedStudent();

        $schedule = Schedule::where('schedule_id', $id)
            ->where('student_id', $student->student_id)
            ->with(['instructor', 'enrollment.instrument'])
            ->first();

        if (!$schedule) {
            return response()->json(['error' => 'Lesson not found'], 404);
        }

        return response()->json([
            'schedule_date' => $schedule->schedule_date->format('F d, Y'),
            'start_time' => \Carbon\Carbon::parse($schedule->start_time)->format('g:i A'),
            'end_time' => \Carbon\Carbon::parse($schedule->end_time)->format('g:i A'),
            'instrument' => $schedule->enrollment?->instrument?->instrument_name ?? 'Lesson',
            'instructor' => trim(($schedule->instructor?->first_name ?? '') . ' ' . ($schedule->instructor?->middle_name ?? '') . ' ' . ($schedule->instructor?->last_name ?? '') . ' ' . ($schedule->instructor?->suffix ?? '')) ?: 'TBA',
            'room_number' => $schedule->room_number ?? 'TBA',
            'status' => ucfirst(str_replace('_', ' ', $schedule->status)),
            'lesson_topic' => $schedule->lesson_topic,
            'lesson_content' => $schedule->lesson_content,
            'notes' => $schedule->notes,
        ]);
    }

    /**
     * Get authenticated student record using authenticated user ID.
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
     * Active enrollments that do not have any confirmed schedule yet.
     */
    private function getPendingScheduleEnrollments(int $studentId)
    {
        return DB::table('enrollment as e')
            ->leftJoin('lesson_session as ls', 'e.session_id', '=', 'ls.session_id')
            ->leftJoin('instrument as inst', 'e.instrument_id', '=', 'inst.instrument_id')
            ->leftJoin('instructor as i', 'e.instructor_id', '=', 'i.instructor_id')
            ->where('e.student_id', $studentId)
            ->where('e.status', 'active')
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('schedule as s')
                    ->whereColumn('s.enrollment_id', 'e.enrollment_id')
                    ->whereNotIn('s.status', ['cancelled', 'no_class']);
            })
            ->select(
                'e.enrollment_id',
                'e.start_date',
                'e.preferred_lesson_days',
                'e.preferred_lesson_time',
                'e.remaining_sessions',
                'ls.session_count',
                'inst.instrument_name',
                DB::raw("CONCAT(i.first_name, ' ', COALESCE(i.middle_name || ' ', ''), i.last_name, COALESCE(' ' || i.suffix, '')) AS instructor_full_name")
            )
            ->orderBy('e.start_date')
            ->get();
    }
}
