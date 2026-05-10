<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

/**
 * ============================================================================
 * LESSON SESSION PACKAGE CONTROLLER
 * app/Http/Controllers/Admin/LessonSessionController.php
 * ============================================================================
 * Manages lesson session packages used by student enrollments.
 *
 * Responsibilities:
 * - Display searchable/filterable lesson packages
 * - Show useful package statistics for admin decision-making
 * - Create, update, delete, and toggle package status
 * - Protect used packages from unsafe price/session/duration changes
 * - Fetch connected enrollments for package usage review
 * ============================================================================
 */
class LessonSessionController extends Controller
{
    /**
     * Display lesson packages with statistics, filtering, sorting, and usage count.
     */
    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(['all', 'active', 'inactive'])],
            'sort_by' => ['nullable', Rule::in([
                'session_count',
                'usage',
                'price_asc',
                'price_desc',
                'newest',
                'oldest',
            ])],
        ]);

        $search = trim($filters['search'] ?? '');
        $status = $filters['status'] ?? 'all';
        $sortBy = $filters['sort_by'] ?? 'session_count';

        $query = DB::table('lesson_session')
            ->leftJoin('enrollment', 'lesson_session.session_id', '=', 'enrollment.session_id')
            ->select(
                'lesson_session.*',
                DB::raw('COUNT(enrollment.enrollment_id) as usage_count'),
                DB::raw('(lesson_session.session_count * lesson_session.duration_minutes) as total_minutes'),
                DB::raw('ROUND((lesson_session.session_count * lesson_session.duration_minutes) / 60.0, 2) as total_hours')
            )
            ->groupBy(
                'lesson_session.session_id',
                'lesson_session.session_count',
                'lesson_session.duration_minutes',
                'lesson_session.price',
                'lesson_session.session_name',
                'lesson_session.description',
                'lesson_session.is_active',
                'lesson_session.created_at',
                'lesson_session.updated_at'
            );

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('lesson_session.session_name', 'ILIKE', "%{$search}%")
                    ->orWhere('lesson_session.description', 'ILIKE', "%{$search}%")
                    ->orWhere(DB::raw('CAST(lesson_session.session_count AS TEXT)'), 'LIKE', "%{$search}%")
                    ->orWhere(DB::raw('CAST(lesson_session.price AS TEXT)'), 'LIKE', "%{$search}%");
            });
        }

        if ($status !== 'all') {
            $query->where('lesson_session.is_active', $status === 'active');
        }

        match ($sortBy) {
            'usage' => $query->orderByRaw('COUNT(enrollment.enrollment_id) DESC'),
            'price_asc' => $query->orderBy('lesson_session.price', 'asc'),
            'price_desc' => $query->orderBy('lesson_session.price', 'desc'),
            'newest' => $query->orderBy('lesson_session.created_at', 'desc'),
            'oldest' => $query->orderBy('lesson_session.created_at', 'asc'),
            default => $query->orderBy('lesson_session.session_count', 'asc'),
        };

        $sessions = $query->paginate(15)->appends($request->query());
        $stats = $this->calculateStatistics();

        return view('admin.lesson-sessions.index', compact('sessions', 'stats'));
    }

    /**
     * Calculate dashboard statistics for the Lesson Packages page.
     */
    private function calculateStatistics(): array
    {
        $total = DB::table('lesson_session')->count();
        $active = DB::table('lesson_session')->where('is_active', true)->count();
        $inactive = DB::table('lesson_session')->where('is_active', false)->count();

        $totalEnrollments = DB::table('enrollment')->whereNotNull('session_id')->count();
        $activeEnrollments = DB::table('enrollment')
            ->whereNotNull('session_id')
            ->where('status', 'active')
            ->count();

        $usedPackages = DB::table('lesson_session')
            ->join('enrollment', 'lesson_session.session_id', '=', 'enrollment.session_id')
            ->distinct('lesson_session.session_id')
            ->count('lesson_session.session_id');

        $unusedPackages = DB::table('lesson_session')
            ->leftJoin('enrollment', 'lesson_session.session_id', '=', 'enrollment.session_id')
            ->whereNull('enrollment.enrollment_id')
            ->count();

        $revenuePotential = DB::table('enrollment')
            ->join('lesson_session', 'enrollment.session_id', '=', 'lesson_session.session_id')
            ->sum('lesson_session.price');

        $averagePrice = DB::table('lesson_session')->avg('price') ?? 0;

        $mostPopular = DB::table('lesson_session')
            ->leftJoin('enrollment', 'lesson_session.session_id', '=', 'enrollment.session_id')
            ->select(
                'lesson_session.session_name',
                'lesson_session.session_count',
                DB::raw('COUNT(enrollment.enrollment_id) as enrollment_count')
            )
            ->groupBy('lesson_session.session_id', 'lesson_session.session_name', 'lesson_session.session_count')
            ->orderByRaw('COUNT(enrollment.enrollment_id) DESC')
            ->first();

        $highestValue = DB::table('lesson_session')
            ->select('session_name', 'session_count', 'price')
            ->orderBy('price', 'desc')
            ->first();

        return [
            'total' => $total,
            'active' => $active,
            'inactive' => $inactive,
            'used' => $usedPackages,
            'unused' => $unusedPackages,
            'total_enrollments' => $totalEnrollments,
            'active_enrollments' => $activeEnrollments,
            'revenue_potential' => $revenuePotential,
            'average_price' => $averagePrice,
            'most_popular_name' => $mostPopular
                ? ($mostPopular->session_name ?: $mostPopular->session_count . ' sessions')
                : 'None yet',
            'most_popular_count' => $mostPopular ? (int) $mostPopular->enrollment_count : 0,
            'highest_value_name' => $highestValue
                ? ($highestValue->session_name ?: $highestValue->session_count . ' sessions')
                : 'None yet',
            'highest_value_price' => $highestValue ? (float) $highestValue->price : 0,
        ];
    }

    /**
     * The create modal is rendered by JavaScript, so this route only confirms availability.
     */
    public function create()
    {
        return response()->json([
            'success' => true,
            'message' => 'Lesson package creation is handled through the admin modal.',
        ]);
    }

    /**
     * Store a newly created lesson package.
     */
    public function store(Request $request)
    {
        $validated = $this->validatePackageData($request);
        $validated = $this->normalizePackageData($validated);

        DB::table('lesson_session')->insert([
            'session_count' => $validated['session_count'],
            'duration_minutes' => $validated['duration_minutes'],
            'price' => $validated['price'],
            'session_name' => $validated['session_name'],
            'description' => $validated['description'] ?? null,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Lesson package created successfully.',
        ]);
    }

    /**
     * Get lesson package data for the edit modal.
     */
    public function edit($session_id)
    {
        $session = $this->findPackageWithUsage((int) $session_id);

        if (!$session) {
            return response()->json([
                'success' => false,
                'message' => 'Lesson package not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'session' => $session,
        ]);
    }

    /**
     * Update a lesson package.
     *
     * Safety rule:
     * - If a package already has enrollments, do not allow changes to
     *   session_count, duration_minutes, or price because those fields affect
     *   package history, payment interpretation, and reports.
     * - Admin may still update name and description.
     */
    public function update(Request $request, $session_id)
    {
        $sessionId = (int) $session_id;
        $session = DB::table('lesson_session')->where('session_id', $sessionId)->first();

        if (!$session) {
            return response()->json([
                'success' => false,
                'message' => 'Lesson package not found.',
            ], 404);
        }

        $validated = $this->validatePackageData($request);
        $validated = $this->normalizePackageData($validated);

        $usageCount = DB::table('enrollment')->where('session_id', $sessionId)->count();

        if ($usageCount > 0 && $this->lockedPackageFieldsChanged($session, $validated)) {
            return response()->json([
                'success' => false,
                'message' => 'This package is already used by enrollments. Session count, duration, and price are locked to protect existing records. You may only update the package name and description.',
            ], 422);
        }

        DB::table('lesson_session')
            ->where('session_id', $sessionId)
            ->update([
                'session_count' => $validated['session_count'],
                'duration_minutes' => $validated['duration_minutes'],
                'price' => $validated['price'],
                'session_name' => $validated['session_name'],
                'description' => $validated['description'] ?? null,
                'updated_at' => now(),
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Lesson package updated successfully.',
        ]);
    }

    /**
     * Toggle active/inactive status.
     */
    public function toggleStatus($session_id)
    {
        $sessionId = (int) $session_id;
        $session = DB::table('lesson_session')->where('session_id', $sessionId)->first();

        if (!$session) {
            return response()->json([
                'success' => false,
                'message' => 'Lesson package not found.',
            ], 404);
        }

        $newStatus = ! (bool) $session->is_active;

        DB::table('lesson_session')
            ->where('session_id', $sessionId)
            ->update([
                'is_active' => $newStatus,
                'updated_at' => now(),
            ]);

        return response()->json([
            'success' => true,
            'message' => $newStatus
                ? 'Lesson package activated successfully.'
                : 'Lesson package deactivated successfully.',
            'is_active' => $newStatus,
        ]);
    }

    /**
     * Delete a lesson package only when it is not connected to enrollments.
     */
    public function destroy($session_id)
    {
        $sessionId = (int) $session_id;
        $session = DB::table('lesson_session')->where('session_id', $sessionId)->first();

        if (!$session) {
            return response()->json([
                'success' => false,
                'message' => 'Lesson package not found.',
            ], 404);
        }

        $usageCount = DB::table('enrollment')->where('session_id', $sessionId)->count();

        if ($usageCount > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete this package because it is used in ' . $usageCount . ' enrollment' . ($usageCount === 1 ? '.' : 's.'),
            ], 422);
        }

        DB::table('lesson_session')->where('session_id', $sessionId)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Lesson package deleted successfully.',
        ]);
    }

    /**
     * Fetch enrollments connected to a selected lesson package.
     */
    public function getEnrollments($session_id)
    {
        $sessionId = (int) $session_id;

        try {
            $session = DB::table('lesson_session')
                ->where('session_id', $sessionId)
                ->first();

            if (!$session) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lesson package not found.',
                ], 404);
            }

            $enrollments = DB::table('enrollment')
                ->join('student', 'enrollment.student_id', '=', 'student.student_id')
                ->join('user_account', 'student.user_id', '=', 'user_account.user_id')
                ->leftJoin('instructor', 'enrollment.instructor_id', '=', 'instructor.instructor_id')
                ->leftJoin('instrument', 'enrollment.instrument_id', '=', 'instrument.instrument_id')
                ->where('enrollment.session_id', $sessionId)
                ->select(
                    'enrollment.enrollment_id',
                    'enrollment.enrollment_date',
                    'enrollment.status',
                    'student.first_name',
                    'student.middle_name',
                    'student.last_name',
                    'user_account.user_email',
                    'instrument.instrument_name',
                    DB::raw("CONCAT(instructor.first_name, ' ', instructor.last_name) as instructor_name")
                )
                ->orderBy('enrollment.enrollment_date', 'desc')
                ->get()
                ->map(function ($enrollment) {
                    $nameParts = array_filter([
                        $enrollment->first_name ?? null,
                        $enrollment->middle_name ?? null,
                        $enrollment->last_name ?? null,
                    ]);

                    $enrollment->student_name = trim(implode(' ', $nameParts));

                    unset($enrollment->first_name, $enrollment->middle_name, $enrollment->last_name);

                    return $enrollment;
                });

            return response()->json([
                'success' => true,
                'session_name' => $session->session_name ?: $session->session_count . ' session package',
                'enrollments' => $enrollments,
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to fetch lesson package enrollments.', [
                'session_id' => $sessionId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch enrollments. Please try again later.',
            ], 500);
        }
    }

    /**
     * Shared validation for create and update.
     */
    private function validatePackageData(Request $request): array
    {
        return $request->validate([
            'session_count' => ['required', 'integer', 'min:1', 'max:100'],
            'duration_minutes' => ['required', 'integer', 'min:15', 'max:300'],
            'price' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'session_name' => ['nullable', 'string', 'max:200'],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);
    }

    /**
     * Normalize package values before saving.
     */
    private function normalizePackageData(array $validated): array
    {
        $validated['session_count'] = (int) $validated['session_count'];
        $validated['duration_minutes'] = (int) $validated['duration_minutes'];
        $validated['price'] = round((float) $validated['price'], 2);

        $sessionName = trim((string) ($validated['session_name'] ?? ''));
        $validated['session_name'] = $sessionName !== ''
            ? $sessionName
            : $validated['session_count'] . ' Session Package';

        $description = trim((string) ($validated['description'] ?? ''));
        $validated['description'] = $description !== '' ? $description : null;

        return $validated;
    }

    /**
     * Fetch a lesson package with its enrollment usage count.
     */
    private function findPackageWithUsage(int $sessionId): ?object
    {
        return DB::table('lesson_session')
            ->leftJoin('enrollment', 'lesson_session.session_id', '=', 'enrollment.session_id')
            ->select(
                'lesson_session.*',
                DB::raw('COUNT(enrollment.enrollment_id) as usage_count')
            )
            ->where('lesson_session.session_id', $sessionId)
            ->groupBy(
                'lesson_session.session_id',
                'lesson_session.session_count',
                'lesson_session.duration_minutes',
                'lesson_session.price',
                'lesson_session.session_name',
                'lesson_session.description',
                'lesson_session.is_active',
                'lesson_session.created_at',
                'lesson_session.updated_at'
            )
            ->first();
    }

    /**
     * Check whether protected package values were changed after usage exists.
     */
    private function lockedPackageFieldsChanged(object $session, array $validated): bool
    {
        return (int) $session->session_count !== (int) $validated['session_count']
            || (int) $session->duration_minutes !== (int) $validated['duration_minutes']
            || (float) $session->price !== (float) $validated['price'];
    }
}
