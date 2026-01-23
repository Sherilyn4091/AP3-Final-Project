<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportsController extends Controller
{
    /**
     * Financial Reports Dashboard
     * KPIs + charts for revenue + enrollment + package popularity
     */
    public function index(Request $request)
    {
        // ------------------------------------------------------------
        // 1) Find "Paid" payment_status_id safely
        // ------------------------------------------------------------
        $paidStatusId = DB::table('payment_status')
            ->whereRaw('LOWER(status_name) = ?', ['paid'])
            ->value('status_id');

        // ------------------------------------------------------------
        // 2) KPI STATS
        // ------------------------------------------------------------
        $totalPaidRevenue = DB::table('payment')
            ->when($paidStatusId, fn ($q) => $q->where('payment_status_id', $paidStatusId))
            ->sum('amount');

        $revenue30Days = DB::table('payment')
            ->when($paidStatusId, fn ($q) => $q->where('payment_status_id', $paidStatusId))
            ->whereDate('payment_date', '>=', now()->subDays(30)->toDateString())
            ->sum('amount');

        $totalEnrollments  = DB::table('enrollment')->count();
        $activeEnrollments = DB::table('enrollment')->where('status', 'active')->count();

        // You can keep inventory KPI if you want, but charts removed.
        $totalInventoryValue = DB::table('inventory')
            ->where('is_active', true)
            ->selectRaw('COALESCE(SUM(quantity * COALESCE(unit_price, 0)), 0) AS total_value')
            ->value('total_value');

        $lowStockCount = DB::table('inventory')
            ->where('is_active', true)
            ->whereColumn('quantity', '<=', 'low_stock_threshold')
            ->where('quantity', '>', 0)
            ->count();

        $outOfStockCount = DB::table('inventory')
            ->where('is_active', true)
            ->where('quantity', 0)
            ->count();

        $stats = [
            'total_paid_revenue'     => (float) $totalPaidRevenue,
            'revenue_30_days'        => (float) $revenue30Days,
            'total_enrollments'      => (int) $totalEnrollments,
            'active_enrollments'     => (int) $activeEnrollments,
            'total_inventory_value'  => (float) $totalInventoryValue,
            'low_stock'              => (int) $lowStockCount,
            'out_of_stock'           => (int) $outOfStockCount,
        ];

        // ------------------------------------------------------------
        // 3) CHART DATA
        // ------------------------------------------------------------

        // A) Daily paid revenue (last 30 days) — includes missing days as 0
        // Using generate_series so you always get 30 points.
        $revenueTrend = DB::select("
            WITH days AS (
                SELECT generate_series(
                    CURRENT_DATE - INTERVAL '29 days',
                    CURRENT_DATE,
                    INTERVAL '1 day'
                )::date AS day
            )
            SELECT
                d.day AS label,
                COALESCE(SUM(p.amount), 0) AS value
            FROM days d
            LEFT JOIN payment p
                ON p.payment_date = d.day
                " . ($paidStatusId ? "AND p.payment_status_id = :paidStatusId" : "") . "
            GROUP BY d.day
            ORDER BY d.day
        ", $paidStatusId ? ['paidStatusId' => $paidStatusId] : []);

        // B) Daily enrollment trend (last 30 days) — includes missing days as 0
        // This fixes the “only 1 dot” problem and makes a real line.
        $enrollmentTrend = DB::select("
            WITH days AS (
                SELECT generate_series(
                    CURRENT_DATE - INTERVAL '29 days',
                    CURRENT_DATE,
                    INTERVAL '1 day'
                )::date AS day
            )
            SELECT
                d.day AS label,
                COALESCE(COUNT(e.enrollment_id), 0) AS value
            FROM days d
            LEFT JOIN enrollment e
                ON DATE(e.created_at) = d.day
            GROUP BY d.day
            ORDER BY d.day
        ");

        // C) Revenue by payment method (paid only)
       $revenueByMethod = DB::table('payment')
            ->join('payment_methods', 'payment.payment_method_id', '=', 'payment_methods.method_id')
            ->when($paidStatusId, fn ($q) => $q->where('payment.payment_status_id', $paidStatusId))
            ->selectRaw('payment_methods.method_name AS label, SUM(payment.amount) AS value')
            ->groupBy('payment_methods.method_name')
            ->orderByRaw('SUM(payment.amount) DESC')
            ->get();

        // D) Enrollment Package Popularity (ONLY 5/10/20)
        // Based on enrollment.total_sessions
        $packagePopularity = DB::table('enrollment')
            ->whereIn('total_sessions', [5, 10, 20])
            ->selectRaw('total_sessions::text AS label, COUNT(*) AS value')
            ->groupBy('total_sessions')
            ->orderByRaw('total_sessions ASC')
            ->get();

        // IMPORTANT: Build chartData ONCE and include everything
        $chartData = [
            'revenueTrend' => [
                'labels' => collect($revenueTrend)->pluck('label')->map(fn($d) => (string)$d)->values(),
                'values' => collect($revenueTrend)->pluck('value')->map(fn($v) => (float)$v)->values(),
            ],
            'enrollmentTrend' => [
                'labels' => collect($enrollmentTrend)->pluck('label')->map(fn($d) => (string)$d)->values(),
                'values' => collect($enrollmentTrend)->pluck('value')->map(fn($v) => (int)$v)->values(),
            ],
            'revenueByMethod' => [
                'labels' => $revenueByMethod->pluck('label')->values(),
                'values' => $revenueByMethod->pluck('value')->map(fn($v) => (float)$v)->values(),
            ],
            'packagePopularity' => [
                // ensure always has 5/10/20 even if 0
                'labels' => collect(['5', '10', '20']),
                'values' => collect(['5','10','20'])->map(function ($pkg) use ($packagePopularity) {
                    $row = $packagePopularity->firstWhere('label', $pkg);
                    return $row ? (int)$row->value : 0;
                })->values(),
            ],
        ];

        return view('admin.reports.index', compact('stats', 'chartData'));
    }

    public function exportPdf(Request $request)
{
    // Reuse the SAME data generation logic from index()
    // Easiest approach: call index() logic by extracting it into a private function.
    // But for now (fast), we just duplicate the chart + stats logic.

    // ------------------------------------------------------------
    // 1) Find paid status id
    // ------------------------------------------------------------
    $paidStatusId = DB::table('payment_status')
        ->whereRaw('LOWER(status_name) = ?', ['paid'])
        ->value('status_id');

    // ------------------------------------------------------------
    // 2) KPI STATS
    // ------------------------------------------------------------
    $totalPaidRevenue = DB::table('payment')
        ->when($paidStatusId, fn ($q) => $q->where('payment_status_id', $paidStatusId))
        ->sum('amount');

    $revenue30Days = DB::table('payment')
        ->when($paidStatusId, fn ($q) => $q->where('payment_status_id', $paidStatusId))
        ->whereDate('payment_date', '>=', now()->subDays(30)->toDateString())
        ->sum('amount');

    $totalEnrollments  = DB::table('enrollment')->count();
    $activeEnrollments = DB::table('enrollment')->where('status', 'active')->count();

    $totalInventoryValue = DB::table('inventory')
        ->where('is_active', true)
        ->selectRaw('COALESCE(SUM(quantity * COALESCE(unit_price, 0)), 0) AS total_value')
        ->value('total_value');

    $lowStockCount = DB::table('inventory')
        ->where('is_active', true)
        ->whereColumn('quantity', '<=', 'low_stock_threshold')
        ->where('quantity', '>', 0)
        ->count();

    $outOfStockCount = DB::table('inventory')
        ->where('is_active', true)
        ->where('quantity', 0)
        ->count();

    $stats = [
        'total_paid_revenue'     => (float) $totalPaidRevenue,
        'revenue_30_days'        => (float) $revenue30Days,
        'total_enrollments'      => (int) $totalEnrollments,
        'active_enrollments'     => (int) $activeEnrollments,
        'total_inventory_value'  => (float) $totalInventoryValue,
        'low_stock'              => (int) $lowStockCount,
        'out_of_stock'           => (int) $outOfStockCount,
    ];

    // ------------------------------------------------------------
    // 3) CHART DATA (same as dashboard)
    // ------------------------------------------------------------
    $revenueTrend = DB::select("
        WITH days AS (
            SELECT generate_series(
                CURRENT_DATE - INTERVAL '29 days',
                CURRENT_DATE,
                INTERVAL '1 day'
            )::date AS day
        )
        SELECT
            d.day AS label,
            COALESCE(SUM(p.amount), 0) AS value
        FROM days d
        LEFT JOIN payment p
            ON p.payment_date = d.day
            " . ($paidStatusId ? "AND p.payment_status_id = :paidStatusId" : "") . "
        GROUP BY d.day
        ORDER BY d.day
    ", $paidStatusId ? ['paidStatusId' => $paidStatusId] : []);

    $enrollmentTrend = DB::select("
        WITH days AS (
            SELECT generate_series(
                CURRENT_DATE - INTERVAL '29 days',
                CURRENT_DATE,
                INTERVAL '1 day'
            )::date AS day
        )
        SELECT
            d.day AS label,
            COALESCE(COUNT(e.enrollment_id), 0) AS value
        FROM days d
        LEFT JOIN enrollment e
            ON DATE(e.created_at) = d.day
        GROUP BY d.day
        ORDER BY d.day
    ");

    $revenueByMethod = DB::table('payment')
        ->join('payment_methods', 'payment.payment_method_id', '=', 'payment_methods.method_id')
        ->when($paidStatusId, fn ($q) => $q->where('payment.payment_status_id', $paidStatusId))
        ->selectRaw('payment_methods.method_name AS label, SUM(payment.amount) AS value')
        ->groupBy('payment_methods.method_name')
        ->orderByRaw('SUM(payment.amount) DESC')
        ->get();

    $packagePopularity = DB::table('enrollment')
        ->whereIn('total_sessions', [5, 10, 20])
        ->selectRaw('total_sessions::text AS label, COUNT(*) AS value')
        ->groupBy('total_sessions')
        ->orderByRaw('total_sessions ASC')
        ->get();

    $chartData = [
        'revenueTrend' => [
            'labels' => collect($revenueTrend)->pluck('label')->map(fn($d) => (string)$d)->values(),
            'values' => collect($revenueTrend)->pluck('value')->map(fn($v) => (float)$v)->values(),
        ],
        'enrollmentTrend' => [
            'labels' => collect($enrollmentTrend)->pluck('label')->map(fn($d) => (string)$d)->values(),
            'values' => collect($enrollmentTrend)->pluck('value')->map(fn($v) => (int)$v)->values(),
        ],
        'revenueByMethod' => [
            'labels' => $revenueByMethod->pluck('label')->values(),
            'values' => $revenueByMethod->pluck('value')->map(fn($v) => (float)$v)->values(),
        ],
        'packagePopularity' => [
            'labels' => collect(['5', '10', '20']),
            'values' => collect(['5','10','20'])->map(function ($pkg) use ($packagePopularity) {
                $row = $packagePopularity->firstWhere('label', $pkg);
                return $row ? (int)$row->value : 0;
            })->values(),
        ],
    ];

    // Render a PDF view (no Chart.js inside PDF)
    $pdf = Pdf::loadView('admin.reports.export-pdf', [
        'stats' => $stats,
        'chartData' => $chartData,
        'generatedAt' => now(),
    ])->setPaper('a4', 'portrait');

    $filename = 'monthly-report-' . now()->format('Y-m-d_His') . '.pdf';

    return $pdf->download($filename);
}

}