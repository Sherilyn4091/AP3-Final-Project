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

        // User Statistics (FIX: Use whereRaw for PostgreSQL boolean)
        $totalUsers = DB::table('user_account')->count();
        $activeStudents = DB::table('student')->whereRaw('is_active = TRUE')->count();
        $activeInstructors = DB::table('instructor')->whereRaw('is_active = TRUE')->count();
        $totalStaff = DB::table('sales_staff')->whereRaw('is_active = TRUE')->count() +
                    DB::table('all_around_staff')->whereRaw('is_active = TRUE')->count();

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

        $pendingStatusId = DB::table('payment_status')
            ->where('status_name', 'Pending')
            ->value('status_id');

        $pendingPayments = DB::table('payment')
            ->where('payment_status_id', $pendingStatusId)
            ->count();

        // Low Stock Alerts
        $lowStockAlerts = DB::table('inventory')
            ->whereRaw('quantity <= low_stock_threshold')
            ->whereRaw('is_active = TRUE')
            ->count();

        // Today's Schedule
        $todaysSchedule = DB::table('schedule')
            ->join('student', 'schedule.student_id', '=', 'student.student_id')
            ->leftJoin('instructor', 'schedule.instructor_id', '=', 'instructor.instructor_id')
            ->select(
                'schedule.*',
                DB::raw("CONCAT(student.first_name, ' ', student.last_name) as student_name"),
                DB::raw("CONCAT(instructor.first_name, ' ', instructor.last_name) as instructor_name")
            )
            ->whereDate('schedule.schedule_date', $today)
            ->orderBy('schedule.start_time')
            ->get();

        // Today's Bookings
        $todaysBookings = DB::table('booking')
            ->select('booking.*', 'booking.contact_name as customer')
            ->whereDate('booking_date', $today)
            ->orderBy('start_time')
            ->get();

        // Recent Activity
        $recentEnrollments = DB::table('enrollment')
            ->join('student', 'enrollment.student_id', '=', 'student.student_id')
            ->select(
                'enrollment.enrollment_id',
                'enrollment.created_at',
                DB::raw("CONCAT(student.first_name, ' ', student.last_name) as student_name")
            )
            ->orderBy('enrollment.created_at', 'desc')
            ->limit(5)
            ->get();

        $recentPayments = DB::table('payment')
            ->join('student', 'payment.student_id', '=', 'student.student_id')
            ->join('payment_methods', 'payment.payment_method_id', '=', 'payment_methods.method_id')
            ->select(
                'payment.*',
                DB::raw("CONCAT(student.first_name, ' ', student.last_name) as student_name"),
                'payment_methods.method_name'
            )
            ->orderBy('payment.created_at', 'desc')
            ->limit(5)
            ->get();

        $recentReports = DB::table('report')
            ->select('report_id', 'report_type', 'report_title', 'generated_at')
            ->orderBy('generated_at', 'desc')
            ->limit(5)
            ->get();

        return compact(
            'totalUsers',
            'activeStudents',
            'activeInstructors',
            'totalStaff',
            'todaysEnrollments',
            'todaysRevenue',
            'pendingPayments',
            'lowStockAlerts',
            'todaysSchedule',
            'todaysBookings',
            'recentEnrollments',
            'recentPayments',
            'recentReports'
        );
    }

    /**
     * Chart API endpoints (keep these as they are, add caching if needed)
     */
    public function getWeeklyRevenue()
    {
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
    }

    public function getEnrollmentTrend()
    {
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
    }

    public function getInstrumentPopularity()
    {
        $data = DB::table('student')
            ->join('instrument', 'student.instrument_id', '=', 'instrument.instrument_id')
            ->select(
                'instrument.instrument_name',
                DB::raw("COUNT(*) as count")
            )
            ->whereNotNull('student.instrument_id')
            ->groupBy('instrument.instrument_id', 'instrument.instrument_name')
            ->orderBy('count', 'desc')
            ->get();

        return response()->json($data);
    }
    public function getInstructorPerformance()
    {
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
    }
}
