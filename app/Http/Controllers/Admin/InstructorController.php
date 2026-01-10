<?php
/**
 * ============================================================================
 * INSTRUCTOR MANAGEMENT CONTROLLER
 * app/Http/Controllers/Admin/InstructorController.php
 * ============================================================================
 * Handles all instructor management operations including:
 * - List view with filters (specialization, availability, status, rating)
 * - Instructor details view
 * - Specialization assignment/removal
 * - Availability management
 * - Performance metrics and reports
 * ============================================================================
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class InstructorController extends Controller
{
    /**
     * Display instructor management page with filters and pagination.
     * 
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Base query with specialization count
        $query = DB::table('instructor as i')
            ->leftJoin('user_account as ua', 'i.user_id', '=', 'ua.user_id')
            ->select(
                'i.instructor_id',
                'i.user_id',
                'i.first_name',
                'i.last_name',
                'i.employee_id',
                'i.email',
                'i.phone',
                'i.years_of_experience',
                'i.is_available',
                'i.is_active',
                'i.total_students_taught',
                'i.average_rating',
                'i.created_at',
                'ua.last_login',
                // Count specializations
                DB::raw('(SELECT COUNT(*) FROM instructor_specialization WHERE instructor_id = i.instructor_id) as specialization_count'),
                // Get primary specialization name
                DB::raw("(SELECT s.specialization_name 
                         FROM instructor_specialization isp 
                         JOIN specialization s ON isp.specialization_id = s.specialization_id 
                         WHERE isp.instructor_id = i.instructor_id AND isp.is_primary = true 
                         LIMIT 1) as primary_specialization"),
                // Count active students
                DB::raw('(SELECT COUNT(DISTINCT student_id) 
                         FROM enrollment 
                         WHERE instructor_id = i.instructor_id AND status = \'active\') as active_students')
            );

        // Filter by specialization
        if ($request->filled('specialization') && $request->specialization !== 'all') {
            $query->whereExists(function($q) use ($request) {
                $q->select(DB::raw(1))
                  ->from('instructor_specialization as isp')
                  ->whereRaw('isp.instructor_id = i.instructor_id')
                  ->where('isp.specialization_id', $request->specialization);
            });
        }

        // Filter by availability
        if ($request->filled('availability') && $request->availability !== 'all') {
            $isAvailable = $request->availability === 'available';
            $query->where('i.is_available', $isAvailable);
        }

        // Filter by status
        if ($request->filled('status') && $request->status !== 'all') {
            $isActive = $request->status === 'active';
            $query->where('i.is_active', $isActive);
        }

        // Filter by rating
        if ($request->filled('rating') && $request->rating !== 'all') {
            $minRating = (int) $request->rating;
            $query->where('i.average_rating', '>=', $minRating)
                  ->whereNotNull('i.average_rating');
        }

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where(DB::raw("CONCAT(i.first_name, ' ', i.last_name)"), 'ILIKE', "%{$search}%")
                  ->orWhere('i.employee_id', 'ILIKE', "%{$search}%")
                  ->orWhere('i.email', 'ILIKE', "%{$search}%")
                  ->orWhere(DB::raw('CAST(i.instructor_id AS TEXT)'), 'ILIKE', "%{$search}%");
            });
        }

        // Date range filter
        $dateColumn = $request->input('date_filter_by', 'created_at') === 'last_login' ? 'ua.last_login' : 'i.created_at';
        if ($request->filled('date_from')) {
            $query->whereDate($dateColumn, '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate($dateColumn, '<=', $request->date_to);
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'name');
        $sortOrder = $request->input('sort_order', 'asc');
        
        switch ($sortBy) {
            case 'rating':
                $query->orderByRaw('i.average_rating ' . $sortOrder . ' NULLS LAST');
                break;
            case 'students':
                $query->orderByRaw('(SELECT COUNT(DISTINCT student_id) FROM enrollment WHERE instructor_id = i.instructor_id AND status = \'active\') ' . $sortOrder);
                break;
            case 'experience':
                $query->orderBy('i.years_of_experience', $sortOrder);
                break;
            default:
                $query->orderBy(DB::raw("CONCAT(i.first_name, ' ', i.last_name)"), $sortOrder);
        }

        $instructors = $query->paginate(20);

        // Get all specializations for filter dropdown
        $specializations = DB::table('specialization')
            ->where('is_active', true)
            ->orderBy('specialization_name')
            ->get();

        return view('admin.instructors.index', compact('instructors', 'specializations'));
    }

    /**
     * Get instructor details for modal view.
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        // Get instructor basic info
        $instructor = DB::table('instructor')->where('instructor_id', $id)->first();
        
        if (!$instructor) {
            return response()->json(['error' => 'Instructor not found'], 404);
        }

        // Get specializations with is_primary flag
        $specializations = DB::table('instructor_specialization as isp')
            ->join('specialization as s', 'isp.specialization_id', '=', 's.specialization_id')
            ->where('isp.instructor_id', $id)
            ->select('s.specialization_id', 's.specialization_name', 'isp.is_primary')
            ->get();

        // Get performance metrics
        $metrics = [
            'active_students' => DB::table('enrollment')
                ->where('instructor_id', $id)
                ->where('status', 'active')
                ->distinct('student_id')
                ->count('student_id'),
            
            'total_lessons' => DB::table('schedule')
                ->where('instructor_id', $id)
                ->where('status', 'completed')
                ->count(),
            
            'attendance_rate' => $this->calculateAttendanceRate($id),
            
            'avg_student_rating' => DB::table('progress')
                ->where('instructor_id', $id)
                ->whereNotNull('student_satisfaction')
                ->avg('student_satisfaction'),
            
            'completion_rate' => $this->calculateCompletionRate($id),
        ];

        // Get current assignments (active enrollments)
        $currentAssignments = DB::table('enrollment as e')
            ->join('student as s', 'e.student_id', '=', 's.student_id')
            ->join('instrument as i', 's.instrument_id', '=', 'i.instrument_id')
            ->where('e.instructor_id', $id)
            ->where('e.status', 'active')
            ->select(
                's.first_name',
                's.last_name',
                'i.instrument_name',
                'e.remaining_sessions',
                'e.enrollment_date'
            )
            ->get();

        // Get this week's schedule
        $weekSchedule = DB::table('schedule as sch')
            ->join('student as s', 'sch.student_id', '=', 's.student_id')
            ->where('sch.instructor_id', $id)
            ->whereBetween('sch.schedule_date', [
                now()->startOfWeek(),
                now()->endOfWeek()
            ])
            ->select(
                'sch.schedule_date',
                'sch.start_time',
                'sch.end_time',
                'sch.room_number',
                's.first_name',
                's.last_name',
                'sch.status'
            )
            ->orderBy('sch.schedule_date')
            ->orderBy('sch.start_time')
            ->get();

        return response()->json([
            'instructor' => $instructor,
            'specializations' => $specializations,
            'metrics' => $metrics,
            'currentAssignments' => $currentAssignments,
            'weekSchedule' => $weekSchedule
        ]);
    }

    /**
     * Update instructor basic information.
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|regex:/^\d{11}$/',
            'employee_id' => 'nullable|string|max:50|unique:instructor,employee_id,' . $id . ',instructor_id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::table('instructor')
            ->where('instructor_id', $id)
            ->update([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'employee_id' => $request->employee_id,
                'updated_at' => now(),
            ]);

        return response()->json(['success' => true, 'message' => 'Instructor updated successfully.']);
    }

    /**
     * Assign specialization to instructor.
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function assignSpecialization(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'specialization_id' => 'required|exists:specialization,specialization_id',
            'is_primary' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Check if already assigned
        $exists = DB::table('instructor_specialization')
            ->where('instructor_id', $id)
            ->where('specialization_id', $request->specialization_id)
            ->exists();

        if ($exists) {
            return response()->json(['error' => 'Specialization already assigned'], 422);
        }

        // If setting as primary, remove primary from others
        if ($request->is_primary) {
            DB::table('instructor_specialization')
                ->where('instructor_id', $id)
                ->update(['is_primary' => false]);
        }

        DB::table('instructor_specialization')->insert([
            'instructor_id' => $id,
            'specialization_id' => $request->specialization_id,
            'is_primary' => $request->is_primary ?? false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Specialization assigned successfully.']);
    }

    /**
     * Remove specialization from instructor.
     * 
     * @param int $id
     * @param int $specializationId
     * @return \Illuminate\Http\JsonResponse
     */
    public function removeSpecialization($id, $specializationId)
    {
        $deleted = DB::table('instructor_specialization')
            ->where('instructor_id', $id)
            ->where('specialization_id', $specializationId)
            ->delete();

        if (!$deleted) {
            return response()->json(['error' => 'Specialization not found'], 404);
        }

        return response()->json(['success' => true, 'message' => 'Specialization removed successfully.']);
    }

    /**
     * Set a specialization as primary.
     * 
     * @param int $id
     * @param int $specializationId
     * @return \Illuminate\Http\JsonResponse
     */
    public function setPrimarySpecialization($id, $specializationId)
    {
        DB::transaction(function() use ($id, $specializationId) {
            // Remove primary from all
            DB::table('instructor_specialization')
                ->where('instructor_id', $id)
                ->update(['is_primary' => false]);

            // Set new primary
            DB::table('instructor_specialization')
                ->where('instructor_id', $id)
                ->where('specialization_id', $specializationId)
                ->update(['is_primary' => true, 'updated_at' => now()]);
        });

        return response()->json(['success' => true, 'message' => 'Primary specialization updated.']);
    }

    /**
     * Update instructor availability.
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateAvailability(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'is_available' => 'required|boolean',
            'available_days' => 'nullable|string',
            'preferred_time_slots' => 'nullable|string|max:255',
            'max_students_per_day' => 'nullable|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::table('instructor')
            ->where('instructor_id', $id)
            ->update([
                'is_available' => $request->is_available,
                'available_days' => $request->available_days,
                'preferred_time_slots' => $request->preferred_time_slots,
                'max_students_per_day' => $request->max_students_per_day,
                'updated_at' => now(),
            ]);

        return response()->json(['success' => true, 'message' => 'Availability updated successfully.']);
    }

    /**
     * Generate performance report for instructor.
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function performanceReport(Request $request, $id)
    {
        $dateFrom = $request->input('date_from', now()->subMonth()->toDateString());
        $dateTo = $request->input('date_to', now()->toDateString());

        $report = [
            'total_lessons' => DB::table('schedule')
                ->where('instructor_id', $id)
                ->where('status', 'completed')
                ->whereBetween('schedule_date', [$dateFrom, $dateTo])
                ->count(),

            'attendance_rate' => $this->calculateAttendanceRate($id, $dateFrom, $dateTo),

            'avg_student_ratings' => DB::table('progress')
                ->where('instructor_id', $id)
                ->whereBetween('progress_date', [$dateFrom, $dateTo])
                ->whereNotNull('student_satisfaction')
                ->avg('student_satisfaction'),

            'student_retention_rate' => $this->calculateRetentionRate($id, $dateFrom, $dateTo),

            'revenue_generated' => DB::table('payment as p')
                ->join('enrollment as e', 'p.enrollment_id', '=', 'e.enrollment_id')
                ->where('e.instructor_id', $id)
                ->whereBetween('p.payment_date', [$dateFrom, $dateTo])
                ->sum('p.amount'),

            // Monthly breakdown
            'lessons_per_month' => DB::table('schedule')
                ->select(
                    DB::raw("TO_CHAR(schedule_date, 'YYYY-MM') as month"),
                    DB::raw('COUNT(*) as count')
                )
                ->where('instructor_id', $id)
                ->where('status', 'completed')
                ->whereBetween('schedule_date', [$dateFrom, $dateTo])
                ->groupBy('month')
                ->orderBy('month')
                ->get(),

            // Rating trend
            'rating_trend' => DB::table('progress')
                ->select(
                    DB::raw("TO_CHAR(progress_date, 'YYYY-MM') as month"),
                    DB::raw('AVG(student_satisfaction) as avg_rating')
                )
                ->where('instructor_id', $id)
                ->whereBetween('progress_date', [$dateFrom, $dateTo])
                ->whereNotNull('student_satisfaction')
                ->groupBy('month')
                ->orderBy('month')
                ->get(),
        ];

        return response()->json($report);
    }

    /**
     * Calculate attendance rate for instructor.
     * 
     * @param int $instructorId
     * @param string|null $dateFrom
     * @param string|null $dateTo
     * @return float
     */
    private function calculateAttendanceRate($instructorId, $dateFrom = null, $dateTo = null)
    {
        $query = DB::table('schedule as sch')
            ->join('attendance as att', 'sch.schedule_id', '=', 'att.schedule_id')
            ->where('sch.instructor_id', $instructorId)
            ->where('sch.status', 'completed');

        if ($dateFrom && $dateTo) {
            $query->whereBetween('sch.schedule_date', [$dateFrom, $dateTo]);
        }

        $total = $query->count();
        if ($total === 0) return 0;

        $present = $query->where('att.attendance_status', 'present')->count();
        
        return round(($present / $total) * 100, 2);
    }

    /**
     * Calculate completion rate for instructor.
     * 
     * @param int $instructorId
     * @return float
     */
    private function calculateCompletionRate($instructorId)
    {
        $total = DB::table('enrollment')
            ->where('instructor_id', $instructorId)
            ->whereIn('status', ['completed', 'cancelled'])
            ->count();

        if ($total === 0) return 0;

        $completed = DB::table('enrollment')
            ->where('instructor_id', $instructorId)
            ->where('status', 'completed')
            ->count();

        return round(($completed / $total) * 100, 2);
    }

    /**
     * Calculate retention rate for instructor within date range.
     * 
     * @param int $instructorId
     * @param string $dateFrom
     * @param string $dateTo
     * @return float
     */
    private function calculateRetentionRate($instructorId, $dateFrom, $dateTo)
    {
        $completed = DB::table('enrollment')
            ->where('instructor_id', $instructorId)
            ->whereBetween('end_date', [$dateFrom, $dateTo])
            ->where('status', 'completed')
            ->count();

        if ($completed === 0) return 0;

        $renewed = DB::table('enrollment as e1')
            ->join('enrollment as e2', function($join) {
                $join->on('e1.student_id', '=', 'e2.student_id')
                     ->on('e1.instructor_id', '=', 'e2.instructor_id')
                     ->whereRaw('e2.enrollment_date > e1.end_date')
                     ->whereRaw('e2.enrollment_date <= e1.end_date + INTERVAL \'30 days\'');
            })
            ->where('e1.instructor_id', $instructorId)
            ->whereBetween('e1.end_date', [$dateFrom, $dateTo])
            ->where('e1.status', 'completed')
            ->distinct('e1.enrollment_id')
            ->count('e1.enrollment_id');

        return round(($renewed / $completed) * 100, 2);
    }
}