{{--
    ============================================================================
    LESSON SESSION PACKAGE MANAGEMENT PAGE
    resources/views/admin/lesson-sessions/index.blade.php
    ============================================================================
    Features:
    - CRUD operations for lesson packages
    - Safe usage checking before destructive actions
    - Better statistics for package monitoring
    - Search, status filter, sorting, pagination
    - Enrollment usage modal with connected student/instructor/instrument data
    ============================================================================
--}}

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Lesson packages - Admin dashboard</title>
    @vite(['resources/css/style.css', 'resources/js/admin-pages/lesson-session.js'])
</head>

<body class="bg-light-gray">

@include('layouts.admin-header')

<main class="lg:ml-64 min-h-screen bg-light-gray">

    {{-- ============================================================= --}}
    {{-- PAGE HEADER --}}
    {{-- ============================================================= --}}
    <header class="bg-white shadow-sm p-4 lg:p-5 border-b-4 border-secondary-blue">
        <div class="flex flex-col gap-5 xl:flex-row xl:items-center xl:justify-between">
            <div>
                <p class="text-sm font-bold uppercase tracking-wide text-secondary-blue">Admin module audit</p>
                <h1 class="mt-1 text-2xl lg:text-2xl font-bold text-primary-dark">Lesson Package Management</h1>
                <p class="mt-2 max-w-3xl text-sm text-gray-600">
                    Manage lesson packages used by student enrollments, pricing, package duration, status, and connected enrollment usage.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-3">

                <button type="button"
                        onclick="openAddModal()"
                        class="inline-flex items-center justify-center rounded-xl bg-forest-green px-4 py-3 text-sm font-bold text-white shadow-lg transition-all hover:bg-forest-green-dark">
                    <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Add package
                </button>
            </div>
        </div>
    </header>

    <div class="p-4 lg:p-4 xl:p-5">

        {{-- ============================================================= --}}
        {{-- STATISTICS CARDS --}}
        {{-- ============================================================= --}}
        <section class="mb-4 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-6">
            <div class="card border-l-4 border-secondary-blue p-5 lg:p-4">
                <p class="text-sm font-bold uppercase tracking-wide text-gray-500">Total packages</p>
                <p class="mt-2 text-2xl font-extrabold text-primary-dark">{{ $stats['total'] ?? 0 }}</p>
                <p class="mt-2 text-sm text-gray-500">All lesson package records</p>
            </div>

            <div class="card border-l-4 border-forest-green p-5 lg:p-4">
                <p class="text-sm font-bold uppercase tracking-wide text-gray-500">Active packages</p>
                <p class="mt-2 text-2xl font-extrabold text-primary-dark">{{ $stats['active'] ?? 0 }}</p>
                <p class="mt-2 text-sm text-gray-500">Available for enrollment</p>
            </div>

            <div class="card border-l-4 border-warm-coral p-5 lg:p-4">
                <p class="text-sm font-bold uppercase tracking-wide text-gray-500">Inactive packages</p>
                <p class="mt-2 text-2xl font-extrabold text-primary-dark">{{ $stats['inactive'] ?? 0 }}</p>
                <p class="mt-2 text-sm text-gray-500">Hidden from new use</p>
            </div>

            <div class="card border-l-4 border-golden-yellow p-5 lg:p-4">
                <p class="text-sm font-bold uppercase tracking-wide text-gray-500">Used packages</p>
                <p class="mt-2 text-2xl font-extrabold text-primary-dark">{{ $stats['used'] ?? 0 }}</p>
                <p class="mt-2 text-sm text-gray-500">{{ $stats['unused'] ?? 0 }} unused package{{ ($stats['unused'] ?? 0) == 1 ? '' : 's' }}</p>
            </div>

            <div class="card border-l-4 border-primary-dark p-5 lg:p-4">
                <p class="text-sm font-bold uppercase tracking-wide text-gray-500">Package enrollments</p>
                <p class="mt-2 text-2xl font-extrabold text-primary-dark">{{ $stats['total_enrollments'] ?? 0 }}</p>
                <p class="mt-2 text-sm text-gray-500">{{ $stats['active_enrollments'] ?? 0 }} active enrollment{{ ($stats['active_enrollments'] ?? 0) == 1 ? '' : 's' }}</p>
            </div>

            <div class="card border-l-4 border-secondary-blue p-5 lg:p-4">
                <p class="text-sm font-bold uppercase tracking-wide text-gray-500">Most popular</p>
                <p class="mt-2 text-lg font-extrabold text-primary-dark">{{ $stats['most_popular_name'] ?? 'None yet' }}</p>
                <p class="mt-2 text-sm text-gray-500">{{ $stats['most_popular_count'] ?? 0 }} enrollment{{ ($stats['most_popular_count'] ?? 0) == 1 ? '' : 's' }}</p>
            </div>
        </section>

        {{-- Secondary stats row --}}
        <section class="mb-4 grid grid-cols-1 gap-4 lg:grid-cols-2">
            <div class="card p-5 lg:p-4">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm font-bold uppercase tracking-wide text-gray-500">Revenue potential from connected enrollments</p>
                        <p class="mt-2 text-2xl font-extrabold text-primary-dark">₱{{ number_format($stats['revenue_potential'] ?? 0, 2) }}</p>
                    </div>
                    <p class="rounded-full bg-secondary-blue bg-opacity-10 px-4 py-2 text-sm font-bold text-secondary-blue">
                        Avg. ₱{{ number_format($stats['average_price'] ?? 0, 2) }} per package
                    </p>
                </div>
            </div>

            <div class="card p-5 lg:p-4">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm font-bold uppercase tracking-wide text-gray-500">Highest value package</p>
                        <p class="mt-2 text-2xl font-extrabold text-primary-dark">{{ $stats['highest_value_name'] ?? 'None yet' }}</p>
                    </div>
                    <p class="rounded-full bg-forest-green bg-opacity-10 px-4 py-2 text-sm font-bold text-forest-green">
                        ₱{{ number_format($stats['highest_value_price'] ?? 0, 2) }}
                    </p>
                </div>
            </div>
        </section>

        {{-- ============================================================= --}}
        {{-- FILTERS --}}
        {{-- ============================================================= --}}
        <section class="card mb-4 p-5 lg:p-4">
            <form method="GET" action="{{ route('admin.lesson-sessions.index') }}" class="grid grid-cols-1 gap-4 lg:grid-cols-12 lg:items-end">
                <div class="lg:col-span-5">
                    <label class="mb-2 block text-sm font-bold text-gray-700">Search packages</label>
                    <input type="text"
                           name="search"
                           value="{{ request('search') }}"
                           placeholder="Search by package name, session count, price, or description..."
                           class="w-full rounded-xl border-2 border-gray-300 px-4 py-3 text-sm focus:border-secondary-blue focus:ring-2 focus:ring-secondary-blue">
                </div>

                <div class="lg:col-span-2">
                    <label class="mb-2 block text-sm font-bold text-gray-700">Status</label>
                    <select name="status" class="w-full rounded-xl border-2 border-gray-300 px-4 py-3 text-sm focus:border-secondary-blue">
                        <option value="all" {{ request('status', 'all') === 'all' ? 'selected' : '' }}>All statuses</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active only</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive only</option>
                    </select>
                </div>

                <div class="lg:col-span-3">
                    <label class="mb-2 block text-sm font-bold text-gray-700">Sort by</label>
                    <select name="sort_by" class="w-full rounded-xl border-2 border-gray-300 px-4 py-3 text-sm focus:border-secondary-blue">
                        <option value="session_count" {{ request('sort_by', 'session_count') === 'session_count' ? 'selected' : '' }}>Session count</option>
                        <option value="usage" {{ request('sort_by') === 'usage' ? 'selected' : '' }}>Most used</option>
                        <option value="price_asc" {{ request('sort_by') === 'price_asc' ? 'selected' : '' }}>Price: low to high</option>
                        <option value="price_desc" {{ request('sort_by') === 'price_desc' ? 'selected' : '' }}>Price: high to low</option>
                        <option value="newest" {{ request('sort_by') === 'newest' ? 'selected' : '' }}>Newest first</option>
                        <option value="oldest" {{ request('sort_by') === 'oldest' ? 'selected' : '' }}>Oldest first</option>
                    </select>
                </div>

                <div class="flex gap-2 lg:col-span-2">
                    <button type="submit" class="flex-1 rounded-xl bg-secondary-blue px-5 py-3 text-sm font-bold text-white transition-all hover:bg-secondary-blue-dark">
                        Apply
                    </button>
                    <a href="{{ route('admin.lesson-sessions.index') }}"
                       class="flex-1 rounded-xl bg-gray-200 px-5 py-3 text-center text-sm font-bold text-gray-700 transition-all hover:bg-gray-300">
                        Clear
                    </a>
                </div>
            </form>
        </section>

        {{-- ============================================================= --}}
        {{-- LESSON PACKAGES TABLE --}}
        {{-- ============================================================= --}}
        <section class="card overflow-hidden">
            <div class="border-b border-gray-200 bg-white px-5 py-4 lg:px-4">
                <h2 class="text-lg font-extrabold text-primary-dark">Lesson packages list</h2>
                <p class="mt-1 text-sm text-gray-500">Used packages can still be renamed, but session count, duration, and price are locked for safety.</p>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-primary-dark">
                        <tr>
                            <th class="px-4 py-4 text-left text-xs font-bold uppercase tracking-wider text-accent-yellow">Package details</th>
                            <th class="px-4 py-4 text-left text-xs font-bold uppercase tracking-wider text-accent-yellow">Sessions and duration</th>
                            <th class="px-4 py-4 text-left text-xs font-bold uppercase tracking-wider text-accent-yellow">Pricing</th>
                            <th class="px-4 py-4 text-left text-xs font-bold uppercase tracking-wider text-accent-yellow">Usage</th>
                            <th class="px-4 py-4 text-left text-xs font-bold uppercase tracking-wider text-accent-yellow">Status</th>
                            <th class="px-4 py-4 text-left text-xs font-bold uppercase tracking-wider text-accent-yellow">Created</th>
                            <th class="px-4 py-4 text-left text-xs font-bold uppercase tracking-wider text-accent-yellow">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse($sessions as $session)
                            @php
                                $pricePerSession = $session->session_count > 0 ? $session->price / $session->session_count : 0;
                            @endphp

                            <tr class="transition-colors hover:bg-accent-yellow-light">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-4">
                                        <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-secondary-blue to-forest-green text-lg font-extrabold text-white shadow-sm">
                                            {{ $session->session_count }}
                                        </div>
                                        <div class="min-w-[220px]">
                                            <p class="text-base font-extrabold text-gray-900">{{ $session->session_name }}</p>
                                            @if($session->description)
                                                <p class="mt-1 text-sm text-gray-500">{{ \Illuminate\Support\Str::limit($session->description, 70) }}</p>
                                            @else
                                                <p class="mt-1 text-sm text-gray-400">No description provided</p>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <td class="px-4 py-3">
                                    <p class="text-sm font-bold text-gray-800">{{ $session->session_count }} session{{ $session->session_count == 1 ? '' : 's' }} × {{ $session->duration_minutes }} minutes</p>
                                    <p class="mt-1 text-sm text-gray-500">Total: {{ number_format($session->total_hours, 1) }} hour{{ $session->total_hours == 1 ? '' : 's' }}</p>
                                </td>

                                <td class="px-4 py-3 whitespace-nowrap">
                                    <p class="text-base font-extrabold text-primary-dark">₱{{ number_format($session->price, 2) }}</p>
                                    <p class="mt-1 text-sm text-gray-500">₱{{ number_format($pricePerSession, 2) }} / session</p>
                                </td>

                                <td class="px-4 py-3 whitespace-nowrap">
                                    @if($session->usage_count > 0)
                                        <button type="button"
                                                onclick="viewSessionEnrollments({{ (int) $session->session_id }})"
                                                class="inline-flex items-center rounded-full bg-secondary-blue px-4 py-2 text-sm font-bold text-white transition-all hover:bg-secondary-blue-dark">
                                            {{ $session->usage_count }} enrollment{{ $session->usage_count == 1 ? '' : 's' }}
                                        </button>
                                    @else
                                        <span class="inline-flex items-center rounded-full bg-gray-200 px-4 py-2 text-sm font-bold text-gray-700">
                                            Not used yet
                                        </span>
                                    @endif
                                </td>

                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="inline-flex items-center rounded-full px-4 py-2 text-xs font-bold {{ $session->is_active ? 'bg-forest-green text-white' : 'bg-gray-400 text-white' }}">
                                        {{ $session->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>

                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                    {{ $session->created_at ? date('M d, Y', strtotime($session->created_at)) : 'N/A' }}
                                </td>

                                <td class="px-4 py-3 whitespace-nowrap text-sm">
                                    <div class="flex items-center gap-2">
                                        <button type="button" onclick="editSession({{ (int) $session->session_id }})" class="action-btn text-secondary-blue">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                            <span class="tooltip">Edit</span>
                                        </button>

                                        <button type="button" onclick="toggleSession({{ (int) $session->session_id }})" class="action-btn {{ $session->is_active ? 'text-warm-coral' : 'text-forest-green' }}">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                @if($session->is_active)
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                                                @else
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                @endif
                                            </svg>
                                            <span class="tooltip">{{ $session->is_active ? 'Deactivate' : 'Activate' }}</span>
                                        </button>

                                        <button type="button" onclick="deleteSession({{ (int) $session->session_id }})" class="action-btn text-red-600">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                            <span class="tooltip">Delete</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-3 text-center text-gray-500">
                                    <svg class="mx-auto mb-4 h-10 w-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                    </svg>
                                    <p class="text-base font-bold">No lesson packages found</p>
                                    <p class="mt-2 text-sm">Try adjusting filters or add a new lesson package.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($sessions->hasPages())
                <div class="border-t border-gray-200 bg-gray-50 px-4 py-4">
                    {{ $sessions->links() }}
                </div>
            @endif
        </section>
    </div>
</main>

{{-- Modal Container (populated by JS) --}}
<div id="session-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-50 p-4"></div>

{{-- Toast Container --}}
<div id="toast-container" class="fixed bottom-6 right-6 z-50 space-y-3"></div>

</body>
</html>
