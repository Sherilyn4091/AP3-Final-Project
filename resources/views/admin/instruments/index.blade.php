{{-- 
    ============================================================================
    INSTRUMENT MANAGEMENT PAGE
    resources/views/admin/instruments/index.blade.php
    ============================================================================
    Features:
    - List all instruments with active student counts
    - Add new instruments via modal
    - Edit existing instruments (except system instruments)
    - Deactivate/Activate instruments (with usage checks)
    - Search by name, filter by category and status
    - Responsive design for mobile/tablet
    - Statistics dashboard
    - View enrolled students modal
    ============================================================================
--}}

@extends('layouts.admin')

@section('title', 'Instrument management')

@section('content')
@vite([
    'resources/css/style.css',
    'resources/js/app.js',
    'resources/js/admin-pages.js',
    'resources/js/admin-pages/instrument.js'
])

{{-- Page Header --}}
<header class="bg-white shadow-sm p-6 border-b-4 border-secondary-blue -mt-6 -mx-6 mb-6">
    <div class="flex items-center justify-between flex-wrap gap-4">
        <div>
            <h1 class="text-3xl font-bold text-primary-dark">Instrument management</h1>
            <p class="text-secondary-blue mt-1">Manage musical instruments offered for lessons</p>
        </div>
        <button onclick="openAddInstrumentModal()" class="bg-forest-green text-white px-6 py-3 rounded-lg font-semibold hover:bg-forest-green-dark transition-all shadow-lg">
            <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Add instrument
        </button>
    </div>
</header>

{{-- Statistics Cards --}}
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-secondary-blue">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 font-semibold">Total instruments</p>
                <p class="text-3xl font-bold text-primary-dark mt-1">{{ $stats['total'] }}</p>
            </div>
            <div class="bg-secondary-blue bg-opacity-20 p-3 rounded-full">
                <svg class="w-8 h-8 text-secondary-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"></path>
                </svg>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-forest-green">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 font-semibold">Active</p>
                <p class="text-3xl font-bold text-primary-dark mt-1">{{ $stats['active'] }}</p>
            </div>
            <div class="bg-forest-green bg-opacity-20 p-3 rounded-full">
                <svg class="w-8 h-8 text-forest-green" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-warm-coral">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 font-semibold">Inactive</p>
                <p class="text-3xl font-bold text-primary-dark mt-1">{{ $stats['inactive'] }}</p>
            </div>
            <div class="bg-warm-coral bg-opacity-20 p-3 rounded-full">
                <svg class="w-8 h-8 text-warm-coral" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                </svg>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-golden-yellow">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 font-semibold">Most popular</p>
                <p class="text-lg font-bold text-primary-dark mt-1">{{ $stats['most_used_name'] }}</p>
                <p class="text-xs text-gray-500">{{ $stats['most_used_count'] }} {{ $stats['most_used_count'] == 1 ? 'student' : 'students' }}</p>
            </div>
            <div class="bg-golden-yellow bg-opacity-20 p-3 rounded-full">
                <svg class="w-8 h-8 text-golden-yellow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                </svg>
            </div>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="bg-white rounded-xl shadow-lg p-6 mb-6">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        <div class="lg:col-span-2">
            <label class="block text-sm font-semibold text-gray-700 mb-2">Search</label>
            <input type="text" id="search-name" placeholder="Search by instrument name..." 
                   class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-secondary-blue focus:ring-2 focus:ring-secondary-blue transition-all">
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Category</label>
            <select id="filter-category" class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-secondary-blue focus:ring-2 focus:ring-secondary-blue transition-all">
                <option value="all">All categories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat }}">{{ $cat }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Status</label>
            <select id="filter-status" class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-secondary-blue focus:ring-2 focus:ring-secondary-blue transition-all">
                <option value="all">All statuses</option>
                <option value="all" selected>All statuses</option>
                <option value="inactive">Inactive only</option>
            </select>
        </div>

        <div class="flex items-end gap-2">
            <button onclick="applyInstrumentFilters()" 
                    class="flex-1 bg-secondary-blue hover:bg-secondary-blue-dark text-white px-5 py-2 rounded-lg font-semibold transition-all shadow-sm">
                Apply
            </button>
            <button onclick="clearInstrumentFilters()" 
                    class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-700 px-5 py-2 rounded-lg font-semibold transition-all">
                Clear
            </button>
        </div>
    </div>
</div>

