{{--  
    ============================================================================ 
    USER MANAGEMENT PAGE - resources/views/admin/users/index.blade.php
    ============================================================================ 
    Features:
    - Compact admin UI sizing
    - Real user_account data display
    - Correct student/instructor/super admin role display
    - Search, role filter, status filter, date filter
    - Responsive table with horizontal scrolling
    - Keeps existing JS functions from resources/js/admin-pages/user.js
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
        /*
        |--------------------------------------------------------------------------
        | Admin Compact UI
        |--------------------------------------------------------------------------
        |
        | Keeps the current colors but reduces oversized display.
        | This makes cards, buttons, filters, table rows, and headers closer
        | to medium / semi-large size.
        |
        */
        .admin-compact-page main > header {
            padding: 1.15rem 1.5rem !important;
        }

        .admin-compact-page h1 {
            font-size: 1.875rem !important;
            line-height: 2.25rem !important;
            letter-spacing: -0.02em;
        }

        .admin-compact-page main > div {
            padding: 1.15rem !important;
        }

        .admin-compact-page .card {
            border-radius: 1rem !important;
        }

        .admin-compact-page .card.p-6,
        .admin-compact-page .card.p-8,
        .admin-compact-page .card.p-3,
        .admin-compact-page .card.md\:p-6 {
            padding: 1rem !important;
        }

        .admin-compact-page .grid {
            gap: 0.9rem !important;
        }

        .admin-compact-page input,
        .admin-compact-page select,
        .admin-compact-page textarea {
            min-height: 2.45rem !important;
            font-size: 0.875rem !important;
            padding-top: 0.45rem !important;
            padding-bottom: 0.45rem !important;
        }

        .admin-compact-page button,
        .admin-compact-page a {
            font-size: 0.875rem !important;
        }

        .admin-compact-page table th {
            padding: 0.75rem 1rem !important;
            font-size: 0.7rem !important;
            letter-spacing: 0.08em !important;
            white-space: nowrap;
        }

        .admin-compact-page table td {
            padding: 0.75rem 1rem !important;
            font-size: 0.875rem !important;
            vertical-align: middle;
        }

        .admin-compact-page .text-3xl {
            font-size: 1.875rem !important;
            line-height: 2.15rem !important;
        }

        .admin-compact-page .text-2xl {
            font-size: 1.4rem !important;
            line-height: 1.9rem !important;
        }

        .admin-compact-page .w-8,
        .admin-compact-page .h-8 {
            width: 1.5rem !important;
            height: 1.5rem !important;
        }

        .admin-compact-page .p-3.rounded-full {
            padding: 0.65rem !important;
        }

        .admin-compact-page .px-6 {
            padding-left: 1rem !important;
            padding-right: 1rem !important;
        }

        .admin-compact-page .py-3 {
            padding-top: 0.65rem !important;
            padding-bottom: 0.65rem !important;
        }

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

<body class="bg-light-gray admin-compact-page"> 
 
@include('layouts.admin-header') 
 
<main class="lg:ml-64 min-h-screen bg-light-gray"> 
     
    {{-- Page Header --}}
    <header class="bg-white shadow-sm border-b-4 border-secondary-blue">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <h1 class="font-bold text-primary-dark">User Management</h1>
                <p class="text-secondary-blue mt-1 text-sm">
                    Manage all system users - Total: {{ $users->total() }}
                </p>
            </div>

            <a href="{{ route('admin.users.create') }}"
               class="bg-forest-green text-white px-5 py-2.5 rounded-lg font-semibold hover:bg-forest-green-dark transition-all shadow-md inline-flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                </svg>
                Add New User
            </a>
        </div>
    </header>
 
    <div>
        {{-- Statistics Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-5">
            <div class="card p-6 border-l-4 border-secondary-blue">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 font-semibold">Total Users</p>
                        <p class="text-3xl font-bold text-primary-dark mt-1">{{ $stats->total_users ?? 0 }}</p>
                    </div>
                    <div class="bg-secondary-blue bg-opacity-20 p-3 rounded-full">
                        <span class="block w-8 h-8 rounded-full bg-secondary-blue"></span>
                    </div>
                </div>
            </div>
            
            <div class="card p-6 border-l-4 border-forest-green">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 font-semibold">Active</p>
                        <p class="text-3xl font-bold text-primary-dark mt-1">{{ $stats->active_users ?? 0 }}</p>
                    </div>
                    <div class="bg-forest-green bg-opacity-20 p-3 rounded-full">
                        <span class="block w-8 h-8 rounded-full bg-forest-green"></span>
                    </div>
                </div>
            </div>

            <div class="card p-6 border-l-4 border-warm-coral">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 font-semibold">Inactive</p>
                        <p class="text-3xl font-bold text-primary-dark mt-1">{{ $stats->inactive_users ?? 0 }}</p>
                    </div>
                    <div class="bg-warm-coral bg-opacity-20 p-3 rounded-full">
                        <span class="block w-8 h-8 rounded-full bg-warm-coral"></span>
                    </div>
                </div>
            </div>

            <div class="card p-6 border-l-4 border-golden-yellow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 font-semibold">Most common role</p>
                        <p class="text-xl font-bold text-primary-dark mt-1">
                            {{ $mostCommonRole ? ucfirst(str_replace('_', ' ', $mostCommonRole->user_role)) : 'N/A' }}
                        </p>
                        <p class="text-xs text-gray-500">
                            {{ $mostCommonRole ? $mostCommonRole->role_count : 0 }} users
                        </p>
                    </div>
                    <div class="bg-golden-yellow bg-opacity-20 p-3 rounded-full">
                        <span class="block w-8 h-8 rounded-full bg-golden-yellow"></span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filters Panel --}}
        <div class="card p-6 mb-5">
            <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-wrap gap-4">
                <div class="flex-1 min-w-[220px]">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Search</label>
                    <input type="text"
                           name="search"
                           value="{{ request('search') }}"
                           placeholder="Name, Email, or ID..."
                           class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-secondary-blue focus:ring-2 focus:ring-secondary-blue">
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Role</label>
                    <select name="role" class="px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-secondary-blue">
                        <option value="all" {{ request('role') == 'all' ? 'selected' : '' }}>All Roles</option>
                        <option value="student" {{ request('role') == 'student' ? 'selected' : '' }}>Student</option>
                        <option value="instructor" {{ request('role') == 'instructor' ? 'selected' : '' }}>Instructor</option>
                        <option value="super_admin" {{ request('role') == 'super_admin' ? 'selected' : '' }}>Super Admin</option>
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
                    <input type="date"
                           name="date_from"
                           value="{{ request('date_from') }}"
                           class="px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-secondary-blue">
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Date To</label>
                    <input type="date"
                           name="date_to"
                           value="{{ request('date_to') }}"
                           class="px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-secondary-blue">
                </div>
                
                <div class="flex items-end gap-2">
                    <button type="submit"
                            class="bg-secondary-blue text-white px-5 py-2 rounded-lg font-semibold hover:bg-secondary-blue-dark transition-all">
                        Apply
                    </button>
                    <a href="{{ route('admin.users.index') }}"
                       class="bg-gray-200 text-gray-700 px-5 py-2 rounded-lg font-semibold hover:bg-gray-300 transition-all">
                        Clear
                    </a>
                </div>
            </form>
        </div>

        {{-- Bulk Actions Bar --}}
        <div id="bulk-actions-bar" class="card p-4 mb-5 hidden"></div>

        {{-- Users Table --}}
        <div class="card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-primary-dark">
                        <tr>
                            <th class="text-left text-xs font-bold text-accent-yellow uppercase tracking-wider w-4">
                                <input type="checkbox" id="select-all" onclick="toggleSelectAll(this)">
                            </th>
                            <th class="text-left text-xs font-bold text-accent-yellow uppercase tracking-wider">ID</th>
                            <th class="text-left text-xs font-bold text-accent-yellow uppercase tracking-wider">Name</th>
                            <th class="text-left text-xs font-bold text-accent-yellow uppercase tracking-wider">Email</th>
                            <th class="text-left text-xs font-bold text-accent-yellow uppercase tracking-wider">Role</th>
                            <th class="text-left text-xs font-bold text-accent-yellow uppercase tracking-wider">Status</th>
                            <th class="text-left text-xs font-bold text-accent-yellow uppercase tracking-wider">Last Login</th>
                            <th class="text-left text-xs font-bold text-accent-yellow uppercase tracking-wider">Created</th>
                            <th class="text-left text-xs font-bold text-accent-yellow uppercase tracking-wider">Updated</th>
                            <th class="text-left text-xs font-bold text-accent-yellow uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($users as $user)
                            @include('partials.user-row', ['user' => $user])
                        @empty
                            <tr>
                                <td colspan="10" class="px-6 py-12 text-center text-gray-500">
                                    <p class="text-lg font-semibold">No users found</p>
                                    <p class="text-sm mt-2">Try adjusting your filters or add a new user.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
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
    /*
    |--------------------------------------------------------------------------
    | Date Filter Guard
    |--------------------------------------------------------------------------
    |
    | Prevents Date To from being earlier than Date From.
    |
    */
    const dateFrom = document.querySelector('input[name="date_from"]');
    const dateTo = document.querySelector('input[name="date_to"]');
    
    if (dateFrom && dateTo) {
        dateFrom.addEventListener('change', function() {
            dateTo.min = this.value;
            
            if (dateTo.value && dateTo.value < this.value) {
                dateTo.value = '';
            }
        });
        
        dateTo.addEventListener('change', function() {
            if (dateFrom.value && this.value < dateFrom.value) {
                alert('End date cannot be before start date');
                this.value = '';
            }
        });
        
        if (dateFrom.value) {
            dateTo.min = dateFrom.value;
        }
    }
</script>

</body> 
</html>