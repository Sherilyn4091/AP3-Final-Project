@extends('layouts.admin-default')

@section('title', 'Edit Item - ' . $item->item_name)

@section('headername')
    <h1 class="text-2xl font-bold text-gray-800">Edit Inventory Item</h1>
    <p class="text-gray-600 mt-1 text-sm">
        {{ $item->item_code }} • {{ $item->item_type }}
    </p>
@endsection

@section('header_actions')
    <a href="{{ route('admin.inventory.index') }}"
       class="inline-flex items-center px-5 py-2.5 bg-gray-600 text-white rounded-lg shadow-sm hover:bg-gray-700 transition-all duration-200 font-medium">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Back to List
    </a>
@endsection

@section('maincontent')
<div class="px-6 lg:px-8 pb-12">
    <div class="mt-4 bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden max-w-5xl mx-auto">

        <div class="bg-[#2d2d2d] px-6 py-5">
            <h2 class="text-xl font-bold text-white">
                Edit: {{ $item->item_name }}
                <span class="text-gray-400 text-base ml-3 font-normal">({{ $item->item_code }})</span>
            </h2>
        </div>

        <form action="{{ route('admin.inventory.update', $item->item_id) }}" method="POST" class="p-6 lg:p-8">
            @csrf
            @method('PUT')

            @if ($errors->any())
                <div class="mb-8 p-4 bg-red-50 border border-red-200 rounded-lg">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-red-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2h-1V9z" clip-rule="evenodd"/>
                        </svg>
                        <strong class="text-red-800">Please fix the following errors:</strong>
                    </div>
                    <ul class="mt-2 list-disc list-inside text-sm text-red-700">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                {{-- Column 1 --}}
                <div class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Item Code *</label>
                        <input type="text" name="item_code" value="{{ old('item_code', $item->item_code) }}"
                               class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('item_code') border-red-300 @enderror"
                               required>
                        @error('item_code') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Item Name *</label>
                        <input type="text" name="item_name" value="{{ old('item_name', $item->item_name) }}"
                               class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('item_name') border-red-300 @enderror"
                               required>
                        @error('item_name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Item Type *</label>
                        <input type="text" name="item_type" list="item_types" value="{{ old('item_type', $item->item_type) }}"
                               class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('item_type') border-red-300 @enderror"
                               required>
                        <datalist id="item_types">
                            @foreach($itemTypes ?? [] as $type)
                                <option value="{{ $type }}"></option>
                            @endforeach
                        </datalist>
                        @error('item_type') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Column 2 --}}
                <div class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Brand</label>
                        <input type="text" name="brand" value="{{ old('brand', $item->brand) }}"
                               class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Model</label>
                        <input type="text" name="model" value="{{ old('model', $item->model) }}"
                               class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Storage Location</label>
                        <input type="text" name="location" value="{{ old('location', $item->location) }}"
                               placeholder="e.g. Shelf A-12, Main Room"
                               class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Warranty Period</label>
                        <input type="text" name="warranty_period" value="{{ old('warranty_period', $item->warranty_period) }}"
                               placeholder="e.g. 1 year, 2 years"
                               class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>
                </div>

                {{-- Column 3 --}}
                <div class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Current Quantity *</label>
                        <input type="number" name="quantity" min="0" value="{{ old('quantity', $item->quantity) }}"
                               class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('quantity') border-red-300 @enderror"
                               required>
                        @error('quantity') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Unit of Measure</label>
                        <input type="text" name="unit_of_measure" value="{{ old('unit_of_measure', $item->unit_of_measure ?? 'piece') }}"
                               placeholder="piece, set, pair, box..."
                               class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Unit Price (Cost) ₱</label>
                        <input type="number" step="0.01" min="0" name="unit_price" value="{{ old('unit_price', $item->unit_price) }}"
                               class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Retail Price ₱</label>
                        <input type="number" step="0.01" min="0" name="retail_price" value="{{ old('retail_price', $item->retail_price) }}"
                               class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>
                </div>

                {{-- Full width --}}
                <div class="md:col-span-2 lg:col-span-3 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mt-4 pt-6 border-t">

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Low Stock Threshold *</label>
                        <input type="number" name="low_stock_threshold" min="0"
                               value="{{ old('low_stock_threshold', $item->low_stock_threshold ?? 5) }}"
                               class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                               required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Suggested Reorder Qty</label>
                        <input type="number" name="reorder_quantity" min="1"
                               value="{{ old('reorder_quantity', $item->reorder_quantity) }}"
                               class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Supplier</label>
                        <select name="supplier_id"
                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            <option value="">— No supplier selected —</option>
                            @foreach($suppliers ?? [] as $id => $name)
                                <option value="{{ $id }}" {{ old('supplier_id', $item->supplier_id) == $id ? 'selected' : '' }}>
                                    {{ $name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Supplier Product Code</label>
                        <input type="text" name="supplier_product_code"
                               value="{{ old('supplier_product_code', $item->supplier_product_code) }}"
                               class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>

                    {{-- Active toggle like Supplier --}}
                    <div class="flex items-center pt-2">
                        <label class="inline-flex items-center cursor-pointer relative">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1" class="sr-only peer"
                                   {{ old('is_active', $item->is_active) ? 'checked' : '' }}>
                            <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white
                                        after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border
                                        after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600">
                            </div>
                            <span class="ml-3 text-sm font-medium text-gray-700">Active</span>
                        </label>
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="mt-10 pt-6 border-t flex flex-col sm:flex-row sm:justify-between gap-4">
                <a href="{{ route('admin.inventory.index') }}"
                   class="text-center sm:text-left px-6 py-3 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition font-medium">
                    Cancel
                </a>

                <button type="submit"
                        class="px-8 py-3 bg-[#2d2d2d] text-white rounded-lg hover:bg-[#525252] transition font-bold shadow-sm">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
