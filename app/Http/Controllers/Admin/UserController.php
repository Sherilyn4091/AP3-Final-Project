<?php  
// app/Http/Controllers/Admin/UserController.php  
  
namespace App\Http\Controllers\Admin;  
  
use App\Http\Controllers\Controller;  
use Illuminate\Http\Request;  
use Illuminate\Support\Facades\DB;  
use Illuminate\Support\Facades\Hash;  
use Illuminate\Support\Str;  
use Illuminate\Support\Facades\Validator;  
use Illuminate\Validation\Rules\Password; // ← FIXED: Added missing import  
  
class UserController extends Controller  
{  
    /**  
     * Display user management page with filters and pagination.  
     *  
     * ============================================================================  
     * 1.  **Super Admin Name**: The `full_name` field will now display 'Super Admin' if the  
     *     `is_super_admin` flag is true, overriding any existing name. This ensures  
     *     clarity in the user list.  
     * 2.  **Robust Null Handling**: The `show` and `getRoleAndDetailsByUserId` methods now  
     *     gracefully handle users who might not have a corresponding entry in a role  
     *     table (e.g., student, instructor). It provides default empty values,  
     *     preventing the "Cannot read properties of null" frontend error and allowing  
     *     these "incomplete" users to be edited.  
     * ============================================================================  
     *  
     * @param Request $request  
     * @return \Illuminate\View\View  
     */  
    public function index(Request $request)  
    {  
        $query = DB::table('user_account as ua')  
            ->leftJoin('student as s', 'ua.user_id', '=', 's.user_id')  
            ->leftJoin('instructor as i', 'ua.user_id', '=', 'i.user_id')  
            ->select(  
                'ua.user_id',  
                'ua.user_email',  
                'ua.last_login',  
                'ua.created_at',  
                'ua.updated_at',  
                'ua.is_super_admin',  
                DB::raw("  
                    CASE  
                        WHEN ua.is_super_admin = TRUE THEN 'super_admin'  
                        WHEN s.user_id IS NOT NULL THEN 'student'  
                        WHEN i.user_id IS NOT NULL THEN 'instructor' 
                        ELSE 'unknown'  
                    END as user_role  
                "),  
                DB::raw("  
                    CASE  
                        WHEN ua.is_super_admin = TRUE THEN 'Super Admin'  
                        ELSE COALESCE(  
                            CONCAT(s.first_name, ' ', s.last_name),  
                            CONCAT(i.first_name, ' ', i.last_name),
                            'N/A'  
                        )  
                    END as full_name  
                "),  
                DB::raw("  
                    CASE  
                        WHEN s.user_id IS NOT NULL THEN s.is_active  
                        WHEN i.user_id IS NOT NULL THEN i.is_active
                        ELSE FALSE  
                    END as is_active  
                ")  
            );  
  
        if ($request->filled('status') && $request->status !== 'all') {
            // Use string 'TRUE' or 'FALSE' for PostgreSQL raw queries
            $isActive = $request->status === 'active' ? 'TRUE' : 'FALSE';
            
            $query->where(function($q) use ($isActive) {
                // Use whereRaw to properly handle PostgreSQL boolean comparison
                $q->where(function($subQ) use ($isActive) {
                    $subQ->whereNotNull('s.user_id')
                        ->whereRaw("s.is_active = {$isActive}");
                })->orWhere(function($subQ) use ($isActive) {
                    $subQ->whereNotNull('i.user_id')
                        ->whereRaw("i.is_active = {$isActive}");
                });
            });
        }

        // Add role filter to main query
        if ($request->filled('role') && $request->role !== 'all') {
            $role = $request->role;
            $query->where(function($q) use ($role) {
                if ($role === 'student') {
                    $q->whereNotNull('s.user_id');
                } elseif ($role === 'instructor') {
                    $q->whereNotNull('i.user_id');
                } elseif ($role === 'super_admin') {
                    $q->where('ua.is_super_admin', true);
                }
            });
        }
        
        if ($request->filled('search')) {  
            $search = $request->search;  
            $query->where(function($q) use ($search) {  
                $q->where('ua.user_email', 'ILIKE', "%{$search}%")  
                  ->orWhere(DB::raw('CAST(ua.user_id AS TEXT)'), 'ILIKE', "%{$search}%")  
                  ->orWhere(DB::raw("CONCAT(s.first_name, ' ', s.last_name)"), 'ILIKE', "%{$search}%")  
                  ->orWhere(DB::raw("CONCAT(i.first_name, ' ', i.last_name)"), 'ILIKE', "%{$search}%");
            });  
        }  
  
        $dateColumn = $request->input('date_filter_by', 'created_at') === 'updated_at' ? 'ua.updated_at' : 'ua.created_at';  
        if ($request->filled('date_from')) {  
            $query->whereDate($dateColumn, '>=', $request->date_from);  
        }  
        if ($request->filled('date_to')) {  
            $query->whereDate($dateColumn, '<=', $request->date_to);  
        }  
  
        // Get paginated users for display
        $users = $query->orderBy('ua.user_id', 'asc')->paginate(20);

        // Calculate accurate statistics from entire filtered dataset (not just current page)
        $statsQuery = DB::table('user_account as ua')
            ->leftJoin('student as s', 'ua.user_id', '=', 's.user_id')
            ->leftJoin('instructor as i', 'ua.user_id', '=', 'i.user_id')
            ->select(
                DB::raw("COUNT(*) as total_users"),
                DB::raw("SUM(CASE WHEN s.is_active = TRUE OR i.is_active = TRUE THEN 1 ELSE 0 END) as active_users"),
                DB::raw("SUM(CASE WHEN (s.user_id IS NOT NULL AND s.is_active = FALSE) OR (i.user_id IS NOT NULL AND i.is_active = FALSE) THEN 1 ELSE 0 END) as inactive_users")
            );

        // Apply same filters to statistics query
        if ($request->filled('search')) {
            $search = $request->search;
            $statsQuery->where(function($q) use ($search) {
                $q->where('ua.user_email', 'ILIKE', "%{$search}%")
                ->orWhere(DB::raw('CAST(ua.user_id AS TEXT)'), 'ILIKE', "%{$search}%")
                ->orWhere(DB::raw("CONCAT(s.first_name, ' ', s.last_name)"), 'ILIKE', "%{$search}%")
                ->orWhere(DB::raw("CONCAT(i.first_name, ' ', i.last_name)"), 'ILIKE', "%{$search}%");
            });
        }

        if ($request->filled('role') && $request->role !== 'all') {
            $role = $request->role;
            $statsQuery->where(function($q) use ($role) {
                if ($role === 'student') {
                    $q->whereNotNull('s.user_id');
                } elseif ($role === 'instructor') {
                    $q->whereNotNull('i.user_id');
                } elseif ($role === 'super_admin') {
                    $q->where('ua.is_super_admin', true);
                }
            });
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $isActive = $request->status === 'active' ? 'TRUE' : 'FALSE';
            $statsQuery->where(function($q) use ($isActive) {
                $q->where(function($subQ) use ($isActive) {
                    $subQ->whereNotNull('s.user_id')
                        ->whereRaw("s.is_active = {$isActive}");
                })->orWhere(function($subQ) use ($isActive) {
                    $subQ->whereNotNull('i.user_id')
                        ->whereRaw("i.is_active = {$isActive}");
                });
            });
        }

        $dateColumn = $request->input('date_filter_by', 'created_at') === 'updated_at' ? 'ua.updated_at' : 'ua.created_at';
        if ($request->filled('date_from')) {
            $statsQuery->whereDate($dateColumn, '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $statsQuery->whereDate($dateColumn, '<=', $request->date_to);
        }

        $stats = $statsQuery->first();

        // Calculate most common role from filtered results
        $roleStats = DB::table('user_account as ua')
            ->leftJoin('student as s', 'ua.user_id', '=', 's.user_id')
            ->leftJoin('instructor as i', 'ua.user_id', '=', 'i.user_id')
            ->select(
                DB::raw("
                    CASE
                        WHEN ua.is_super_admin = TRUE THEN 'super_admin'
                        WHEN s.user_id IS NOT NULL THEN 'student'
                        WHEN i.user_id IS NOT NULL THEN 'instructor' 
                        ELSE 'unknown'
                    END as user_role
                "),
                DB::raw('COUNT(*) as role_count')
            );

        // Apply same filters to role statistics
        if ($request->filled('search')) {
            $search = $request->search;
            $roleStats->where(function($q) use ($search) {
                $q->where('ua.user_email', 'ILIKE', "%{$search}%")
                ->orWhere(DB::raw('CAST(ua.user_id AS TEXT)'), 'ILIKE', "%{$search}%")
                ->orWhere(DB::raw("CONCAT(s.first_name, ' ', s.last_name)"), 'ILIKE', "%{$search}%")
                ->orWhere(DB::raw("CONCAT(i.first_name, ' ', i.last_name)"), 'ILIKE', "%{$search}%");
            });
        }

        if ($request->filled('role') && $request->role !== 'all') {
            $role = $request->role;
            $roleStats->where(function($q) use ($role) {
                if ($role === 'student') {
                    $q->whereNotNull('s.user_id');
                } elseif ($role === 'instructor') {
                    $q->whereNotNull('i.user_id');
                } elseif ($role === 'super_admin') {
                    $q->where('ua.is_super_admin', true);
                }
            });
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $isActive = $request->status === 'active' ? 'TRUE' : 'FALSE';
            $roleStats->where(function($q) use ($isActive) {
                $q->where(function($subQ) use ($isActive) {
                    $subQ->whereNotNull('s.user_id')
                        ->whereRaw("s.is_active = {$isActive}");
                })->orWhere(function($subQ) use ($isActive) {
                    $subQ->whereNotNull('i.user_id')
                        ->whereRaw("i.is_active = {$isActive}");
                });
            });
        }

        if ($request->filled('date_from')) {
            $roleStats->whereDate($dateColumn, '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $roleStats->whereDate($dateColumn, '<=', $request->date_to);
        }

        $mostCommonRole = $roleStats->groupBy('user_role')
            ->orderByDesc('role_count')
            ->first();

        return view('admin.users.index', compact('users', 'stats', 'mostCommonRole'));
    }
  
      
    /**  
     * Show the form for creating a new resource.  
     */  
    public function create()  
    {
        $specializations = DB::table('specialization')  
            ->whereRaw('is_active = TRUE')  
            ->orderBy('specialization_name')  
            ->get();  
          
        return view('admin.users.create', compact('specializations'));  
    }  
  
    /**  
     * ============================================================================  
     * Store a newly created user with proper database structure  
     * ============================================================================  
     * This method now:  
     * 1. Validates all inputs including password strength  
     * 2. Auto-formats names (capitalizes first letter of each word)  
     * 3. Auto-formats email (converts to lowercase)  
     * 4. Creates user_account entry first  
     * 5. Creates role-specific entry in appropriate table  
     * 6. Handles instructor specializations if role is instructor  
     * 7. Uses transactions for data integrity  
     * ============================================================================  
     */  
    public function store(Request $request)  
    {  
        // ========================================================================  
        // STEP 1: Validate incoming request data  
        // ========================================================================  
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|string|email|max:255|unique:user_account,user_email',
            'password' => ['required', 'string', Password::min(8)->mixedCase()->numbers()->symbols()],
            'phone' => 'nullable|string|regex:/^\d{11}$/',
            'role' => 'required|string|in:student,instructor',
            
            // Student-specific validation
            'student_status_id' => 'required_if:role,student|exists:student_status,status_id',
            
            // Instructor-specific validation (years_of_experience is now OPTIONAL)
            'instructor.years_of_experience' => 'nullable|integer|min:0',
            'instructor.education_level' => 'nullable|string|max:100',
            'instructor.music_degree' => 'nullable|string|max:200',
            'instructor.bio' => 'nullable|string',
            'instructor.certifications' => 'nullable|string',
            'instructor.teaching_style' => 'nullable|string|max:255',
            'instructor.languages_spoken' => 'nullable|string|max:255',
            'instructor.max_students_per_day' => 'nullable|integer|min:1',
            'instructor.preferred_time_slots' => 'nullable|string|max:255',
            'instructor.available_days' => 'nullable|array',
            'instructor.available_days.*' => 'string|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
            
            // Specializations
            'specializations' => 'nullable|array',
            'specializations.*' => 'exists:specialization,specialization_id',
            'primary_specialization' => 'nullable|exists:specialization,specialization_id',
        ]);  
  
        // If validation fails, return with errors  
        if ($validator->fails()) {  
            return back()->withErrors($validator)->withInput();  
        }  
  
        // ========================================================================  
        // STEP 2: Auto-format data (capitalize names, lowercase email)  
        // ========================================================================  
        $firstName = ucwords(strtolower(trim($request->first_name)));  
        $lastName = ucwords(strtolower(trim($request->last_name)));  
        $email = strtolower(trim($request->email));  
        $phone = $request->phone ? trim($request->phone) : null;  
  
        // ========================================================================  
        // STEP 3: Start database transaction  
        // ========================================================================  
        DB::beginTransaction();  
          
        try {  
            // ====================================================================  
            // STEP 4: Create user_account entry  
            // ====================================================================  
            $userId = DB::table('user_account')->insertGetId([  
            'user_email' => $email,  
            'user_password' => Hash::make($request->password),  
            'is_super_admin' => false,  
            'created_at' => now(),  
            'updated_at' => now(),  
        ], 'user_id'); // ← ADD THIS SECOND PARAMETER

  
        // ====================================================================  
        // STEP 5: Create role-specific entry based on selected role  
        // ====================================================================  
        $role = $request->role;  

        switch ($role) {  
            case 'student':
                DB::table('student')->insert([
                    'user_id' => $userId,
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'phone' => $phone,
                    'email' => $email,
                    'student_status_id' => $request->input('student_status_id'), // Use selected status from form
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            break;

            case 'instructor':  
                // Prepare instructor data  
                $instructorData = [  
                    'user_id' => $userId,  
                    'first_name' => $firstName,  
                    'last_name' => $lastName,  
                    'phone' => $phone,  
                    'email' => $email,  
                    'years_of_experience' => $request->input('instructor.years_of_experience') ?? null,
                    'education_level' => $request->input('instructor.education_level'),  
                    'music_degree' => $request->input('instructor.music_degree'),  
                    'bio' => $request->input('instructor.bio'),  
                    'certifications' => $request->input('instructor.certifications'),  
                    'teaching_style' => $request->input('instructor.teaching_style'),  
                    'languages_spoken' => $request->input('instructor.languages_spoken'),  
                    'is_available' => $request->has('instructor.is_available') ? true : false,  
                    'available_days' => $request->input('instructor.available_days')   
                        ? json_encode($request->input('instructor.available_days'))   
                        : null,  
                    'preferred_time_slots' => $request->input('instructor.preferred_time_slots'),  
                    'max_students_per_day' => $request->input('instructor.max_students_per_day', 8),  
                    'is_active' => true,  
                    'created_at' => now(),  
                    'updated_at' => now(),  
                ];  

                $instructorId = DB::table('instructor')->insertGetId($instructorData, 'instructor_id');  

                // Handle specializations if provided  
                if ($request->has('specializations') && is_array($request->specializations)) {  
                    $primarySpecId = $request->input('primary_specialization');  
                        
                    foreach ($request->specializations as $specId) {  
                        DB::table('instructor_specialization')->insert([  
                            'instructor_id' => $instructorId,  
                            'specialization_id' => $specId,  
                            'is_primary' => ($specId == $primarySpecId),  
                            'created_at' => now(),  
                            'updated_at' => now(),  
                        ]);  
                    }  
                }  
            break;  
        }  
  
            // ====================================================================  
            // STEP 6: Commit transaction  
            // ====================================================================  
            DB::commit();  
  
            // ====================================================================  
            // STEP 7: Redirect with success message  
            // ====================================================================  
            return redirect()->route('admin.users.index')  
                ->with('success', 'User created successfully! Email: ' . $email);  
  
        } catch (\Exception $e) {  
            // ====================================================================  
            // STEP 8: Rollback on error and enhance logging  
            // ====================================================================  
            DB::rollBack();  
              
            // Log the detailed error for debugging purposes  
            \Log::error('User creation failed: ' . $e->getMessage(), [  
                'request' => $request->except('password'), // Don't log password  
                'trace' => $e->getTraceAsString()  
            ]);  
              
            // Return a user-friendly error message  
            return back()  
                ->withInput()  
                ->with('error', 'A server error occurred while creating the user. Please check the system logs for details.');  
        }  
    }  
  
    /**  
     * Get user details for modal view. Now handles users without a role entry.  
     */  
    public function show($id)  
    {  
        $user = DB::table('user_account')->where('user_id', $id)->first();  
        if (!$user) {  
            return response()->json(['error' => 'User not found'], 404);  
        }  
  
        list($role, $roleData, $fullName, $isActive) = $this->getRoleAndDetailsByUserId($user->user_id);  
          
        if ($user->is_super_admin) {  
            $fullName = 'Super Admin';  
        }  
          
        $activityData = $this->getActivityData($user->user_id, $role, $roleData);  
  
        return response()->json([  
            'user' => $user,  
            'roleData' => $roleData ?? (object)['first_name' => '', 'last_name' => '', 'phone' => ''],  
            'activityData' => $activityData,  
            'full_name' => $fullName,  
            'is_active' => $isActive,  
            'user_role' => $role ?? 'unknown'  
        ]);  
    }  
  
    /**  
     * Update user details.  
     */  
    public function update(Request $request, $id)  
    {  
        $user = DB::table('user_account')->where('user_id', $id)->first();  
        if (!$user) {  
            return response()->json(['error' => 'User not found'], 404);  
        }  
      
        $validator = Validator::make($request->all(), [
            'first_name' => ($user->is_super_admin ? 'nullable' : 'required') . '|string|max:100',
            'last_name' => ($user->is_super_admin ? 'nullable' : 'required') . '|string|max:100',
            'user_email' => 'required|email|max:255|unique:user_account,user_email,' . $id . ',user_id',
            'phone' => 'nullable|string|regex:/^\d{11}$/',
        ]);  
      
        if ($validator->fails()) {  
            return response()->json(['errors' => $validator->errors()], 422);  
        }  
      
        DB::transaction(function () use ($request, $id, $user) {  
            DB::table('user_account')  
                ->where('user_id', $id)  
                ->update([  
                    'user_email' => $request->input('user_email'),  
                    'updated_at' => now(),  
                ]);  
      
            list($role) = $this->getRoleAndDetailsByUserId($id);  
            if ($role && !$user->is_super_admin) {  
                $tableMap = [  
                    'student' => 'student', 'instructor' => 'instructor',  
                    'sales' => 'sales_staff', 'all_around_staff' => 'all_around_staff'  
                ];  
                $tableName = $tableMap[$role];  
      
                DB::table($tableName)  
                    ->where('user_id', $id)  
                    ->update([  
                        'first_name' => $request->input('first_name'),  
                        'last_name' => $request->input('last_name'),  
                        'phone' => $request->input('phone'),  
                        'email' => $request->input('user_email'),  
                        'updated_at' => now(),  
                    ]);  
            }  
        });  
      
        return response()->json(['success' => true, 'message' => 'User updated successfully.']);  
    }  
      
    /**  
     * Helper to get role details. Now returns default values for users without a role entry.  
     */  
    private function getRoleAndDetailsByUserId($userId)  
    {  
        $user = DB::table('user_account')->where('user_id', $userId)->first();  
  
        if ($roleData = DB::table('student')->where('user_id', $userId)->first()) {  
            $fullName = trim($roleData->first_name . ' ' . $roleData->last_name);  
            return ['student', $roleData, $fullName, (bool)$roleData->is_active];  
        }  
        if ($roleData = DB::table('instructor')->where('user_id', $userId)->first()) {  
            $fullName = trim($roleData->first_name . ' ' . $roleData->last_name);  
            return ['instructor', $roleData, $fullName, (bool)$roleData->is_active];  
        }
  
        $fullName = $user->is_super_admin ? 'Super Admin' : 'N/A';  
        return [null, null, $fullName, false];  
    }  
  
    /**  
     * Get activity data based on role.  
     */  
    private function getActivityData($userId, $role, $roleData)  
    {  
        $data = [];  
        if (!$roleData) return $data;  
  
        switch ($role) {  
            case 'student':  
                $student_id = $roleData->student_id;  
                $data['enrollments'] = DB::table('enrollment')->where('student_id', $student_id)->count();  
                $data['payments'] = DB::table('payment')->where('student_id', $student_id)->sum('amount');  
                $data['attendance'] = DB::table('attendance')->where('student_id', $student_id)->where('attendance_type', 'lesson')->count();  
                break;  
            case 'instructor':  
                $instructor_id = $roleData->instructor_id;  
                $data['assigned_students'] = DB::table('enrollment')->where('instructor_id', $instructor_id)->where('status', 'active')->distinct('student_id')->count('student_id');  
                $data['total_lessons'] = DB::table('schedule')->where('instructor_id', $instructor_id)->where('status', 'completed')->count();  
                break;  
            
        }  
        return $data;  
    }  
  
    public function resetPassword(Request $request, $id)  
    {  
        $newPassword = Str::random(12);  
        DB::table('user_account')  
            ->where('user_id', $id)  
            ->update(['user_password' => Hash::make($newPassword)]);  
  
        return response()->json(['success' => true, 'password' => $newPassword]);  
    }  
  
    public function deactivate($id) { return $this->updateUserStatus($id, false); }  
    public function activate($id) { return $this->updateUserStatus($id, true); }  
  
    private function updateUserStatus($id, $isActive)  
    {  
        list($role) = $this->getRoleAndDetailsByUserId($id);  
        if (!$role) return response()->json(['error' => 'User has no role to update.'], 404);  

        $tableMap = [  
            'student' => 'student', 
            'instructor' => 'instructor',
        ];  
        $tableName = $tableMap[$role];  

        // Use DB::raw() to send actual PostgreSQL boolean TRUE/FALSE
        DB::table($tableName)
            ->where('user_id', $id)
            ->update([
                'is_active' => DB::raw($isActive ? 'TRUE' : 'FALSE'),
                'updated_at' => now()
            ]);
            
        return response()->json(['success' => true, 'message' => 'User status updated successfully']);  
    }
  
    public function destroy($id)  
    {  
        DB::table('user_account')->where('user_id', $id)->delete();  
        return response()->json(['success' => true, 'message' => 'User deleted successfully']);  
    }  
  
    public function getDeletionImpact($id)  
    {  
        list($role, $roleData) = $this->getRoleAndDetailsByUserId($id);  
        if (!$roleData) return response()->json(['impact' => []]);  
        $impact = [];  
        return response()->json(['impact' => $impact]);  
    }  
  
    public function bulkDeactivate(Request $request)  
    {  
        $userIds = $request->input('user_ids', []);  
        DB::transaction(function () use ($userIds) {  
            $usersByRole = ['student' => [], 'instructor' => []];  
            foreach ($userIds as $userId) {  
                list($role) = $this->getRoleAndDetailsByUserId($userId);  
                if ($role) $usersByRole[$role][] = $userId;  
            }  
            if (!empty($usersByRole['student'])) {
                DB::table('student')->whereIn('user_id', $usersByRole['student'])
                    ->update(['is_active' => false, 'updated_at' => now()]);  
            }
            if (!empty($usersByRole['instructor'])) {
                DB::table('instructor')->whereIn('user_id', $usersByRole['instructor'])
                    ->update(['is_active' => false, 'updated_at' => now()]);
            }
        });  
        return response()->json(['success' => true, 'message' => count($userIds) . ' users deactivated successfully.']);  
    }  
  
    public function bulkDestroy(Request $request)  
    {  
        $userIds = $request->input('user_ids', []);  
        DB::table('user_account')->whereIn('user_id', $userIds)->delete();  
        return response()->json(['success' => true, 'message' => count($userIds) . ' users deleted successfully.']);  
    }  
}