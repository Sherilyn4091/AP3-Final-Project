{{--
    ============================================================================
    GENRE MANAGEMENT PAGE - resources/views/admin/genres/index.blade.php
    ============================================================================
    Features:
    - CRUD operations for genres (Rock, Pop, Jazz, Classical, etc.)
    - Statistics cards (Total, Active, Most Popular)
    - Search and filter functionality
    - Usage checking before deletion
    - View students who prefer this genre modal
    - Toggle active/inactive status
    ============================================================================
--}}

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Genre Management - Admin Dashboard</title>
    @vite(['resources/css/style.css', 'resources/js/app.js', 'resources/js/admin-pages/genre.js'])
</head>

<body class="bg-light-gray">

@include('layouts.admin-header')

<main class="lg:ml-64 min-h-screen bg-light-gray">

    {{-- Page Header --}}
    <header class="bg-white shadow-sm p-4 lg:p-5 border-b-2 border-secondary-blue">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <h1 class="text-2xl md:text-3xl font-bold text-primary-dark">Genre management</h1>
                <p class="text-secondary-blue mt-1">Manage music genres and student preferences</p>
            </div>
            <button onclick="openAddModal()" class="bg-forest-green text-white px-4 py-2.5 rounded-lg text-sm font-semibold hover:bg-forest-green-dark transition-all shadow">
                <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Add genre
            </button>
        </div>
    </header>

    <div class="p-4 lg:p-6">

        {{-- Statistics Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3 mb-5">
            <div class="card p-4 border-l-4 border-secondary-blue">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 font-semibold">Total genres</p>
                        <p class="text-2xl md:text-2xl font-bold text-primary-dark mt-1">{{ $stats['total'] }}</p>
                    </div>
                </div>
            </div>
            
            <div class="card p-4 border-l-4 border-forest-green">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 font-semibold">Active</p>
                        <p class="text-2xl md:text-2xl font-bold text-primary-dark mt-1">{{ $stats['active'] }}</p>
                    </div>
                </div>
            </div>
            
            <div class="card p-4 border-l-4 border-warm-coral">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 font-semibold">Inactive</p>
                        <p class="text-2xl md:text-2xl font-bold text-primary-dark mt-1">{{ $stats['inactive'] }}</p>
                    </div>
                </div>
            </div>
            
            <div class="card p-4 border-l-4 border-golden-yellow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 font-semibold">Most popular</p>
                        <p class="text-base font-bold text-primary-dark mt-1">{{ $stats['most_used_name'] }}</p>
                        <p class="text-xs text-gray-500">{{ $stats['most_used_count'] }} students</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filters --}}
        <div class="card p-4 mb-5">
            <form method="GET" action="{{ route('admin.genres.index') }}" class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-5 xl:items-end">
                <div class="sm:col-span-2 xl:col-span-2">
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
                        <option value="students" {{ request('sort_by') == 'students' ? 'selected' : '' }}>Most popular</option>
                        <option value="newest" {{ request('sort_by') == 'newest' ? 'selected' : '' }}>Newest first</option>
                        <option value="oldest" {{ request('sort_by') == 'oldest' ? 'selected' : '' }}>Oldest first</option>
                    </select>
                </div>
                
                <div class="flex items-end gap-2">
                    <button type="submit" class="bg-secondary-blue text-white px-6 py-2 rounded-lg font-semibold hover:bg-secondary-blue-dark transition-all">
                        Apply
                    </button>
                    <a href="{{ route('admin.genres.index') }}" class="bg-gray-200 text-gray-700 px-6 py-2 rounded-lg font-semibold hover:bg-gray-300 transition-all">
                        Clear
                    </a>
                </div>
            </form>
        </div>

        {{-- Genres Table --}}
        <div class="card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-primary-dark">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-bold text-accent-yellow uppercase tracking-wider">Genre</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-accent-yellow uppercase tracking-wider">Description</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-accent-yellow uppercase tracking-wider">Students (view)</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-accent-yellow uppercase tracking-wider">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-accent-yellow uppercase tracking-wider">Created</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-accent-yellow uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($genres as $genre)
                        <tr class="hover:bg-accent-yellow-light transition-colors">
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <span class="inline-flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-lg bg-gray-100 text-xs font-bold text-gray-700">
                                        {{ $genres->firstItem() + $loop->index }}
                                    </span>
                                    <div>
                                        <div class="text-sm font-bold text-gray-900">{{ $genre->genre_name }}</div>
                                        <div class="text-xs text-gray-500">ID: {{ $genre->genre_id }}</div>
                                    </div>
                                </div>
                            </td>
                            
                            <td class="px-4 py-3">
                                <div class="text-sm text-gray-900">{{ Str::limit($genre->description ?? 'No description', 50) }}</div>
                            </td>
                            
                            <td class="px-4 py-3 whitespace-nowrap">
                                @if($genre->student_count > 0)
                                    <button onclick="viewStudents({{ $genre->genre_id }})" class="inline-flex items-center rounded-full bg-secondary-blue px-2.5 py-1 text-xs font-semibold text-white hover:bg-secondary-blue-dark transition-all">
                                        {{ $genre->student_count }} {{ $genre->student_count == 1 ? 'student' : 'students' }}
                                    </button>
                                @else
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-gray-200 text-gray-700">
                                        No students
                                    </span>
                                @endif
                            </td>
                            
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold {{ $genre->is_active ? 'bg-forest-green text-white' : 'bg-gray-400 text-white' }}">
                                    {{ $genre->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                {{ date('M d, Y', strtotime($genre->created_at)) }}
                            </td>
                            
                            <td class="px-4 py-3 whitespace-nowrap text-sm">
                                <div class="flex flex-wrap gap-1.5">
                                    <button onclick="editGenre({{ $genre->genre_id }})" class="action-btn text-secondary-blue">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                        <span class="tooltip">Edit</span>
                                    </button>
                                    
                                    <button onclick="toggleStatus({{ $genre->genre_id }})" class="action-btn {{ $genre->is_active ? 'text-warm-coral' : 'text-forest-green' }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            @if($genre->is_active)
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                                            @else
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            @endif
                                        </svg>
                                        <span class="tooltip">{{ $genre->is_active ? 'Deactivate' : 'Activate' }}</span>
                                    </button>
                                    
                                    <button onclick="deleteGenre({{ $genre->genre_id }})" class="action-btn text-red-600">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        <span class="tooltip">Delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                                <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"></path></svg>
                                <p class="text-base font-semibold">No genres found</p>
                                <p class="text-sm mt-2">Try adjusting your filters or add a new genre</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            {{-- Pagination --}}
            @if($genres->hasPages())
            <div class="px-4 py-3 border-t border-gray-200 bg-gray-50">
                {{ $genres->links() }}
            </div>
            @endif
        </div>
    </div>

</main>

{{-- Add/Edit Modal (will be populated by JS) --}}
<div id="genre-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-4"></div>

{{-- Student List Modal (will be populated by JS) --}}
<div id="student-list-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-4"></div>

{{-- Toast Container --}}
<div id="toast-container" class="fixed bottom-6 right-6 z-50 space-y-3"></div>

</body>
</html>