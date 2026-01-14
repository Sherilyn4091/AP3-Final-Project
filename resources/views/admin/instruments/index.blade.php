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
<div class="container mx-auto px-4 py-8">
    
    {{-- Page Header --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-primary-dark mb-2">Instrument management</h1>
            <p class="text-gray-600">Manage musical instruments offered for lessons</p>
        </div>
        <button onclick="openAddInstrumentModal()" class="mt-4 md:mt-0 bg-forest-green text-white px-6 py-3 rounded-lg hover:bg-forest-green-dark font-semibold shadow-lg transition-all">
            <span class="flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Add instrument
            </span>
        </button>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6 mb-8">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Search name</label>
                <input type="text" id="search-name" placeholder="Guitar, Piano..." 
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-secondary-blue focus:border-secondary-blue transition-all text-sm">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Category</label>
                <select id="filter-category" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-secondary-blue focus:border-secondary-blue transition-all text-sm">
                    <option value="all">All categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}">{{ $cat }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Status</label>
                <select id="filter-status" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-secondary-blue focus:border-secondary-blue transition-all text-sm">
                    <option value="all">All</option>
                    <option value="active" selected>Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>

            <div class="flex items-end gap-3">
                <button onclick="applyInstrumentFilters()" 
                        class="flex-1 bg-secondary-blue hover:bg-secondary-blue-dark text-white px-5 py-2.5 rounded-lg font-medium transition-colors shadow-sm">
                    Filter
                </button>
                <button onclick="clearInstrumentFilters()" 
                        class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 px-5 py-2.5 rounded-lg font-medium transition-colors border border-gray-300">
                    Clear
                </button>
            </div>
        </div>
    </div>

    <!-- Single Table – modern version -->
    <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gradient-to-r from-secondary-blue to-primary-dark">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-white uppercase tracking-wider">#</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-white uppercase tracking-wider">Instrument</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-white uppercase tracking-wider">Category</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-white uppercase tracking-wider hidden md:table-cell">Description</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-white uppercase tracking-wider">Students</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-white uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-white uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @forelse($instruments as $index => $instrument)
                    <tr class="instrument-row hover:bg-blue-50/40 transition-colors duration-150"
                        data-name="{{ strtolower($instrument->instrument_name) }}"
                        data-category="{{ $instrument->category }}"
                        data-status="{{ $instrument->is_active ? 'active' : 'inactive' }}">

                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $index + 1 }}</td>

                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <span class="font-medium text-gray-900">{{ $instrument->instrument_name }}</span>
                                @if($instrument->is_system)
                                <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-secondary-blue/10 text-secondary-blue">
                                    System
                                </span>
                                @endif
                            </div>
                        </td>

                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                {{ $instrument->category }}
                            </span>
                        </td>

                        <td class="px-6 py-4 text-sm text-gray-600 hidden md:table-cell">
                            {{ $instrument->description ? Str::limit($instrument->description, 60) : '-' }}
                        </td>

                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <span class="inline-flex h-9 w-9 items-center justify-center rounded-full text-sm font-bold
                                {{ $instrument->students_count > 0 ? 'bg-forest-green/10 text-forest-green border border-forest-green/30' : 'bg-gray-100 text-gray-600' }}">
                                {{ $instrument->students_count }}
                            </span>
                        </td>

                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full
                                {{ $instrument->is_active ? 'bg-forest-green/10 text-forest-green border border-forest-green/30' : 'bg-gray-100 text-gray-600 border border-gray-300' }}">
                                {{ $instrument->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>

                        <td class="px-4 md:px-6 py-3 md:py-4 whitespace-nowrap text-center text-sm font-medium">
                            <div class="flex items-center justify-center gap-2 md:gap-3">
                                <button onclick="editInstrument({{ $instrument->instrument_id }})"
                                        class="p-2 rounded-lg text-secondary-blue hover:bg-secondary-blue hover:text-white transition-all duration-200 shadow-sm hover:shadow"
                                        title="Edit instrument">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>

                                @if($instrument->is_active)
                                    @if(!$instrument->is_system)
                                        <button onclick="deactivateInstrument({{ $instrument->instrument_id }})"
                                                class="p-2 rounded-lg text-warm-coral hover:bg-warm-coral hover:text-white transition-all duration-200 shadow-sm hover:shadow"
                                                title="Deactivate">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 5.636l-12.728 12.728m0-12.728l12.728 12.728" />
                                            </svg>
                                        </button>
                                    @else
                                        <span class="text-xs font-medium text-gray-500 bg-gray-100 px-2.5 py-1 rounded">Protected</span>
                                    @endif
                                @else
                                    <button onclick="activateInstrument({{ $instrument->instrument_id }})"
                                            class="p-2 rounded-lg text-forest-green hover:bg-forest-green hover:text-white transition-all duration-200 shadow-sm hover:shadow"
                                            title="Activate">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"></path>
                                </svg>
                                <p class="text-lg font-semibold mb-2">No instruments found</p>
                                <p class="text-sm">Add your first instrument to get started</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Hidden data for JS -->
    <div id="instrument-data" 
         data-categories='@json($categories)'
         style="display: none;"></div>

    <!-- Single Modal Container -->
    <div id="instrument-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <!-- Modal content will be dynamically inserted here by JS -->
    </div>

    <!-- Toast Container -->
    <div id="toast-container" class="fixed top-4 right-4 z-50 space-y-2"></div>
</div>
@endsection

@section('scripts')
<script>
    // Initialize filters on page load
    document.addEventListener('DOMContentLoaded', function() {
        applyInstrumentFilters(); // Apply default filter (active only)
    });
</script>
@endsection