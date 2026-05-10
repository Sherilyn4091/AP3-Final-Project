{{-- 
    ============================================================================
    ADMIN INSTRUMENTS MANAGEMENT PAGE
    resources/views/admin/instruments/index.blade.php
    ============================================================================

    Purpose:
    - Manage Music Lab instruments offered for lessons.
    - Keep UI consistent with the improved Admin module style.
    - Display useful relationship-aware statistics.
    - Protect system instruments and instruments connected to active records.
    - Keep large elements semi-large, clean, and responsive.
    ============================================================================
--}}

@extends('layouts.admin')

@section('title', 'Instrument Management')

@section('content')
@vite([
    'resources/css/style.css',
    'resources/js/app.js',
    'resources/js/admin-pages.js',
    'resources/js/admin-pages/instrument.js'
])

<div class="-m-6 min-h-screen bg-[#F8F7F4] px-4 py-6 sm:px-6 lg:px-8">

    {{-- ============================================================= --}}
    {{-- PAGE HEADER --}}
    {{-- ============================================================= --}}
    <header class="mb-6 rounded-[28px] border border-[#D8DDD8] bg-white px-5 py-5 shadow-sm sm:px-6 lg:px-7">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.22em] text-[#768A96]" style="font-family: 'Inter', sans-serif;">
                    Admin Module
                </p>

                <h1 class="mt-2 text-2xl font-extrabold tracking-tight text-[#223030] sm:text-3xl" style="font-family: 'Sora', sans-serif;">
                    Instrument Management
                </h1>

                <p class="mt-2 max-w-3xl text-sm leading-6 text-[#44576D]" style="font-family: 'Inter', sans-serif;">
                    Manage lesson instruments, monitor student and enrollment connections, and protect records before deactivation.
                </p>
            </div>

            <button type="button"
                    onclick="openAddInstrumentModal()"
                    class="inline-flex items-center justify-center rounded-2xl bg-[#223030] px-5 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-[#29353C] focus:outline-none focus:ring-4 focus:ring-[#D8DDD8]"
                    style="font-family: 'Inter', sans-serif;">
                <span class="mr-2 text-lg leading-none">+</span>
                Add Instrument
            </button>
        </div>
    </header>

    {{-- ============================================================= --}}
    {{-- STATS CARDS --}}
    {{-- ============================================================= --}}
    <section class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-6">

        <article class="admin-instrument-card rounded-[24px] border border-[#D8DDD8] bg-white p-5 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wide text-[#768A96]" style="font-family: 'Inter', sans-serif;">Total</p>
            <div class="mt-3 flex items-end justify-between gap-3">
                <p class="js-count-up text-3xl font-extrabold text-[#223030]" data-count="{{ $stats['total'] }}" style="font-family: 'Sora', sans-serif;">0</p>
                <span class="rounded-full bg-[#EEF2F4] px-3 py-1 text-xs font-bold text-[#44576D]">Records</span>
            </div>
        </article>

        <article class="admin-instrument-card rounded-[24px] border border-[#D8DDD8] bg-white p-5 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wide text-[#768A96]" style="font-family: 'Inter', sans-serif;">Active</p>
            <div class="mt-3 flex items-end justify-between gap-3">
                <p class="js-count-up text-3xl font-extrabold text-[#223030]" data-count="{{ $stats['active'] }}" style="font-family: 'Sora', sans-serif;">0</p>
                <span class="rounded-full bg-[#F1F3EF] px-3 py-1 text-xs font-bold text-[#223030]">Available</span>
            </div>
        </article>

        <article class="admin-instrument-card rounded-[24px] border border-[#D8DDD8] bg-white p-5 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wide text-[#768A96]" style="font-family: 'Inter', sans-serif;">Inactive</p>
            <div class="mt-3 flex items-end justify-between gap-3">
                <p class="js-count-up text-3xl font-extrabold text-[#223030]" data-count="{{ $stats['inactive'] }}" style="font-family: 'Sora', sans-serif;">0</p>
                <span class="rounded-full bg-[#F6EFEC] px-3 py-1 text-xs font-bold text-[#523D35]">Hidden</span>
            </div>
        </article>

        <article class="admin-instrument-card rounded-[24px] border border-[#D8DDD8] bg-white p-5 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wide text-[#768A96]" style="font-family: 'Inter', sans-serif;">Linked Students</p>
            <div class="mt-3 flex items-end justify-between gap-3">
                <p class="js-count-up text-3xl font-extrabold text-[#223030]" data-count="{{ $stats['active_linked_students'] }}" style="font-family: 'Sora', sans-serif;">0</p>
                <span class="rounded-full bg-[#EEF2F4] px-3 py-1 text-xs font-bold text-[#44576D]">Active</span>
            </div>
        </article>

        <article class="admin-instrument-card rounded-[24px] border border-[#D8DDD8] bg-white p-5 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wide text-[#768A96]" style="font-family: 'Inter', sans-serif;">Active Enrollments</p>
            <div class="mt-3 flex items-end justify-between gap-3">
                <p class="js-count-up text-3xl font-extrabold text-[#223030]" data-count="{{ $stats['active_linked_enrollments'] }}" style="font-family: 'Sora', sans-serif;">0</p>
                <span class="rounded-full bg-[#F1F3EF] px-3 py-1 text-xs font-bold text-[#223030]">Lessons</span>
            </div>
        </article>

        <article class="admin-instrument-card rounded-[24px] border border-[#D8DDD8] bg-white p-5 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wide text-[#768A96]" style="font-family: 'Inter', sans-serif;">Top Instrument</p>
            <div class="mt-3">
                <p class="truncate text-lg font-extrabold text-[#223030]" title="{{ $stats['most_used_name'] }}" style="font-family: 'Sora', sans-serif;">
                    {{ $stats['most_used_name'] }}
                </p>
                <p class="mt-1 text-xs font-semibold text-[#768A96]" style="font-family: 'Inter', sans-serif;">
                    {{ $stats['most_used_count'] }} active student(s)
                </p>
            </div>
        </article>
    </section>

    {{-- ============================================================= --}}
    {{-- CATEGORY SUMMARY --}}
    {{-- ============================================================= --}}
    @if($categoryStats->count())
        <section class="mb-6 rounded-[24px] border border-[#D8DDD8] bg-white p-5 shadow-sm">
            <div class="mb-4 flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h2 class="text-base font-extrabold text-[#223030]" style="font-family: 'Sora', sans-serif;">Category Overview</h2>
                    <p class="text-sm text-[#768A96]" style="font-family: 'Inter', sans-serif;">Quick summary of instruments grouped by category.</p>
                </div>
                <span class="rounded-full bg-[#EEF2F4] px-3 py-1 text-xs font-bold text-[#44576D]" style="font-family: 'Inter', sans-serif;">
                    {{ $stats['category_count'] }} categor{{ $stats['category_count'] === 1 ? 'y' : 'ies' }}
                </span>
            </div>

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
                @foreach($categoryStats as $categoryStat)
                    @php
                        $percent = $stats['total'] > 0 ? min(100, round(((int) $categoryStat->total / $stats['total']) * 100)) : 0;
                    @endphp

                    <div class="rounded-2xl border border-[#EEF1EC] bg-[#FCFCFA] p-4">
                        <div class="flex items-center justify-between gap-3">
                            <p class="truncate text-sm font-bold text-[#223030]" style="font-family: 'Inter', sans-serif;">
                                {{ $categoryStat->category_name }}
                            </p>
                            <span class="text-sm font-bold text-[#44576D]" style="font-family: 'JetBrains Mono', monospace;">
                                {{ $categoryStat->total }}
                            </span>
                        </div>

                        <div class="mt-3 h-2 overflow-hidden rounded-full bg-[#EEF1EC]">
                            <div class="h-full rounded-full bg-[#768A96]" style="width: {{ $percent }}%;"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    {{-- ============================================================= --}}
    {{-- FILTERS --}}
    {{-- ============================================================= --}}
    <section class="mb-6 rounded-[24px] border border-[#D8DDD8] bg-white p-5 shadow-sm">
        <form method="GET" action="{{ route('admin.instruments.index') }}" class="grid grid-cols-1 gap-4 lg:grid-cols-12">

            <div class="lg:col-span-4">
                <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-[#768A96]" style="font-family: 'Inter', sans-serif;">
                    Search
                </label>
                <input type="text"
                       name="search"
                       value="{{ $filters['search'] }}"
                       placeholder="Search name, category, or description..."
                       class="w-full rounded-2xl border border-[#D8DDD8] bg-white px-4 py-3 text-sm text-[#223030] outline-none transition focus:border-[#768A96] focus:ring-4 focus:ring-[#EEF2F4]"
                       style="font-family: 'Inter', sans-serif;">
            </div>

            <div class="lg:col-span-2">
                <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-[#768A96]" style="font-family: 'Inter', sans-serif;">
                    Category
                </label>
                <select name="category"
                        class="w-full rounded-2xl border border-[#D8DDD8] bg-white px-4 py-3 text-sm text-[#223030] outline-none transition focus:border-[#768A96] focus:ring-4 focus:ring-[#EEF2F4]">
                    <option value="all">All categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category }}" @selected($filters['category'] === $category)>
                            {{ $category }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="lg:col-span-2">
                <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-[#768A96]" style="font-family: 'Inter', sans-serif;">
                    Status
                </label>
                <select name="status"
                        class="w-full rounded-2xl border border-[#D8DDD8] bg-white px-4 py-3 text-sm text-[#223030] outline-none transition focus:border-[#768A96] focus:ring-4 focus:ring-[#EEF2F4]">
                    <option value="all" @selected($filters['status'] === 'all')>All statuses</option>
                    <option value="active" @selected($filters['status'] === 'active')>Active</option>
                    <option value="inactive" @selected($filters['status'] === 'inactive')>Inactive</option>
                </select>
            </div>

            <div class="lg:col-span-2">
                <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-[#768A96]" style="font-family: 'Inter', sans-serif;">
                    Type
                </label>
                <select name="type"
                        class="w-full rounded-2xl border border-[#D8DDD8] bg-white px-4 py-3 text-sm text-[#223030] outline-none transition focus:border-[#768A96] focus:ring-4 focus:ring-[#EEF2F4]">
                    <option value="all" @selected($filters['type'] === 'all')>All types</option>
                    <option value="system" @selected($filters['type'] === 'system')>System</option>
                    <option value="custom" @selected($filters['type'] === 'custom')>Custom</option>
                </select>
            </div>

            <div class="lg:col-span-2">
                <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-[#768A96]" style="font-family: 'Inter', sans-serif;">
                    Sort
                </label>
                <select name="sort"
                        class="w-full rounded-2xl border border-[#D8DDD8] bg-white px-4 py-3 text-sm text-[#223030] outline-none transition focus:border-[#768A96] focus:ring-4 focus:ring-[#EEF2F4]">
                    <option value="name_asc" @selected($filters['sort'] === 'name_asc')>Name A-Z</option>
                    <option value="name_desc" @selected($filters['sort'] === 'name_desc')>Name Z-A</option>
                    <option value="category_asc" @selected($filters['sort'] === 'category_asc')>Category</option>
                    <option value="students_desc" @selected($filters['sort'] === 'students_desc')>Most students</option>
                    <option value="enrollments_desc" @selected($filters['sort'] === 'enrollments_desc')>Most enrollments</option>
                    <option value="newest" @selected($filters['sort'] === 'newest')>Newest</option>
                </select>
            </div>

            <div class="flex flex-col gap-2 sm:flex-row lg:col-span-12 lg:justify-end">
                <a href="{{ route('admin.instruments.index') }}"
                   class="inline-flex items-center justify-center rounded-2xl border border-[#D8DDD8] bg-white px-5 py-3 text-sm font-bold text-[#223030] transition hover:bg-[#F4F5F2]"
                   style="font-family: 'Inter', sans-serif;">
                    Reset
                </a>

                <button type="submit"
                        class="inline-flex items-center justify-center rounded-2xl bg-[#44576D] px-5 py-3 text-sm font-bold text-white transition hover:bg-[#29353C]"
                        style="font-family: 'Inter', sans-serif;">
                    Apply Filters
                </button>
            </div>
        </form>
    </section>

    {{-- ============================================================= --}}
    {{-- TABLE --}}
    {{-- ============================================================= --}}
    <section class="overflow-hidden rounded-[26px] border border-[#D8DDD8] bg-white shadow-sm">
        <div class="border-b border-[#D8DDD8] bg-[#FCFCFA] px-5 py-4">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-lg font-extrabold text-[#223030]" style="font-family: 'Sora', sans-serif;">
                        Instrument Records
                    </h2>
                    <p class="text-sm text-[#768A96]" style="font-family: 'Inter', sans-serif;">
                        Showing {{ $instruments->count() }} of {{ $instruments->total() }} record(s).
                    </p>
                </div>

                <span class="rounded-full bg-[#EEF2F4] px-3 py-1 text-xs font-bold text-[#44576D]" style="font-family: 'Inter', sans-serif;">
                    Page {{ $instruments->currentPage() }} of {{ $instruments->lastPage() }}
                </span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-[1050px] w-full text-left">
                <thead class="bg-white">
                    <tr class="border-b border-[#EEF1EC]">
                        <th class="px-5 py-4 text-xs font-bold uppercase tracking-wide text-[#768A96]" style="font-family: 'Inter', sans-serif;">Instrument</th>
                        <th class="px-5 py-4 text-xs font-bold uppercase tracking-wide text-[#768A96]" style="font-family: 'Inter', sans-serif;">Category</th>
                        <th class="px-5 py-4 text-xs font-bold uppercase tracking-wide text-[#768A96]" style="font-family: 'Inter', sans-serif;">Usage</th>
                        <th class="px-5 py-4 text-xs font-bold uppercase tracking-wide text-[#768A96]" style="font-family: 'Inter', sans-serif;">Type</th>
                        <th class="px-5 py-4 text-xs font-bold uppercase tracking-wide text-[#768A96]" style="font-family: 'Inter', sans-serif;">Status</th>
                        <th class="px-5 py-4 text-xs font-bold uppercase tracking-wide text-[#768A96]" style="font-family: 'Inter', sans-serif;">Updated</th>
                        <th class="px-5 py-4 text-right text-xs font-bold uppercase tracking-wide text-[#768A96]" style="font-family: 'Inter', sans-serif;">Actions</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-[#EEF1EC]">
                    @forelse($instruments as $instrument)
                        <tr class="transition hover:bg-[#FCFCFA]">
                            <td class="px-5 py-4">
                                <div class="flex items-start gap-3">
                                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-[#EEF2F4] text-sm font-extrabold text-[#44576D]" style="font-family: 'Sora', sans-serif;">
                                        {{ strtoupper(substr($instrument->instrument_name, 0, 1)) }}
                                    </div>

                                    <div>
                                        <p class="font-extrabold text-[#223030]" style="font-family: 'Sora', sans-serif;">
                                            {{ $instrument->instrument_name }}
                                        </p>

                                        <p class="mt-1 line-clamp-2 max-w-md text-sm leading-5 text-[#768A96]" style="font-family: 'Inter', sans-serif;">
                                            {{ $instrument->description ?: 'No description added.' }}
                                        </p>

                                        <p class="mt-1 text-xs text-[#959D90]" style="font-family: 'JetBrains Mono', monospace;">
                                            ID: {{ $instrument->instrument_id }}
                                        </p>
                                    </div>
                                </div>
                            </td>

                            <td class="px-5 py-4">
                                <span class="rounded-full bg-[#EEF2F4] px-3 py-1 text-xs font-bold text-[#44576D]" style="font-family: 'Inter', sans-serif;">
                                    {{ $instrument->category ?: 'Uncategorized' }}
                                </span>
                            </td>

                            <td class="px-5 py-4">
                                <div class="space-y-1">
                                    <p class="text-sm font-bold text-[#223030]" style="font-family: 'Inter', sans-serif;">
                                        {{ (int) $instrument->active_students_count }} active student(s)
                                    </p>
                                    <p class="text-xs text-[#768A96]" style="font-family: 'Inter', sans-serif;">
                                        {{ (int) $instrument->active_enrollments_count }} active enrollment(s)
                                    </p>
                                </div>
                            </td>

                            <td class="px-5 py-4">
                                @if($instrument->is_system)
                                    <span class="rounded-full bg-[#F1F3EF] px-3 py-1 text-xs font-bold text-[#223030]" style="font-family: 'Inter', sans-serif;">
                                        System
                                    </span>
                                @else
                                    <span class="rounded-full bg-[#EEF2F4] px-3 py-1 text-xs font-bold text-[#44576D]" style="font-family: 'Inter', sans-serif;">
                                        Custom
                                    </span>
                                @endif
                            </td>

                            <td class="px-5 py-4">
                                @if($instrument->is_active)
                                    <span class="rounded-full bg-[#F1F3EF] px-3 py-1 text-xs font-bold text-[#223030]" style="font-family: 'Inter', sans-serif;">
                                        Active
                                    </span>
                                @else
                                    <span class="rounded-full bg-[#F6EFEC] px-3 py-1 text-xs font-bold text-[#523D35]" style="font-family: 'Inter', sans-serif;">
                                        Inactive
                                    </span>
                                @endif
                            </td>

                            <td class="px-5 py-4 text-sm text-[#768A96]" style="font-family: 'JetBrains Mono', monospace;">
                                {{ $instrument->updated_at ? \Carbon\Carbon::parse($instrument->updated_at)->format('M d, Y') : 'N/A' }}
                            </td>

                            <td class="px-5 py-4">
                                <div class="flex flex-wrap justify-end gap-2">
                                    <button type="button"
                                            onclick="viewInstrument({{ $instrument->instrument_id }})"
                                            class="rounded-xl border border-[#D8DDD8] bg-white px-3 py-2 text-xs font-bold text-[#223030] transition hover:bg-[#F4F5F2]">
                                        View
                                    </button>

                                    <button type="button"
                                            onclick="editInstrument({{ $instrument->instrument_id }})"
                                            class="rounded-xl border border-[#D8DDD8] bg-white px-3 py-2 text-xs font-bold text-[#44576D] transition hover:bg-[#F4F5F2]">
                                        Edit
                                    </button>

                                    @if(!$instrument->is_system)
                                        <button type="button"
                                                onclick="toggleInstrumentStatus({{ $instrument->instrument_id }}, {{ $instrument->is_active ? 'true' : 'false' }})"
                                                class="rounded-xl border border-[#D8DDD8] bg-white px-3 py-2 text-xs font-bold text-[#523D35] transition hover:bg-[#F6EFEC]">
                                            {{ $instrument->is_active ? 'Deactivate' : 'Activate' }}
                                        </button>
                                    @else
                                        <span class="rounded-xl bg-[#F4F5F2] px-3 py-2 text-xs font-bold text-[#959D90]">
                                            Protected
                                        </span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-14 text-center">
                                <p class="text-xl font-extrabold text-[#223030]" style="font-family: 'Sora', sans-serif;">
                                    No instruments found
                                </p>
                                <p class="mt-2 text-sm text-[#768A96]" style="font-family: 'Inter', sans-serif;">
                                    Try adjusting your filters or add a new custom instrument.
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($instruments->hasPages())
            <div class="border-t border-[#EEF1EC] px-5 py-4">
                {{ $instruments->links() }}
            </div>
        @endif
    </section>
</div>

{{-- ============================================================= --}}
{{-- ADD / EDIT MODAL --}}
{{-- ============================================================= --}}
<div id="instrumentModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 px-4 py-6">
    <div class="max-h-[92vh] w-full max-w-2xl overflow-y-auto rounded-[28px] bg-white shadow-xl">
        <div class="border-b border-[#D8DDD8] bg-[#FCFCFA] px-6 py-5">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 id="instrumentModalTitle" class="text-xl font-extrabold text-[#223030]" style="font-family: 'Sora', sans-serif;">
                        Add Instrument
                    </h2>
                    <p id="instrumentModalSubtitle" class="mt-1 text-sm text-[#768A96]" style="font-family: 'Inter', sans-serif;">
                        Create a new custom instrument record.
                    </p>
                </div>

                <button type="button"
                        onclick="closeInstrumentModal()"
                        class="rounded-2xl border border-[#D8DDD8] bg-white px-3 py-2 text-sm font-bold text-[#223030] hover:bg-[#F4F5F2]">
                    Close
                </button>
            </div>
        </div>

        <form id="instrumentForm" class="space-y-5 px-6 py-6">
            @csrf

            <input type="hidden" id="instrumentId" name="instrument_id">

            <div id="systemNotice" class="hidden rounded-2xl border border-[#D8DDD8] bg-[#F6EFEC] px-4 py-3 text-sm font-semibold text-[#523D35]" style="font-family: 'Inter', sans-serif;">
                This is a protected system instrument. Only the description can be updated.
            </div>

            <div>
                <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-[#768A96]" style="font-family: 'Inter', sans-serif;">
                    Instrument Name
                </label>
                <input type="text"
                       id="instrumentName"
                       name="instrument_name"
                       maxlength="100"
                       required
                       class="w-full rounded-2xl border border-[#D8DDD8] bg-white px-4 py-3 text-sm text-[#223030] outline-none transition focus:border-[#768A96] focus:ring-4 focus:ring-[#EEF2F4]"
                       placeholder="Example: Piano">
                <p class="mt-1 hidden text-xs font-semibold text-[#523D35]" data-error-for="instrument_name"></p>
            </div>

            <div>
                <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-[#768A96]" style="font-family: 'Inter', sans-serif;">
                    Category
                </label>
                <select id="instrumentCategory"
                        name="category"
                        required
                        class="w-full rounded-2xl border border-[#D8DDD8] bg-white px-4 py-3 text-sm text-[#223030] outline-none transition focus:border-[#768A96] focus:ring-4 focus:ring-[#EEF2F4]">
                    <option value="">Select category</option>
                    @foreach($categories as $category)
                        <option value="{{ $category }}">{{ $category }}</option>
                    @endforeach
                </select>
                <p class="mt-1 hidden text-xs font-semibold text-[#523D35]" data-error-for="category"></p>
            </div>

            <div>
                <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-[#768A96]" style="font-family: 'Inter', sans-serif;">
                    Description
                </label>
                <textarea id="instrumentDescription"
                          name="description"
                          rows="4"
                          maxlength="500"
                          class="w-full resize-none rounded-2xl border border-[#D8DDD8] bg-white px-4 py-3 text-sm text-[#223030] outline-none transition focus:border-[#768A96] focus:ring-4 focus:ring-[#EEF2F4]"
                          placeholder="Short description for this lesson instrument..."></textarea>
                <p class="mt-1 hidden text-xs font-semibold text-[#523D35]" data-error-for="description"></p>
            </div>

            <div class="flex flex-col-reverse gap-3 border-t border-[#EEF1EC] pt-5 sm:flex-row sm:justify-end">
                <button type="button"
                        onclick="closeInstrumentModal()"
                        class="rounded-2xl border border-[#D8DDD8] bg-white px-5 py-3 text-sm font-bold text-[#223030] transition hover:bg-[#F4F5F2]">
                    Cancel
                </button>

                <button type="submit"
                        id="instrumentSubmitButton"
                        class="rounded-2xl bg-[#223030] px-5 py-3 text-sm font-bold text-white transition hover:bg-[#29353C] disabled:opacity-60">
                    Save Instrument
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ============================================================= --}}
{{-- VIEW / USAGE MODAL --}}
{{-- ============================================================= --}}
<div id="instrumentViewModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 px-4 py-6">
    <div class="max-h-[92vh] w-full max-w-4xl overflow-y-auto rounded-[28px] bg-white shadow-xl">
        <div class="border-b border-[#D8DDD8] bg-[#FCFCFA] px-6 py-5">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 id="viewInstrumentName" class="text-xl font-extrabold text-[#223030]" style="font-family: 'Sora', sans-serif;">
                        Instrument Details
                    </h2>
                    <p id="viewInstrumentMeta" class="mt-1 text-sm text-[#768A96]" style="font-family: 'Inter', sans-serif;">
                        Loading details...
                    </p>
                </div>

                <button type="button"
                        onclick="closeInstrumentViewModal()"
                        class="rounded-2xl border border-[#D8DDD8] bg-white px-3 py-2 text-sm font-bold text-[#223030] hover:bg-[#F4F5F2]">
                    Close
                </button>
            </div>
        </div>

        <div class="space-y-5 px-6 py-6">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-4">
                <div class="rounded-2xl border border-[#D8DDD8] bg-[#FCFCFA] p-4">
                    <p class="text-xs font-bold uppercase tracking-wide text-[#768A96]">Active Students</p>
                    <p id="viewActiveStudents" class="mt-2 text-2xl font-extrabold text-[#223030]" style="font-family: 'Sora', sans-serif;">0</p>
                </div>

                <div class="rounded-2xl border border-[#D8DDD8] bg-[#FCFCFA] p-4">
                    <p class="text-xs font-bold uppercase tracking-wide text-[#768A96]">Total Students</p>
                    <p id="viewTotalStudents" class="mt-2 text-2xl font-extrabold text-[#223030]" style="font-family: 'Sora', sans-serif;">0</p>
                </div>

                <div class="rounded-2xl border border-[#D8DDD8] bg-[#FCFCFA] p-4">
                    <p class="text-xs font-bold uppercase tracking-wide text-[#768A96]">Active Enrollments</p>
                    <p id="viewActiveEnrollments" class="mt-2 text-2xl font-extrabold text-[#223030]" style="font-family: 'Sora', sans-serif;">0</p>
                </div>

                <div class="rounded-2xl border border-[#D8DDD8] bg-[#FCFCFA] p-4">
                    <p class="text-xs font-bold uppercase tracking-wide text-[#768A96]">Total Enrollments</p>
                    <p id="viewTotalEnrollments" class="mt-2 text-2xl font-extrabold text-[#223030]" style="font-family: 'Sora', sans-serif;">0</p>
                </div>
            </div>

            <div class="rounded-2xl border border-[#D8DDD8] bg-white p-5">
                <h3 class="text-base font-extrabold text-[#223030]" style="font-family: 'Sora', sans-serif;">Connected Students</h3>
                <p class="mt-1 text-sm text-[#768A96]" style="font-family: 'Inter', sans-serif;">
                    Students are listed if connected through their student profile or enrollment record.
                </p>

                <div id="connectedStudentsContainer" class="mt-4 overflow-x-auto">
                    <p class="text-sm text-[#768A96]">Loading students...</p>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ============================================================= --}}
{{-- TOAST --}}
{{-- ============================================================= --}}
<div id="instrumentToast" class="fixed bottom-5 right-5 z-[60] hidden max-w-sm rounded-2xl border border-[#D8DDD8] bg-white px-5 py-4 text-sm font-semibold text-[#223030] shadow-xl" style="font-family: 'Inter', sans-serif;"></div>

@endsection

@push('styles')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;600;700&family=Sora:wght@500;600;700;800&display=swap');

    .admin-instrument-card {
        opacity: 0;
        transform: translateY(12px) scale(0.98);
        animation: adminInstrumentCardIn 520ms ease forwards;
    }

    .admin-instrument-card:nth-child(1) { animation-delay: 40ms; }
    .admin-instrument-card:nth-child(2) { animation-delay: 90ms; }
    .admin-instrument-card:nth-child(3) { animation-delay: 140ms; }
    .admin-instrument-card:nth-child(4) { animation-delay: 190ms; }
    .admin-instrument-card:nth-child(5) { animation-delay: 240ms; }
    .admin-instrument-card:nth-child(6) { animation-delay: 290ms; }

    @keyframes adminInstrumentCardIn {
        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    @media (prefers-reduced-motion: reduce) {
        .admin-instrument-card {
            animation: none;
            opacity: 1;
            transform: none;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    /*
    |--------------------------------------------------------------------------
    | Admin Instrument JavaScript Config
    |--------------------------------------------------------------------------
    |
    | Route URLs are generated in Blade so the JS file can stay clean and reusable.
    |
    */
    window.adminInstrumentConfig = {
        csrfToken: @json(csrf_token()),
        storeUrl: @json(route('admin.instruments.store')),
        showUrlTemplate: @json(url('/admin/instruments/__ID__')),
        updateUrlTemplate: @json(url('/admin/instruments/__ID__')),
        destroyUrlTemplate: @json(url('/admin/instruments/__ID__')),
        toggleUrlTemplate: @json(url('/admin/instruments/__ID__/toggle-status')),
        studentsUrlTemplate: @json(url('/admin/instruments/__ID__/students')),
        usageUrlTemplate: @json(url('/admin/instruments/__ID__/usage')),
    };
</script>
@endpush