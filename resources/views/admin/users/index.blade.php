{{--  
    ============================================================================ 
    USER MANAGEMENT PAGE - resources/views/admin/users/index.blade.php
    ============================================================================ 
    *   **Inline Editing**: Click the pencil icon to edit row directly in the table. 
    *   **Dynamic Tooltips**: Action buttons now show clean, CSS-based tooltips on hover. 
    *   **Professional UI**: Sharp, organized design using pure Tailwind CSS classes. 
    *   **Enhanced Toast**: Reset password toast now uses the specified #377357 color. 
    *   **Robust Error Handling**: Javascript now gracefully handles users with null/incomplete data. 
    ============================================================================ 
--}} 
 
<!DOCTYPE html> 
<html lang="en"> 
<head> 
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <meta name="csrf-token" content="{{ csrf_token() }}"> 
    <title>User Management - Admin Dashboard</title> 
    @vite(['resources/css/style.css', 'resources/js/app.js', 'resources/js/admin-pages/user.js'])

    <style>
        /* Action button tooltips */
        .action-btn:hover .tooltip {
            opacity: 1;
            visibility: visible;
        }
        .toast-success {
            background-color: #377357 !important;
        }
    </style>

</head> 
<body class="bg-light-gray"> 
 
@include('layouts.admin-header') 
 
