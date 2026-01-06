{{-- resources/views/admin/users/index.blade.php --}}
{{-- 
    ============================================================================
    USER MANAGEMENT PAGE - REVISION 3
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
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/admin-pages'])
</head>
<body class="bg-light-gray">

@include('layouts.admin-header')

<main class="lg:ml-64 min-h-screen bg-light-gray">
    
    <header class="bg-white shadow-sm p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-primary-dark">User Management</h1>
                <p class="text-secondary-blue mt-1">Manage all system users - Total: {{ $users->total() }}</p>
            </div>
            <div class="mt-4 sm:mt-0">
                <a href="{{ route('admin.users.create') }}" class="btn-primary inline-flex items-center px-6 py-3">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                    Add New User
                </a>
            </div>
        </div>
    </header>

    <div class="p-4 lg:p-6">
        
        {{-- Filters Panel --}}
        <div class="card p-4 mb-6">
            <details class="group">
                <summary class="flex items-center justify-between cursor-pointer list-none">
                    <h3 class="text-lg font-medium text-primary-dark flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v10a1 1 0 01-1 1H4a1 1 0 01-1-1V10zM15 10a1 1 0 011-1h2a1 1 0 011 1v10a1 1 0 01-1 1h-2a1 1 0 01-1-1V10z"></path></svg>
                        Filter & Search Options
                    </h3>
                    <svg class="w-5 h-5 transform transition-transform duration-200 group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </summary>
                <form method="GET" action="{{ route('admin.users.index') }}" class="mt-4 animate-fade-in">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Name, Email, or ID..." class="input-field lg:col-span-2">
                        <select name="role" class="input-field">
                            <option value="all">All Roles</option>
                            <option value="student" @if(request('role') == 'student') selected @endif>Student</option>
                            <option value="instructor" @if(request('role') == 'instructor') selected @endif>Instructor</option>
                            <option value="sales" @if(request('role') == 'sales') selected @endif>Sales Staff</option>
                            <option value="all_around_staff" @if(request('role') == 'all_around_staff') selected @endif>All-Around Staff</option>
                        </select>
                        <select name="status" class="input-field">
                            <option value="all">All Status</option>
                            <option value="active" @if(request('status') == 'active') selected @endif>Active</option>
                            <option value="inactive" @if(request('status') == 'inactive') selected @endif>Inactive</option>
                        </select>
                        <select name="date_filter_by" class="input-field">
                            <option value="created_at" @if(request('date_filter_by') == 'created_at') selected @endif>Filter by Created Date</option>
                            <option value="updated_at" @if(request('date_filter_by') == 'updated_at') selected @endif>Filter by Updated Date</option>
                        </select>
                        <input type="date" name="date_from" value="{{ request('date_from') }}" class="input-field" title="Date from">
                        <input type="date" name="date_to" value="{{ request('date_to') }}" class="input-field" title="Date to">
                    </div>
                    <div class="flex flex-wrap gap-3 mt-4">
                        <button type="submit" class="btn-primary px-6 py-2">Apply Filters</button>
                        <a href="{{ route('admin.users.index') }}" class="btn-secondary px-6 py-2">Reset</a>
                    </div>
                </form>
            </details>
        </div>

        {{-- Bulk Actions Bar --}}
        <div id="bulk-actions-bar" class="card p-4 mb-6 hidden"></div>

        {{-- Users Table --}}
        <div class="card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-gradient-to-r from-primary-dark to-secondary-blue text-accent-yellow">
                        <tr>
                            <th class="p-4 w-4"><input type="checkbox" id="select-all" onclick="toggleSelectAll(this)"></th>
                            <th class="p-4">ID</th>
                            <th class="p-4">Name</th>
                            <th class="p-4">Email</th>
                            <th class="p-4">Role</th>
                            <th class="p-4">Status</th>
                            <th class="p-4">Last Login</th>
                            <th class="p-4">Created</th>
                            <th class="p-4">Updated</th>
                            <th class="p-4 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm" id="users-tbody">
                        @forelse ($users as $user)
                            @include('partials.user-row', ['user' => $user])
                        @empty
                            <tr><td colspan="10" class="p-12 text-center text-gray-500">No users found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($users->hasPages())
                <div class="p-4 border-t bg-gray-50">{{ $users->appends(request()->query())->links() }}</div>
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

</body>
</html>
