{{--
    ============================================================================
    INSTRUCTOR MANAGEMENT PAGE - resources/views/admin/instructors/index.blade.php
    ============================================================================
    Features:
    - Instructor list table with colorful badges and status indicators
    - Advanced filters (specialization, availability, status, rating)
    - Search functionality
    - Instructor detail modal with tabs
    - Specialization management modal
    - Availability editor modal
    - Performance metrics display
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
</head>
<body class="bg-light-gray">

@include('layouts.admin-header')

<main class="lg:ml-64 min-h-screen bg-light-gray">

    {{-- Page Header --}}
    <header class="bg-white shadow-sm p-6 border-b-4 border-warm-coral">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <h1 class="text-3xl font-bold text-primary-dark">Instructor Management</h1>
                <p class="text-secondary-blue mt-1">Manage instructors, specializations, and performance metrics</p>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('admin.users.create') }}?role=instructor" class="bg-forest-green text-white px-6 py-3 rounded-lg font-semibold hover:bg-forest-green-dark transition-all shadow-lg">
                    <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Add New Instructor
                </a>
            </div>
        </div>
    </header>

    <div class="p-4 lg:p-6">

        {{-- Filters Section --}}
        <div class="card p-6 mb-6">
            <form method="GET" action="{{ route('admin.instructors.index') }}" class="space-y-4">
                
                {{-- First Row: Search and Quick Filters --}}
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Search</label>
                        <input type="text" name="search" value="{{ request('search') }}" 
                               placeholder="Search by name, email, or employee ID..." 
                               class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-secondary-blue focus:ring-2 focus:ring-secondary-blue">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Specialization</label>
                        <select name="specialization" class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-secondary-blue">
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
                        <select name="availability" class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-secondary-blue">
                            <option value="all">All</option>
                            <option value="available" {{ request('availability') == 'available' ? 'selected' : '' }}>Available</option>
                            <option value="unavailable" {{ request('availability') == 'unavailable' ? 'selected' : '' }}>Unavailable</option>
                        </select>
                    </div>
                </div>

                {{-- Second Row: More Filters --}}
                <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Status</label>
                        <select name="status" class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-secondary-blue">
                            <option value="all">All</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Min. Rating</label>
                        <select name="rating" class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-secondary-blue">
                            <option value="all">All Ratings</option>
                            <option value="4" {{ request('rating') == '4' ? 'selected' : '' }}>4+ Stars</option>
                            <option value="3" {{ request('rating') == '3' ? 'selected' : '' }}>3+ Stars</option>
                            <option value="2" {{ request('rating') == '2' ? 'selected' : '' }}>2+ Stars</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Sort By</label>
                        <select name="sort_by" class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-secondary-blue">
                            <option value="name" {{ request('sort_by') == 'name' ? 'selected' : '' }}>Name</option>
                            <option value="rating" {{ request('sort_by') == 'rating' ? 'selected' : '' }}>Rating</option>
                            <option value="students" {{ request('sort_by') == 'students' ? 'selected' : '' }}>Active Students</option>
                            <option value="experience" {{ request('sort_by') == 'experience' ? 'selected' : '' }}>Experience</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Date From</label>
                        <input type="date" name="date_from" value="{{ request('date_from') }}" 
                               class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-secondary-blue">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Date To</label>
                        <input type="date" name="date_to" value="{{ request('date_to') }}" 
                               class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-secondary-blue">
                    </div>
                </div>

                {{-- Filter Actions --}}
                <div class="flex gap-3">
                    <button type="submit" class="bg-secondary-blue text-white px-6 py-2 rounded-lg font-semibold hover:bg-secondary-blue-dark transition-all">
                        Apply Filters
                    </button>
                    <a href="{{ route('admin.instructors.index') }}" class="bg-gray-200 text-gray-700 px-6 py-2 rounded-lg font-semibold hover:bg-gray-300 transition-all">
                        Clear All
                    </a>
                </div>
            </form>
        </div>

        {{-- Statistics Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="card p-6 border-l-4 border-warm-coral">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 font-semibold">Total Instructors</p>
                        <p class="text-3xl font-bold text-primary-dark mt-1">{{ $instructors->total() }}</p>
                    </div>
                    <div class="bg-warm-coral bg-opacity-20 p-3 rounded-full">
                        <svg class="w-8 h-8 text-warm-coral" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    </div>
                </div>
            </div>
            
            <div class="card p-6 border-l-4 border-forest-green">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 font-semibold">Available Now</p>
                        <p class="text-3xl font-bold text-primary-dark mt-1">{{ $instructors->where('is_available', true)->count() }}</p>
                    </div>
                    <div class="bg-forest-green bg-opacity-20 p-3 rounded-full">
                        <svg class="w-8 h-8 text-forest-green" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                </div>
            </div>
            
            <div class="card p-6 border-l-4 border-golden-yellow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 font-semibold">Avg. Rating</p>
                        <p class="text-3xl font-bold text-primary-dark mt-1">{{ number_format($instructors->avg('average_rating'), 1) }}</p>
                    </div>
                    <div class="bg-golden-yellow bg-opacity-20 p-3 rounded-full">
                        <svg class="w-8 h-8 text-golden-yellow" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                    </div>
                </div>
            </div>
            
            <div class="card p-6 border-l-4 border-secondary-blue">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 font-semibold">Active Students</p>
                        <p class="text-3xl font-bold text-primary-dark mt-1">{{ $instructors->sum('active_students') }}</p>
                    </div>
                    <div class="bg-secondary-blue bg-opacity-20 p-3 rounded-full">
                        <svg class="w-8 h-8 text-secondary-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- Instructors Table --}}
        <div class="card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-primary-dark">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold text-accent-yellow uppercase tracking-wider">Instructor</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-accent-yellow uppercase tracking-wider">Contact</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-accent-yellow uppercase tracking-wider">Specializations</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-accent-yellow uppercase tracking-wider">Students</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-accent-yellow uppercase tracking-wider">Rating</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-accent-yellow uppercase tracking-wider">Status</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-accent-yellow uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($instructors as $instructor)
                        <tr class="hover:bg-accent-yellow-light transition-colors" id="instructor-row-{{ $instructor->instructor_id }}">
                            
                            {{-- Instructor Info --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10 bg-gradient-to-br from-warm-coral to-golden-yellow rounded-full flex items-center justify-center text-white font-bold text-lg">
                                        {{ substr($instructor->first_name, 0, 1) }}{{ substr($instructor->last_name, 0, 1) }}
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-bold text-gray-900">{{ $instructor->first_name }} {{ $instructor->last_name }}</div>
                                        <div class="text-xs text-gray-500">ID: {{ $instructor->employee_id ?? 'N/A' }}</div>
                                    </div>
                                </div>
                            </td>
                            
                            {{-- Contact --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $instructor->email }}</div>
                                <div class="text-xs text-gray-500">{{ $instructor->phone ?? 'N/A' }}</div>
                            </td>
                            
                            {{-- Specializations --}}
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-1">
                                    @if($instructor->primary_specialization)
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-forest-green text-white">
                                            {{ $instructor->primary_specialization }}
                                        </span>
                                    @endif
                                    @if($instructor->specialization_count > 1)
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-gray-200 text-gray-700">
                                            +{{ $instructor->specialization_count - 1 }} more
                                        </span>
                                    @endif
                                    @if($instructor->specialization_count == 0)
                                        <span class="text-xs text-gray-400">None</span>
                                    @endif
                                </div>
                            </td>
                            
                            {{-- Students --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-semibold text-primary-dark">{{ $instructor->active_students }} Active</div>
                                <div class="text-xs text-gray-500">{{ $instructor->total_students_taught }} Total</div>
                            </td>
                            
                            {{-- Rating --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($instructor->average_rating)
                                    <div class="flex items-center">
                                        <span class="text-sm font-bold text-golden-yellow mr-1">{{ number_format($instructor->average_rating, 1) }}</span>
                                        <svg class="w-4 h-4 text-golden-yellow" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                                    </div>
                                @else
                                    <span class="text-xs text-gray-400">No ratings</span>
                                @endif
                            </td>
                            
                            {{-- Status --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex flex-col gap-1">
                                    @if($instructor->is_active)
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-forest-green text-white">Active</span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-gray-400 text-white">Inactive</span>
                                    @endif
                                    
                                    @if($instructor->is_available)
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-secondary-blue text-white">Available</span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-warm-coral text-white">Busy</span>
                                    @endif
                                </div>
                            </td>
                            
                            {{-- Actions --}}
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <div class="flex gap-2">
                                    <button onclick="viewInstructor({{ $instructor->instructor_id }})" class="action-btn text-secondary-blue">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                        <span class="tooltip">View Details</span>
                                    </button>
                                    
                                    <button onclick="manageSpecializations({{ $instructor->instructor_id }})" class="action-btn text-warm-coral">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                                        <span class="tooltip">Specializations</span>
                                    </button>
                                    
                                    <button onclick="viewPerformance({{ $instructor->instructor_id }})" class="action-btn text-golden-yellow">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                                        <span class="tooltip">Performance</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                                <p class="text-lg font-semibold">No instructors found</p>
                                <p class="text-sm mt-2">Try adjusting your filters or add a new instructor</p>
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

{{-- Instructor Detail Modal (will be populated via JS) --}}
<div id="instructor-detail-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-4"></div>

{{-- Specialization Management Modal (will be populated via JS) --}}
<div id="specialization-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-4"></div>

{{-- Performance Report Modal (will be populated via JS) --}}
<div id="performance-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-4"></div>

{{-- Toast Container --}}
<div id="toast-container" class="fixed bottom-6 right-6 z-50 space-y-3"></div>

</body>
</html>