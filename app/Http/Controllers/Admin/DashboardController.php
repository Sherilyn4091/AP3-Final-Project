<?php
// app/Http/Controllers/Admin/DashboardController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard with real-time data.
     * Fetches all statistics, recent activity, and today's schedule/bookings.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // ============================================================================
        // AUTHORIZATION CHECK
        // ============================================================================
        $isSuperAdmin = DB::table('user_account')
            ->where('user_id', Auth::id())
            ->value('is_super_admin');

        if (!$isSuperAdmin) {
            abort(403, 'Unauthorized access');
        }

        $today = now()->format('Y-m-d');

        // ============================================================================
        // TOP ROW CARDS - User Statistics
        // ============================================================================
        $totalUsers = DB::table('user_account')->count();
        $activeStudents = DB::table('student')->where('is_active', true)->count();
        $activeInstructors = DB::table('instructor')->where('is_active', true)->count();
        $totalStaff = DB::table('sales_staff')->where('is_active', true)->count() +
                      DB::table('all_around_staff')->where('is_active', true)->count();

        // ============================================================================
        // SECOND ROW CARDS - Today's Activity & Alerts
        // ============================================================================
        $todaysEnrollments = DB::table('enrollment')
            ->whereDate('enrollment_date', $today)
            ->count();

        // Get 'Paid' status ID for revenue calculation
        $paidStatusId = DB::table('payment_status')
            ->where('status_name', 'Paid')
            ->value('status_id');

        $todaysRevenue = DB::table('payment')
            ->whereDate('payment_date', $today)
            ->where('payment_status_id', $paidStatusId)
            ->sum('amount') ?? 0;

        $pendingPayments = DB::table('enrollment')
            ->whereIn('payment_status', ['pending', 'partial'])
            ->count();

        $lowStockAlerts = DB::table('v_low_stock_items')->count();

        // ============================================================================
        // RECENT ACTIVITY FEED - Last 5 records each
        // ============================================================================
        // Recent Enrollments with Student Names
        $recentEnrollments = DB::table('enrollment')
            ->join('student', 'enrollment.student_id', '=', 'student.student_id')
            ->select(
                'enrollment.enrollment_id',
                DB::raw("CONCAT(student.first_name, ' ', student.last_name) as student_name"),
                'enrollment.created_at'
            )
            ->orderBy('enrollment.created_at', 'desc')
            ->limit(5)
            ->get();

        // Recent Payments with Student Names
        $recentPayments = DB::table('payment')
            ->join('student', 'payment.student_id', '=', 'student.student_id')
            ->join('payment_method', 'payment.payment_method_id', '=', 'payment_method.method_id')
            ->select(
                'payment.payment_id',
                'payment.amount',
                DB::raw("CONCAT(student.first_name, ' ', student.last_name) as student_name"),
                'payment_method.method_name',
                'payment.created_at'
            )
            ->orderBy('payment.created_at', 'desc')
            ->limit(5)
            ->get();

        // Recent Reports
        $recentReports = DB::table('report')
            ->select('report_id', 'report_type', 'report_title', 'generated_at')
            ->orderBy('generated_at', 'desc')
            ->limit(5)
            ->get();

        // ============================================================================
        // TODAY'S ACTIVITY PANEL - Schedules & Bookings
        // ============================================================================
        // Today's Schedule with Names
        $todaysSchedule = DB::table('schedule')
            ->join('student', 'schedule.student_id', '=', 'student.student_id')
            ->leftJoin('instructor', 'schedule.instructor_id', '=', 'instructor.instructor_id')
            ->whereDate('schedule.schedule_date', $today)
            ->select(
                'schedule.start_time',
                'schedule.end_time',
                DB::raw("CONCAT(student.first_name, ' ', student.last_name) as student_name"),
                DB::raw("CONCAT(instructor.first_name, ' ', instructor.last_name) as instructor_name"),
                'schedule.room_number',
                'schedule.status'
            )
            ->orderBy('schedule.start_time', 'asc')
            ->get();

        // Today's Bookings
        $todaysBookings = DB::table('booking')
            ->whereDate('booking_date', $today)
            ->select(
                'start_time',
                'end_time',
                'room_number',
                'contact_name as customer',
                'booking_status as status'
            )
            ->orderBy('start_time', 'asc')
            ->get();

        // ============================================================================
        // RETURN VIEW WITH ALL DATA
        // ============================================================================
        return view('admin.dashboard', compact(
            'totalUsers',
            'activeStudents',
            'activeInstructors',
            'totalStaff',
            'todaysEnrollments',
            'todaysRevenue',
            'pendingPayments',
            'lowStockAlerts',
            'recentEnrollments',
            'recentPayments',
            'recentReports',
            'todaysSchedule',
            'todaysBookings'
        ));
    }

    // ============================================================================
    // CHART API ENDPOINTS
    // ============================================================================

    /**
     * Get weekly revenue for the last 30 days (~4 weeks)
     */
    public function getWeeklyRevenue()
    {
        $paidStatusId = DB::table('payment_status')
            ->where('status_name', 'Paid')
            ->value('status_id');

        $startDate = Carbon::now()->subDays(30);
        
        $weeklyData = DB::table('payment')
            ->where('payment_status_id', $paidStatusId)
            ->where('payment_date', '>=', $startDate)
            ->select(
                DB::raw("DATE_TRUNC('week', payment_date) as week_start"),
                DB::raw("SUM(amount) as revenue")
            )
            ->groupBy('week_start')
            ->orderBy('week_start', 'asc')
            ->get();

        $formattedData = $weeklyData->map(function($item) {
            $weekStart = Carbon::parse($item->week_start);
            $weekEnd = $weekStart->copy()->addDays(6);
            
            return [
                'week_label' => $weekStart->format('M d') . ' - ' . $weekEnd->format('M d'),
                'revenue' => (float) $item->revenue
            ];
        });

        return response()->json(['data' => $formattedData]);
    }

    /**
     * Get daily enrollment trend for the last 30 days
     */
    public function getEnrollmentTrend()
    {
        $startDate = Carbon::now()->subDays(30);
        
        $dailyData = DB::table('enrollment')
            ->where('enrollment_date', '>=', $startDate)
            ->select(
                DB::raw("DATE(enrollment_date) as date"),
                DB::raw("COUNT(*) as count")
            )
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        $formattedData = $dailyData->map(function($item) {
            return [
                'date_label' => Carbon::parse($item->date)->format('M d'),
                'count' => (int) $item->count,
                'full_date' => $item->date
            ];
        });

        return response()->json(['data' => $formattedData]);
    }

    /**
     * Get instrument popularity (total students per instrument)
     */
    public function getInstrumentPopularity()
    {
        $data = DB::table('student')
            ->join('instrument', 'student.instrument_id', '=', 'instrument.instrument_id')
            ->select(
                'instrument.instrument_name as name',
                DB::raw("COUNT(*) as count")
            )
            ->where('student.is_active', true)
            ->groupBy('instrument.instrument_name')
            ->orderBy('count', 'desc')
            ->get();

        return response()->json(['data' => $data]);
    }

    /**
     * Get instructor performance (total students taught)
     */
    public function getInstructorPerformance()
    {
        $data = DB::table('instructor')
            ->leftJoin('enrollment', 'instructor.instructor_id', '=', 'enrollment.instructor_id')
            ->select(
                DB::raw("CONCAT(instructor.first_name, ' ', instructor.last_name) as instructor_name"),
                DB::raw("COUNT(DISTINCT enrollment.student_id) as total_students")
            )
            ->where('instructor.is_active', true)
            ->groupBy('instructor.instructor_id', 'instructor.first_name', 'instructor.last_name')
            ->orderBy('total_students', 'desc')
            ->limit(10)
            ->get();

        return response()->json(['data' => $data]);
    }
}