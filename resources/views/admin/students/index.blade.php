{{--
    ============================================================================
    STUDENT MANAGEMENT PAGE - resources/views/admin/students/index.blade.php
    ============================================================================
    Features:
    - Student list table with status indicators and payment badges
    - Advanced filters (status, instrument, genre, enrollment, payment, search)
    - Student detail modal with multiple tabs
    - Responsive design with clean, professional styling
    - Bulk actions for status updates
    ============================================================================
--}}

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Student Management - Admin Dashboard</title>
    @vite(['resources/css/style.css', 'resources/js/app.js', 'resources/js/admin-pages.js'])
</head>
<body class="bg-light-gray">

@include('layouts.admin-header')

<main class="lg:ml-64 min-h-screen bg-light-gray">

    {{-- Page Header --}}
    <header class="bg-white shadow-sm p-6 border-b-4 border-secondary-blue">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <h1 class="text-3xl font-bold text-primary-dark">Student management</h1>
                <p class="text-secondary-blue mt-1">Manage student records, enrollments, and progress</p>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('admin.users.create') }}?role=student" class="bg-forest-green text-white px-6 py-3 rounded-lg font-semibold hover:bg-forest-green-dark transition-all shadow-lg">
                    <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Add new student
                </a>
            </div>
        </div>
    </header>

    <div class="p-4 lg:p-6">

        {{-- Filters Section --}}
        <div class="card p-6 mb-6">
            <form method="GET" action="{{ route('admin.students.index') }}" class="space-y-4">
                
                {{-- First Row: Search and Quick Filters --}}
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Search</label>
                        <input type="text" name="search" value="{{ request('search') }}" 
                               placeholder="Search by name, email, or phone..." 
                               class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-secondary-blue focus:ring-2 focus:ring-secondary-blue">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Status</label>
                        <select name="status" class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-secondary-blue">
                            <option value="all">All statuses</option>
                            @foreach($statuses as $status)
                                <option value="{{ $status->status_id }}" {{ request('status') == $status->status_id ? 'selected' : '' }}>
                                    {{ $status->status_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Instrument</label>
                        <select name="instrument" class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-secondary-blue">
                            <option value="all">All instruments</option>
                            @foreach($instruments as $instrument)
                                <option value="{{ $instrument->instrument_id }}" {{ request('instrument') == $instrument->instrument_id ? 'selected' : '' }}>
                                    {{ $instrument->instrument_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Second Row: More Filters --}}
                <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Genre</label>
                        <select name="genre" class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-secondary-blue">
                            <option value="all">All genres</option>
                            @foreach($genres as $genre)
                                <option value="{{ $genre->genre_id }}" {{ request('genre') == $genre->genre_id ? 'selected' : '' }}>
                                    {{ $genre->genre_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Enrollment</label>
                        <select name="enrollment_status" class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-secondary-blue">
                            <option value="all">All</option>
                            <option value="active" {{ request('enrollment_status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="completed" {{ request('enrollment_status') == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="cancelled" {{ request('enrollment_status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Payment</label>
                        <select name="payment_status" class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-secondary-blue">
                            <option value="all">All</option>
                            <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>Paid</option>
                            <option value="partial" {{ request('payment_status') == 'partial' ? 'selected' : '' }}>Partial</option>
                            <option value="pending" {{ request('payment_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Sort by</label>
                        <select name="sort_by" class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-secondary-blue">
                            <option value="name" {{ request('sort_by') == 'name' ? 'selected' : '' }}>Name</option>
                            <option value="enrollment_date" {{ request('sort_by') == 'enrollment_date' ? 'selected' : '' }}>Enrollment date</option>
                            <option value="last_lesson" {{ request('sort_by') == 'last_lesson' ? 'selected' : '' }}>Last lesson</option>
                            <option value="sessions" {{ request('sort_by') == 'sessions' ? 'selected' : '' }}>Remaining sessions</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Date from</label>
                        <input type="date" name="date_from" value="{{ request('date_from') }}" 
                               class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-secondary-blue">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Date to</label>
                        <input type="date" name="date_to" value="{{ request('date_to') }}" 
                               class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-secondary-blue">
                    </div>
                </div>

                {{-- Filter Actions --}}
                <div class="flex gap-3">
                    <button type="submit" class="bg-secondary-blue text-white px-6 py-2 rounded-lg font-semibold hover:bg-secondary-blue-dark transition-all">
                        Apply filters
                    </button>
                    <a href="{{ route('admin.students.index') }}" class="bg-gray-200 text-gray-700 px-6 py-2 rounded-lg font-semibold hover:bg-gray-300 transition-all">
                        Clear all
                    </a>
                </div>
            </form>
        </div>

        {{-- Statistics Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="card p-6 border-l-4 border-secondary-blue">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 font-semibold">Total students</p>
                        <p class="text-3xl font-bold text-primary-dark mt-1">{{ $students->total() }}</p>
                    </div>
                    <div class="bg-secondary-blue bg-opacity-20 p-3 rounded-full">
                        <svg class="w-8 h-8 text-secondary-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    </div>
                </div>
            </div>
            
            <div class="card p-6 border-l-4 border-forest-green">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 font-semibold">Active enrollments</p>
                        <p class="text-3xl font-bold text-primary-dark mt-1">{{ $students->sum('active_enrollments') }}</p>
                    </div>
                    <div class="bg-forest-green bg-opacity-20 p-3 rounded-full">
                        <svg class="w-8 h-8 text-forest-green" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                </div>
            </div>
            
            <div class="card p-6 border-l-4 border-golden-yellow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 font-semibold">Sessions remaining</p>
                        <p class="text-3xl font-bold text-primary-dark mt-1">{{ $students->sum('total_remaining_sessions') }}</p>
                    </div>
                    <div class="bg-golden-yellow bg-opacity-20 p-3 rounded-full">
                        <svg class="w-8 h-8 text-golden-yellow" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                </div>
            </div>
            
            <div class="card p-6 border-l-4 border-warm-coral">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 font-semibold">New this month</p>
                        <p class="text-3xl font-bold text-primary-dark mt-1">{{ $students->where('enrollment_date', '>=', now()->startOfMonth())->count() }}</p>
                    </div>
                    <div class="bg-warm-coral bg-opacity-20 p-3 rounded-full">
                        <svg class="w-8 h-8 text-warm-coral" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- Bulk Actions Bar (Hidden by default) --}}
        <div id="bulk-actions-bar" class="hidden mb-4"></div>

        {{-- Students Table --}}
        <div class="card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-primary-dark">
                        <tr>
                            <th class="px-6 py-4 text-left">
                                <input type="checkbox" id="select-all" class="checkbox-custom" onclick="toggleSelectAll(this)">
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-accent-yellow uppercase tracking-wider">Student</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-accent-yellow uppercase tracking-wider">Contact</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-accent-yellow uppercase tracking-wider">Instrument/Genre</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-accent-yellow uppercase tracking-wider">Enrollments</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-accent-yellow uppercase tracking-wider">Sessions</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-accent-yellow uppercase tracking-wider">Last lesson</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-accent-yellow uppercase tracking-wider">Status</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-accent-yellow uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($students as $student)
                        <tr class="hover:bg-accent-yellow-light transition-colors" id="student-row-{{ $student->student_id }}">
                            
                            {{-- Checkbox --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                <input type="checkbox" class="student-checkbox checkbox-custom" value="{{ $student->student_id }}" onclick="updateBulkActions()">
                            </td>
                            
                            {{-- Student Info --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10 bg-gradient-to-br from-secondary-blue to-forest-green rounded-full flex items-center justify-center text-white font-bold text-lg">
                                        {{ substr($student->first_name, 0, 1) }}{{ substr($student->last_name, 0, 1) }}
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-bold text-gray-900">{{ $student->first_name }} {{ $student->last_name }}</div>
                                        <div class="text-xs text-gray-500">ID: {{ $student->student_id }}</div>
                                    </div>
                                </div>
                            </td>
                            
                            {{-- Contact --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $student->email ?? 'N/A' }}</div>
                                <div class="text-xs text-gray-500">{{ $student->phone ?? 'N/A' }}</div>
                            </td>
                            
                            {{-- Instrument/Genre --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-semibold text-gray-900">{{ $student->instrument_name ?? 'None' }}</div>
                                <div class="text-xs text-gray-500">{{ $student->genre_name ?? 'No preference' }}</div>
                            </td>
                            
                            {{-- Enrollments --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold {{ $student->active_enrollments > 0 ? 'bg-forest-green text-white' : 'bg-gray-200 text-gray-700' }}">
                                    {{ $student->active_enrollments }} active
                                </span>
                            </td>
                            
                            {{-- Sessions --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-bold text-primary-dark">{{ $student->total_remaining_sessions }}</div>
                                <div class="text-xs text-gray-500">remaining</div>
                            </td>
                            
                            {{-- Last Lesson --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $student->last_lesson_date ? date('M d, Y', strtotime($student->last_lesson_date)) : 'No lessons yet' }}</div>
                            </td>
                            
                            {{-- Status --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex flex-col gap-1">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold 
                                        {{ $student->status_name == 'Active' ? 'bg-forest-green text-white' : '' }}
                                        {{ $student->status_name == 'Inactive' ? 'bg-gray-400 text-white' : '' }}
                                        {{ $student->status_name == 'Completed' ? 'bg-secondary-blue text-white' : '' }}
                                        {{ $student->status_name == 'Withdrawn' ? 'bg-warm-coral text-white' : '' }}
                                        {{ $student->status_name == 'On Hold' ? 'bg-golden-yellow text-white' : '' }}">
                                        {{ $student->status_name }}
                                    </span>
                                    
                                    @if($student->payment_status)
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold
                                            {{ $student->payment_status == 'paid' ? 'bg-forest-green text-white' : '' }}
                                            {{ $student->payment_status == 'partial' ? 'bg-golden-yellow text-white' : '' }}
                                            {{ $student->payment_status == 'pending' ? 'bg-warm-coral text-white' : '' }}
                                            {{ $student->payment_status == 'refunded' ? 'bg-gray-400 text-white' : '' }}">
                                            {{ ucfirst($student->payment_status) }}
                                        </span>
                                    @endif
                                </div>
                            </td>
                            
                            {{-- Actions --}}
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <div class="flex gap-2">
                                    <button onclick="viewStudent({{ $student->student_id }})" class="action-btn text-secondary-blue">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                        <span class="tooltip">View details</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="px-6 py-12 text-center text-gray-500">
                                <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                                <p class="text-lg font-semibold">No students found</p>
                                <p class="text-sm mt-2">Try adjusting your filters or add a new student</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            {{-- Pagination --}}
            @if($students->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                {{ $students->links() }}
            </div>
            @endif
        </div>
    </div>

</main>

{{-- Student Detail Modal (will be populated via JS) --}}
<div id="student-detail-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-4 overflow-y-auto"></div>

{{-- Bulk Status Update Modal (will be populated via JS) --}}
<div id="bulk-status-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-4"></div>

{{-- Toast Container --}}
<div id="toast-container" class="fixed bottom-6 right-6 z-50 space-y-3"></div>

</body>
</html>