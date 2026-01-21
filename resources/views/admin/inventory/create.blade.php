@extends('layouts.admin-default')

@section('title', 'Add New Inventory Item')

@section('headername')
    <h1 class="text-2xl font-bold text-gray-800">Add New Inventory Item</h1>
    <p class="text-gray-600 mt-1 text-sm">
        Create a new product/stock item for the Music Lab
    </p>
@endsection

@section('header_actions')
    <a href="{{ route('admin.inventory.index') }}"
       class="inline-flex items-center px-5 py-2.5 bg-gray-600 text-white rounded-lg shadow-sm hover:bg-gray-700 transition-all duration-200 font-medium">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Back to Inventory
    </a>
@endsection

@section('maincontent')
<div class="px-6 lg:px-8 pb-12">
    <div class="mt-4 bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden max-w-5xl mx-auto">
        <!-- Header -->
        <div class="bg-[#2d2d2d] px-6 py-5">
            <h2 class="text-xl font-bold text-white">
                New Item Registration
            </h2>
        </div>

        <form action="{{ route('admin.inventory.store') }}" method="POST" class="p-6 lg:p-8">
            @csrf

            @if ($errors->any())
                <div class="mb-8 p-4 bg-red-50 border border-red-200 rounded-lg">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-red-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2h-1V9z" clip-rule="evenodd"/>
                        </svg>
                        <strong class="text-red-800">Please correct the following errors:</strong>
                    </div>
                    <ul class="mt-2 list-disc list-inside text-sm text-red-700">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                <!-- Column 1 - Basic Item Info -->
                <div class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Item Code *</label>
                        <input type="text" name="item_code" value="{{ old('item_code') }}"
                               class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('item_code') border-red-300 @enderror"
                               placeholder="e.g. MIC-AKG-C414" required>
                        @error('item_code')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Item Name *</label>
                        <input type="text" name="item_name" value="{{ old('item_name') }}"
                               class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('item_name') border-red-300 @enderror"
                               placeholder="e.g. AKG C414 XLS Condenser Microphone" required>
                        @error('item_name')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Item Type *</label>
                        <input type="text" name="item_type" list="item_types" value="{{ old('item_type') }}"
                               class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('item_type') border-red-300 @enderror"
                               placeholder="e.g. Microphone, Mixer, Cable..." required>
                        <datalist id="item_types">
                            @foreach($itemTypes ?? [] as $type)
                                <option value="{{ $type }}"></option>
                            @endforeach
                        </datalist>
                        @error('item_type')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Column 2 - Brand / Model / Location / Warranty -->
                <div class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Brand</label>
                        <input type="text" name="brand" value="{{ old('brand') }}"
                               class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                               placeholder="e.g. AKG, Behringer, Boss...">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Model</label>
                        <input type="text" name="model" value="{{ old('model') }}"
                               class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                               placeholder="e.g. C414 XLS, X32, DD-8...">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Storage Location</label>
                        <input type="text" name="location" value="{{ old('location') }}"
                               class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                               placeholder="e.g. Shelf A-12, Mic Cabinet...">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Warranty Period</label>
                        <input type="text" name="warranty_period" value="{{ old('warranty_period') }}"
                               class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                               placeholder="e.g. 1 year, 2 years, Lifetime...">
                    </div>
                </div>

                <!-- Column 3 - Stock & Pricing -->
                <div class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Initial Quantity *</label>
                        <input type="number" name="quantity" min="0" value="{{ old('quantity', 0) }}"
                               class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('quantity') border-red-300 @enderror"
                               required>
                        @error('quantity')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Unit of Measure</label>
                        <input type="text" name="unit_of_measure" value="{{ old('unit_of_measure', 'piece') }}"
                               class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                               placeholder="piece, set, pair, box...">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Unit Price (Cost) ₱</label>
                        <input type="number" step="0.01" min="0" name="unit_price" value="{{ old('unit_price') }}"
                               class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Retail Price ₱</label>
                        <input type="number" step="0.01" min="0" name="retail_price" value="{{ old('retail_price') }}"
                               class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>
                </div>

                <!-- Full width fields -->
                <div class="md:col-span-2 lg:col-span-3 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mt-4 pt-6 border-t">

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Low Stock Threshold *</label>
                        <input type="number" name="low_stock_threshold" min="0" value="{{ old('low_stock_threshold', 5) }}"
                               class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                               required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Suggested Reorder Qty</label>
                        <input type="number" name="reorder_quantity" min="1" value="{{ old('reorder_quantity') }}"
                               class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Supplier</label>
                        <select name="supplier_id"
                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            <option value="">— Select supplier (optional) —</option>
                            @foreach($suppliers ?? [] as $id => $name)
                                <option value="{{ $id }}" {{ old('supplier_id') == $id ? 'selected' : '' }}>
                                    {{ $name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Supplier's Product Code</label>
                        <input type="text" name="supplier_product_code" value="{{ old('supplier_product_code') }}"
                               class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                               placeholder="Supplier's internal code/reference">
                    </div>

                    <div class="flex items-center pt-4">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1"
                                   class="sr-only peer" checked>
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                        </label>
                        <span class="ml-3 text-sm font-medium text-gray-700">Active / Visible in system</span>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="mt-10 pt-6 border-t flex flex-col sm:flex-row sm:justify-between gap-4">
                <a href="{{ route('admin.inventory.index') }}"
                   class="text-center sm:text-left px-6 py-3 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition font-medium">
                    Cancel
                </a>

                <div class="flex flex-col sm:flex-row gap-4">
                    <button type="submit" name="action" value="save_and_new"
                            class="px-8 py-3 bg-[#2d2d2d] text-white rounded-lg hover:bg-[#525252] transition font-bold shadow-sm">
                        Save & Add Another
                    </button>

                    <button type="submit"
                            class="px-8 py-3 bg-[#2d2d2d] text-white rounded-lg hover:bg-[#525252] transition font-bold shadow-sm">
                        Save Item
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection