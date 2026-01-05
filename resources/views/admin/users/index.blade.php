{{-- resources/views/admin/users/index.blade.php --}}
{{-- 
    ============================================================================
    USER MANAGEMENT PAGE - COMPLETE
    CRUD operations for all user types with advanced filtering
    Features: Search by name/email/ID, Modal view, Bulk actions, Color harmony
    ============================================================================
--}}

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>User Management - Admin Dashboard</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-light-gray">

@include('layouts.admin-header')

<main class="lg:ml-64 min-h-screen bg-light-gray">
    
    {{-- Page Header --}}
    <header class="bg-white shadow-sm p-6 lg:p-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl lg:text-3xl font-bold text-primary-dark">User Management</h1>
                <p class="text-secondary-blue mt-1">Manage all system users - Total: {{ $users->total() }}</p>
            </div>
            <div class="mt-4 sm:mt-0">
                <a href="{{ route('admin.users.create') }}" class="btn-primary inline-flex items-center px-6 py-3 hover:scale-105 transition-transform">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                    </svg>
                    Add New User
                </a>
            </div>
        </div>
    </header>

    <div class="p-4 lg:p-8">
        
        {{-- ============================================================================
            FILTERS SECTION
            ============================================================================ --}}
        <div class="card p-4 lg:p-6 mb-6">
            <form method="GET" action="{{ route('admin.users.index') }}" class="space-y-4">
                
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                    
                    {{-- Role Filter --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Role</label>
                        <select name="role" class="input-field">
                            <option value="all" {{ request('role') == 'all' ? 'selected' : '' }}>All Roles</option>
                            <option value="student" {{ request('role') == 'student' ? 'selected' : '' }}>Student</option>
                            <option value="instructor" {{ request('role') == 'instructor' ? 'selected' : '' }}>Instructor</option>
                            <option value="sales" {{ request('role') == 'sales' ? 'selected' : '' }}>Sales Staff</option>
                            <option value="all_around_staff" {{ request('role') == 'all_around_staff' ? 'selected' : '' }}>All-Around Staff</option>
                        </select>
                    </div>

                    {{-- Status Filter --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select name="status" class="input-field">
                            <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>All Status</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>

                    {{-- Search by Name/Email/ID --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Name, Email, or ID..." class="input-field">
                    </div>

                    {{-- Date From --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Date From</label>
                        <input type="date" name="date_from" value="{{ request('date_from') }}" class="input-field">
                    </div>

                    {{-- Date To --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Date To</label>
                        <input type="date" name="date_to" value="{{ request('date_to') }}" class="input-field">
                    </div>
                </div>

                {{-- Filter Buttons --}}
                <div class="flex flex-wrap gap-3">
                    <button type="submit" class="btn-primary px-6 py-2">
                        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        Apply Filters
                    </button>
                    <a href="{{ route('admin.users.index') }}" class="btn-secondary px-6 py-2">
                        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Reset
                    </a>
                </div>
            </form>
        </div>

        {{-- ============================================================================
            BULK ACTIONS BAR (Shows when users are selected)
            ============================================================================ --}}
        <div id="bulk-actions-bar" class="card p-4 mb-6 hidden bg-warm-coral bg-opacity-10 border-l-4 border-warm-coral">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-4">
                    <span id="selected-count" class="text-sm font-medium text-primary-dark">0 users selected</span>
                </div>
                <div class="flex gap-3">
                    <button onclick="bulkDeactivate()" class="btn-secondary px-4 py-2 text-sm hover:bg-warm-coral hover:text-white transition-colors">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                        </svg>
                        Deactivate Selected
                    </button>
                    <button onclick="bulkDelete()" class="btn-secondary px-4 py-2 text-sm hover:bg-red-600 hover:text-white transition-colors">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        Delete Selected
                    </button>
                    <button onclick="clearSelection()" class="btn-secondary px-4 py-2 text-sm">Clear Selection</button>
                </div>
            </div>
        </div>

        {{-- ============================================================================
            USERS TABLE WITH COLOR HARMONY
            ============================================================================ --}}
        <div class="card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-gradient-to-r from-primary-dark to-secondary-blue text-accent-yellow">
                        <tr>
                            <th class="px-4 py-3 text-sm font-semibold">
                                <input type="checkbox" id="select-all" onclick="toggleSelectAll(this)" class="w-4 h-4 rounded border-gray-300 cursor-pointer">
                            </th>
                            <th class="px-4 py-3 text-sm font-semibold">User ID</th>
                            <th class="px-4 py-3 text-sm font-semibold">Name</th>
                            <th class="px-4 py-3 text-sm font-semibold">Email</th>
                            <th class="px-4 py-3 text-sm font-semibold">Role</th>
                            <th class="px-4 py-3 text-sm font-semibold">Status</th>
                            <th class="px-4 py-3 text-sm font-semibold text-center">Super Admin</th>
                            <th class="px-4 py-3 text-sm font-semibold">Last Login</th>
                            <th class="px-4 py-3 text-sm font-semibold">Created</th>
                            <th class="px-4 py-3 text-sm font-semibold text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm">
                        @forelse ($users as $index => $user)
                            {{-- Alternating row colors for professional look --}}
                            <tr class="border-b transition-all duration-200 {{ $index % 2 == 0 ? 'bg-white hover:bg-accent-yellow hover:bg-opacity-20' : 'bg-gray-50 hover:bg-accent-yellow hover:bg-opacity-20' }}">
                                {{-- Checkbox --}}
                                <td class="px-4 py-3">
                                    <input type="checkbox" class="user-checkbox w-4 h-4 rounded border-gray-300 cursor-pointer" value="{{ $user->user_id }}" onchange="updateBulkActions()">
                                </td>

                                {{-- User ID --}}
                                <td class="px-4 py-3 font-bold text-primary-dark">{{ $user->user_id }}</td>

                                {{-- Name with role-based color accent --}}
                                <td class="px-4 py-3 font-semibold 
                                    {{ $user->user_role === 'student' ? 'text-golden-yellow' : '' }}
                                    {{ $user->user_role === 'instructor' ? 'text-warm-coral' : '' }}
                                    {{ $user->user_role === 'sales' ? 'text-forest-green' : '' }}
                                    {{ $user->user_role === 'all_around_staff' ? 'text-secondary-blue' : '' }}">
                                    {{ $user->full_name }}
                                </td>

                                {{-- Email --}}
                                <td class="px-4 py-3 text-secondary-blue">{{ $user->user_email }}</td>

                                {{-- Role Badge with Professional Colors --}}
                                <td class="px-4 py-3">
                                    <span class="px-3 py-1 rounded-full text-xs font-bold
                                        @if($user->user_role === 'student') bg-golden-yellow bg-opacity-20 text-golden-yellow border border-golden-yellow
                                        @elseif($user->user_role === 'instructor') bg-warm-coral bg-opacity-20 text-warm-coral border border-warm-coral
                                        @elseif($user->user_role === 'sales') bg-forest-green bg-opacity-20 text-forest-green border border-forest-green
                                        @else bg-secondary-blue bg-opacity-20 text-secondary-blue border border-secondary-blue
                                        @endif">
                                        {{ ucfirst(str_replace('_', ' ', $user->user_role)) }}
                                    </span>
                                </td>

                                {{-- Status Badge --}}
                                <td class="px-4 py-3">
                                    <span class="px-3 py-1 rounded-full text-xs font-bold
                                        {{ $user->is_active ? 'bg-green-100 text-green-800 border border-green-300' : 'bg-red-100 text-red-800 border border-red-300' }}">
                                        {{ $user->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>

                                {{-- Super Admin Star --}}
                                <td class="px-4 py-3 text-center">
                                    @if($user->is_super_admin)
                                        <svg class="w-5 h-5 text-golden-yellow inline" fill="currentColor" viewBox="0 0 20 20" title="Super Admin">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                    @else
                                        <span class="text-gray-300">—</span>
                                    @endif
                                </td>

                                {{-- Last Login --}}
                                <td class="px-4 py-3 text-xs text-gray-600">
                                    {{ $user->last_login ? \Carbon\Carbon::parse($user->last_login)->diffForHumans() : 'Never' }}
                                </td>

                                {{-- Created At --}}
                                <td class="px-4 py-3 text-xs text-gray-600">
                                    {{ \Carbon\Carbon::parse($user->created_at)->format('M d, Y') }}
                                </td>

                                {{-- Action Icons --}}
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-center gap-2">
                                        {{-- View Button --}}
                                        <button onclick="viewUser({{ $user->user_id }})" 
                                                class="p-1 text-secondary-blue hover:text-white hover:bg-secondary-blue rounded transition-all" 
                                                title="View Details">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                        </button>

                                        {{-- Reset Password Button --}}
                                        <button onclick="resetPassword({{ $user->user_id }})" 
                                                class="p-1 text-golden-yellow hover:text-white hover:bg-golden-yellow rounded transition-all" 
                                                title="Reset Password">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                                            </svg>
                                        </button>

                                        {{-- Deactivate/Activate Button --}}
                                        @if($user->is_active)
                                            <button onclick="deactivateUser({{ $user->user_id }})" 
                                                    class="p-1 text-warm-coral hover:text-white hover:bg-warm-coral rounded transition-all" 
                                                    title="Deactivate">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                                </svg>
                                            </button>
                                        @else
                                            <button onclick="activateUser({{ $user->user_id }})" 
                                                    class="p-1 text-forest-green hover:text-white hover:bg-forest-green rounded transition-all" 
                                                    title="Activate">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                            </button>
                                        @endif

                                        {{-- Delete Button --}}
                                        <button onclick="deleteUser({{ $user->user_id }})" 
                                                class="p-1 text-red-600 hover:text-white hover:bg-red-600 rounded transition-all" 
                                                title="Delete">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="px-4 py-12 text-center">
                                    <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                    </svg>
                                    <p class="text-gray-500 font-medium">No users found</p>
                                    <p class="text-gray-400 text-sm mt-1">Try adjusting your filters</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($users->hasPages())
                <div class="p-4 border-t bg-gray-50">
                    {{ $users->appends(request()->query())->links() }}
                </div>
            @endif
        </div>

        {{-- Back to Top Button --}}
        <button onclick="window.scrollTo({top: 0, behavior: 'smooth'})" 
                class="fixed bottom-8 right-8 p-4 bg-gradient-to-r from-primary-dark to-secondary-blue text-accent-yellow rounded-full shadow-2xl hover:shadow-3xl transition-all hover:scale-110 z-40"
                title="Back to Top">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
            </svg>
        </button>

    </div>

    {{-- Footer --}}
    <footer class="bg-white border-t border-gray-200 py-4 text-center mt-8">
        <p class="text-xs text-gray-500">© {{ date('Y') }} Music Lab. All rights reserved.</p>
    </footer>

</main>

{{-- ============================================================================
    TOAST NOTIFICATION CONTAINER
    ============================================================================ --}}
<div id="toast-container" class="fixed top-20 right-4 z-50 space-y-2"></div>

{{-- ============================================================================
    VIEW USER MODAL
    ============================================================================ --}}
<div id="view-user-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto shadow-2xl">
        <div class="sticky top-0 bg-gradient-to-r from-primary-dark to-secondary-blue text-accent-yellow border-b p-6 flex items-center justify-between rounded-t-2xl">
            <h3 class="text-2xl font-bold flex items-center">
                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                User Details
            </h3>
            <button onclick="closeModal()" class="text-accent-yellow hover:text-white transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div id="modal-content" class="p-6">
            {{-- Loading state --}}
            <div class="text-center py-12">
                <svg class="animate-spin h-12 w-12 mx-auto text-secondary-blue" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <p class="mt-4 text-secondary-blue font-medium">Loading user details...</p>
            </div>
        </div>
    </div>
</div>

{{-- ============================================================================
    RESET PASSWORD MODAL
    ============================================================================ --}}
<div id="reset-password-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl">
        <h3 class="text-xl font-bold text-primary-dark mb-4 flex items-center">
            <svg class="w-6 h-6 mr-2 text-golden-yellow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
            </svg>
            New Password Generated
        </h3>
        <div class="bg-golden-yellow bg-opacity-20 border-2 border-golden-yellow rounded-lg p-4 mb-4">
            <p class="text-sm text-gray-700 mb-2 font-medium">Copy this password and share it with the user:</p>
            <div class="flex items-center gap-2">
                <input type="text" id="generated-password" readonly class="input-field flex-1 font-mono font-bold text-primary-dark text-center text-lg">
                <button onclick="copyPassword()" class="btn-secondary px-4 py-2 hover:bg-golden-yellow hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                    </svg>
                </button>
            </div>
            <p class="text-xs text-gray-600 mt-2">⚠️ Make sure to save this password - it will not be shown again</p>
        </div>
        <button onclick="closeResetModal()" class="btn-primary w-full">Done</button>
    </div>
</div>

<script>
/**
 * ============================================================================
 * USER MANAGEMENT JAVASCRIPT - COMPLETE
 * All functions for CRUD operations with toast notifications
 * ============================================================================
 */

// CSRF Token for all AJAX requests
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

/**
 * ============================================================================
 * SELECTION FUNCTIONS
 * ============================================================================
 */

/**
 * Toggle select all checkboxes
 */
function toggleSelectAll(checkbox) {
    document.querySelectorAll('.user-checkbox').forEach(cb => {
        cb.checked = checkbox.checked;
    });
    updateBulkActions();
}

/**
 * Update bulk actions bar visibility based on selection
 */
function updateBulkActions() {
    const selected = document.querySelectorAll('.user-checkbox:checked');
    const bulkBar = document.getElementById('bulk-actions-bar');
    const count = document.getElementById('selected-count');
    
    if (selected.length > 0) {
        bulkBar.classList.remove('hidden');
        count.textContent = `${selected.length} user${selected.length > 1 ? 's' : ''} selected`;
    } else {
        bulkBar.classList.add('hidden');
    }
}

/**
 * Clear all selections
 */
function clearSelection() {
    document.querySelectorAll('.user-checkbox').forEach(cb => cb.checked = false);
    document.getElementById('select-all').checked = false;
    updateBulkActions();
}

/**
 * ============================================================================
 * MODAL & NOTIFICATION FUNCTIONS
 * ============================================================================
 */

const viewModal = document.getElementById('view-user-modal');
const resetModal = document.getElementById('reset-password-modal');
const modalContent = document.getElementById('modal-content');

function openModal() {
    viewModal.classList.remove('hidden');
    viewModal.classList.add('flex');
}

function closeModal() {
    viewModal.classList.add('hidden');
    viewModal.classList.remove('flex');
    modalContent.innerHTML = `
        <div class="text-center py-12">
            <svg class="animate-spin h-12 w-12 mx-auto text-secondary-blue" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <p class="mt-4 text-secondary-blue font-medium">Loading user details...</p>
        </div>
    `;
}

function openResetModal() {
    resetModal.classList.remove('hidden');
    resetModal.classList.add('flex');
}

function closeResetModal() {
    resetModal.classList.add('hidden');
    resetModal.classList.remove('flex');
}

/**
 * Show toast notification
 */
function showToast(message, type = 'success') {
    const container = document.getElementById('toast-container');
    const toast = document.createElement('div');
    const bgColor = type === 'success' ? 'bg-forest-green' : 'bg-red-600';
    const icon = type === 'success' ? 
        `<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>` :
        `<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>`;

    toast.className = `flex items-center gap-4 p-4 rounded-lg shadow-lg text-white ${bgColor} animate-fade-in`;
    toast.innerHTML = `${icon}<span>${message}</span>`;
    container.appendChild(toast);

    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(-20px)';
        setTimeout(() => toast.remove(), 300);
    }, 5000);
}

/**
 * ============================================================================
 * API CALLS & CRUD ACTIONS
 * ============================================================================
 */

async function viewUser(userId) {
    openModal();
    try {
        const response = await fetch(`/admin/users/${userId}`, {
            headers: { 'Accept': 'application/json' }
        });
        if (!response.ok) throw new Error('User not found');
        const data = await response.json();
        
        // Populate modal with user data
        modalContent.innerHTML = `
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="md:col-span-1 space-y-4">
                    <h4 class="font-bold text-lg text-primary-dark">${data.full_name}</h4>
                    <p class="text-sm text-secondary-blue">${data.user.user_email}</p>
                    <div class="flex items-center gap-2">
                        <span class="px-3 py-1 rounded-full text-xs font-bold ${data.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">${data.is_active ? 'Active' : 'Inactive'}</span>
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-secondary-blue bg-opacity-20 text-secondary-blue">${data.user.user_role.replace('_', ' ')}</span>
                    </div>
                </div>
                <div class="md:col-span-2 grid grid-cols-2 gap-4">
                    <div><strong class="block text-gray-600">User ID:</strong> ${data.user.user_id}</div>
                    <div><strong class="block text-gray-600">Last Login:</strong> ${data.user.last_login ? new Date(data.user.last_login).toLocaleString() : 'Never'}</div>
                    <div><strong class="block text-gray-600">Member Since:</strong> ${new Date(data.user.created_at).toLocaleDateString()}</div>
                    ${data.roleData.phone ? `<div><strong class="block text-gray-600">Phone:</strong> ${data.roleData.phone}</div>` : ''}
                </div>
            </div>
            <div class="border-t my-6"></div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h5 class="font-bold text-md mb-2 text-primary-dark">Activity</h5>
                    <ul class="space-y-1 text-sm">
                        ${Object.entries(data.activityData).map(([key, value]) => `<li><strong class="capitalize">${key.replace('_', ' ')}:</strong> ${value}</li>`).join('')}
                    </ul>
                </div>
                <div>
                    <h5 class="font-bold text-md mb-2 text-primary-dark">Personal Info</h5>
                    <ul class="space-y-1 text-sm">
                        ${data.roleData.gender ? `<li><strong>Gender:</strong> ${data.roleData.gender}</li>` : ''}
                        ${data.roleData.date_of_birth ? `<li><strong>DoB:</strong> ${new Date(data.roleData.date_of_birth).toLocaleDateString()}</li>` : ''}
                        ${data.roleData.address_line1 ? `<li><strong>Address:</strong> ${data.roleData.address_line1}, ${data.roleData.city}</li>` : ''}
                    </ul>
                </div>
            </div>
        `;
    } catch (error) {
        showToast(error.message, 'error');
        closeModal();
    }
}

async function resetPassword(userId) {
    if (!confirm('Are you sure you want to reset the password for this user?')) return;

    try {
        const response = await fetch(`/admin/users/${userId}/reset-password`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        });
        const data = await response.json();
        if (!data.success) throw new Error(data.message || 'Failed to reset password');
        
        document.getElementById('generated-password').value = data.password;
        openResetModal();
        showToast('Password has been reset.');
    } catch (error) {
        showToast(error.message, 'error');
    }
}

async function deactivateUser(userId) {
    if (!confirm('Are you sure you want to deactivate this user?')) return;
    
    try {
        const response = await fetch(`/admin/users/${userId}/deactivate`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
        });
        const data = await response.json();
        if (!data.success) throw new Error('Failed to deactivate user');
        
        showToast('User deactivated successfully.');
        setTimeout(() => window.location.reload(), 1500);
    } catch (error) {
        showToast(error.message, 'error');
    }
}
async function activateUser(userId) {
    if (!confirm('Are you sure you want to activate this user?')) return;
    
    try {
        const response = await fetch(`/admin/users/${userId}/activate`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
        });
        const data = await response.json();
        if (!data.success) throw new Error('Failed to activate user');
        
        showToast('User activated successfully.');
        setTimeout(() => window.location.reload(), 1500);
    } catch (error) {
        showToast(error.message, 'error');
    }
}

async function deleteUser(userId) {
    // Fetch impact first
    const impactRes = await fetch(`/admin/users/${userId}/deletion-impact`);
    const impactData = await impactRes.json();
    let impactHtml = Object.entries(impactData.impact).map(([key, value]) => `<li><strong>${value}</strong> ${key}</li>`).join('');
    
    if (confirm(`Are you sure you want to permanently delete this user? This will also delete:\n${impactHtml.replace(/<li><strong>/g, '- ').replace(/<\/strong> /g, ' ').replace(/<\/li>/g, '\n')}`)) {
        try {
            const response = await fetch(`/admin/users/${userId}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': csrfToken },
            });
            const data = await response.json();
            if (!data.success) throw new Error('Failed to delete user');

            showToast('User deleted successfully.');
            setTimeout(() => window.location.reload(), 1500);
        } catch (error) {
            showToast(error.message, 'error');
        }
    }
}

/**
 * ============================================================================
 * BULK ACTIONS
 * ============================================================================
 */

function getSelectedUserIds() {
    return Array.from(document.querySelectorAll('.user-checkbox:checked')).map(cb => cb.value);
}

async function bulkDeactivate() {
    const userIds = getSelectedUserIds();
    if (userIds.length === 0) return showToast('No users selected', 'error');
    
    if (confirm(`Are you sure you want to deactivate ${userIds.length} users?`)) {
        try {
            const response = await fetch('/admin/users/bulk-deactivate', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ user_ids: userIds })
            });
            const data = await response.json();
            if (!data.success) throw new Error('Bulk deactivation failed');
            
            showToast(data.message);
            setTimeout(() => window.location.reload(), 1500);
        } catch (error) {
            showToast(error.message, 'error');
        }
    }
}

async function bulkDelete() {
    const userIds = getSelectedUserIds();
    if (userIds.length === 0) return showToast('No users selected', 'error');

    if (confirm(`Are you sure you want to permanently delete ${userIds.length} users? This action cannot be undone.`)) {
        try {
            const response = await fetch('/admin/users/bulk-destroy', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ user_ids: userIds })
            });
            const data = await response.json();
            if (!data.success) throw new Error('Bulk delete failed');

            showToast(data.message);
            setTimeout(() => window.location.reload(), 1500);
        } catch (error) {
            showToast(error.message, 'error');
        }
    }
}

function copyPassword() {
    const passwordInput = document.getElementById('generated-password');
    passwordInput.select();
    document.execCommand('copy');
    showToast('Password copied to clipboard!');
}

</script>
</body>
</html>
