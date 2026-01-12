<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * ============================================================================
 * GENRE CONTROLLER - Manage music genres
 * app/Http/Controllers/Admin/GenreController.php
 * ============================================================================
 * Handles CRUD operations for genres (Rock, Pop, Jazz, Classical, etc.)
 * Features:
 * - Create/update/delete genres
 * - Toggle active/inactive status
 * - Usage checking (prevent deletion if students prefer this genre)
 * - View students who prefer this genre
 * ============================================================================
 */
class GenreController extends Controller
{
    /**
     * Display genre management page with statistics
     */
    public function index(Request $request)
    {
        // Build base query - first get genre with student counts as a subquery
        $query = DB::table('genre')
            ->leftJoin(
                DB::raw('(SELECT preferred_genre_id, COUNT(DISTINCT student_id) as student_count 
                          FROM student 
                          WHERE preferred_genre_id IS NOT NULL
                          GROUP BY preferred_genre_id) as genre_counts'),
                'genre.genre_id', '=', 'genre_counts.preferred_genre_id'
            )
            ->select(
                'genre.genre_id',
                'genre.genre_name',
                'genre.description',
                'genre.is_active',
                'genre.created_at',
                'genre.updated_at',
                DB::raw('COALESCE(genre_counts.student_count, 0) as student_count')
            );

        // Apply search filter
        if ($request->has('search') && $request->search) {
            $search = trim($request->search);
            $query->where(function($q) use ($search) {
                $q->where('genre.genre_name', 'ILIKE', "%{$search}%")
                  ->orWhere('genre.description', 'ILIKE', "%{$search}%");
            });
        }

        // Apply status filter
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('genre.is_active', $request->status === 'active');
        }

        // Apply sorting
        $sortBy = $request->get('sort_by', 'name');
        switch ($sortBy) {
            case 'students':
                $query->orderByRaw('COALESCE(genre_counts.student_count, 0) DESC');
                break;
            case 'newest':
                $query->orderBy('genre.created_at', 'DESC');
                break;
            case 'oldest':
                $query->orderBy('genre.created_at', 'ASC');
                break;
            default: // 'name'
                $query->orderBy('genre.genre_name', 'ASC');
        }

        $genres = $query->paginate(15);

        // Calculate statistics (separate queries - more efficient)
        $stats = [
            'total' => DB::table('genre')->count(),
            'active' => DB::table('genre')->where('is_active', true)->count(),
            'inactive' => DB::table('genre')->where('is_active', false)->count(),
            'most_used' => DB::table('student')
                ->select('preferred_genre_id', DB::raw('COUNT(*) as count'))
                ->whereNotNull('preferred_genre_id')
                ->groupBy('preferred_genre_id')
                ->orderByDesc('count')
                ->first()
        ];

        // Get most popular genre name if exists
        if ($stats['most_used']) {
            $mostUsedGenre = DB::table('genre')
                ->where('genre_id', $stats['most_used']->preferred_genre_id)
                ->first();
            $stats['most_used_name'] = $mostUsedGenre ? $mostUsedGenre->genre_name : 'N/A';
            $stats['most_used_count'] = $stats['most_used']->count;
        } else {
            $stats['most_used_name'] = 'N/A';
            $stats['most_used_count'] = 0;
        }

        return view('admin.genres.index', compact('genres', 'stats'));
    }

    /**
     * Store a new genre
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'genre_name' => 'required|string|max:100|unique:genre,genre_name',
            'description' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Trim whitespace and capitalize properly
            $name = trim($request->genre_name);
            $description = $request->description ? trim($request->description) : null;

            $genreId = DB::table('genre')->insertGetId([
                'genre_name' => $name,
                'description' => $description,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ], 'genre_id');

            return response()->json([
                'success' => true,
                'message' => 'Genre created successfully',
                'genre_id' => $genreId
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create genre: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get genre details
     */
    public function show($id)
    {
        $genre = DB::table('genre')
            ->leftJoin(
                DB::raw('(SELECT preferred_genre_id, COUNT(DISTINCT student_id) as student_count 
                          FROM student 
                          WHERE preferred_genre_id IS NOT NULL
                          GROUP BY preferred_genre_id) as genre_counts'),
                'genre.genre_id', '=', 'genre_counts.preferred_genre_id'
            )
            ->select(
                'genre.genre_id',
                'genre.genre_name',
                'genre.description',
                'genre.is_active',
                'genre.created_at',
                'genre.updated_at',
                DB::raw('COALESCE(genre_counts.student_count, 0) as student_count')
            )
            ->where('genre.genre_id', $id)
            ->first();

        if (!$genre) {
            return response()->json([
                'success' => false,
                'message' => 'Genre not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'genre' => $genre
        ]);
    }

    /**
     * Update genre
     */
    public function update(Request $request, $id)
    {
        // Check if genre exists
        $existing = DB::table('genre')->where('genre_id', $id)->first();
        
        if (!$existing) {
            return response()->json([
                'success' => false,
                'message' => 'Genre not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'genre_name' => 'required|string|max:100|unique:genre,genre_name,' . $id . ',genre_id',
            'description' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Trim whitespace
            $name = trim($request->genre_name);
            $description = $request->description ? trim($request->description) : null;

            DB::table('genre')
                ->where('genre_id', $id)
                ->update([
                    'genre_name' => $name,
                    'description' => $description,
                    'updated_at' => now()
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Genre updated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update genre: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete genre (with usage check - HARD BLOCK)
     */
    public function destroy($id)
    {
        try {
            // Check if any students prefer this genre
            $usageCount = DB::table('student')
                ->where('preferred_genre_id', $id)
                ->count();

            if ($usageCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete — ' . $usageCount . ' student(s) prefer this genre',
                    'usage_count' => $usageCount
                ], 409); // 409 Conflict
            }

            $deleted = DB::table('genre')
                ->where('genre_id', $id)
                ->delete();

            if (!$deleted) {
                return response()->json([
                    'success' => false,
                    'message' => 'Genre not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Genre deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete genre: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle genre active status
     */
    public function toggleStatus($id)
    {
        try {
            $genre = DB::table('genre')
                ->where('genre_id', $id)
                ->first();

            if (!$genre) {
                return response()->json([
                    'success' => false,
                    'message' => 'Genre not found'
                ], 404);
            }

            $newStatus = !$genre->is_active;

            DB::table('genre')
                ->where('genre_id', $id)
                ->update([
                    'is_active' => $newStatus,
                    'updated_at' => now()
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Status updated successfully',
                'new_status' => $newStatus
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to toggle status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get list of students who prefer this genre
     */
    public function getStudents($id)
    {
        try {
            $students = DB::table('student')
                ->where('preferred_genre_id', $id)
                ->select(
                    'student_id',
                    DB::raw("CONCAT(first_name, ' ', last_name) as full_name"),
                    'email',
                    'phone',
                    'enrollment_date',
                    'is_active'
                )
                ->orderBy('last_name', 'ASC')
                ->orderBy('first_name', 'ASC')
                ->get();

            return response()->json([
                'success' => true,
                'students' => $students
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch students: ' . $e->getMessage()
            ], 500);
        }
    }
}