<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * ============================================================================
 * SPECIALIZATION CONTROLLER - Manage instructor specializations
 * app/Http/Controllers/Admin/SpecializationController.php
 * ============================================================================
 * Handles CRUD operations for specializations (Guitar, Piano, Drums, etc.)
 * Features:
 * - Create/update/delete specializations
 * - Toggle active/inactive status
 * - Usage checking (prevent deletion if instructors assigned)
 * - View assigned instructors
 * ============================================================================
 */
class SpecializationController extends Controller
{
    /**
     * Display specialization management page with statistics
     */
    public function index(Request $request)
    {
        // Build base query - first get specialization with instructor counts as a subquery
        $query = DB::table('specialization')
            ->leftJoin(
                DB::raw('(SELECT specialization_id, COUNT(DISTINCT instructor_id) as instructor_count 
                        FROM instructor_specialization 
                        GROUP BY specialization_id) as spec_counts'),
                'specialization.specialization_id', '=', 'spec_counts.specialization_id'
            )
            ->select(
                'specialization.specialization_id',
                'specialization.specialization_name',
                'specialization.description',
                'specialization.is_active',
                'specialization.created_at',
                'specialization.updated_at',
                DB::raw('COALESCE(spec_counts.instructor_count, 0) as instructor_count')
            );

        // Apply search filter
        if ($request->has('search') && $request->search) {
            $search = trim($request->search);
            $query->where(function($q) use ($search) {
                $q->where('specialization.specialization_name', 'ILIKE', "%{$search}%")
                ->orWhere('specialization.description', 'ILIKE', "%{$search}%");
            });
        }

        // Apply status filter
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('specialization.is_active', $request->status === 'active');
        }

        // Apply sorting
        $sortBy = $request->get('sort_by', 'name');
        switch ($sortBy) {
            case 'instructors':
                $query->orderByRaw('COALESCE(spec_counts.instructor_count, 0) DESC');
                break;
            case 'newest':
                $query->orderBy('specialization.created_at', 'DESC');
                break;
            case 'oldest':
                $query->orderBy('specialization.created_at', 'ASC');
                break;
            default: // 'name'
                $query->orderBy('specialization.specialization_name', 'ASC');
        }

        $specializations = $query->paginate(15);

        // Calculate statistics (separate queries - more efficient)
        $stats = [
            'total' => DB::table('specialization')->count(),
            'active' => DB::table('specialization')->whereRaw('is_active = TRUE')->count(),
            'inactive' => DB::table('specialization')->whereRaw('is_active = FALSE')->count(),
            'most_used' => DB::table('instructor_specialization')
                ->select('specialization_id', DB::raw('COUNT(*) as count'))
                ->groupBy('specialization_id')
                ->orderByDesc('count')
                ->first()
        ];

        // Get most used specialization name if exists
        if ($stats['most_used']) {
            $mostUsedSpec = DB::table('specialization')
                ->where('specialization_id', $stats['most_used']->specialization_id)
                ->first();
            $stats['most_used_name'] = $mostUsedSpec ? $mostUsedSpec->specialization_name : 'N/A';
            $stats['most_used_count'] = $stats['most_used']->count;
        } else {
            $stats['most_used_name'] = 'N/A';
            $stats['most_used_count'] = 0;
        }

        return view('admin.specializations.index', compact('specializations', 'stats'));
    }

    /**
     * Store a new specialization
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'specialization_name' => 'required|string|max:100|unique:specialization,specialization_name',
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
            $name = trim($request->specialization_name);
            $description = $request->description ? trim($request->description) : null;

            $specializationId = DB::table('specialization')->insertGetId([
            'specialization_name' => $name,
            'description' => $description,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now()
        ], 'specialization_id');  // <-- Specify the primary key column name

            return response()->json([
                'success' => true,
                'message' => 'Specialization created successfully',
                'specialization_id' => $specializationId
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create specialization: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get specialization details
     */
    public function show($id)
    {
        $specialization = DB::table('specialization')
            ->leftJoin(
                DB::raw('(SELECT specialization_id, COUNT(DISTINCT instructor_id) as instructor_count 
                        FROM instructor_specialization 
                        GROUP BY specialization_id) as spec_counts'),
                'specialization.specialization_id', '=', 'spec_counts.specialization_id'
            )
            ->select(
                'specialization.specialization_id',
                'specialization.specialization_name',
                'specialization.description',
                'specialization.is_active',
                'specialization.created_at',
                'specialization.updated_at',
                DB::raw('COALESCE(spec_counts.instructor_count, 0) as instructor_count')
            )
            ->where('specialization.specialization_id', $id)
            ->first();

        if (!$specialization) {
            return response()->json([
                'success' => false,
                'message' => 'Specialization not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'specialization' => $specialization
        ]);
    }

    /**
     * Update specialization
     */
    public function update(Request $request, $id)
    {
        // Check if specialization exists
        $existing = DB::table('specialization')->where('specialization_id', $id)->first();
        
        if (!$existing) {
            return response()->json([
                'success' => false,
                'message' => 'Specialization not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'specialization_name' => 'required|string|max:100|unique:specialization,specialization_name,' . $id . ',specialization_id',
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
            $name = trim($request->specialization_name);
            $description = $request->description ? trim($request->description) : null;

            DB::table('specialization')
                ->where('specialization_id', $id)
                ->update([
                    'specialization_name' => $name,
                    'description' => $description,
                    'updated_at' => now()
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Specialization updated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update specialization: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete specialization (with usage check)
     */
    public function destroy($id)
    {
        try {
            // Check if any instructors are using this specialization
            $usageCount = DB::table('instructor_specialization')
                ->where('specialization_id', $id)
                ->count();

            if ($usageCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete: ' . $usageCount . ' instructor(s) are using this specialization',
                    'usage_count' => $usageCount
                ], 409); // 409 Conflict
            }

            $deleted = DB::table('specialization')
                ->where('specialization_id', $id)
                ->delete();

            if (!$deleted) {
                return response()->json([
                    'success' => false,
                    'message' => 'Specialization not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Specialization deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete specialization: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle specialization active status
     */
    public function toggleStatus($id)
    {
        try {
            $specialization = DB::table('specialization')
                ->where('specialization_id', $id)
                ->first();

            if (!$specialization) {
                return response()->json([
                    'success' => false,
                    'message' => 'Specialization not found'
                ], 404);
            }

            $newStatus = !$specialization->is_active;

            DB::table('specialization')
                ->where('specialization_id', $id)
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
     * Get list of instructors assigned to a specialization
     */
    public function getInstructors($id)
    {
        try {
            $instructors = DB::table('instructor_specialization as is')
                ->join('instructor as i', 'is.instructor_id', '=', 'i.instructor_id')
                ->where('is.specialization_id', $id)
                ->select(
                    'i.instructor_id',
                    DB::raw("CONCAT(i.first_name, ' ', i.last_name) as full_name"),
                    'i.email',
                    'i.phone',
                    'i.employee_id',
                    'is.is_primary',
                    'i.is_active'
                )
                ->orderBy('is.is_primary', 'DESC')
                ->orderBy('i.last_name', 'ASC')
                ->get();

            return response()->json([
                'success' => true,
                'instructors' => $instructors
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch instructors: ' . $e->getMessage()
            ], 500);
        }
    }
}