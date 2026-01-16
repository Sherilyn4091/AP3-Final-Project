<?php
// app/Http/Controllers/Admin/DashboardController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard with cached data
     */
    public function index()
    {
        // Authorization check (not cached)
        $isSuperAdmin = DB::table('user_account')
            ->where('user_id', Auth::id())
            ->value('is_super_admin');

        if (!$isSuperAdmin) {
            abort(403, 'Unauthorized access');
        }

        // Cache dashboard data for 5 minutes
        $dashboardData = Cache::remember('admin_dashboard_data', 300, function () {
            return $this->getDashboardData();
        });

        return view('admin.dashboard', $dashboardData);
    }

    /**
     * Fetch all dashboard data (will be cached)
     */
    private function getDashboardData()
    {
        $today = now()->format('Y-m-d');

        // User Statistics
        $totalUsers = DB::table('user_account')->count();
        $activeStudents = DB::table('student')->where('is_active', true)->count();
        $activeInstructors = DB::table('instructor')->where('is_active', true)->count();
        $totalStaff = DB::table('sales_staff')->where('is_active', true)->count() +
                      DB::table('all_around_staff')->where('is_active', true)->count();

        // Today's Activity
        $todaysEnrollments = DB::table('enrollment')
            ->whereDate('enrollment_date', $today)
            ->count();

        $paidStatusId = DB::table('payment_status')
            ->where('status_name', 'Paid')
            ->value('status_id');

        $todaysRevenue = DB::table('payment')
            ->whereDate('payment_date', $today)
            ->where('payment_status_id', $paidStatusId)
            ->sum('amount') ?? 0;

        $pendingPayments = DB::table('enrollment')
            ->where('payment_status', '!=', 'Paid')
            ->count();

        $lowStockAlerts = DB::table('v_low_stock_items')->count();

        // Recent Activity
        $recentEnrollments = DB::table('enrollment')
        ->join('student', 'enrollment.student_id', '=', 'student.student_id')
        ->join('lesson_session', 'enrollment.session_id', '=', 'lesson_session.session_id')
        ->select(
            'enrollment.*',
            DB::raw("CONCAT(student.first_name, ' ', student.last_name) as student_name"),
            'lesson_session.session_name as lesson_type'
        )
        ->orderBy('enrollment.enrollment_date', 'desc')
        ->limit(5)
        ->get();

        $recentPayments = DB::table('payment')
            ->join('enrollment', 'payment.enrollment_id', '=', 'enrollment.enrollment_id')
            ->join('student', 'enrollment.student_id', '=', 'student.student_id')
            ->select(
                'payment.*',
                DB::raw("CONCAT(student.first_name, ' ', student.last_name) as student_name")
            )
            ->orderBy('payment.payment_date', 'desc')
            ->limit(5)
            ->get();

        $recentReports = DB::table('report')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Today's Schedule
        $todaysSchedule = DB::table('schedule')
        ->join('enrollment', 'schedule.enrollment_id', '=', 'enrollment.enrollment_id')
        ->join('student', 'enrollment.student_id', '=', 'student.student_id')
        ->join('instructor', 'schedule.instructor_id', '=', 'instructor.instructor_id')
        ->leftJoin('room', 'schedule.room_number', '=', 'room.room_number')
        ->select(
            'schedule.*',
            DB::raw("CONCAT(student.first_name, ' ', student.last_name) as student_name"),
            DB::raw("CONCAT(instructor.first_name, ' ', instructor.last_name) as instructor_name"),
            'room.room_name'
        )
            ->whereDate('schedule.schedule_date', $today)
            ->orderBy('schedule.start_time')
            ->get();

        $todaysBookings = DB::table('booking')
        ->whereDate('booking_date', $today)
        ->get();

        return [
            'totalUsers' => $totalUsers,
            'activeStudents' => $activeStudents,
            'activeInstructors' => $activeInstructors,
            'totalStaff' => $totalStaff,
            'todaysEnrollments' => $todaysEnrollments,
            'todaysRevenue' => $todaysRevenue,
            'pendingPayments' => $pendingPayments,
            'lowStockAlerts' => $lowStockAlerts,
            'recentEnrollments' => $recentEnrollments,
            'recentPayments' => $recentPayments,
            'recentReports' => $recentReports,
            'todaysSchedule' => $todaysSchedule,
            'todaysBookings' => $todaysBookings,
        ];
    }

    /**
     * Chart API endpoints (keep these as they are, add caching if needed)
     */
    public function getWeeklyRevenue()
    {
        return Cache::remember('weekly_revenue', 300, function () {
            $paidStatusId = DB::table('payment_status')
                ->where('status_name', 'Paid')
                ->value('status_id');

            $weeklyData = DB::table('payment')
                ->select(
                    DB::raw("DATE_TRUNC('week', payment_date) as week_start"),
                    DB::raw("SUM(amount) as revenue")
                )
                ->where('payment_status_id', $paidStatusId)
                ->where('payment_date', '>=', now()->subWeeks(8))
                ->groupBy('week_start')
                ->orderBy('week_start')
                ->get();

            return response()->json($weeklyData);
        });
    }

    public function getEnrollmentTrend()
    {
        return Cache::remember('enrollment_trend', 300, function () {
            $dailyData = DB::table('enrollment')
                ->select(
                    DB::raw("DATE(enrollment_date) as date"),
                    DB::raw("COUNT(*) as count")
                )
                ->where('enrollment_date', '>=', now()->subDays(30))
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            return response()->json($dailyData);
        });
    }

    public function getInstrumentPopularity()
    {
        return Cache::remember('instrument_popularity', 300, function () {
            $data = DB::table('student')
                ->join('genre', 'student.preferred_genre_id', '=', 'genre.genre_id')
                ->select(
                    'genre.genre_name',
                    DB::raw("COUNT(*) as count")
                )
                ->groupBy('genre.genre_id', 'genre.genre_name')
                ->orderBy('count', 'desc')
                ->get();

            return response()->json($data);
        });
    }

    public function getInstructorPerformance()
    {
        return Cache::remember('instructor_performance', 300, function () {
            $data = DB::table('instructor')
                ->leftJoin('schedule', 'instructor.instructor_id', '=', 'schedule.instructor_id')
                ->leftJoin('enrollment', 'schedule.enrollment_id', '=', 'enrollment.enrollment_id')
                ->select(
                    DB::raw("CONCAT(instructor.first_name, ' ', instructor.last_name) as instructor_name"),
                    DB::raw("COUNT(DISTINCT enrollment.student_id) as total_students")
                )
                ->groupBy('instructor.instructor_id', 'instructor.first_name', 'instructor.last_name')
                ->orderBy('total_students', 'desc')
                ->limit(10)
                ->get();

            return response()->json($data);
        });
    }
}
