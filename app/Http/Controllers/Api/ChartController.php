<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;


class ChartController extends Controller
{
    public function enrollmentTrend()
    {
        try {
            $data = DB::table('enrollment')
                ->selectRaw("DATE(enrollment_date) as date, COUNT(*) as count")
                ->where('enrollment_date', '>=', Carbon::now()->subDays(30))
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function revenueWeekly()
    {
        try {
            // PostgreSQL-compatible query (since you're deploying to Supabase)
            $data = DB::table('payment')
                ->selectRaw("DATE_TRUNC('week', payment_date) as week_start, SUM(amount) as revenue")
                ->where('payment_date', '>=', Carbon::now()->subWeeks(4))
                ->groupBy('week_start')
                ->orderBy('week_start')
                ->get();

            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function instrumentPopularity()
    {
        try {
            // Direct query from student table since lesson_session doesn't have instrument_id
            $data = DB::table('student')
                ->join('instrument', 'student.instrument_id', '=', 'instrument.instrument_id')
                ->select(
                    'instrument.instrument_name', 
                    DB::raw('COUNT(*) as count')
                )
                ->whereNotNull('student.instrument_id')
                ->groupBy('instrument.instrument_id', 'instrument.instrument_name')
                ->orderByDesc('count')
                ->limit(10)
                ->get()
                ->map(function ($item) {
                    return [
                        'instrument_name' => $item->instrument_name,
                        'count' => (int) $item->count
                    ];
                });
            
            return response()->json($data);
        } catch (\Exception $e) {
            \Log::error('Chart API Error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function instructorPerformance()
    {
        try {
            // Join instructor -> enrollment -> count unique students
            $data = DB::table('instructor')
                ->leftJoin('enrollment', 'instructor.instructor_id', '=', 'enrollment.instructor_id')
                ->selectRaw("CONCAT(instructor.first_name, ' ', instructor.last_name) as instructor_name, COUNT(DISTINCT enrollment.student_id) as total_students")
                ->groupBy('instructor.instructor_id', 'instructor.first_name', 'instructor.last_name')
                ->orderByDesc('total_students')
                ->limit(10)
                ->get();

            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}