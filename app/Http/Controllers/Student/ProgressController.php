<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProgressController extends Controller
{
    /**
     * Display student's progress history
     * Shows all lesson feedback and ratings from instructors
     */
    public function index()
    {
        $userId = Auth::id();
        $student = DB::table('student')->where('user_id', $userId)->first();
        
        if (!$student) {
            abort(404, 'Student record not found');
        }
        
        // Fetch progress history
        $progressHistory = DB::table('progress as p')
            ->leftJoin('instructor as i', 'p.instructor_id', '=', 'i.instructor_id')
            ->where('p.student_id', $student->student_id)
            ->select(
                'p.*',
                'i.first_name as instructor_first_name',
                'i.last_name as instructor_last_name'
            )
            ->orderBy('p.progress_date', 'desc')
            ->get()
            ->map(function($p) {
                $p->progress_date = Carbon::parse($p->progress_date);
                return $p;
            });
        
        // Fetch attendance history for lessons only
        $attendanceHistory = DB::table('attendance as a')
            ->join('schedule as s', 'a.schedule_id', '=', 's.schedule_id')
            ->leftJoin('instructor as i', 's.instructor_id', '=', 'i.instructor_id')
            ->where('a.student_id', $student->student_id)
            ->where('a.attendance_type', 'lesson')
            ->select(
                'a.attendance_date',
                'a.attendance_status',
                's.start_time',
                's.lesson_topic',
                'i.first_name as instructor_first_name',
                'i.last_name as instructor_last_name'
            )
            ->orderBy('a.attendance_date', 'desc')
            ->get()
            ->map(function($a) {
                $a->attendance_date = Carbon::parse($a->attendance_date);
                return $a;
            });
        
        return view('student.progress', compact('progressHistory', 'attendanceHistory'));
    }
}