<main class="lg:ml-64 min-h-screen bg-light-gray"> 
     
    <header class="bg-white shadow-sm p-6 border-b-4 border-secondary-blue">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <h1 class="text-3xl font-bold text-primary-dark">User Management</h1>
                <p class="text-secondary-blue mt-1">Manage all system users - Total: {{ $users->total() }}</p>
            </div>
            <a href="{{ route('admin.users.create') }}" class="bg-forest-green text-white px-6 py-3 rounded-lg font-semibold hover:bg-forest-green-dark transition-all shadow-lg inline-flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                Add New User
            </a>
        </div>
    </header>
 
    <div class="p-4 lg:p-6">
         
        {{-- Statistics Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            {{-- Total Users --}}
            <div class="card p-6 border-l-4 border-secondary-blue">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 font-semibold">Total Users</p>
                        <p class="text-3xl font-bold text-primary-dark mt-1">{{ $stats->total_users ?? 0 }}</p>
                    </div>
                    <div class="bg-secondary-blue bg-opacity-20 p-3 rounded-full">
                        <svg class="w-8 h-8 text-secondary-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
            
            {{-- Active Users --}}
            <div class="card p-6 border-l-4 border-forest-green">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 font-semibold">Active</p>
                        <p class="text-3xl font-bold text-primary-dark mt-1">{{ $stats->active_users ?? 0 }}</p>
                    </div>
                    <div class="bg-forest-green bg-opacity-20 p-3 rounded-full">
                        <svg class="w-8 h-8 text-forest-green" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Inactive Users --}}
            <div class="card p-6 border-l-4 border-warm-coral">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 font-semibold">Inactive</p>
                        <p class="text-3xl font-bold text-primary-dark mt-1">{{ $stats->inactive_users ?? 0 }}</p>
                    </div>
                    <div class="bg-warm-coral bg-opacity-20 p-3 rounded-full">
                        <svg class="w-8 h-8 text-warm-coral" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Most Common Role --}}
            <div class="card p-6 border-l-4 border-golden-yellow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 font-semibold">Most common role</p>
                        <p class="text-lg font-bold text-primary-dark mt-1">
                            {{ $mostCommonRole ? ucfirst(str_replace('_', ' ', $mostCommonRole->user_role)) : 'N/A' }}
                        </p>
                        <p class="text-xs text-gray-500">
                            {{ $mostCommonRole ? $mostCommonRole->role_count : 0 }} users
                        </p>
                    </div>
                    <div class="bg-golden-yellow bg-opacity-20 p-3 rounded-full">
                        <svg class="w-8 h-8 text-golden-yellow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filters Panel --}}
        <div class="card p-6 mb-6">
            <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-wrap gap-4">
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}" 
                        placeholder="Name, Email, or ID..." 
                        class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-secondary-blue focus:ring-2 focus:ring-secondary-blue">
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Role</label>
                    <select name="role" class="px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-secondary-blue">
                        <option value="all" {{ request('role') == 'all' ? 'selected' : '' }}>All Roles</option>
                        <option value="student" {{ request('role') == 'student' ? 'selected' : '' }}>Student</option>
                        <option value="instructor" {{ request('role') == 'instructor' ? 'selected' : '' }}>Instructor</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Status</label>
                    <select name="status" class="px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-secondary-blue">
                        <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>All Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Date Filter</label>
                    <select name="date_filter_by" class="px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-secondary-blue">
                        <option value="created_at" {{ request('date_filter_by') == 'created_at' ? 'selected' : '' }}>Created Date</option>
                        <option value="updated_at" {{ request('date_filter_by') == 'updated_at' ? 'selected' : '' }}>Updated Date</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Date From</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-secondary-blue">
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Date To</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-secondary-blue">
                </div>
                
                <div class="flex items-end gap-2">
                    <button type="submit" class="bg-secondary-blue text-white px-6 py-2 rounded-lg font-semibold hover:bg-secondary-blue-dark transition-all">
                        Apply
                    </button>
                    <a href="{{ route('admin.users.index') }}" class="bg-gray-200 text-gray-700 px-6 py-2 rounded-lg font-semibold hover:bg-gray-300 transition-all">
                        Clear
                    </a>
                </div>
            </form>
        </div>

        {{-- Bulk Actions Bar --}}
        <div id="bulk-actions-bar" class="card p-4 mb-6 hidden"></div>

        {{-- Users Table --}}
        <div class="card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-primary-dark">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold text-accent-yellow uppercase tracking-wider w-4">
                                <input type="checkbox" id="select-all" onclick="toggleSelectAll(this)">
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-accent-yellow uppercase tracking-wider">ID</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-accent-yellow uppercase tracking-wider">Name</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-accent-yellow uppercase tracking-wider">Email</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-accent-yellow uppercase tracking-wider">Role</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-accent-yellow uppercase tracking-wider">Status</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-accent-yellow uppercase tracking-wider">Last Login</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-accent-yellow uppercase tracking-wider">Created</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-accent-yellow uppercase tracking-wider">Updated</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-accent-yellow uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($users as $user)
                            @include('partials.user-row', ['user' => $user])
                        @empty
                            <tr>
                                <td colspan="10" class="px-6 py-12 text-center text-gray-500">
                                    <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                    </svg>
                                    <p class="text-lg font-semibold">No users found</p>
                                    <p class="text-sm mt-2">Try adjusting your filters or add a new user</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            {{-- Pagination --}}
            @if($users->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                {{ $users->appends(request()->query())->links() }}
            </div>
            @endif
        </div>
    </div> 
 
    <footer class="bg-white border-t py-4 text-center mt-8"> 
        <p class="text-xs text-gray-500">© {{ date('Y') }} Music Lab. All rights reserved.</p> 
    </footer> 
</main> 
 
{{-- Toast & Modals --}} 
<div id="toast-container" class="fixed top-20 right-4 z-[100] space-y-2"></div> 
<div id="reset-password-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-4"></div> 
 
<script>
    // Prevent "Date To" from being before "Date From"
    const dateFrom = document.querySelector('input[name="date_from"]');
    const dateTo = document.querySelector('input[name="date_to"]');
    
    if (dateFrom && dateTo) {
        dateFrom.addEventListener('change', function() {
            // Set minimum date for "Date To" to be same as "Date From"
            dateTo.min = this.value;
            
            // If "Date To" is already set and is before "Date From", clear it
            if (dateTo.value && dateTo.value < this.value) {
                dateTo.value = '';
            }
        });
        
        dateTo.addEventListener('change', function() {
            // If "Date To" is before "Date From", show alert and clear
            if (dateFrom.value && this.value < dateFrom.value) {
                alert('End date cannot be before start date');
                this.value = '';
            }
        });
        
        // Set initial min value if "Date From" is already set
        if (dateFrom.value) {
            dateTo.min = dateFrom.value;
        }
    }
</script>

</body> 
</html>