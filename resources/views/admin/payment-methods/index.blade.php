{{-- 
    resources/views/admin/payment-methods/index.blade.php

    Admin Payment Methods Module
    - Compact responsive UI
    - No large decorative circles
    - No table initials; row numbers are used instead
    - CRUD handled through modal forms and payment-method.js
--}}

@extends('layouts.admin')

@section('title', 'Payment Methods')

@section('content')
@vite(['resources/css/style.css'])

@php
    $currentSort = request('sort_by', 'method_name');
    $currentOrder = request('sort_order', 'asc');
@endphp

<div class="min-h-screen bg-gray-100 px-3 py-4 sm:px-5 lg:px-6">
    {{-- PAGE HEADER --}}
    <div class="mb-4 rounded-xl border border-gray-200 bg-white p-4 shadow-sm sm:p-5">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 sm:text-3xl">Payment method management</h1>
                <p class="mt-1 text-sm text-gray-600">Manage accepted payment options used in payment records.</p>
            </div>

            <button type="button"
                    onclick="openCreateModal()"
                    class="inline-flex items-center justify-center rounded-lg bg-forest-green px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-forest-green-dark">
                Add payment method
            </button>
        </div>
    </div>

    {{-- STATISTICS CARDS --}}
    <div class="mb-4 grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
        <button type="button"
                onclick="filterByStatus('all')"
                class="rounded-xl border border-gray-200 bg-white p-4 text-left shadow-sm transition hover:border-secondary-blue hover:shadow-md">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Total methods</p>
            <p class="admin-stat-number mt-2 text-2xl font-bold text-gray-900" data-count="{{ $stats['total'] }}">{{ $stats['total'] }}</p>
            <p class="mt-1 text-xs text-gray-500">All recorded methods</p>
        </button>

        <button type="button"
                onclick="filterByStatus('active')"
                class="rounded-xl border border-gray-200 bg-white p-4 text-left shadow-sm transition hover:border-forest-green hover:shadow-md">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Active</p>
            <p class="admin-stat-number mt-2 text-2xl font-bold text-gray-900" data-count="{{ $stats['active'] }}">{{ $stats['active'] }}</p>
            <p class="mt-1 text-xs text-gray-500">Available for use</p>
        </button>

        <button type="button"
                onclick="filterByStatus('inactive')"
                class="rounded-xl border border-gray-200 bg-white p-4 text-left shadow-sm transition hover:border-warm-coral hover:shadow-md">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Inactive</p>
            <p class="admin-stat-number mt-2 text-2xl font-bold text-gray-900" data-count="{{ $stats['inactive'] }}">{{ $stats['inactive'] }}</p>
            <p class="mt-1 text-xs text-gray-500">Hidden or disabled</p>
        </button>

        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Most popular</p>
            <p class="mt-2 truncate text-lg font-bold text-gray-900">
                {{ $stats['most_used'] ? $stats['most_used']->method_name : 'N/A' }}
            </p>
            <p class="mt-1 text-xs text-gray-500">
                {{ $stats['most_used']->count ?? 0 }} payment{{ (($stats['most_used']->count ?? 0) == 1) ? '' : 's' }}
            </p>
        </div>
    </div>

    {{-- FILTERS --}}
    <div class="mb-4 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
        <form method="GET" id="filterForm" class="grid grid-cols-1 gap-3 md:grid-cols-4">
            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-600">Search</label>
                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-secondary-blue focus:ring-2 focus:ring-secondary-blue"
                       placeholder="Search name or description">
            </div>

            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-600">Status</label>
                <select name="status"
                        id="statusFilter"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-secondary-blue focus:ring-2 focus:ring-secondary-blue">
                    <option value="all" {{ request('status', 'all') === 'all' ? 'selected' : '' }}>All statuses</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>

            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-600">Sort by</label>
                <select name="sort_by"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-secondary-blue focus:ring-2 focus:ring-secondary-blue">
                    <option value="method_name" {{ $currentSort === 'method_name' ? 'selected' : '' }}>Name</option>
                    <option value="created_at" {{ $currentSort === 'created_at' ? 'selected' : '' }}>Created date</option>
                    <option value="usage" {{ $currentSort === 'usage' ? 'selected' : '' }}>Usage</option>
                </select>
            </div>

            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-600">Order</label>
                <select name="sort_order"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-secondary-blue focus:ring-2 focus:ring-secondary-blue">
                    <option value="asc" {{ $currentOrder === 'asc' ? 'selected' : '' }}>Ascending</option>
                    <option value="desc" {{ $currentOrder === 'desc' ? 'selected' : '' }}>Descending</option>
                </select>
            </div>
        </form>

        <div class="mt-3 flex flex-wrap gap-2">
            <button type="submit"
                    form="filterForm"
                    class="rounded-lg bg-secondary-blue px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-secondary-blue-dark">
                Apply
            </button>

            <a href="{{ route('admin.payment-methods.index') }}"
               class="rounded-lg bg-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-300">
                Clear
            </a>
        </div>
    </div>

    {{-- PAYMENT METHODS TABLE --}}
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-[860px] w-full text-sm">
                <thead class="bg-primary-dark">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-accent-yellow">#</th>
                        <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-accent-yellow">Method</th>
                        <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-accent-yellow">Description</th>
                        <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-accent-yellow">Usage</th>
                        <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-accent-yellow">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-accent-yellow">Created</th>
                        <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-accent-yellow">Actions</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-200">
                    @forelse($methods as $method)
                        <tr class="transition hover:bg-gray-50">
                            <td class="px-4 py-3 font-semibold text-gray-700">
                                {{ $methods->firstItem() + $loop->index }}
                            </td>

                            <td class="px-4 py-3">
                                <p class="font-semibold text-gray-900">{{ $method->method_name }}</p>
                                <p class="text-xs text-gray-500">ID: {{ $method->method_id }}</p>
                            </td>

                            <td class="max-w-xs px-4 py-3 text-gray-600">
                                <span class="line-clamp-2">
                                    {{ $method->description ?: 'No description provided.' }}
                                </span>
                            </td>

                            <td class="px-4 py-3">
                                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold
                                    {{ $method->usage_count > 0 ? 'bg-secondary-blue text-white' : 'bg-gray-200 text-gray-600' }}">
                                    {{ $method->usage_count }} payment{{ $method->usage_count == 1 ? '' : 's' }}
                                </span>
                            </td>

                            <td class="px-4 py-3">
                                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold
                                    {{ $method->is_active ? 'bg-forest-green text-white' : 'bg-gray-400 text-white' }}">
                                    {{ $method->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>

                            <td class="px-4 py-3 text-gray-600">
                                {{ $method->created_at ? \Carbon\Carbon::parse($method->created_at)->format('M d, Y') : 'N/A' }}
                            </td>

                            <td class="px-4 py-3">
                                <div class="flex flex-wrap gap-2">
                                    <button type="button"
                                            onclick="openEditModal({{ $method->method_id }})"
                                            class="rounded-lg bg-gray-100 px-3 py-1.5 text-xs font-semibold text-secondary-blue transition hover:bg-gray-200">
                                        Edit
                                    </button>

                                    <button type="button"
                                            onclick="togglePaymentMethodStatus({{ $method->method_id }})"
                                            class="rounded-lg bg-gray-100 px-3 py-1.5 text-xs font-semibold text-golden-yellow-dark transition hover:bg-gray-200">
                                        {{ $method->is_active ? 'Deactivate' : 'Activate' }}
                                    </button>

                                    <button type="button"
                                            onclick="deleteMethod({{ $method->method_id }}, {{ $method->usage_count }})"
                                            class="rounded-lg bg-gray-100 px-3 py-1.5 text-xs font-semibold text-warm-coral transition hover:bg-gray-200">
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-10 text-center">
                                <p class="text-sm font-semibold text-gray-700">No payment methods found.</p>
                                <p class="mt-1 text-xs text-gray-500">Try clearing the filters or adding a new payment method.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($methods->hasPages())
            <div class="border-t border-gray-200 px-4 py-3">
                {{ $methods->links() }}
            </div>
        @endif
    </div>
</div>

{{-- CREATE MODAL --}}
<div id="createModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4">
    <div class="w-full max-w-lg rounded-xl bg-white shadow-2xl">
        <div class="flex items-center justify-between border-b p-4">
            <h3 class="text-lg font-bold text-gray-900">Create payment method</h3>
            <button type="button"
                    onclick="closeCreateModal()"
                    class="rounded-lg p-2 text-gray-500 transition hover:bg-gray-100 hover:text-gray-700">
                Close
            </button>
        </div>

        <form id="createForm" onsubmit="submitCreate(event)">
            @csrf

            <div class="space-y-4 p-4">
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Method name <span class="text-red-500">*</span></label>
                    <input type="text"
                           name="method_name"
                           required
                           maxlength="50"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-secondary-blue focus:ring-2 focus:ring-secondary-blue"
                           placeholder="e.g., Cash, GCash, Bank transfer">
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Description</label>
                    <textarea name="description"
                              rows="3"
                              maxlength="500"
                              class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-secondary-blue focus:ring-2 focus:ring-secondary-blue"
                              placeholder="Optional notes about this payment method"></textarea>
                </div>

                <p class="hidden rounded-lg bg-red-50 px-3 py-2 text-sm text-red-600" id="create-error"></p>
            </div>

            <div class="flex flex-wrap justify-end gap-2 border-t bg-gray-50 p-4">
                <button type="button"
                        onclick="closeCreateModal()"
                        class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold transition hover:bg-gray-100">
                    Cancel
                </button>

                <button type="submit"
                        class="rounded-lg bg-forest-green px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-forest-green-dark">
                    Create
                </button>
            </div>
        </form>
    </div>
</div>

{{-- EDIT MODAL --}}
<div id="editModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4">
    <div class="w-full max-w-lg rounded-xl bg-white shadow-2xl">
        <div class="flex items-center justify-between border-b p-4">
            <h3 class="text-lg font-bold text-gray-900">Edit payment method</h3>
            <button type="button"
                    onclick="closeEditModal()"
                    class="rounded-lg p-2 text-gray-500 transition hover:bg-gray-100 hover:text-gray-700">
                Close
            </button>
        </div>

        <form id="editForm" onsubmit="submitEdit(event)">
            @csrf
            @method('PUT')

            <input type="hidden" id="edit-method-id" name="method_id">

            <div class="space-y-4 p-4">
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Method name <span class="text-red-500">*</span></label>
                    <input type="text"
                           id="edit-method-name"
                           name="method_name"
                           required
                           maxlength="50"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-secondary-blue focus:ring-2 focus:ring-secondary-blue">
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Description</label>
                    <textarea id="edit-description"
                              name="description"
                              rows="3"
                              maxlength="500"
                              class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-secondary-blue focus:ring-2 focus:ring-secondary-blue"></textarea>
                </div>

                <p class="hidden rounded-lg bg-red-50 px-3 py-2 text-sm text-red-600" id="edit-error"></p>
            </div>

            <div class="flex flex-wrap justify-end gap-2 border-t bg-gray-50 p-4">
                <button type="button"
                        onclick="closeEditModal()"
                        class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold transition hover:bg-gray-100">
                    Cancel
                </button>

                <button type="submit"
                        class="rounded-lg bg-secondary-blue px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-secondary-blue-dark">
                    Update
                </button>
            </div>
        </form>
    </div>
</div>

@vite('resources/js/admin-pages/payment-method.js')
@endsection
