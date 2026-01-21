{{-- resources/views/admin/inventory/index.blade.php --}}
@extends('layouts.admin-default')

@section('title', 'Inventory Management')

@section('headername')
    <h1 class="text-2xl font-bold text-gray-800">Inventory Management</h1>
    <p class="text-gray-600 mt-1 text-sm">
        Total Items: <span class="font-semibold">{{ $stats['total_items'] ?? 0 }}</span> •
        Low Stock: <span class="text-amber-600 font-semibold">{{ $stats['low_stock'] ?? 0 }}</span> •
        Out of Stock: <span class="text-red-600 font-semibold">{{ $stats['out_of_stock'] ?? 0 }}</span>
    </p>
@endsection

@section('header_actions')
    <a href="{{ route('admin.inventory.create') }}"
       class="inline-flex items-center px-5 py-2.5 bg-[#2d2d2d] text-white rounded-lg shadow-sm hover:bg-[#525252] transition-all duration-200 font-medium">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
        </svg>
        Add New Item
    </a>
@endsection

@section('maincontent')
<div class="mt-4 px-6 lg:px-8">

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="mb-6 p-4 rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    {{-- Quick Stats Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
            <p class="text-sm font-medium text-gray-500">Total Inventory Value</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">
                ₱{{ number_format($stats['total_inventory_value'] ?? 0, 2) }}
            </p>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
            <p class="text-sm font-medium text-gray-500">Active Items</p>
            <p class="text-2xl font-bold text-indigo-600 mt-1">
                {{ $stats['active_items'] ?? ($stats['total_items'] ?? 0) }}
            </p>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
            <p class="text-sm font-medium text-amber-600">Low Stock Items</p>
            <p class="text-2xl font-bold text-amber-700 mt-1">
                {{ $stats['low_stock'] ?? 0 }}
            </p>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
            <p class="text-sm font-medium text-red-600">Out of Stock</p>
            <p class="text-2xl font-bold text-red-700 mt-1">
                {{ $stats['out_of_stock'] ?? 0 }}
            </p>
        </div>
    </div>

    {{-- Filters & Search --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div class="md:col-span-2">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Search by name, code, brand..."
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <div>
                <select name="type" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">All Item Types</option>
                    @foreach($itemTypes ?? [] as $type)
                        <option value="{{ $type }}" {{ request('type') === $type ? 'selected' : '' }}>
                            {{ $type }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <select name="stock_status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">All Stock Status</option>
                    <option value="available" {{ request('stock_status') === 'available' ? 'selected' : '' }}>Available</option>
                    <option value="low" {{ request('stock_status') === 'low' ? 'selected' : '' }}>Low Stock</option>
                    <option value="out" {{ request('stock_status') === 'out' ? 'selected' : '' }}>Out of Stock</option>
                </select>
            </div>

            <div class="flex items-end gap-3">
                <button type="submit" class="flex-1 bg-[#2d2d2d] text-white px-6 py-2 rounded-lg hover:bg-[#525252] transition">
                    Filter
                </button>
                <a href="{{ route('admin.inventory.index') }}"
                   class="px-6 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition">
                    Reset
                </a>
            </div>
        </form>
    </div>

    {{-- Inventory Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full divide-y divide-gray-200 text-sm">
                <thead>
                    <tr class="bg-[#2d2d2d] text-white">
                        <th class="px-4 py-4 text-left text-xs font-bold uppercase tracking-wider">Code</th>
                        <th class="px-4 py-4 text-left text-xs font-bold uppercase tracking-wider">Item Name</th>
                        <th class="px-4 py-4 text-left text-xs font-bold uppercase tracking-wider">Type</th>
                        <th class="px-4 py-4 text-center text-xs font-bold uppercase tracking-wider">Stock</th>
                        <th class="px-4 py-4 text-center text-xs font-bold uppercase tracking-wider">Unit Price</th>
                        <th class="px-4 py-4 text-left text-xs font-bold uppercase tracking-wider">Supplier</th>
                        <th class="px-4 py-4 text-center text-xs font-bold uppercase tracking-wider">Status</th>
                        <th class="px-4 py-4 text-right text-xs font-bold uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>

                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($inventoryItems as $item)
                        <tr class="hover:bg-gray-50 transition-colors {{ session('highlighted_item_id') == $item->item_id ? 'bg-emerald-50 border-l-4 border-emerald-500' : '' }}">
                            <td class="px-4 py-4 font-medium text-gray-900 whitespace-nowrap">
                                {{ $item->item_code }}
                            </td>

                            <td class="px-4 py-4">
                                <div class="font-medium text-gray-900">{{ $item->item_name }}</div>
                                <div class="text-xs text-gray-500 mt-0.5">
                                    {{ $item->brand ?? '' }}{{ $item->model ? ' • '.$item->model : '' }}
                                </div>
                            </td>

                            <td class="px-4 py-4 text-gray-600 whitespace-nowrap">
                                {{ $item->item_type }}
                            </td>

                            <td class="px-4 py-4 text-center">
                                @if(!$item->is_active)
                                    <span class="text-gray-500 font-medium">{{ $item->quantity }}</span>
                                @elseif($item->quantity === 0)
                                    <span class="text-red-600 font-medium">0</span>
                                @elseif($item->quantity <= $item->low_stock_threshold)
                                    <span class="text-amber-600 font-medium">{{ $item->quantity }}</span>
                                @else
                                    <span class="text-green-600 font-medium">{{ $item->quantity }}</span>
                                @endif
                                <span class="text-xs text-gray-500"> / {{ $item->low_stock_threshold }}</span>
                            </td>

                            <td class="px-4 py-4 text-center whitespace-nowrap">
                                ₱{{ number_format($item->unit_price ?? 0, 2) }}
                            </td>

                            <td class="px-4 py-4 text-gray-600 whitespace-nowrap">
                                {{ $item->supplier?->supplier_name ?? '—' }}
                            </td>

                            <td class="px-4 py-4 text-center whitespace-nowrap">
                                @if(!$item->is_active)
                                    <span class="inline-flex px-2.5 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-500 border border-gray-300">
                                        Inactive
                                    </span>
                                @elseif($item->quantity === 0)
                                    <span class="inline-flex px-2.5 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800">
                                        Out of Stock
                                    </span>
                                @elseif($item->quantity <= $item->low_stock_threshold)
                                    <span class="inline-flex px-2.5 py-1 text-xs font-medium rounded-full bg-amber-100 text-amber-800">
                                        Low Stock
                                    </span>
                                @else
                                    <span class="inline-flex px-2.5 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">
                                        Active
                                    </span>
                                @endif
                            </td>

                            <td class="px-4 py-4 text-right whitespace-nowrap text-sm font-medium space-x-3">
                                <a href="{{ route('admin.inventory.edit', $item->item_id) }}"
                                   class="text-indigo-600 hover:text-indigo-800 transition-colors" title="Edit">
                                    <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                    </svg>
                                </a>

                                <form action="{{ route('admin.inventory.destroy', $item->item_id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" onclick="return confirm('Permanently delete this item?')"
                                            class="text-red-600 hover:text-red-800 transition-colors" title="Delete">
                                        <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 01 16.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-16 text-center text-gray-500">
                                No inventory items found matching your criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-6 py-5 border-t border-gray-200">
            {{ $inventoryItems->links('pagination::tailwind') }}
        </div>
    </div>
</div>
@endsection