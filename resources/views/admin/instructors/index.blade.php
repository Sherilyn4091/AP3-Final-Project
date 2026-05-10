{{--
    ============================================================================
    INSTRUCTOR MANAGEMENT PAGE - resources/views/admin/instructors/index.blade.php
    ============================================================================
    Features:
    - Compact admin UI sizing
    - Instructor list table with number column
    - Removed initials/avatar symbol before the name
    - Advanced filters
    - Instructor detail modal
    - Specialization management modal
    - Availability editor modal
    - Performance report modal
    ============================================================================
--}}

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Instructor Management - Admin Dashboard</title>

    @vite(['resources/css/style.css', 'resources/js/app.js', 'resources/js/admin-pages.js'])

    <style>
        /*
        |--------------------------------------------------------------------------
        | Admin Compact UI
        |--------------------------------------------------------------------------
        |
        | Keeps the existing colors but reduces oversized display.
        | This makes cards, filters, buttons, table rows, and page headers closer
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
    </style>
</head>

<body class="bg-light-gray admin-compact-page">

@include('layouts.admin-header')

<main class="lg:ml-64 min-h-screen bg-light-gray">

    {{-- Page Header --}}
    <header class="bg-white shadow-sm border-b-4 border-warm-coral">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <h1 class="font-bold text-primary-dark">Instructor Management</h1>
                <p class="text-secondary-blue mt-1 text-sm">
                    Manage instructors, specializations, and performance metrics
                </p>
            </div>

            <a href="{{ route('admin.users.create') }}?role=instructor"
               class="bg-forest-green text-white px-5 py-2.5 rounded-lg font-semibold hover:bg-forest-green-dark transition-all shadow-md inline-flex items-center">
                <svg class="w-4 h-4 inline-block mr-2"
                     fill="none"
                     stroke="currentColor"
                     viewBox="0 0 24 24">
                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M12 4v16m8-8H4">
                    </path>
                </svg>
                Add New Instructor
            </a>
        </div>
    </header>

    <div>
        {{-- Filters Section --}}
        <div class="card p-6 mb-5">
            <form method="GET" action="{{ route('admin.instructors.index') }}" class="space-y-4">

                {{-- First Row: Search and Quick Filters --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Search</label>
                        <input type="text"
                               name="search"
                               value="{{ request('search') }}"
                               placeholder="Search by name, email, or employee ID..."
                               class="w-full px-4 py-2 text-sm border-2 border-gray-300 rounded-lg focus:border-secondary-blue focus:ring-2 focus:ring-secondary-blue">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Specialization</label>
                        <select name="specialization"
                                class="w-full px-4 py-2 text-sm border-2 border-gray-300 rounded-lg focus:border-secondary-blue">
                            <option value="all">All Specializations</option>
                            @foreach($specializations as $spec)
                                <option value="{{ $spec->specialization_id }}" {{ request('specialization') == $spec->specialization_id ? 'selected' : '' }}>
                                    {{ $spec->specialization_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Availability</label>
                        <select name="availability"
                                class="w-full px-4 py-2 text-sm border-2 border-gray-300 rounded-lg focus:border-secondary-blue">
                            <option value="all">All</option>
                            <option value="available" {{ request('availability') == 'available' ? 'selected' : '' }}>Available</option>
                            <option value="unavailable" {{ request('availability') == 'unavailable' ? 'selected' : '' }}>Unavailable</option>
                        </select>
                    </div>
                </div>

                {{-- Second Row: More Filters --}}
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Status</label>
                        <select name="status"
                                class="w-full px-4 py-2 text-sm border-2 border-gray-300 rounded-lg focus:border-secondary-blue">
                            <option value="all">All</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Sort By</label>
                        <select name="sort_by"
                                class="w-full px-4 py-2 text-sm border-2 border-gray-300 rounded-lg focus:border-secondary-blue">
                            <option value="name" {{ request('sort_by') == 'name' ? 'selected' : '' }}>Name</option>
                            <option value="students" {{ request('sort_by') == 'students' ? 'selected' : '' }}>Active Students</option>
                            <option value="experience" {{ request('sort_by') == 'experience' ? 'selected' : '' }}>Experience</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Date From</label>
                        <input type="date"
                               name="date_from"
                               value="{{ request('date_from') }}"
                               class="w-full px-4 py-2 text-sm border-2 border-gray-300 rounded-lg focus:border-secondary-blue">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Date To</label>
                        <input type="date"
                               name="date_to"
                               value="{{ request('date_to') }}"
                               class="w-full px-4 py-2 text-sm border-2 border-gray-300 rounded-lg focus:border-secondary-blue">
                    </div>
                </div>

                {{-- Filter Actions --}}
                <div class="flex flex-col sm:flex-row gap-3">
                    <button type="submit"
                            class="bg-secondary-blue text-white px-5 py-2 rounded-lg text-sm font-semibold hover:bg-secondary-blue-dark transition-all">
                        Apply Filters
                    </button>

                    <a href="{{ route('admin.instructors.index') }}"
                       class="bg-gray-200 text-gray-700 px-5 py-2 rounded-lg text-sm font-semibold hover:bg-gray-300 transition-all text-center">
                        Clear All
                    </a>
                </div>
            </form>
        </div>

        {{-- Statistics Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-5">
            <div class="card p-6 border-l-4 border-warm-coral">
                <p class="text-sm text-gray-600 font-semibold">Total Instructors</p>
                <p class="text-3xl font-bold text-primary-dark mt-1">{{ $instructors->total() }}</p>
            </div>

            <div class="card p-6 border-l-4 border-forest-green">
                <p class="text-sm text-gray-600 font-semibold">Available Now</p>
                <p class="text-3xl font-bold text-primary-dark mt-1">
                    {{ $instructors->where('is_available', true)->count() }}
                </p>
            </div>

            <div class="card p-6 border-l-4 border-golden-yellow">
                <p class="text-sm text-gray-600 font-semibold">Avg. Rating</p>
                <p class="text-3xl font-bold text-primary-dark mt-1">
                    {{ number_format($instructors->avg('average_rating'), 1) }}
                </p>
            </div>

            <div class="card p-6 border-l-4 border-secondary-blue">
                <p class="text-sm text-gray-600 font-semibold">Active Students</p>
                <p class="text-3xl font-bold text-primary-dark mt-1">
                    {{ $instructors->sum('active_students') }}
                </p>
            </div>
        </div>

        {{-- Instructors Table --}}
        <div class="card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-primary-dark">
                        <tr>
                            <th class="text-left text-xs font-bold text-accent-yellow uppercase tracking-wider">#</th>
                            <th class="text-left text-xs font-bold text-accent-yellow uppercase tracking-wider">Instructor</th>
                            <th class="text-left text-xs font-bold text-accent-yellow uppercase tracking-wider">Contact</th>
                            <th class="text-left text-xs font-bold text-accent-yellow uppercase tracking-wider">Specializations</th>
                            <th class="text-left text-xs font-bold text-accent-yellow uppercase tracking-wider">Students</th>
                            <th class="text-left text-xs font-bold text-accent-yellow uppercase tracking-wider">Status</th>
                            <th class="text-left text-xs font-bold text-accent-yellow uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($instructors as $instructor)
                            <tr class="hover:bg-accent-yellow-light transition-colors" id="instructor-row-{{ $instructor->instructor_id }}">

                                {{-- Row Number --}}
                                <td class="whitespace-nowrap text-sm font-bold text-gray-700">
                                    {{ $loop->iteration + (($instructors->currentPage() - 1) * $instructors->perPage()) }}
                                </td>

                                {{-- Instructor Info --}}
                                <td class="whitespace-nowrap">
                                    <div>
                                        <div class="text-sm font-bold text-gray-900">
                                            {{ trim(($instructor->first_name ?? '') . ' ' . ($instructor->last_name ?? '')) ?: 'No name' }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            ID: {{ $instructor->employee_id ?? 'N/A' }}
                                        </div>
                                    </div>
                                </td>

                                {{-- Contact --}}
                                <td class="whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $instructor->email ?? 'N/A' }}</div>
                                    <div class="text-xs text-gray-500">{{ $instructor->phone ?? 'N/A' }}</div>
                                </td>

                                {{-- Specializations --}}
                                <td>
                                    <div class="flex flex-wrap gap-1">
                                        @if($instructor->primary_specialization)
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-forest-green text-white">
                                                {{ $instructor->primary_specialization }}
                                            </span>
                                        @endif

                                        @if($instructor->specialization_count > 1)
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-200 text-gray-700">
                                                +{{ $instructor->specialization_count - 1 }} more
                                            </span>
                                        @endif

                                        @if($instructor->specialization_count == 0)
                                            <span class="text-xs text-gray-400">None</span>
                                        @endif
                                    </div>
                                </td>

                                {{-- Students --}}
                                <td class="whitespace-nowrap">
                                    <div class="text-sm font-semibold text-primary-dark">
                                        {{ $instructor->active_students }} Active
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        {{ $instructor->total_students_taught ?? 0 }} Total
                                    </div>
                                </td>

                                {{-- Status --}}
                                <td class="whitespace-nowrap">
                                    <div class="flex flex-col gap-1">
                                        @if($instructor->is_active)
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-forest-green text-white">
                                                Active
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-400 text-white">
                                                Inactive
                                            </span>
                                        @endif

                                        @if($instructor->is_available)
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-secondary-blue text-white">
                                                Available
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-warm-coral text-white">
                                                Busy
                                            </span>
                                        @endif
                                    </div>
                                </td>

                                {{-- Actions --}}
                                <td class="whitespace-nowrap text-sm">
                                    <button onclick="viewInstructor({{ $instructor->instructor_id }})"
                                            class="inline-flex items-center gap-2 text-secondary-blue hover:text-secondary-blue-dark font-semibold">
                                        <svg class="w-4 h-4"
                                             fill="none"
                                             stroke="currentColor"
                                             viewBox="0 0 24 24">
                                            <path stroke-linecap="round"
                                                  stroke-linejoin="round"
                                                  stroke-width="2"
                                                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z">
                                            </path>
                                            <path stroke-linecap="round"
                                                  stroke-linejoin="round"
                                                  stroke-width="2"
                                                  d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                            </path>
                                        </svg>
                                        View Details
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                    <p class="text-lg font-semibold">No instructors found</p>
                                    <p class="text-sm mt-2">Try adjusting your filters or add a new instructor.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($instructors->hasPages())
                <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                    {{ $instructors->links() }}
                </div>
            @endif
        </div>
    </div>
</main>

{{-- Instructor Detail Modal --}}
<div id="instructor-detail-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-4"></div>

{{-- Specialization Management Modal --}}
<div id="specialization-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-4"></div>

{{-- Performance Report Modal --}}
<div id="performance-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-4"></div>

{{-- Toast Container --}}
<div id="toast-container" class="fixed bottom-6 right-6 z-50 space-y-3"></div>

@vite(['resources/js/admin-pages/instructor.js'])

</body>
</html>