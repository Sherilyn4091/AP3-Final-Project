{{-- resources/views/admin/inventory/index.blade.php --}}
@extends('layouts.admin-default')

@section('title', 'Inventory Management')

@section('headername')
    <h1 class="text-2xl font-bold text-gray-800">Inventory Management</h1>
    <p class="text-gray-600 mt-1 text-sm">
        Total Items: <span class="font-semibold">{{ $stats['total_items'] ?? 0 }}</span> â€¢
        Low Stock: <span class="text-amber-600 font-semibold">{{ $stats['low_stock'] ?? 0 }}</span> â€¢
        Out of Stock: <span class="text-red-600 font-semibold">{{ $stats['out_of_stock'] ?? 0 }}</span>
    </p>
@endsection

@section('header_actions')
    <div class="flex flex-wrap items-center gap-2">
        <a href="{{ route('admin.inventory.export') }}"
           class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded-lg shadow-sm hover:bg-gray-200 transition-all duration-200 text-sm font-medium">
            Export CSV
        </a>

        <a href="{{ route('admin.inventory.create') }}"
           class="inline-flex items-center px-4 py-2 bg-[#2d2d2d] text-white rounded-lg shadow-sm hover:bg-[#525252] transition-all duration-200 text-sm font-medium">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            Add Item
        </a>
    </div>
@endsection

@section('maincontent')
<div class="mt-4 px-4 lg:px-6">

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="mb-4 p-3 rounded-lg border border-emerald-200 bg-emerald-50 text-sm text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 p-3 rounded-lg border border-red-200 bg-red-50 text-sm text-red-800">
            {{ session('error') }}
        </div>
    @endif

    {{-- Quick Stats Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow-sm p-4 border border-gray-200">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Total Inventory Value</p>
            <p class="text-xl font-bold text-gray-900 mt-1">
                â‚±{{ number_format($stats['total_inventory_value'] ?? 0, 2) }}
            </p>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-4 border border-gray-200">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Active Items</p>
            <p class="text-xl font-bold text-indigo-600 mt-1">
                {{ $stats['active_items'] ?? ($stats['total_items'] ?? 0) }}
            </p>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-4 border border-gray-200">
            <p class="text-xs font-semibold text-amber-600 uppercase tracking-wide">Low Stock Items</p>
            <p class="text-xl font-bold text-amber-700 mt-1">
                {{ $stats['low_stock'] ?? 0 }}
            </p>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-4 border border-gray-200">
            <p class="text-xs font-semibold text-red-600 uppercase tracking-wide">Out of Stock</p>
            <p class="text-xl font-bold text-red-700 mt-1">
                {{ $stats['out_of_stock'] ?? 0 }}
            </p>
        </div>
    </div>

    {{-- Filters & Search --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-12 gap-3">
            <div class="md:col-span-5">
                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="Search by name, code, brand..."
                       class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <div class="md:col-span-3">
                <select name="type" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">All Item Types</option>
                    @foreach($itemTypes ?? [] as $type)
                        <option value="{{ $type }}" {{ request('type') === $type ? 'selected' : '' }}>
                            {{ $type }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="md:col-span-2">
                <select name="stock_status" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">All Stock Status</option>
                    <option value="available" {{ request('stock_status') === 'available' ? 'selected' : '' }}>Available</option>
                    <option value="low" {{ request('stock_status') === 'low' ? 'selected' : '' }}>Low Stock</option>
                    <option value="out" {{ request('stock_status') === 'out' ? 'selected' : '' }}>Out of Stock</option>
                </select>
            </div>

            <div class="md:col-span-2 flex gap-2">
                <button type="submit" class="flex-1 bg-[#2d2d2d] text-white px-4 py-2 text-sm rounded-lg hover:bg-[#525252] transition">
                    Filter
                </button>
                <a href="{{ route('admin.inventory.index') }}"
                   class="px-4 py-2 bg-gray-100 text-gray-700 text-sm rounded-lg hover:bg-gray-200 transition">
                    Reset
                </a>
            </div>
        </form>
    </div>

    {{-- Inventory Table --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full divide-y divide-gray-200 text-sm">
                <thead>
                    <tr class="bg-[#2d2d2d] text-white">
                        <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider">Code</th>
                        <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider">Item Name</th>
                        <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider">Type</th>
                        <th class="px-4 py-3 text-center text-xs font-bold uppercase tracking-wider">Stock</th>
                        <th class="px-4 py-3 text-center text-xs font-bold uppercase tracking-wider">Unit Price</th>
                        <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider">Supplier</th>
                        <th class="px-4 py-3 text-center text-xs font-bold uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-right text-xs font-bold uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>

                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($inventoryItems as $item)
                        <tr class="hover:bg-gray-50 transition-colors {{ session('highlighted_item_id') == $item->item_id ? 'bg-emerald-50 border-l-4 border-emerald-500' : '' }}">
                            <td class="px-4 py-3 font-medium text-gray-900 whitespace-nowrap">
                                {{ $item->item_code }}
                            </td>

                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-900">{{ $item->item_name }}</div>
                                <div class="text-xs text-gray-500 mt-0.5">
                                    {{ $item->brand ?? '' }}{{ $item->model ? ' â€¢ '.$item->model : '' }}
                                </div>
                            </td>

                            <td class="px-4 py-3 text-gray-600 whitespace-nowrap">
                                {{ $item->item_type }}
                            </td>

                            <td class="px-4 py-3 text-center">
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

                            <td class="px-4 py-3 text-center whitespace-nowrap">
                                â‚±{{ number_format($item->unit_price ?? 0, 2) }}
                            </td>

                            <td class="px-4 py-3 text-gray-600 whitespace-nowrap">
                                {{ $item->supplier?->supplier_name ?? 'â€”' }}
                            </td>

                            <td class="px-4 py-3 text-center whitespace-nowrap">
                                @if(!$item->is_active)
                                    <span class="inline-flex px-2.5 py-1 text-xs font-medium rounded-md bg-gray-100 text-gray-500 border border-gray-300">
                                        Inactive
                                    </span>
                                @elseif($item->quantity === 0)
                                    <span class="inline-flex px-2.5 py-1 text-xs font-medium rounded-md bg-red-100 text-red-800">
                                        Out of Stock
                                    </span>
                                @elseif($item->quantity <= $item->low_stock_threshold)
                                    <span class="inline-flex px-2.5 py-1 text-xs font-medium rounded-md bg-amber-100 text-amber-800">
                                        Low Stock
                                    </span>
                                @else
                                    <span class="inline-flex px-2.5 py-1 text-xs font-medium rounded-md bg-green-100 text-green-800">
                                        Active
                                    </span>
                                @endif
                            </td>

                            <td class="px-4 py-3 text-right whitespace-nowrap text-sm font-medium space-x-3">
                                <a href="{{ route('admin.inventory.edit', $item->item_id) }}"
                                   class="text-indigo-600 hover:text-indigo-800 transition-colors"
                                   title="Edit">
                                    <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                    </svg>
                                </a>

                                <form action="{{ route('admin.inventory.destroy', $item->item_id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            onclick="return confirm('Permanently delete this item?')"
                                            class="text-red-600 hover:text-red-800 transition-colors"
                                            title="Delete">
                                        <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-10 text-center text-gray-500">
                                No inventory items found matching your criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-4 py-4 border-t border-gray-200">
            {{ $inventoryItems->links('pagination::tailwind') }}
        </div>
    </div>
</div>
@endsection