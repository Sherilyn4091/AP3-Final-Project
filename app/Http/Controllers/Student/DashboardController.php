<?php

// app/Http/Controllers/Student/DashboardController.php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $userId = Auth::id();
        
        // Get student record
        $student = DB::table('student')->where('user_id', $userId)->first();
        
        if (!$student) {
            abort(404, 'Student record not found');
        }
        
        // Get current enrollment with lesson session details
        $currentEnrollment = DB::table('enrollment as e')
            ->leftJoin('lesson_session as ls', 'e.session_id', '=', 'ls.session_id')
            ->where('e.student_id', $student->student_id)
            ->where('e.status', 'active')
            ->select('e.*', 'ls.session_count', 'ls.session_name', 'ls.price')
            ->first();
        
        // Calculate progress percentage
        $progressPercentage = 0;
        if ($currentEnrollment && $currentEnrollment->total_sessions > 0) {
            $progressPercentage = round(($currentEnrollment->completed_sessions / $currentEnrollment->total_sessions) * 100);
        }
        
        // Get next lesson with instructor details
        $nextLesson = DB::table('schedule as s')
            ->leftJoin('instructor as i', 's.instructor_id', '=', 'i.instructor_id')
            ->where('s.student_id', $student->student_id)
            ->where('s.schedule_date', '>=', now()->toDateString())
            ->where('s.status', 'scheduled')
            ->select(
                's.*',
                'i.first_name as instructor_first_name',
                'i.last_name as instructor_last_name'
            )
            ->orderBy('s.schedule_date')
            ->orderBy('s.start_time')
            ->first();
        
        // Get recent progress
        $recentProgress = DB::table('progress')
            ->where('student_id', $student->student_id)
            ->orderBy('progress_date', 'desc')
            ->limit(6)
            ->get();
        
        // Convert dates to Carbon instances for blade
        if ($currentEnrollment) {
            $currentEnrollment->enrollment_date = $currentEnrollment->enrollment_date 
                ? Carbon::parse($currentEnrollment->enrollment_date) 
                : null;
        }
        
        if ($nextLesson) {
            $nextLesson->schedule_date = Carbon::parse($nextLesson->schedule_date);
            $nextLesson->start_time = Carbon::parse($nextLesson->start_time);
            $nextLesson->end_time = Carbon::parse($nextLesson->end_time);
            
            // Create instructor object for blade compatibility
            $nextLesson->instructor = (object)[
                'first_name' => $nextLesson->instructor_first_name,
                'last_name' => $nextLesson->instructor_last_name,
            ];
        }
        
        // Convert progress dates
        $recentProgress = $recentProgress->map(function($p) {
            $p->progress_date = Carbon::parse($p->progress_date);
            return $p;
        });
        
        // Create lesson session object if enrollment exists
        if ($currentEnrollment) {
            $currentEnrollment->lessonSession = (object)[
                'session_count' => $currentEnrollment->session_count,
                'session_name' => $currentEnrollment->session_name,
                'price' => $currentEnrollment->price,
            ];
        }
        
        return view('student.dashboard', compact(
            'student',
            'currentEnrollment',
            'progressPercentage',
            'nextLesson',
            'recentProgress'
        ));
    }
}