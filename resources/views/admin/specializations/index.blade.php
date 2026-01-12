{{--
    ============================================================================
    SPECIALIZATION MANAGEMENT PAGE - resources/views/admin/specializations/index.blade.php
    ============================================================================
    Features:
    - CRUD operations for specializations (Guitar, Piano, Drums, etc.)
    - Statistics cards (Total, Active, Most Used)
    - Search and filter functionality
    - Usage checking before deletion
    - View assigned instructors modal
    - Toggle active/inactive status
    ============================================================================
--}}

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Specialization Management - Admin Dashboard</title>
    @vite(['resources/css/style.css', 'resources/js/app.js', 'resources/js/admin-pages/specialization.js'])
</head>

<body class="bg-light-gray">

@include('layouts.admin-header')

<main class="lg:ml-64 min-h-screen bg-light-gray">

    {{-- Page Header --}}
    <header class="bg-white shadow-sm p-6 border-b-4 border-secondary-blue">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <h1 class="text-3xl font-bold text-primary-dark">Specialization management</h1>
                <p class="text-secondary-blue mt-1">Manage instructor specializations and teaching areas</p>
            </div>
            <button onclick="openAddModal()" class="bg-forest-green text-white px-6 py-3 rounded-lg font-semibold hover:bg-forest-green-dark transition-all shadow-lg">
                <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Add specialization
            </button>
        </div>
    </header>

    <div class="p-4 lg:p-6">

        {{-- Statistics Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="card p-6 border-l-4 border-secondary-blue">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 font-semibold">Total specializations</p>
                        <p class="text-3xl font-bold text-primary-dark mt-1">{{ $stats['total'] }}</p>
                    </div>
                    <div class="bg-secondary-blue bg-opacity-20 p-3 rounded-full">
                        <svg class="w-8 h-8 text-secondary-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"></path></svg>
                    </div>
                </div>
            </div>
            
            <div class="card p-6 border-l-4 border-forest-green">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 font-semibold">Active</p>
                        <p class="text-3xl font-bold text-primary-dark mt-1">{{ $stats['active'] }}</p>
                    </div>
                    <div class="bg-forest-green bg-opacity-20 p-3 rounded-full">
                        <svg class="w-8 h-8 text-forest-green" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                </div>
            </div>
            
            <div class="card p-6 border-l-4 border-warm-coral">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 font-semibold">Inactive</p>
                        <p class="text-3xl font-bold text-primary-dark mt-1">{{ $stats['inactive'] }}</p>
                    </div>
                    <div class="bg-warm-coral bg-opacity-20 p-3 rounded-full">
                        <svg class="w-8 h-8 text-warm-coral" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
                    </div>
                </div>
            </div>
            
            <div class="card p-6 border-l-4 border-golden-yellow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 font-semibold">Most popular</p>
                        <p class="text-lg font-bold text-primary-dark mt-1">{{ $stats['most_used_name'] }}</p>
                        <p class="text-xs text-gray-500">{{ $stats['most_used_count'] }} instructors</p>
                    </div>
                    <div class="bg-golden-yellow bg-opacity-20 p-3 rounded-full">
                        <svg class="w-8 h-8 text-golden-yellow" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path></svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filters --}}
        <div class="card p-6 mb-6">
            <form method="GET" action="{{ route('admin.specializations.index') }}" class="flex flex-wrap gap-4">
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="Search by name or description..." 
                           class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-secondary-blue focus:ring-2 focus:ring-secondary-blue">
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Status</label>
                    <select name="status" class="px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-secondary-blue">
                        <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>All statuses</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active only</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive only</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Sort by</label>
                    <select name="sort_by" class="px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-secondary-blue">
                        <option value="name" {{ request('sort_by') == 'name' ? 'selected' : '' }}>Name (A-Z)</option>
                        <option value="instructors" {{ request('sort_by') == 'instructors' ? 'selected' : '' }}>Most used</option>
                        <option value="newest" {{ request('sort_by') == 'newest' ? 'selected' : '' }}>Newest first</option>
                        <option value="oldest" {{ request('sort_by') == 'oldest' ? 'selected' : '' }}>Oldest first</option>
                    </select>
                </div>
                
                <div class="flex items-end gap-2">
                    <button type="submit" class="bg-secondary-blue text-white px-6 py-2 rounded-lg font-semibold hover:bg-secondary-blue-dark transition-all">
                        Apply
                    </button>
                    <a href="{{ route('admin.specializations.index') }}" class="bg-gray-200 text-gray-700 px-6 py-2 rounded-lg font-semibold hover:bg-gray-300 transition-all">
                        Clear
                    </a>
                </div>
            </form>
        </div>

        {{-- Specializations Table --}}
        <div class="card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-primary-dark">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold text-accent-yellow uppercase tracking-wider">Specialization</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-accent-yellow uppercase tracking-wider">Description</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-accent-yellow uppercase tracking-wider">Instructors</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-accent-yellow uppercase tracking-wider">Status</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-accent-yellow uppercase tracking-wider">Created</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-accent-yellow uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($specializations as $spec)
                        <tr class="hover:bg-accent-yellow-light transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10 bg-gradient-to-br from-secondary-blue to-forest-green rounded-full flex items-center justify-center text-white font-bold">
                                        {{ substr($spec->specialization_name, 0, 1) }}
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-bold text-gray-900">{{ $spec->specialization_name }}</div>
                                        <div class="text-xs text-gray-500">ID: {{ $spec->specialization_id }}</div>
                                    </div>
                                </div>
                            </td>
                            
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900">{{ Str::limit($spec->description ?? 'No description', 50) }}</div>
                            </td>
                            
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($spec->instructor_count > 0)
                                    <button onclick="viewInstructors({{ $spec->specialization_id }})" class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-secondary-blue text-white hover:bg-secondary-blue-dark transition-all">
                                        {{ $spec->instructor_count }} {{ $spec->instructor_count == 1 ? 'instructor' : 'instructors' }}
                                    </button>
                                @else
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-gray-200 text-gray-700">
                                        No instructors
                                    </span>
                                @endif
                            </td>
                            
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $spec->is_active ? 'bg-forest-green text-white' : 'bg-gray-400 text-white' }}">
                                    {{ $spec->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ date('M d, Y', strtotime($spec->created_at)) }}
                            </td>
                            
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <div class="flex gap-2">
                                    <button onclick="editSpecialization({{ $spec->specialization_id }})" class="action-btn text-secondary-blue">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                        <span class="tooltip">Edit</span>
                                    </button>
                                    
                                    <button onclick="toggleStatus({{ $spec->specialization_id }})" class="action-btn {{ $spec->is_active ? 'text-warm-coral' : 'text-forest-green' }}">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            @if($spec->is_active)
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                                            @else
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            @endif
                                        </svg>
                                        <span class="tooltip">{{ $spec->is_active ? 'Deactivate' : 'Activate' }}</span>
                                    </button>
                                    
                                    <button onclick="deleteSpecialization({{ $spec->specialization_id }})" class="action-btn text-red-600">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        <span class="tooltip">Delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"></path></svg>
                                <p class="text-lg font-semibold">No specializations found</p>
                                <p class="text-sm mt-2">Try adjusting your filters or add a new specialization</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            {{-- Pagination --}}
            @if($specializations->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                {{ $specializations->links() }}
            </div>
            @endif
        </div>
    </div>

</main>

{{-- Add/Edit Modal (will be populated by JS) --}}
<div id="specialization-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-4"></div>

{{-- Instructor List Modal (will be populated by JS) --}}
<div id="instructor-list-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-4"></div>

{{-- Toast Container --}}
<div id="toast-container" class="fixed bottom-6 right-6 z-50 space-y-3"></div>

</body>
</html>