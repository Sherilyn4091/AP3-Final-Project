<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\PythonAnalyticsService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

/*
|--------------------------------------------------------------------------
| ReportsController
|--------------------------------------------------------------------------
|
| Handles the Admin Monthly Reports page.
|
| Combined report structure:
| - Monthly Reports / descriptive business analytics
| - Student Retention Risk Analytics / Decision Tree data mining result
|
| Export design:
| - One reports page = one combined PDF export.
| - One reports page = one combined CSV export.
|
*/

class ReportsController extends Controller
{
    private PythonAnalyticsService $analyticsService;

    /**
     * Laravel auto-injects the same analytics service used by Student Risk Analytics.
     */
    public function __construct(PythonAnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    /**
     * Show the combined Monthly Reports page.
     */
    public function index(Request $request)
    {
        $report = $this->buildMonthlyReportData();

        return view('admin.reports.index', [
            'stats' => $report['stats'],
            'chartData' => $report['chartData'],
        ]);
    }

    /**
     * Export the same page as one combined PDF.
     */
    public function exportPdf(Request $request)
    {
        $report = $this->buildMonthlyReportData();
        $riskResult = $this->buildRiskAnalyticsResult('full');

        $pdf = Pdf::loadView('admin.reports.export-pdf', [
            'stats' => $report['stats'],
            'chartData' => $report['chartData'],
            'riskResult' => $riskResult,
            'generatedAt' => now(),
            'reportDateRange' => $this->lastThirtyDaysLabel(),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('music-lab-combined-report-' . now()->format('Y-m-d_His') . '.pdf');
    }

    /**
     * Export the same page as one combined CSV.
     *
     * CSV design:
     * - Section 1: Monthly Reports
     * - Blank row
     * - Section 2: Student Retention Risk Report
     *
     * This avoids mixing different column structures into one confusing table.
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $report = $this->buildMonthlyReportData();
        $riskResult = $this->buildRiskAnalyticsResult('full');
        $fileName = 'music-lab-combined-report-' . now()->format('Y-m-d_His') . '.csv';

        return response()->streamDownload(function () use ($report, $riskResult) {
            $handle = fopen('php://output', 'w');

            $this->writeMonthlySummaryCsv($handle, $report['stats']);
            $this->writeChartDataCsv($handle, $report['chartData']);
            $this->writeRiskAnalyticsCsv($handle, $riskResult);

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv',
        ]);
    }

    /**
     * Build all monthly report KPIs and chart data.
     */
    private function buildMonthlyReportData(): array
    {
        $paidStatusId = DB::table('payment_status')
            ->whereRaw('LOWER(status_name) = ?', ['paid'])
            ->value('status_id');

        $totalPaidRevenue = DB::table('payment')
            ->when($paidStatusId, fn ($query) => $query->where('payment_status_id', $paidStatusId))
            ->sum('amount');

        $revenue30Days = DB::table('payment')
            ->when($paidStatusId, fn ($query) => $query->where('payment_status_id', $paidStatusId))
            ->whereDate('payment_date', '>=', now()->subDays(29)->toDateString())
            ->sum('amount');

        $totalEnrollments = DB::table('enrollment')->count();
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
            'total_paid_revenue' => (float) $totalPaidRevenue,
            'revenue_30_days' => (float) $revenue30Days,
            'total_enrollments' => (int) $totalEnrollments,
            'active_enrollments' => (int) $activeEnrollments,
            'total_inventory_value' => (float) $totalInventoryValue,
            'low_stock' => (int) $lowStockCount,
            'out_of_stock' => (int) $outOfStockCount,
        ];

        $chartData = [
            'revenueTrend' => $this->dailyPaidRevenueTrend($paidStatusId),
            'enrollmentTrend' => $this->dailyEnrollmentTrend(),
            'revenueByMethod' => $this->revenueByPaymentMethod($paidStatusId),
            'packagePopularity' => $this->packagePopularity(),
        ];

        return [
            'stats' => $stats,
            'chartData' => $chartData,
        ];
    }

    /**
     * Daily paid revenue trend for the last 30 days.
     */
    private function dailyPaidRevenueTrend($paidStatusId): array
    {
        $paidCondition = $paidStatusId ? 'AND p.payment_status_id = :paidStatusId' : 'AND 1 = 0';

        $rows = DB::select("
            WITH days AS (
                SELECT generate_series(
                    CURRENT_DATE - INTERVAL '29 days',
                    CURRENT_DATE,
                    INTERVAL '1 day'
                )::date AS day
            )
            SELECT
                d.day::text AS label,
                COALESCE(SUM(p.amount), 0)::numeric AS value
            FROM days d
            LEFT JOIN payment p
                ON DATE(p.payment_date) = d.day
                {$paidCondition}
            GROUP BY d.day
            ORDER BY d.day
        ", $paidStatusId ? ['paidStatusId' => $paidStatusId] : []);

        return $this->toChartPair($rows, 'label', 'value', 'float');
    }

    /**
     * Daily enrollment trend for the last 30 days.
     */
    private function dailyEnrollmentTrend(): array
    {
        $rows = DB::select("
            WITH days AS (
                SELECT generate_series(
                    CURRENT_DATE - INTERVAL '29 days',
                    CURRENT_DATE,
                    INTERVAL '1 day'
                )::date AS day
            )
            SELECT
                d.day::text AS label,
                COALESCE(COUNT(e.enrollment_id), 0)::int AS value
            FROM days d
            LEFT JOIN enrollment e
                ON DATE(e.created_at) = d.day
            GROUP BY d.day
            ORDER BY d.day
        ");

        return $this->toChartPair($rows, 'label', 'value', 'int');
    }

    /**
     * Paid revenue grouped by payment method.
     */
    private function revenueByPaymentMethod($paidStatusId): array
    {
        $rows = DB::table('payment')
            ->join('payment_methods', 'payment.payment_method_id', '=', 'payment_methods.method_id')
            ->when($paidStatusId, fn ($query) => $query->where('payment.payment_status_id', $paidStatusId))
            ->selectRaw('payment_methods.method_name AS label, SUM(payment.amount) AS value')
            ->groupBy('payment_methods.method_name')
            ->orderByRaw('SUM(payment.amount) DESC')
            ->get();

        return $this->toChartPair($rows, 'label', 'value', 'float');
    }

    /**
     * Enrollment package popularity for 5, 10, and 20 sessions.
     */
    private function packagePopularity(): array
    {
        $rows = DB::table('enrollment')
            ->whereIn('total_sessions', [5, 10, 20])
            ->selectRaw('total_sessions::text AS label, COUNT(*) AS value')
            ->groupBy('total_sessions')
            ->orderByRaw('total_sessions ASC')
            ->get();

        return [
            'labels' => collect(['5', '10', '20']),
            'values' => collect(['5', '10', '20'])->map(function ($package) use ($rows) {
                $row = $rows->firstWhere('label', $package);
                return $row ? (int) $row->value : 0;
            })->values(),
        ];
    }

    /**
     * Convert database rows into Chart.js-friendly labels and values.
     */
    private function toChartPair($rows, string $labelKey, string $valueKey, string $type): array
    {
        $collection = collect($rows);

        return [
            'labels' => $collection->pluck($labelKey)->map(fn ($value) => (string) $value)->values(),
            'values' => $collection->pluck($valueKey)->map(function ($value) use ($type) {
                return $type === 'int' ? (int) $value : (float) $value;
            })->values(),
        ];
    }

    /**
     * Build student risk analytics using the existing Python service.
     */
    private function buildRiskAnalyticsResult(string $mode): array
    {
        try {
            $result = $this->analyticsService->runStudentRiskAnalysis(
                $this->buildStudentDataset(),
                $mode
            );

            $students = $result['students'] ?? [];
            usort($students, function (array $first, array $second): int {
                return (float) ($second['risk_score'] ?? 0) <=> (float) ($first['risk_score'] ?? 0);
            });

            $summary = $this->normalizeRiskSummary($result['summary'] ?? [], $students);

            $result['summary'] = $summary;
            $result['students'] = $students;
            $result['top_high_risk_students'] = array_slice(array_values(array_filter(
                $students,
                fn (array $student): bool => ($student['risk_level'] ?? '') === 'High Risk'
            )), 0, 10);

            return $result;
        } catch (\Throwable $exception) {
            Log::error('Combined report risk analytics failed.', [
                'message' => $exception->getMessage(),
            ]);

            return [
                'ok' => false,
                'message' => 'Student Risk Analytics is temporarily unavailable.',
                'summary' => [
                    'analyzed' => 0,
                    'total_students_analyzed' => 0,
                    'low_risk' => 0,
                    'medium_risk' => 0,
                    'high_risk' => 0,
                ],
                'students' => [],
                'top_high_risk_students' => [],
            ];
        }
    }

    /**
     * Fixes the PDF bug where analyzed count can show 0 even when risk groups have data.
     */
    private function normalizeRiskSummary(array $summary, array $students): array
    {
        $lowRisk = (int) ($summary['low_risk'] ?? 0);
        $mediumRisk = (int) ($summary['medium_risk'] ?? 0);
        $highRisk = (int) ($summary['high_risk'] ?? 0);

        $computedTotal = $lowRisk + $mediumRisk + $highRisk;
        $reportedTotal = (int) ($summary['analyzed'] ?? ($summary['total_students_analyzed'] ?? 0));
        $finalTotal = $reportedTotal > 0 ? $reportedTotal : ($computedTotal > 0 ? $computedTotal : count($students));

        $summary['analyzed'] = $finalTotal;
        $summary['total_students_analyzed'] = $finalTotal;
        $summary['low_risk'] = $lowRisk;
        $summary['medium_risk'] = $mediumRisk;
        $summary['high_risk'] = $highRisk;

        return $summary;
    }

    /**
     * Build student features for the Python Decision Tree classifier.
     */
    private function buildStudentDataset(): array
    {
        $students = $this->baseStudentRows();
        $latestEnrollments = $this->latestEnrollmentRows();
        $attendanceStats = $this->attendanceStatsByStudent();
        $progressStats = $this->progressStatsByStudent();
        $scheduleStats = $this->scheduleStatsByStudent();

        return $students->map(function ($student) use ($latestEnrollments, $attendanceStats, $progressStats, $scheduleStats) {
            $studentId = (int) $student->student_id;
            $enrollment = $latestEnrollments[$studentId] ?? null;
            $attendance = $attendanceStats[$studentId] ?? null;
            $progress = $progressStats[$studentId] ?? null;
            $schedule = $scheduleStats[$studentId] ?? null;

            $totalAttendance = (int) ($attendance->total_attendance ?? 0);
            $presentCount = (int) ($attendance->present_count ?? 0);
            $attendanceRate = $totalAttendance > 0
                ? round(($presentCount / $totalAttendance) * 100, 2)
                : 100.0;

            return [
                'student_id' => $studentId,
                'student_name' => trim($student->student_name),
                'email' => $student->email,
                'phone' => $student->phone,
                'is_active' => (bool) $student->is_active,
                'student_status' => $student->status_name,
                'instrument_name' => $student->instrument_name,
                'instructor_name' => $enrollment->instructor_name ?? 'Unassigned',
                'enrollment_status' => $enrollment->enrollment_status ?? 'none',
                'payment_status' => $enrollment->payment_status ?? 'none',
                'total_sessions' => (int) ($enrollment->total_sessions ?? 0),
                'completed_sessions' => (int) ($enrollment->completed_sessions ?? 0),
                'remaining_sessions' => (int) ($enrollment->remaining_sessions ?? 0),
                'attendance_rate' => $attendanceRate,
                'absence_count' => (int) ($attendance->absence_count ?? 0),
                'late_count' => (int) ($attendance->late_count ?? 0),
                'average_progress_rating' => $enrollment ? round((float) ($progress->average_progress_rating ?? 8.0), 2) : null,
                'last_lesson_date' => $schedule->last_lesson_date ?? null,
                'days_since_last_lesson' => max(0, (int) ($schedule->days_since_last_lesson ?? 0)),
            ];
        })->values()->all();
    }

    private function baseStudentRows()
    {
        return DB::table('student as s')
            ->leftJoin('student_status as ss', 's.student_status_id', '=', 'ss.status_id')
            ->leftJoin('instrument as inst', 's.instrument_id', '=', 'inst.instrument_id')
            ->select(
                's.student_id',
                DB::raw("CONCAT(s.first_name, ' ', s.last_name) as student_name"),
                's.email',
                's.phone',
                's.is_active',
                'ss.status_name',
                'inst.instrument_name'
            )
            ->orderBy('s.last_name')
            ->orderBy('s.first_name')
            ->get();
    }

    private function latestEnrollmentRows(): array
    {
        return DB::table(DB::raw('( 
                SELECT DISTINCT ON (e.student_id)
                    e.student_id,
                    e.enrollment_id,
                    e.status as enrollment_status,
                    e.payment_status,
                    e.total_sessions,
                    e.completed_sessions,
                    e.remaining_sessions,
                    e.enrollment_date,
                    e.created_at,
                    CONCAT(i.first_name, \' \', i.last_name) as instructor_name
                FROM enrollment e
                LEFT JOIN instructor i ON e.instructor_id = i.instructor_id
                ORDER BY e.student_id, e.enrollment_date DESC NULLS LAST, e.created_at DESC NULLS LAST
            ) as latest_enrollment'))
            ->get()
            ->keyBy('student_id')
            ->all();
    }

    private function attendanceStatsByStudent(): array
    {
        return DB::table('attendance')
            ->where('attendance_type', 'lesson')
            ->select(
                'student_id',
                DB::raw('COUNT(*) as total_attendance'),
                DB::raw("SUM(CASE WHEN attendance_status = 'present' THEN 1 ELSE 0 END) as present_count"),
                DB::raw("SUM(CASE WHEN attendance_status IN ('absent', 'late', 'half_day', 'on_leave') THEN 1 ELSE 0 END) as absence_count"),
                DB::raw("SUM(CASE WHEN attendance_status = 'late' THEN 1 ELSE 0 END) as late_count")
            )
            ->whereNotNull('student_id')
            ->groupBy('student_id')
            ->get()
            ->keyBy('student_id')
            ->all();
    }

    private function progressStatsByStudent(): array
    {
        return DB::table('progress')
            ->select(
                'student_id',
                DB::raw('AVG(COALESCE(performance_rating, technical_skills_rating, musicality_rating, effort_rating)) as average_progress_rating')
            )
            ->whereNotNull('student_id')
            ->groupBy('student_id')
            ->get()
            ->keyBy('student_id')
            ->all();
    }

    private function scheduleStatsByStudent(): array
    {
        return DB::table('schedule')
            ->select(
                'student_id',
                DB::raw('MAX(schedule_date) as last_lesson_date'),
                DB::raw('GREATEST(COALESCE((CURRENT_DATE - MAX(schedule_date))::int, 0), 0) as days_since_last_lesson')
            )
            ->whereNotNull('student_id')
            ->where('schedule_date', '<=', now()->toDateString())
            ->where('status', '!=', 'cancelled')
            ->groupBy('student_id')
            ->get()
            ->keyBy('student_id')
            ->all();
    }

    /**
     * Format the last 30 days for the PDF/CSV label.
     */
    private function lastThirtyDaysLabel(): string
    {
        return now()->subDays(29)->format('M d, Y') . ' - ' . now()->format('M d, Y');
    }

    /**
     * CSV section: KPI summary.
     */
    private function writeMonthlySummaryCsv($handle, array $stats): void
    {
        fputcsv($handle, ['MUSIC LAB COMBINED REPORT']);
        fputcsv($handle, ['Generated At', now()->format('Y-m-d H:i:s')]);
        fputcsv($handle, ['Date Range', $this->lastThirtyDaysLabel()]);
        fputcsv($handle, []);

        fputcsv($handle, ['MONTHLY REPORT SUMMARY']);
        fputcsv($handle, ['Metric', 'Value']);
        fputcsv($handle, ['Total Paid Revenue', $stats['total_paid_revenue'] ?? 0]);
        fputcsv($handle, ['Revenue Last 30 Days', $stats['revenue_30_days'] ?? 0]);
        fputcsv($handle, ['Total Enrollments', $stats['total_enrollments'] ?? 0]);
        fputcsv($handle, ['Active Enrollments', $stats['active_enrollments'] ?? 0]);
        fputcsv($handle, ['Total Inventory Value', $stats['total_inventory_value'] ?? 0]);
        fputcsv($handle, ['Low Stock Items', $stats['low_stock'] ?? 0]);
        fputcsv($handle, ['Out of Stock Items', $stats['out_of_stock'] ?? 0]);
        fputcsv($handle, []);
    }

    /**
     * CSV section: chart source data.
     */
    private function writeChartDataCsv($handle, array $chartData): void
    {
        $sections = [
            'Revenue Trend' => $chartData['revenueTrend'] ?? [],
            'Enrollment Trend' => $chartData['enrollmentTrend'] ?? [],
            'Revenue by Payment Method' => $chartData['revenueByMethod'] ?? [],
            'Package Popularity' => $chartData['packagePopularity'] ?? [],
        ];

        foreach ($sections as $title => $data) {
            fputcsv($handle, [$title]);
            fputcsv($handle, ['Label', 'Value']);

            $labels = collect($data['labels'] ?? [])->values();
            $values = collect($data['values'] ?? [])->values();
            $sectionTotal = 0;

            foreach ($labels as $index => $label) {
                $value = $values[$index] ?? 0;
                $sectionTotal += is_numeric($value) ? (float) $value : 0;
                fputcsv($handle, [$label, $value]);
            }

            if (in_array($title, ['Revenue by Payment Method', 'Package Popularity'], true)) {
                fputcsv($handle, ['TOTAL', $sectionTotal]);
            }

            fputcsv($handle, []);
        }
    }

    /**
     * CSV section: student risk analytics.
     */
    private function writeRiskAnalyticsCsv($handle, array $riskResult): void
    {
        $summary = $this->normalizeRiskSummary($riskResult['summary'] ?? [], $riskResult['students'] ?? []);
        $students = $riskResult['students'] ?? [];

        fputcsv($handle, ['STUDENT RETENTION RISK REPORT']);
        fputcsv($handle, ['Data Mining Technique', 'Decision Tree Classification']);
        fputcsv($handle, ['Risk Factors Evaluated', 'Attendance Rate, Payment Status, Session Completion, Progress Rating, Lesson Recency']);
        fputcsv($handle, []);

        fputcsv($handle, ['Risk Summary']);
        fputcsv($handle, ['Analyzed', $summary['analyzed'] ?? 0]);
        fputcsv($handle, ['Low Risk', $summary['low_risk'] ?? 0]);
        fputcsv($handle, ['Medium Risk', $summary['medium_risk'] ?? 0]);
        fputcsv($handle, ['High Risk', $summary['high_risk'] ?? 0]);
        fputcsv($handle, []);

        fputcsv($handle, [
            'Student ID',
            'Student Name',
            'Instrument',
            'Instructor',
            'Risk Level',
            'Risk Score',
            'Attendance Rate',
            'Absences / Non-Present',
            'Late Count',
            'Average Progress Rating',
            'Enrollment Status',
            'Payment Status',
            'Remaining Sessions',
            'Days Since Last Lesson',
            'Primary Reason',
            'Recommended Action',
        ]);

        foreach ($students as $student) {
            fputcsv($handle, [
                $student['student_id'] ?? '',
                $student['student_name'] ?? '',
                $student['instrument_name'] ?? '',
                $student['instructor_name'] ?? '',
                $student['risk_level'] ?? '',
                $student['risk_score'] ?? '',
                $student['attendance_rate'] ?? '',
                $student['absence_count'] ?? '',
                $student['late_count'] ?? '',
                $student['average_progress_rating'] ?? 'N/A',
                $student['enrollment_status'] ?? '',
                $student['payment_status'] ?? '',
                $student['remaining_sessions'] ?? '',
                max(0, (int) ($student['days_since_last_lesson'] ?? 0)),
                $student['primary_reason'] ?? '',
                $student['recommended_action'] ?? '',
            ]);
        }
    }
}