<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/*
|--------------------------------------------------------------------------
| ChartController
|--------------------------------------------------------------------------
|
| Provides JSON chart data for the Admin Dashboard.
|
| Important:
| - Uses PostgreSQL-safe queries.
| - Uses Sunday as the start of the week because the client requested it.
| - Raw SQL fragments are hardcoded and not user-controlled.
|
*/

class ChartController extends Controller
{
    /**
     * Enrollment trend for the last 30 days.
     */
    public function enrollmentTrend()
    {
        try {
            $data = DB::select("
                WITH days AS (
                    SELECT generate_series(
                        CURRENT_DATE - INTERVAL '29 days',
                        CURRENT_DATE,
                        INTERVAL '1 day'
                    )::date AS day
                )
                SELECT
                    d.day::text AS date,
                    COALESCE(COUNT(e.enrollment_id), 0)::int AS count
                FROM days d
                LEFT JOIN enrollment e
                    ON DATE(e.enrollment_date) = d.day
                GROUP BY d.day
                ORDER BY d.day
            ");

            return response()->json($data);
        } catch (\Throwable $exception) {
            Log::error('Enrollment trend chart failed.', [
                'message' => $exception->getMessage(),
            ]);

            return response()->json([
                'error' => 'Unable to load enrollment trend data.',
            ], 500);
        }
    }

    /**
     * Weekly paid revenue within the last 30 days.
     *
     * Client requirement:
     * - Week starts on Sunday.
     *
     * PostgreSQL note:
     * - EXTRACT(DOW FROM date) returns 0 for Sunday, 1 for Monday, etc.
     * - date - dow_integer gives the Sunday of that week.
     */
    public function revenueWeekly()
    {
        try {
            $paidStatusId = $this->paidStatusId();

            // Safe because both possible fragments are hardcoded and contain no user input.
            $paidCondition = $paidStatusId ? 'AND p.payment_status_id = :paidStatusId' : 'AND 1 = 0';

            $data = DB::select("
                WITH date_bounds AS (
                    SELECT
                        (CURRENT_DATE - INTERVAL '29 days')::date AS start_day,
                        CURRENT_DATE::date AS end_day
                ), weeks AS (
                    SELECT generate_series(
                        start_day - EXTRACT(DOW FROM start_day)::int,
                        end_day - EXTRACT(DOW FROM end_day)::int,
                        INTERVAL '1 week'
                    )::date AS week_start
                    FROM date_bounds
                )
                SELECT
                    w.week_start::text AS week_start,
                    COALESCE(SUM(p.amount), 0)::numeric AS revenue
                FROM weeks w
                LEFT JOIN payment p
                    ON (DATE(p.payment_date) - EXTRACT(DOW FROM DATE(p.payment_date))::int) = w.week_start
                    AND DATE(p.payment_date) >= CURRENT_DATE - INTERVAL '29 days'
                    {$paidCondition}
                GROUP BY w.week_start
                ORDER BY w.week_start
            ", $paidStatusId ? ['paidStatusId' => $paidStatusId] : []);

            return response()->json($data);
        } catch (\Throwable $exception) {
            Log::error('Weekly revenue chart failed.', [
                'message' => $exception->getMessage(),
            ]);

            return response()->json([
                'error' => 'Unable to load weekly revenue data.',
            ], 500);
        }
    }

    /**
     * Instrument popularity based on students assigned to instruments.
     */
    public function instrumentPopularity()
    {
        try {
            $data = DB::table('student')
                ->join('instrument', 'student.instrument_id', '=', 'instrument.instrument_id')
                ->whereNotNull('student.instrument_id')
                ->select(
                    'instrument.instrument_name',
                    DB::raw('COUNT(student.student_id)::int as count')
                )
                ->groupBy('instrument.instrument_id', 'instrument.instrument_name')
                ->orderByDesc('count')
                ->limit(10)
                ->get()
                ->map(function ($item) {
                    return [
                        'instrument_name' => $item->instrument_name,
                        'count' => (int) $item->count,
                    ];
                });

            return response()->json($data);
        } catch (\Throwable $exception) {
            Log::error('Instrument popularity chart failed.', [
                'message' => $exception->getMessage(),
            ]);

            return response()->json([
                'error' => 'Unable to load instrument popularity data.',
            ], 500);
        }
    }

    /**
     * Top instructors based on unique students in enrollments.
     */
    public function instructorPerformance()
    {
        try {
            $data = DB::table('instructor')
                ->leftJoin('enrollment', 'instructor.instructor_id', '=', 'enrollment.instructor_id')
                ->selectRaw("
                    CONCAT(instructor.first_name, ' ', instructor.last_name) AS instructor_name,
                    COUNT(DISTINCT enrollment.student_id)::int AS total_students
                ")
                ->groupBy('instructor.instructor_id', 'instructor.first_name', 'instructor.last_name')
                ->orderByDesc('total_students')
                ->limit(10)
                ->get();

            return response()->json($data);
        } catch (\Throwable $exception) {
            Log::error('Instructor performance chart failed.', [
                'message' => $exception->getMessage(),
            ]);

            return response()->json([
                'error' => 'Unable to load instructor performance data.',
            ], 500);
        }
    }

    /**
     * Get the ID of the "Paid" payment status safely.
     */
    private function paidStatusId()
    {
        return DB::table('payment_status')
            ->whereRaw('LOWER(status_name) = ?', ['paid'])
            ->value('status_id');
    }
}