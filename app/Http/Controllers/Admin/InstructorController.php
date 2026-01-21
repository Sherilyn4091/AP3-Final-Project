<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * ============================================================================
 * INSTRUCTOR MANAGEMENT CONTROLLER
 * ============================================================================
 * Handles instructor operations including:
 * - List with filters and statistics
 * - View details with performance metrics
 * - Manage specializations
 * - Update availability
 * - Performance reports
 * ============================================================================
 */
class InstructorController extends Controller
{
    /**
     * Display list of instructors with filters and statistics
     */
    public function index(Request $request)
    {
        // Get all specializations for filter dropdown
        $specializations = DB::table('specialization')
            ->whereRaw('is_active = TRUE')
            ->orderBy('specialization_name')
            ->get();

        // Base query with active student count
        $query = DB::table('instructor as i')
            ->leftJoin('user_account as u', 'i.user_id', '=', 'u.user_id')
            ->leftJoin('enrollment as e', function($join) {
                $join->on('i.instructor_id', '=', 'e.instructor_id')
                     ->where('e.status', '=', 'active');
            })
            ->leftJoin('instructor_specialization as isp', function($join) {
                $join->on('i.instructor_id', '=', 'isp.instructor_id')
                    ->whereRaw('isp.is_primary = TRUE');
            })
            ->leftJoin('specialization as sp', 'isp.specialization_id', '=', 'sp.specialization_id')
            ->select(
                'i.*',
                'u.user_email',
                DB::raw('COUNT(DISTINCT e.enrollment_id) as active_students'),
                DB::raw('sp.specialization_name as primary_specialization'),
                DB::raw('(SELECT COUNT(*) FROM instructor_specialization WHERE instructor_id = i.instructor_id) as specialization_count')
            )
            ->groupBy(
                'i.instructor_id',
                'i.user_id',
                'i.first_name',
                'i.middle_name',
                'i.last_name',
                'i.suffix',
                'i.phone',
                'i.email',
                'i.address_line1',
                'i.address_line2',
                'i.city',
                'i.province',
                'i.postal_code',
                'i.country',
                'i.date_of_birth',
                'i.gender',
                'i.nationality',
                'i.emergency_contact_name',
                'i.emergency_contact_relationship',
                'i.emergency_contact_phone',
                'i.employee_id',
                'i.hire_date',
                'i.employment_status',
                'i.contract_type',
                'i.hourly_rate',
                'i.monthly_salary',
                'i.education_level',
                'i.music_degree',
                'i.certifications',
                'i.years_of_experience',
                'i.teaching_style',
                'i.bio',
                'i.languages_spoken',
                'i.is_available',
                'i.available_days',
                'i.preferred_time_slots',
                'i.max_students_per_day',
                'i.total_students_taught',
                'i.average_rating',
                'i.is_active',
                'i.notes',
                'i.created_at',
                'i.updated_at',
                'u.user_email',
                'sp.specialization_name'
            );

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('i.first_name', 'ILIKE', "%{$search}%")
                  ->orWhere('i.last_name', 'ILIKE', "%{$search}%")
                  ->orWhere('i.email', 'ILIKE', "%{$search}%")
                  ->orWhere('i.employee_id', 'ILIKE', "%{$search}%");
            });
        }

        if ($request->filled('specialization') && $request->specialization !== 'all') {
            $query->whereExists(function($q) use ($request) {
                $q->select(DB::raw(1))
                  ->from('instructor_specialization')
                  ->whereColumn('instructor_specialization.instructor_id', 'i.instructor_id')
                  ->where('instructor_specialization.specialization_id', $request->specialization);
            });
        }

        if ($request->filled('availability') && $request->availability !== 'all') {
            $isAvailable = $request->availability === 'available';
            $query->whereRaw("i.is_available = " . ($isAvailable ? 'TRUE' : 'FALSE'));
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $isActive = $request->status === 'active';
            $query->whereRaw("i.is_active = " . ($isActive ? 'TRUE' : 'FALSE'));
        }

        if ($request->filled('rating') && $request->rating !== 'all') {
            $minRating = (float) $request->rating;
            $query->where('i.average_rating', '>=', $minRating);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'name');
        switch ($sortBy) {
            case 'rating':
                $query->orderBy('i.average_rating', 'DESC');
                break;
            case 'students':
                $query->orderBy('active_students', 'DESC');
                break;
            case 'experience':
                $query->orderBy('i.years_of_experience', 'DESC');
                break;
            default:
                $query->orderBy('i.last_name')->orderBy('i.first_name');
        }

        // Paginate results
        $instructors = $query->paginate(15)->withQueryString();

        return view('admin.instructors.index', compact('instructors', 'specializations'));
    }

    /**
     * Display instructor details with relationships
     */
    public function show($id)
    {
        try {
            // Get instructor details
            $instructor = DB::table('instructor')
                ->where('instructor_id', $id)
                ->first();

            if (!$instructor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Instructor not found.'
                ], 404);
            }

            // Get specializations
            $specializations = DB::table('instructor_specialization as isp')
                ->join('specialization as s', 'isp.specialization_id', '=', 's.specialization_id')
                ->where('isp.instructor_id', $id)
                ->select('s.*', 'isp.is_primary')
                ->orderBy('isp.is_primary', 'DESC')
                ->orderBy('s.specialization_name')
                ->get();

            // Get current assignments
            $currentAssignments = DB::table('enrollment as e')
                ->join('student as s', 'e.student_id', '=', 's.student_id')
                ->leftJoin('instrument as inst', 's.instrument_id', '=', 'inst.instrument_id')
                ->where('e.instructor_id', $id)
                ->where('e.status', 'active')
                ->select(
                    's.first_name',
                    's.last_name',
                    'inst.instrument_name',
                    'e.remaining_sessions'
                )
                ->get();

            // Get performance metrics
            $metrics = $this->calculateMetrics($id);

            // Get week schedule
            $weekSchedule = DB::table('schedule')
                ->where('instructor_id', $id)
                ->whereBetween('schedule_date', [
                    now()->startOfWeek(),
                    now()->endOfWeek()
                ])
                ->where('status', '!=', 'cancelled')
                ->orderBy('schedule_date')
                ->orderBy('start_time')
                ->get();

            return response()->json([
                'success' => true,
                'instructor' => $instructor,
                'specializations' => $specializations,
                'currentAssignments' => $currentAssignments,
                'metrics' => $metrics,
                'weekSchedule' => $weekSchedule
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch instructor: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Assign specialization to instructor
     */
    public function assignSpecialization(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'specialization_id' => 'required|exists:specialization,specialization_id',
            'is_primary' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Check if already assigned
            $exists = DB::table('instructor_specialization')
                ->where('instructor_id', $id)
                ->where('specialization_id', $request->specialization_id)
                ->exists();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'This specialization is already assigned.'
                ], 409);
            }

            $isPrimary = filter_var($request->is_primary, FILTER_VALIDATE_BOOLEAN);

            // If setting as primary, remove primary from others
            if ($isPrimary) {
                DB::statement('UPDATE instructor_specialization SET is_primary = FALSE WHERE instructor_id = ?', [$id]);
            }

            // Insert new specialization using raw SQL for boolean
            DB::statement(
                'INSERT INTO instructor_specialization (instructor_id, specialization_id, is_primary, created_at, updated_at) 
                VALUES (?, ?, ?, NOW(), NOW())',
                [$id, $request->specialization_id, $isPrimary ? 'TRUE' : 'FALSE']
            );

            return response()->json([
                'success' => true,
                'message' => 'Specialization assigned successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to assign specialization: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove specialization from instructor
     */
    public function removeSpecialization($id, $specializationId)
    {
        try {
            $deleted = DB::table('instructor_specialization')
                ->where('instructor_id', $id)
                ->where('specialization_id', $specializationId)
                ->delete();

            if (!$deleted) {
                return response()->json([
                    'success' => false,
                    'message' => 'Specialization not found.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Specialization removed successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove specialization: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Set specialization as primary
     */
    public function setPrimarySpecialization($id, $specializationId)
    {
        try {
            // Verify specialization is assigned
            $exists = DB::table('instructor_specialization')
                ->where('instructor_id', $id)
                ->where('specialization_id', $specializationId)
                ->exists();

            if (!$exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Specialization not assigned to this instructor.'
                ], 404);
            }

            // Remove primary from all (using raw SQL for boolean)
            DB::statement('UPDATE instructor_specialization SET is_primary = FALSE WHERE instructor_id = ?', [$id]);

            // Set new primary (using raw SQL for boolean)
            DB::statement(
                'UPDATE instructor_specialization SET is_primary = TRUE, updated_at = NOW() 
                WHERE instructor_id = ? AND specialization_id = ?',
                [$id, $specializationId]
            );

            return response()->json([
                'success' => true,
                'message' => 'Primary specialization updated successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update primary: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update instructor availability
     */
    public function updateAvailability(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'is_available' => 'required|boolean',
            'available_days' => 'nullable|string|max:255',
            'preferred_time_slots' => 'nullable|string|max:255',
            'max_students_per_day' => 'nullable|integer|min:1|max:20'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $isAvailable = filter_var($request->is_available, FILTER_VALIDATE_BOOLEAN);

            // Use raw SQL for boolean update
            DB::statement(
                'UPDATE instructor 
                SET is_available = ?, available_days = ?, preferred_time_slots = ?, max_students_per_day = ?, updated_at = NOW()
                WHERE instructor_id = ?',
                [
                    $isAvailable ? 'TRUE' : 'FALSE',
                    $request->available_days ? trim($request->available_days) : null,
                    $request->preferred_time_slots ? trim($request->preferred_time_slots) : null,
                    $request->max_students_per_day,
                    $id
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Availability updated successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update availability: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get instructor performance report
     */
    public function performanceReport($id)
    {
        try {
            $metrics = $this->calculateMetrics($id);

            return response()->json($metrics);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate report: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update instructor details
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'nullable|regex:/^\d{11}$/',
            'email' => 'nullable|email|max:255',
            'is_available' => 'boolean',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $updateData = array_filter([
                'phone' => $request->phone,
                'email' => $request->email,
                'is_available' => $request->is_available,
                'is_active' => $request->is_active,
                'updated_at' => now()
            ], function($value) {
                return !is_null($value);
            });

            DB::table('instructor')
                ->where('instructor_id', $id)
                ->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Instructor updated successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update instructor: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate instructor performance metrics
     */
    private function calculateMetrics($instructorId)
    {
        // Active students count
        $activeStudents = DB::table('enrollment')
            ->where('instructor_id', $instructorId)
            ->where('status', 'active')
            ->count();

        // Total lessons conducted
        $totalLessons = DB::table('schedule')
            ->where('instructor_id', $instructorId)
            ->where('status', 'completed')
            ->count();

        // Attendance rate (completed vs scheduled)
        $scheduledLessons = DB::table('schedule')
            ->where('instructor_id', $instructorId)
            ->whereIn('status', ['scheduled', 'completed'])
            ->count();

        $attendanceRate = $scheduledLessons > 0 
            ? round(($totalLessons / $scheduledLessons) * 100) 
            : 0;

        // Average student rating from progress table
        $avgStudentRating = DB::table('progress')
            ->where('instructor_id', $instructorId)
            ->whereNotNull('performance_rating')
            ->avg('performance_rating');

        // Completion rate (completed enrollments vs total)
        $totalEnrollments = DB::table('enrollment')
            ->where('instructor_id', $instructorId)
            ->count();

        $completedEnrollments = DB::table('enrollment')
            ->where('instructor_id', $instructorId)
            ->where('status', 'completed')
            ->count();

        $completionRate = $totalEnrollments > 0 
            ? round(($completedEnrollments / $totalEnrollments) * 100) 
            : 0;

        // Revenue generated (from completed enrollments)
        $revenueGenerated = DB::table('enrollment')
            ->where('instructor_id', $instructorId)
            ->where('status', 'completed')
            ->sum('total_amount');

        return [
            'active_students' => $activeStudents,
            'total_lessons' => $totalLessons,
            'attendance_rate' => $attendanceRate,
            'avg_student_rating' => $avgStudentRating ? round($avgStudentRating, 2) : null,
            'completion_rate' => $completionRate,
            'student_retention_rate' => $completionRate, // Same as completion for now
            'revenue_generated' => $revenueGenerated ?? 0
        ];
    }
}