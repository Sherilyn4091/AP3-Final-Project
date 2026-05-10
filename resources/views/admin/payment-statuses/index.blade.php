{{--
    ============================================================================
    PAYMENT STATUS MANAGEMENT PAGE
    ============================================================================
    Features:
    - CRUD operations for payment statuses
    - Compact statistics cards
    - Search, status filter, and sorting
    - Usage checking before deletion
    - Toggle active/inactive status
    ============================================================================
--}}

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Payment Status Management - Admin Dashboard</title>
    @vite(['resources/css/style.css', 'resources/js/admin-pages/payment-status.js'])
</head>

<body class="bg-light-gray">

@include('layouts.admin-header')

<main class="lg:ml-64 min-h-screen bg-light-gray">

    {{-- Page Header --}}
    <header class="bg-white shadow-sm p-4 border-b-4 border-secondary-blue">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-primary-dark">Payment Status Management</h1>
                <p class="text-secondary-blue mt-1 text-sm">Manage payment status labels for manual records</p>
            </div>

            <button onclick="openAddModal()"
                    class="inline-flex items-center justify-center bg-forest-green text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-forest-green-dark transition-all shadow-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Add Status
            </button>
        </div>
    </header>

    <div class="p-4 lg:p-5">

        {{-- Statistics Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3 mb-5">
            <div class="card p-4 border-l-4 border-secondary-blue">
                <p class="text-xs text-gray-600 font-semibold uppercase tracking-wide">Total Statuses</p>
                <p class="text-2xl font-bold text-primary-dark mt-1">{{ $stats['total'] }}</p>
            </div>

            <div class="card p-4 border-l-4 border-forest-green">
                <p class="text-xs text-gray-600 font-semibold uppercase tracking-wide">Active</p>
                <p class="text-2xl font-bold text-primary-dark mt-1">{{ $stats['active'] }}</p>
            </div>

            <div class="card p-4 border-l-4 border-warm-coral">
                <p class="text-xs text-gray-600 font-semibold uppercase tracking-wide">Inactive</p>
                <p class="text-2xl font-bold text-primary-dark mt-1">{{ $stats['inactive'] }}</p>
            </div>

            <div class="card p-4 border-l-4 border-golden-yellow">
                <p class="text-xs text-gray-600 font-semibold uppercase tracking-wide">Most Used</p>
                <p class="text-lg font-bold text-primary-dark mt-1 truncate">{{ $stats['most_used_name'] }}</p>
                <p class="text-xs text-gray-500">
                    {{ $stats['most_used_count'] }} {{ $stats['most_used_count'] === 1 ? 'payment' : 'payments' }}
                </p>
            </div>
        </div>

        {{-- Filters --}}
        <div class="card p-4 mb-5">
            <form method="GET" action="{{ route('admin.payment-statuses.index') }}" class="grid grid-cols-1 gap-3 md:grid-cols-12">
                <div class="md:col-span-5">
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Search</label>
                    <input type="text"
                           name="search"
                           value="{{ request('search') }}"
                           placeholder="Search by status name..."
                           class="w-full px-3 py-2 text-sm border-2 border-gray-300 rounded-lg focus:border-secondary-blue focus:ring-2 focus:ring-secondary-blue">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Status</label>
                    <select name="status" class="w-full px-3 py-2 text-sm border-2 border-gray-300 rounded-lg focus:border-secondary-blue">
                        <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>All</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <div class="md:col-span-3">
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Sort By</label>
                    <select name="sort_by" class="w-full px-3 py-2 text-sm border-2 border-gray-300 rounded-lg focus:border-secondary-blue">
                        <option value="name" {{ request('sort_by') == 'name' ? 'selected' : '' }}>Name A-Z</option>
                        <option value="usage" {{ request('sort_by') == 'usage' ? 'selected' : '' }}>Most Used</option>
                        <option value="newest" {{ request('sort_by') == 'newest' ? 'selected' : '' }}>Newest</option>
                        <option value="oldest" {{ request('sort_by') == 'oldest' ? 'selected' : '' }}>Oldest</option>
                    </select>
                </div>

                <div class="md:col-span-2 flex items-end gap-2">
                    <button type="submit" class="flex-1 bg-secondary-blue text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-secondary-blue-dark transition-all">
                        Apply
                    </button>
                    <a href="{{ route('admin.payment-statuses.index') }}" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-semibold hover:bg-gray-300 transition-all">
                        Clear
                    </a>
                </div>
            </form>
        </div>

        {{-- Payment Statuses Table --}}
        <div class="card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-primary-dark">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-bold text-accent-yellow uppercase tracking-wider">Status Name</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-accent-yellow uppercase tracking-wider">Usage</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-accent-yellow uppercase tracking-wider">Availability</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-accent-yellow uppercase tracking-wider">Created</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-accent-yellow uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($statuses as $status)
                            <tr class="hover:bg-accent-yellow-light transition-colors">
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="font-bold text-gray-900">{{ $status->status_name }}</div>
                                    <div class="text-xs text-gray-500">ID: {{ $status->status_id }}</div>
                                </td>

                                <td class="px-4 py-3 whitespace-nowrap">
                                    @if($status->usage_count > 0)
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-secondary-blue text-white">
                                            Used in {{ $status->usage_count }} {{ $status->usage_count == 1 ? 'payment' : 'payments' }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-gray-200 text-gray-700">
                                            Not used yet
                                        </span>
                                    @endif
                                </td>

                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold {{ $status->is_active ? 'bg-forest-green text-white' : 'bg-gray-400 text-white' }}">
                                        {{ $status->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>

                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                    {{ $status->created_at ? date('M d, Y', strtotime($status->created_at)) : 'â€”' }}
                                </td>

                                <td class="px-4 py-3 whitespace-nowrap text-sm">
                                    <div class="flex gap-2">
                                        <button type="button"
                                                onclick="editStatus({{ $status->status_id }}, @js($status->status_name))"
                                                class="action-btn text-secondary-blue">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                            <span class="tooltip">Edit</span>
                                        </button>

                                        <button type="button"
                                                onclick="toggleStatus({{ $status->status_id }})"
                                                class="action-btn {{ $status->is_active ? 'text-warm-coral' : 'text-forest-green' }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                @if($status->is_active)
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                          d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                                @else
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                          d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                @endif
                                            </svg>
                                            <span class="tooltip">{{ $status->is_active ? 'Deactivate' : 'Activate' }}</span>
                                        </button>

                                        <button type="button"
                                                onclick="deleteStatus({{ $status->status_id }})"
                                                class="action-btn text-red-600">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                            <span class="tooltip">Delete</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-10 text-center text-gray-500">
                                    <svg class="w-10 h-10 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <p class="text-base font-semibold">No payment statuses found</p>
                                    <p class="text-sm mt-1">Try adjusting your filters or add a new status.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($statuses->hasPages())
                <div class="px-4 py-3 border-t border-gray-200 bg-gray-50">
                    {{ $statuses->links() }}
                </div>
            @endif
        </div>
    </div>
</main>

{{-- Modal Container --}}
<div id="status-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-4"></div>

{{-- Toast Container --}}
<div id="toast-container" class="fixed bottom-4 right-4 z-50 space-y-2"></div>

</body>
</html>