{{--/**
 * Payment Methods Index View
 * resources/views/admin/payment-methods/index.blade.php


 * This Blade template provides the administrative interface for managing
 * payment methods in the system. It displays a list of all available payment
 * methods and allows administrators to perform CRUD operations including
 * creating, reading, updating, and deleting payment method records.
 *
 */ --}}

@extends('layouts.admin')

@section('title', 'Payment Methods')

@section('content')

@vite(['resources/css/style.css'])
<div class="min-h-screen bg-gray-100">
    <!-- Page Header -->
    <div class="bg-white shadow-sm p-6 border-b-4 border-secondary-blue mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Payment method management</h1>
                <p class="text-sm text-gray-600 mt-1">Manage accepted payment methods</p>
            </div>
            <button onclick="openCreateModal()"
                    class="bg-forest-green text-white px-6 py-2.5 rounded-lg hover:bg-forest-green-dark transition flex items-center gap-2 shadow-md">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Add payment method
            </button>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <!-- Total Methods Card -->
        <div onclick="filterByStatus('all')" 
             class="bg-white p-6 rounded-xl shadow-md border-2 border-transparent hover:border-secondary-blue cursor-pointer transition-all hover:shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 font-medium">Total methods</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['total'] }}</p>
                </div>
                <div class="w-16 h-16 bg-secondary-blue rounded-full flex items-center justify-center">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Active Card -->
        <div onclick="filterByStatus('active')" 
             class="bg-white p-6 rounded-xl shadow-md border-2 border-transparent hover:border-forest-green cursor-pointer transition-all hover:shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 font-medium">Active</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['active'] }}</p>
                </div>
                <div class="w-16 h-16 bg-forest-green rounded-full"></div>
            </div>
        </div>

        <!-- Inactive Card -->
        <div onclick="filterByStatus('inactive')" 
             class="bg-white p-6 rounded-xl shadow-md border-2 border-transparent hover:border-warm-coral cursor-pointer transition-all hover:shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 font-medium">Inactive</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['inactive'] }}</p>
                </div>
                <div class="w-16 h-16 bg-warm-coral rounded-full"></div>
            </div>
        </div>

        <!-- Most Used Card -->
        <div class="bg-white p-6 rounded-xl shadow-md border-2 border-transparent hover:border-golden-yellow cursor-pointer transition-all hover:shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 font-medium">Most popular</p>
                    <p class="text-xl font-bold text-gray-900 mt-2">
                        {{ $stats['most_used'] ? $stats['most_used']->method_name : 'N/A' }}
                    </p>
                    <p class="text-xs text-gray-500 mt-1">{{ $stats['most_used']->count ?? 0 }} payments</p>
                </div>
                <div class="w-16 h-16 bg-golden-yellow rounded-full"></div>
            </div>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="bg-white p-6 rounded-xl shadow-md mb-6">
        <form method="GET" id="filterForm" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                <input type="text" name="search" value="{{ request('search') }}"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-secondary-blue focus:border-secondary-blue"
                       placeholder="Search by name...">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select name="status" id="statusFilter" 
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-secondary-blue focus:border-secondary-blue">
                    <option value="all">All statuses</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Sort by</label>
                <select name="sort_by" 
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-secondary-blue focus:border-secondary-blue">
                    <option value="method_name">Name (A-Z)</option>
                    <option value="created_at" {{ request('sort_by') == 'created_at' ? 'selected' : '' }}>Created date</option>
                    <option value="usage" {{ request('sort_by') == 'usage' ? 'selected' : '' }}>Usage</option>
                </select>
            </div>
        </form>
        <div class="flex gap-3 mt-4">
            <button type="submit" form="filterForm" 
                    class="bg-secondary-blue text-white px-6 py-2 rounded-lg hover:bg-secondary-blue-dark transition shadow-md">
                Apply
            </button>
            <a href="{{ route('admin.payment-methods.index') }}" 
               class="bg-gray-200 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-300 transition">
                Clear
            </a>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl shadow-md overflow-hidden">
        <table class="min-w-full">
            <thead class="bg-primary-dark">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-bold text-accent-yellow uppercase tracking-wider">Name</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-accent-yellow uppercase tracking-wider">Usage</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-accent-yellow uppercase tracking-wider">Status</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-accent-yellow uppercase tracking-wider">Created</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-accent-yellow uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($methods as $method)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-secondary-blue rounded-full flex items-center justify-center text-white font-bold">
                                    {{ strtoupper(substr($method->method_name, 0, 1)) }}
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-900">{{ $method->method_name }}</p>
                                    <p class="text-xs text-gray-500">ID: {{ $method->method_id }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                                {{ $method->usage_count > 0 ? 'bg-secondary-blue text-white' : 'bg-gray-200 text-gray-600' }}">
                                {{ $method->usage_count }} payment{{ $method->usage_count == 1 ? '' : 's' }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                                {{ $method->is_active ? 'bg-forest-green text-white' : 'bg-gray-400 text-white' }}">
                                {{ $method->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            {{ \Carbon\Carbon::parse($method->created_at)->format('M d, Y') }}
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex gap-2">
                                <button onclick="openEditModal({{ $method->method_id }})" 
                                        class="text-secondary-blue hover:text-secondary-blue-dark p-2 rounded-lg hover:bg-gray-100 transition"
                                        title="Edit">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </button>
                                <button onclick="togglePaymentMethodStatus({{ $method->method_id }})" 
                                        class="text-golden-yellow hover:text-golden-yellow-dark p-2 rounded-lg hover:bg-gray-100 transition"
                                        title="{{ $method->is_active ? 'Deactivate' : 'Activate' }}">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                    </svg>
                                </button>
                                <button onclick="deleteMethod({{ $method->method_id }}, {{ $method->usage_count }})" 
                                        class="text-warm-coral hover:text-warm-coral-dark p-2 rounded-lg hover:bg-gray-100 transition"
                                        title="Delete">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                            </svg>
                            <p class="text-gray-500">No payment methods found</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($methods->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $methods->links() }}
            </div>
        @endif
    </div>
</div>

<!-- CREATE MODAL -->
<div id="createModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl max-w-md w-full">
        <div class="flex justify-between items-center p-6 border-b">
            <h3 class="text-xl font-bold text-gray-900">Create payment method</h3>
            <button onclick="closeCreateModal()" class="text-gray-500 hover:text-gray-700 p-2 rounded-lg hover:bg-gray-100 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <form id="createForm" onsubmit="submitCreate(event)">
            @csrf
            <div class="p-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Method name <span class="text-red-500">*</span></label>
                    <input type="text" name="method_name" required
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-secondary-blue focus:border-secondary-blue"
                           placeholder="e.g., Cash, GCash, Bank transfer">
                    <p class="text-sm text-red-600 mt-2 hidden" id="create-error"></p>
                </div>
            </div>
            <div class="flex justify-end gap-3 p-6 border-t bg-gray-50">
                <button type="button" onclick="closeCreateModal()" 
                        class="px-6 py-2.5 border border-gray-300 rounded-lg hover:bg-gray-100 transition font-medium">
                    Cancel
                </button>
                <button type="submit" 
                        class="px-6 py-2.5 bg-forest-green text-white rounded-lg hover:bg-forest-green-dark transition font-medium shadow-md">
                    Create
                </button>
            </div>
        </form>
    </div>
</div>

<!-- EDIT MODAL -->
<div id="editModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl max-w-md w-full">
        <div class="flex justify-between items-center p-6 border-b">
            <h3 class="text-xl font-bold text-gray-900">Edit payment method</h3>
            <button onclick="closeEditModal()" class="text-gray-500 hover:text-gray-700 p-2 rounded-lg hover:bg-gray-100 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <form id="editForm" onsubmit="submitEdit(event)">
            @csrf
            @method('PUT')
            <input type="hidden" id="edit-method-id" name="method_id">
            <div class="p-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Method name <span class="text-red-500">*</span></label>
                    <input type="text" id="edit-method-name" name="method_name" required
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-secondary-blue focus:border-secondary-blue">
                    <p class="text-sm text-red-600 mt-2 hidden" id="edit-error"></p>
                </div>
            </div>
            <div class="flex justify-end gap-3 p-6 border-t bg-gray-50">
                <button type="button" onclick="closeEditModal()" 
                        class="px-6 py-2.5 border border-gray-300 rounded-lg hover:bg-gray-100 transition font-medium">
                    Cancel
                </button>
                <button type="submit" 
                        class="px-6 py-2.5 bg-secondary-blue text-white rounded-lg hover:bg-secondary-blue-dark transition font-medium shadow-md">
                    Update
                </button>
            </div>
        </form>
    </div>
</div>

@vite('resources/js/admin-pages/payment-method.js')
@endsection