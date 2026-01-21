<?php
// app/Http/Controllers/Instructor/InstructorDashboardController.php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class InstructorDashboardController extends Controller
{
    public function index()
    {
        $userId = Auth::user()->user_id;

        // Get instructor record
        $instructor = DB::table('instructor')
            ->where('user_id', $userId)
            ->first();

        // If instructor is missing, still render safely
        if (!$instructor) {
            return view('instructor.dashboard', [
                'instructor' => null,
                'stats' => [
                    'total_students' => 0,
                    'upcoming_classes' => 0,
                    'completed_classes' => 0,
                ],
                'todayClasses' => collect(),
            ]);
        }

        $instructorId = $instructor->instructor_id;

        // Stats
        $stats = [
            // distinct students enrolled under this instructor
            'total_students' => DB::table('enrollment')
                ->where('instructor_id', $instructorId)
                ->distinct('student_id')
                ->count('student_id'),

            // upcoming schedules (planned classes)
            'upcoming_classes' => DB::table('schedule')
                ->where('instructor_id', $instructorId)
                ->whereDate('schedule_date', '>=', now()->toDateString())
                ->whereIn('status', ['scheduled', 'in_progress'])
                ->count(),

            // completed classes (actual attendance marked OK)
            // present + late are considered okay/completed
            'completed_classes' => DB::table('attendance')
                ->where('instructor_id', $instructorId)
                ->where('attendance_type', 'lesson')
                ->whereIn('attendance_status', ['present', 'late'])
                ->count(),
        ];

        // Today's schedule list
        $todayClasses = DB::table('schedule')
            ->join('student', 'schedule.student_id', '=', 'student.student_id')
            ->where('schedule.instructor_id', $instructorId)
            ->whereDate('schedule.schedule_date', Carbon::today())
            ->orderBy('schedule.start_time')
            ->select([
                DB::raw("student.first_name || ' ' || student.last_name as student_name"),
                'schedule.start_time',
                'schedule.end_time',
                'schedule.status',
            ])
            ->get();

        return view('instructor.dashboard', compact('instructor', 'stats', 'todayClasses'));
    }
}
