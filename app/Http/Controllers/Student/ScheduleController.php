<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Schedule;

class ScheduleController extends Controller
{
    /**
     * Display the student's personal schedule (lessons)
     * Shows upcoming and past lessons grouped by date
     */
    public function index()
    {
        // Get student record using authenticated user ID
        $student = DB::table('student')->where('user_id', Auth::id())->first();
        
        // Validate student exists
        if (!$student) {
            abort(404, 'Student record not found');
        }

        // Fetch all schedules with instructor details
        $schedules = Schedule::where('student_id', $student->student_id)
            ->with(['instructor' => function ($q) {
                $q->select('instructor_id', 'first_name', 'last_name');
            }])
            ->orderBy('schedule_date', 'desc')  // Most recent dates first
            ->orderBy('start_time')              // Then by time
            ->get()
            ->groupBy(function($item) {
                // Group by date string (Y-m-d format) for Blade template
                return $item->schedule_date->format('Y-m-d');
            });

        return view('student.schedule', compact('schedules'));
    }

    /**
     * Show details of a specific lesson
     * Includes instructor, enrollment, and progress data
     */
    public function show($id)
    {
        // Get student record using authenticated user ID
        $student = DB::table('student')->where('user_id', Auth::id())->first();
        
        // Validate student exists
        if (!$student) {
            abort(404, 'Student record not found');
        }

        // Fetch specific schedule
        $schedule = Schedule::findOrFail($id);

        // Security check: ensure this lesson belongs to the authenticated student
        if ($schedule->student_id !== $student->student_id) {
            abort(403, 'This lesson does not belong to you.');
        }

        // Load related data: instructor, enrollment package, and progress
        $schedule->load([
            'instructor',
            'enrollment.lessonSession',  // Package details through enrollment
            'progress'                    // Any progress reports for this lesson
        ]);

        return view('student.schedule-show', compact('schedule'));
    }

    /**
     * API: Get lesson details for modal
     */
    public function getDetails($id)
    {
        $student = DB::table('student')->where('user_id', Auth::id())->first();
        
        if (!$student) {
            return response()->json(['error' => 'Student not found'], 404);
        }
        
        $schedule = Schedule::where('schedule_id', $id)
            ->where('student_id', $student->student_id)
            ->with('instructor')
            ->first();
        
        if (!$schedule) {
            return response()->json(['error' => 'Lesson not found'], 404);
        }
        
        return response()->json([
            'schedule_date' => $schedule->schedule_date->format('F d, Y'),
            'start_time' => \Carbon\Carbon::parse($schedule->start_time)->format('g:i A'),
            'end_time' => \Carbon\Carbon::parse($schedule->end_time)->format('g:i A'),
            'lesson_content' => $schedule->lesson_content,
            'notes' => $schedule->notes,
        ]);
    }

}