{{-- Instruments Table --}}
<div class="bg-white rounded-xl shadow-lg overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-primary-dark">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-bold text-accent-yellow uppercase tracking-wider">Instrument</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-accent-yellow uppercase tracking-wider">Category</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-accent-yellow uppercase tracking-wider hidden md:table-cell">Description</th>
                    <th class="px-6 py-4 text-center text-xs font-bold text-accent-yellow uppercase tracking-wider">Students (view)</th>
                    <th class="px-6 py-4 text-center text-xs font-bold text-accent-yellow uppercase tracking-wider">Status</th>
                    <th class="px-6 py-4 text-center text-xs font-bold text-accent-yellow uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
                @forelse($instruments as $index => $instrument)
                <tr class="instrument-row hover:bg-accent-yellow-light transition-colors duration-150"
                    data-instrument-id="{{ $instrument->instrument_id }}"
                    data-name="{{ strtolower($instrument->instrument_name) }}"
                    data-category="{{ $instrument->category }}"
                    data-status="{{ $instrument->is_active ? 'active' : 'inactive' }}">

                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-10 w-10 bg-gradient-to-br from-secondary-blue to-forest-green rounded-full flex items-center justify-center text-white font-bold">
                                {{ substr($instrument->instrument_name, 0, 1) }}
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-bold text-gray-900">{{ $instrument->instrument_name }}</div>
                                @if($instrument->is_system)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-secondary-blue/10 text-secondary-blue">
                                    System
                                </span>
                                @endif
                            </div>
                        </div>
                    </td>

                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                            {{ $instrument->category }}
                        </span>
                    </td>

                    <td class="px-6 py-4 text-sm text-gray-600 hidden md:table-cell">
                        {{ $instrument->description ? Str::limit($instrument->description, 50) : 'No description' }}
                    </td>

                    <td class="px-6 py-4 whitespace-nowrap text-center">
                        @if($instrument->students_count > 0)
                            <button onclick="viewInstrumentStudents({{ $instrument->instrument_id }}, '{{ addslashes($instrument->instrument_name) }}')"
                                    class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-secondary-blue text-white hover:bg-secondary-blue-dark transition-all">
                                {{ $instrument->students_count }} {{ $instrument->students_count == 1 ? 'student' : 'students' }}
                            </button>
                        @else
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-gray-200 text-gray-700">
                                No students
                            </span>
                        @endif
                    </td>

                    <td class="px-6 py-4 whitespace-nowrap text-center">
                        <span class="status-badge inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $instrument->is_active ? 'bg-forest-green text-white' : 'bg-gray-400 text-white' }}">
                            {{ $instrument->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>

                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
                        <div class="flex items-center justify-center gap-2">
                            {{-- Edit Button --}}
                            <button onclick="editInstrument({{ $instrument->instrument_id }})"
                                    class="relative group p-2 rounded-lg text-secondary-blue hover:bg-secondary-blue hover:text-white transition-all duration-200 shadow-sm hover:shadow"
                                    title="Edit instrument">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 text-xs text-white bg-gray-900 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none">
                                    Edit
                                </span>
                            </button>

                            {{-- Toggle Status Button --}}
                            @if($instrument->is_active)
                                @if(!$instrument->is_system)
                                    <button onclick="deactivateInstrument({{ $instrument->instrument_id }})"
                                            class="action-toggle-btn relative group p-2 rounded-lg text-warm-coral hover:bg-warm-coral hover:text-white transition-all duration-200 shadow-sm hover:shadow"
                                            title="Deactivate">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636" />
                                        </svg>
                                        <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 text-xs text-white bg-gray-900 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none">
                                            Deactivate
                                        </span>
                                    </button>
                                @else
                                    <span class="text-xs font-medium text-gray-500 bg-gray-100 px-2.5 py-1 rounded">Protected</span>
                                @endif
                            @else
                                <button onclick="activateInstrument({{ $instrument->instrument_id }})"
                                        class="action-toggle-btn relative group p-2 rounded-lg text-forest-green hover:bg-forest-green hover:text-white transition-all duration-200 shadow-sm hover:shadow"
                                        title="Activate">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 text-xs text-white bg-gray-900 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none">
                                        Activate
                                    </span>
                                </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                            <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"></path>
                            </svg>
                            <p class="text-lg font-semibold mb-2">No instruments found</p>
                            <p class="text-sm">Try adjusting your filters or add a new instrument</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Hidden data for JS --}}
<div id="instrument-data" 
     data-categories='@json($categories)'
     style="display: none;"></div>

{{-- Modal Container --}}
<div id="instrument-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <!-- Modal content will be dynamically inserted here by JS -->
</div>

{{-- Toast Container --}}
<div id="toast-container" class="fixed bottom-6 right-6 z-50 space-y-3"></div>

@endsection

@section('scripts')
<script>
    // Initialize filters on page load
    document.addEventListener('DOMContentLoaded', function() {
        applyInstrumentFilters(); // Apply default filter (active only)
    });
</script>
@endsection