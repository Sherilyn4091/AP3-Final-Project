{{--
    ============================================================================
    STUDENT MANAGEMENT PAGE - resources/views/admin/students/index.blade.php
    ============================================================================
    Features:
    - Compact admin UI sizing
    - Student list table with number column
    - Removed initials/avatar symbol before the name
    - Advanced filters
    - Student detail modal with JS
    - Bulk status update support
    ============================================================================
--}}

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Student Management - Admin Dashboard</title>

    @vite(['resources/css/style.css', 'resources/js/app.js', 'resources/js/admin-pages/student.js'])

    <style>
        /*
        |--------------------------------------------------------------------------
        | Admin Compact UI
        |--------------------------------------------------------------------------
        |
        | Keeps the existing colors but reduces oversized display.
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
    </style>
</head>

<body class="bg-light-gray admin-compact-page">

@include('layouts.admin-header')

<main class="lg:ml-64 min-h-screen bg-light-gray">

    {{-- Page Header --}}
    <header class="bg-white shadow-sm border-b-4 border-secondary-blue">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div>
                <h1 class="font-bold text-primary-dark">Student Management</h1>
                <p class="text-secondary-blue mt-1 text-sm">
                    Manage student records, enrollments, and progress
                </p>
            </div>

            <a href="{{ route('admin.users.create') }}?role=student"
               class="bg-forest-green text-white px-5 py-2.5 rounded-lg font-semibold hover:bg-forest-green-dark transition-all shadow-md inline-flex items-center">
                <svg class="w-4 h-4 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 4v16m8-8H4"></path>
                </svg>
                Add New Student
            </a>
        </div>
    </header>

    <div>
        {{-- Filters Section --}}
        <div class="card p-6 mb-5">
            <form method="GET" action="{{ route('admin.students.index') }}" class="space-y-4">

                {{-- First Row: Search and Quick Filters --}}
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Search</label>
                        <input type="text"
                               name="search"
                               value="{{ request('search') }}"
                               placeholder="Name, email, or phone..."
                               class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-secondary-blue focus:ring-2 focus:ring-secondary-blue text-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Status</label>
                        <select name="status" class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-secondary-blue text-sm">
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
                        <select name="instrument" class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-secondary-blue text-sm">
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
                <div class="grid grid-cols-2 md:grid-cols-6 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Genre</label>
                        <select name="genre" class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-secondary-blue text-sm">
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
                        <select name="enrollment_status" class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-secondary-blue text-sm">
                            <option value="all">All</option>
                            <option value="active" {{ request('enrollment_status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="completed" {{ request('enrollment_status') == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="cancelled" {{ request('enrollment_status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Payment</label>
                        <select name="payment_status" class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-secondary-blue text-sm">
                            <option value="all">All</option>
                            <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>Paid</option>
                            <option value="partial" {{ request('payment_status') == 'partial' ? 'selected' : '' }}>Partial</option>
                            <option value="pending" {{ request('payment_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Sort by</label>
                        <select name="sort_by" class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-secondary-blue text-sm">
                            <option value="name" {{ request('sort_by') == 'name' ? 'selected' : '' }}>Name</option>
                            <option value="enrollment_date" {{ request('sort_by') == 'enrollment_date' ? 'selected' : '' }}>Enrollment date</option>
                            <option value="last_lesson" {{ request('sort_by') == 'last_lesson' ? 'selected' : '' }}>Last lesson</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Date from</label>
                        <input type="date"
                               name="date_from"
                               value="{{ request('date_from') }}"
                               class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-secondary-blue text-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Date to</label>
                        <input type="date"
                               name="date_to"
                               value="{{ request('date_to') }}"
                               class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-secondary-blue text-sm">
                    </div>
                </div>

                {{-- Filter Actions --}}
                <div class="flex gap-3">
                    <button type="submit"
                            class="bg-secondary-blue text-white px-5 py-2 rounded-lg font-semibold hover:bg-secondary-blue-dark transition-all text-sm">
                        Apply filters
                    </button>

                    <a href="{{ route('admin.students.index') }}"
                       class="bg-gray-200 text-gray-700 px-5 py-2 rounded-lg font-semibold hover:bg-gray-300 transition-all text-sm">
                        Clear all
                    </a>
                </div>
            </form>
        </div>

        {{-- Statistics Cards --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-5">
            <div class="card p-6 border-l-4 border-secondary-blue">
                <p class="text-sm text-gray-600 font-semibold">Total students</p>
                <p class="text-3xl font-bold text-primary-dark mt-1">{{ $students->total() }}</p>
            </div>

            <div class="card p-6 border-l-4 border-forest-green">
                <p class="text-sm text-gray-600 font-semibold">Active enrollments</p>
                <p class="text-3xl font-bold text-primary-dark mt-1">{{ $students->sum('active_enrollments') }}</p>
            </div>

            <div class="card p-6 border-l-4 border-golden-yellow">
                <p class="text-sm text-gray-600 font-semibold">Sessions remaining</p>
                <p class="text-3xl font-bold text-primary-dark mt-1">{{ $students->sum('total_remaining_sessions') }}</p>
            </div>

            <div class="card p-6 border-l-4 border-warm-coral">
                <p class="text-sm text-gray-600 font-semibold">New this month</p>
                <p class="text-3xl font-bold text-primary-dark mt-1">
                    {{ $students->where('created_at', '>=', now()->startOfMonth())->count() }}
                </p>
            </div>
        </div>

        {{-- Bulk Actions Bar --}}
        <div id="bulk-actions-bar" class="hidden mb-4"></div>

        {{-- Students Table --}}
        <div class="card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-primary-dark">
                        <tr>
                            <th class="text-left">
                                <input type="checkbox" id="select-all" class="checkbox-custom" onclick="toggleSelectAll(this)">
                            </th>
                            <th class="text-left text-xs font-bold text-accent-yellow uppercase tracking-wider">#</th>
                            <th class="text-left text-xs font-bold text-accent-yellow uppercase tracking-wider">Student</th>
                            <th class="text-left text-xs font-bold text-accent-yellow uppercase tracking-wider hidden md:table-cell">Contact</th>
                            <th class="text-left text-xs font-bold text-accent-yellow uppercase tracking-wider hidden lg:table-cell">Instrument</th>
                            <th class="text-left text-xs font-bold text-accent-yellow uppercase tracking-wider">Status</th>
                            <th class="text-left text-xs font-bold text-accent-yellow uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($students as $student)
                            <tr class="hover:bg-accent-yellow-light transition-colors" id="student-row-{{ $student->student_id }}">
                                {{-- Checkbox --}}
                                <td class="whitespace-nowrap">
                                    <input type="checkbox"
                                           class="student-checkbox checkbox-custom"
                                           value="{{ $student->student_id }}"
                                           onclick="updateBulkActions()">
                                </td>

                                {{-- Row Number --}}
                                <td class="whitespace-nowrap text-sm font-bold text-gray-700">
                                    {{ $loop->iteration + (($students->currentPage() - 1) * $students->perPage()) }}
                                </td>

                                {{-- Student Info --}}
                                <td>
                                    <div>
                                        <div class="text-sm font-bold text-gray-900">
                                            {{ trim(($student->first_name ?? '') . ' ' . ($student->last_name ?? '')) ?: 'No name' }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            ID: {{ $student->student_id }}
                                        </div>
                                    </div>
                                </td>

                                {{-- Contact --}}
                                <td class="whitespace-nowrap hidden md:table-cell">
                                    <div class="text-sm text-gray-900">{{ $student->email ?? 'N/A' }}</div>
                                    <div class="text-xs text-gray-500">{{ $student->phone ?? 'N/A' }}</div>
                                </td>

                                {{-- Instrument --}}
                                <td class="whitespace-nowrap hidden lg:table-cell">
                                    <div class="text-sm font-semibold text-gray-900">{{ $student->instrument_name ?? 'None' }}</div>
                                    <div class="text-xs text-gray-500">{{ $student->genre_name ?? 'No preference' }}</div>
                                </td>

                                {{-- Status --}}
                                <td class="whitespace-nowrap">
                                    @php
                                        $statusClass = match($student->status_name) {
                                            'Active' => 'bg-forest-green text-white',
                                            'Inactive' => 'bg-gray-400 text-white',
                                            'Completed' => 'bg-secondary-blue text-white',
                                            default => 'bg-gray-200 text-gray-700',
                                        };
                                    @endphp

                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $statusClass }}">
                                        {{ $student->status_name ?? 'N/A' }}
                                    </span>
                                </td>

                                {{-- Actions --}}
                                <td class="whitespace-nowrap text-sm">
                                    <button onclick="viewStudent({{ $student->student_id }})"
                                            class="text-secondary-blue hover:text-secondary-blue-dark font-semibold">
                                        View
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                    <p class="text-lg font-semibold">No students found</p>
                                    <p class="text-sm mt-2">Try adjusting your filters or add a new student.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($students->hasPages())
                <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                    {{ $students->links() }}
                </div>
            @endif
        </div>
    </div>
</main>

{{-- Student Detail Modal --}}
<div id="student-detail-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-4 overflow-y-auto"></div>

{{-- Bulk Status Update Modal --}}
<div id="bulk-status-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-4"></div>

{{-- Toast Container --}}
<div id="toast-container" class="fixed top-20 right-4 z-[100] space-y-2"></div>

</body>
</html>