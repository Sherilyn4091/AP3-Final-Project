<?php
// app/Http/Controllers/Admin/UserController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserController extends Controller
{
    /**
     * Display user management page with filters and pagination.
     *
     * ============================================================================
     * REFACTOR SUMMARY (DATABASE FIX)
     * ============================================================================
     * This method has been completely refactored to fix the "relation does not exist" error.
     *
     * 1.  **Removed View Dependency**: The query no longer uses the `v_complete_user_profile` view.
     * 2.  **Direct Table Joins**: It now queries the `user_account` table directly and uses LEFT JOINs 
     *     to connect to each role-specific table (student, instructor, etc.).
     * 3.  **Dynamic Role & Data**: The user's role, full name, and active status are now determined
     *     dynamically within the SQL query itself using CASE statements. This is efficient and
     *     avoids extra queries in a loop (resolving N+1 performance issues).
     * 4.  **Corrected Filters**: All filters (role, status, search) have been rewritten to work
     *     with the new joined table structure.
     *
     * This approach is robust and aligned with the provided database schema.
     * ============================================================================
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = DB::table('user_account as ua')
            // LEFT JOIN each role table to fetch role-specific data in one go.
            ->leftJoin('student as s', 'ua.user_id', '=', 's.user_id')
            ->leftJoin('instructor as i', 'ua.user_id', '=', 'i.user_id')
            ->leftJoin('sales_staff as ss', 'ua.user_id', '=', 'ss.user_id')
            ->leftJoin('all_around_staff as aas', 'ua.user_id', '=', 'aas.user_id')
            ->select(
                'ua.user_id',
                'ua.user_email',
                'ua.is_super_admin',
                'ua.last_login',
                'ua.created_at',
                // Dynamically determine the user's role based on which JOIN found a match.
                DB::raw("
                    CASE
                        WHEN s.user_id IS NOT NULL THEN 'student'
                        WHEN i.user_id IS NOT NULL THEN 'instructor'
                        WHEN ss.user_id IS NOT NULL THEN 'sales'
                        WHEN aas.user_id IS NOT NULL THEN 'all_around_staff'
                        ELSE 'unknown'
                    END as user_role
                "),
                // Dynamically get the full name by coalescing from the joined tables.
                DB::raw("
                    COALESCE(
                        CONCAT(s.first_name, ' ', s.last_name),
                        CONCAT(i.first_name, ' ', i.last_name),
                        CONCAT(ss.first_name, ' ', ss.last_name),
                        CONCAT(aas.first_name, ' ', aas.last_name),
                        'N/A'
                    ) as full_name
                "),
                // Dynamically get the active status from the correct role table.
                DB::raw("
                    CASE
                        WHEN s.user_id IS NOT NULL THEN s.is_active
                        WHEN i.user_id IS NOT NULL THEN i.is_active
                        WHEN ss.user_id IS NOT NULL THEN ss.is_active
                        WHEN aas.user_id IS NOT NULL THEN aas.is_active
                        ELSE FALSE
                    END as is_active
                ")
            );

        // Filter by Role - This now checks for the existence of the user in the selected role table.
        if ($request->filled('role') && $request->role !== 'all') {
            switch ($request->role) {
                case 'student':
                    $query->whereNotNull('s.user_id');
                    break;
                case 'instructor':
                    $query->whereNotNull('i.user_id');
                    break;
                case 'sales':
                    $query->whereNotNull('ss.user_id');
                    break;
                case 'all_around_staff':
                    $query->whereNotNull('aas.user_id');
                    break;
            }
        }

        // Filter by Status - This now checks the 'is_active' flag in the correct joined table.
        if ($request->filled('status') && $request->status !== 'all') {
            $isActive = $request->status === 'active';
            $query->where(function($q) use ($isActive) {
                $q->where('s.is_active', '=', $isActive)
                  ->orWhere('i.is_active', '=', $isActive)
                  ->orWhere('ss.is_active', '=', $isActive)
                  ->orWhere('aas.is_active', '=', $isActive);
            });
        }

        // Search Filter - Now correctly searches across the user account and all joined name fields.
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('ua.user_email', 'ILIKE', "%{$search}%")
                  // Since user_id is a number, direct ILIKE can be problematic. Cast it to text.
                  ->orWhere(DB::raw('CAST(ua.user_id AS TEXT)'), 'ILIKE', "%{$search}%")
                  ->orWhere(DB::raw("CONCAT(s.first_name, ' ', s.last_name)"), 'ILIKE', "%{$search}%")
                  ->orWhere(DB::raw("CONCAT(i.first_name, ' ', i.last_name)"), 'ILIKE', "%{$search}%")
                  ->orWhere(DB::raw("CONCAT(ss.first_name, ' ', ss.last_name)"), 'ILIKE', "%{$search}%")
                  ->orWhere(DB::raw("CONCAT(aas.first_name, ' ', aas.last_name)"), 'ILIKE', "%{$search}%");
            });
        }

        // Date Range Filter
        if ($request->filled('date_from')) {
            $query->whereDate('ua.created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('ua.created_at', '<=', $request->date_to);
        }

        // The main query now has all the data, so no need for extra processing in a loop.
        $users = $query->orderBy('ua.created_at', 'desc')->paginate(20);

        return view('admin.users.index', compact('users'));
    }

    /**
     * Get user details for modal view.
     * Refactored to query base tables instead of a view.
     */
    public function show($id)
    {
        $user = DB::table('user_account')->where('user_id', $id)->first();
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        // Helper function now determines role and fetches all necessary data.
        list($role, $roleData, $fullName, $isActive) = $this->getRoleAndDetailsByUserId($user->user_id);
        
        $activityData = $this->getActivityData($user->user_id, $role, $roleData);

        return response()->json([
            'user' => $user,
            'roleData' => $roleData,
            'activityData' => $activityData,
            'full_name' => $fullName,
            'is_active' => $isActive
        ]);
    }
    
    /**
     * Helper to get user's role, role-specific data, full name, and active status.
     * This is a crucial helper that checks each role table to find out the user's role.
     *
     * @param int $userId
     * @return array [role, roleData, fullName, isActive]
     */
    private function getRoleAndDetailsByUserId($userId)
    {
        if ($roleData = DB::table('student')->where('user_id', $userId)->first()) {
            $fullName = trim($roleData->first_name . ' ' . $roleData->last_name);
            return ['student', $roleData, $fullName, (bool)$roleData->is_active];
        }
        if ($roleData = DB::table('instructor')->where('user_id', $userId)->first()) {
            $fullName = trim($roleData->first_name . ' ' . $roleData->last_name);
            return ['instructor', $roleData, $fullName, (bool)$roleData->is_active];
        }
        if ($roleData = DB::table('sales_staff')->where('user_id', $userId)->first()) {
            $fullName = trim($roleData->first_name . ' ' . $roleData->last_name);
            return ['sales', $roleData, $fullName, (bool)$roleData->is_active];
        }
        if ($roleData = DB::table('all_around_staff')->where('user_id', $userId)->first()) {
            $fullName = trim($roleData->first_name . ' ' . $roleData->last_name);
            return ['all_around_staff', $roleData, $fullName, (bool)$roleData->is_active];
        }
        return [null, null, 'N/A', false];
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
            case 'sales':
            case 'all_around_staff':
                $data['work_days'] = DB::table('attendance')->where('user_id', $userId)->where('attendance_type', 'work')->count();
                break;
        }
        return $data;
    }

    /**
     * Reset user password.
     */
    public function resetPassword(Request $request, $id)
    {
        $newPassword = Str::random(12);
        DB::table('user_account')
            ->where('user_id', $id)
            ->update(['user_password' => Hash::make($newPassword)]);

        return response()->json(['success' => true, 'password' => $newPassword]);
    }

    /**
     * Deactivate user.
     */
    public function deactivate($id)
    {
        return $this->updateUserStatus($id, false);
    }

    /**
     * Activate user.
     */
    public function activate($id)
    {
        return $this->updateUserStatus($id, true);
    }

    /**
     * Helper to update user status by first finding the correct role table.
     */
    private function updateUserStatus($id, $isActive)
    {
        // First, find out the user's role.
        list($role) = $this->getRoleAndDetailsByUserId($id);
        
        if (!$role) {
            return response()->json(['error' => 'User role not found'], 404);
        }

        // Map the simple role name to the actual database table name.
        $tableMap = [
            'student' => 'student',
            'instructor' => 'instructor',
            'sales' => 'sales_staff',
            'all_around_staff' => 'all_around_staff'
        ];
        
        $tableName = $tableMap[$role] ?? null;

        if ($tableName) {
            DB::table($tableName)->where('user_id', $id)->update(['is_active' => $isActive]);
            return response()->json(['success' => true, 'message' => 'User status updated successfully']);
        }

        return response()->json(['error' => 'Failed to update user status: Invalid role table'], 500);
    }

    /**
     * Delete user. The ON DELETE CASCADE constraint in the schema handles the rest.
     */
    public function destroy($id)
    {
        try {
            DB::table('user_account')->where('user_id', $id)->delete();
            return response()->json(['success' => true, 'message' => 'User deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete user: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get deletion impact counts.
     */
    public function getDeletionImpact($id)
    {
        list($role, $roleData) = $this->getRoleAndDetailsByUserId($id);
        if (!$roleData) return response()->json(['impact' => []]);

        $impact = [];
        switch ($role) {
            case 'student':
                $student_id = $roleData->student_id;
                $impact['Enrollments'] = DB::table('enrollment')->where('student_id', $student_id)->count();
                $impact['Payments'] = DB::table('payment')->where('student_id', $student_id)->count();
                $impact['Attendance Records'] = DB::table('attendance')->where('student_id', $student_id)->count();
                break;
            case 'instructor':
                $instructor_id = $roleData->instructor_id;
                $impact['Assigned Schedules'] = DB::table('schedule')->where('instructor_id', $instructor_id)->count();
                $impact['Progress Records'] = DB::table('progress')->where('instructor_id', $instructor_id)->count();
                break;
        }
        return response()->json(['impact' => $impact]);
    }

    /**
     * Bulk deactivate users.
     */
    public function bulkDeactivate(Request $request)
    {
        $userIds = $request->input('user_ids', []);
        if (empty($userIds)) return response()->json(['error' => 'No users selected'], 400);

        // This requires a transaction to ensure all updates succeed or none do.
        DB::transaction(function () use ($userIds) {
            // Group users by their roles to perform efficient bulk updates.
            $usersByRole = [
                'student' => [],
                'instructor' => [],
                'sales' => [],
                'all_around_staff' => []
            ];

            foreach ($userIds as $userId) {
                list($role) = $this->getRoleAndDetailsByUserId($userId);
                if ($role) {
                    $usersByRole[$role][] = $userId;
                }
            }
            
            // Update each table with the list of user IDs for that role.
            if (!empty($usersByRole['student'])) {
                DB::table('student')->whereIn('user_id', $usersByRole['student'])->update(['is_active' => false]);
            }
            if (!empty($usersByRole['instructor'])) {
                DB::table('instructor')->whereIn('user_id', $usersByRole['instructor'])->update(['is_active' => false]);
            }
            if (!empty($usersByRole['sales'])) {
                DB::table('sales_staff')->whereIn('user_id', $usersByRole['sales'])->update(['is_active' => false]);
            }
            if (!empty($usersByRole['all_around_staff'])) {
                DB::table('all_around_staff')->whereIn('user_id', $usersByRole['all_around_staff'])->update(['is_active' => false]);
            }
        });

        return response()->json(['success' => true, 'message' => count($userIds) . ' users deactivated successfully.']);
    }

    /**
     * Bulk delete users. This is simple because of the CASCADE constraint.
     */
    public function bulkDestroy(Request $request)
    {
        $userIds = $request->input('user_ids', []);
        if (empty($userIds)) return response()->json(['error' => 'No users selected'], 400);

        DB::table('user_account')->whereIn('user_id', $userIds)->delete();
        return response()->json(['success' => true, 'message' => count($userIds) . ' users deleted successfully.']);
    }
}
