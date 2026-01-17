<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * ============================================================================
 * LESSON SESSION PACKAGE CONTROLLER
 * app/Http/Controllers/Admin/LessonSessionController.php
 * ============================================================================
 * Manages lesson session packages (5, 10, 20 sessions)
 * - Full CRUD operations
 * - Usage tracking (counts enrollments using each package)
 * - Active/inactive toggle
 * - Prevents deletion if package is used in any enrollment
 * ============================================================================
 */
class LessonSessionController extends Controller
{
    /**
     * Display a listing of lesson session packages with statistics and filters
     * 
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Base query with usage count from enrollments
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

        // Search filter (by session name or description)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('lesson_session.session_name', 'ILIKE', "%{$search}%")
                  ->orWhere('lesson_session.description', 'ILIKE', "%{$search}%")
                  ->orWhere(DB::raw('CAST(lesson_session.session_count AS TEXT)'), 'LIKE', "%{$search}%");
            });
        }

        // Status filter (active/inactive)
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('lesson_session.is_active', $request->status === 'active');
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'session_count');
        switch ($sortBy) {
            case 'usage':
                $query->orderByRaw('COUNT(enrollment.enrollment_id) DESC');
                break;
            case 'price_asc':
                $query->orderBy('lesson_session.price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('lesson_session.price', 'desc');
                break;
            case 'newest':
                $query->orderBy('lesson_session.created_at', 'desc');
                break;
            case 'oldest':
                $query->orderBy('lesson_session.created_at', 'asc');
                break;
            default: // session_count
                $query->orderBy('lesson_session.session_count', 'asc');
        }

        // Paginate results
        $sessions = $query->paginate(15)->appends($request->query());

        // Calculate statistics
        $stats = $this->calculateStatistics();

        return view('admin.lesson-sessions.index', compact('sessions', 'stats'));
    }

    /**
     * Calculate statistics for dashboard cards
     * 
     * @return array
     */
    private function calculateStatistics()
    {
        // Total, active, and inactive counts
        $total = DB::table('lesson_session')->count();
        $active = DB::table('lesson_session')->whereRaw('is_active = TRUE')->count();
        $inactive = $total - $active;

        // Most popular package (by enrollment count)
        $mostPopular = DB::table('lesson_session')
            ->leftJoin('enrollment', 'lesson_session.session_id', '=', 'enrollment.session_id')
            ->select(
                'lesson_session.session_count',
                'lesson_session.session_name',
                DB::raw('COUNT(enrollment.enrollment_id) as enrollment_count')
            )
            ->groupBy('lesson_session.session_count', 'lesson_session.session_name')
            ->orderByRaw('COUNT(enrollment.enrollment_id) DESC')
            ->first();

        return [
            'total' => $total,
            'active' => $active,
            'inactive' => $inactive,
            'most_popular_name' => $mostPopular ? "{$mostPopular->session_count} sessions" : 'None yet',
            'most_popular_count' => $mostPopular ? $mostPopular->enrollment_count : 0,
        ];
    }

    /**
     * Show the form for creating a new lesson session package
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function create()
    {
        return response()->json([
            'success' => true,
            'html' => view('admin.lesson-sessions.partials.form-modal', [
                'mode' => 'create',
                'session' => null
            ])->render()
        ]);
    }

    /**
     * Store a newly created lesson session package
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Validate input
        $validated = $request->validate([
            'session_count' => 'required|integer|min:1|max:100',
            'duration_minutes' => 'required|integer|min:15|max:300',
            'price' => 'required|numeric|min:0|max:999999.99',
            'session_name' => 'nullable|string|max:200',
            'description' => 'nullable|string',
        ]);

        // Auto-generate session name if not provided
        if (empty($validated['session_name'])) {
            $validated['session_name'] = $validated['session_count'] . ' session package';
        }

        // Trim whitespace
        $validated['session_name'] = trim($validated['session_name']);
        if (isset($validated['description'])) {
            $validated['description'] = trim($validated['description']);
        }

        // Insert into database
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
            'message' => 'Lesson package created successfully'
        ]);
    }

    /**
     * Get lesson session package data for editing
     * Returns JSON data only (no view rendering - modal HTML generated client-side)
     * 
     * @param int $session_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function edit($session_id)
    {
        // Fetch session package with usage count
        $session = DB::table('lesson_session')
            ->leftJoin('enrollment', 'lesson_session.session_id', '=', 'enrollment.session_id')
            ->select(
                'lesson_session.*',
                DB::raw('COUNT(enrollment.enrollment_id) as usage_count')
            )
            ->where('lesson_session.session_id', $session_id)
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

        if (!$session) {
            return response()->json([
                'success' => false,
                'message' => 'Lesson package not found'
            ], 404);
        }

        // Return session data as JSON (JavaScript will generate the modal HTML)
        return response()->json([
            'success' => true,
            'session' => $session
        ]);
    }

    /**
     * Update the specified lesson session package
     * 
     * @param Request $request
     * @param int $session_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $session_id)
    {
        // Check if package exists
        $exists = DB::table('lesson_session')->where('session_id', $session_id)->exists();
        if (!$exists) {
            return response()->json([
                'success' => false,
                'message' => 'Lesson package not found'
            ], 404);
        }

        // Validate input
        $validated = $request->validate([
            'session_count' => 'required|integer|min:1|max:100',
            'duration_minutes' => 'required|integer|min:15|max:300',
            'price' => 'required|numeric|min:0|max:999999.99',
            'session_name' => 'nullable|string|max:200',
            'description' => 'nullable|string',
        ]);

        // Auto-generate session name if not provided
        if (empty($validated['session_name'])) {
            $validated['session_name'] = $validated['session_count'] . ' session package';
        }

        // Trim whitespace
        $validated['session_name'] = trim($validated['session_name']);
        if (isset($validated['description'])) {
            $validated['description'] = trim($validated['description']);
        }

        // Update in database
        DB::table('lesson_session')
            ->where('session_id', $session_id)
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
            'message' => 'Lesson package updated successfully'
        ]);
    }

    /**
     * Toggle active status of a lesson session package
     * 
     * @param int $session_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function toggleStatus($session_id)
    {
        // Fetch current status
        $session = DB::table('lesson_session')
            ->where('session_id', $session_id)
            ->first();

        if (!$session) {
            return response()->json([
                'success' => false,
                'message' => 'Lesson package not found'
            ], 404);
        }

        // Toggle status
        $newStatus = !$session->is_active;
        DB::table('lesson_session')
            ->where('session_id', $session_id)
            ->update([
                'is_active' => $newStatus,
                'updated_at' => now()
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully',
            'is_active' => $newStatus
        ]);
    }

    /**
     * Remove the specified lesson session package
     * Prevents deletion if package is used in any enrollment
     * 
     * @param int $session_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($session_id)
    {
        // Check if package exists
        $session = DB::table('lesson_session')->where('session_id', $session_id)->first();
        if (!$session) {
            return response()->json([
                'success' => false,
                'message' => 'Lesson package not found'
            ], 404);
        }

        // Check usage count
        $usageCount = DB::table('enrollment')
            ->where('session_id', $session_id)
            ->count();

        if ($usageCount > 0) {
            return response()->json([
                'success' => false,
                'message' => "Cannot delete — used in {$usageCount} enrollment" . ($usageCount > 1 ? 's' : '')
            ], 422);
        }

        // Delete package
        DB::table('lesson_session')->where('session_id', $session_id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Lesson package deleted successfully'
        ]);
    }
}