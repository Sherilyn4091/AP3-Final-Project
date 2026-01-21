@extends('layouts.admin-default')

@section('title', 'Add New Supplier')

@section('headername')
    <h1 class="text-2xl font-bold text-gray-800">Register New Supplier</h1>
    <p class="text-gray-600 mt-1 text-sm">
        Fill in the details below to add a new supplier to the system.
    </p>
@endsection

@section('header_actions')
    <a href="{{ route('admin.suppliers.index') }}"
       class="inline-flex items-center px-5 py-2.5 bg-gray-600 text-white rounded-lg shadow-sm hover:bg-gray-700 transition font-medium">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Back to List
    </a>
@endsection

@section('maincontent')
<div class="px-6 lg:px-8 pb-12">
    <div class="mt-4 bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden max-w-6xl mx-auto">

        <div class="bg-[#2d2d2d] px-6 py-5">
            <h2 class="text-xl font-bold text-white">Register New Supplier</h2>
        </div>

        <form action="{{ route('admin.suppliers.store') }}" method="POST" class="p-6 lg:p-8">
            @csrf

            {{-- Error Summary --}}
            @if ($errors->any())
                <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded">
                    <p class="font-bold text-sm mb-1">Please fix the following:</p>
                    <ul class="list-disc list-inside text-xs space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                {{-- IDENTIFICATION --}}
                <div class="space-y-4">
                    <h3 class="font-bold text-gray-700 border-b pb-1">Identification</h3>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            Supplier Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="supplier_name"
                               value="{{ old('supplier_name') }}"
                               required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            Supplier Code <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="supplier_code"
                               value="{{ old('supplier_code') }}"
                               placeholder="e.g. SUP-001"
                               required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        @error('supplier_code')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Website</label>
                        <input type="text" name="website"
                               value="{{ old('website') }}"
                               placeholder="supplier.com"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>
                </div>

                {{-- CONTACT --}}
                <div class="space-y-4">
                    <h3 class="font-bold text-gray-700 border-b pb-1">Contact</h3>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Contact Person</label>
                        <input type="text" name="contact_person"
                               value="{{ old('contact_person') }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" name="email"
                               value="{{ old('email') }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Phone</label>
                        <input type="text" name="phone"
                               value="{{ old('phone') }}"
                               placeholder="09XXXXXXXXX"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>

                    <div class="grid grid-cols-2 gap-2 items-center">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Rating</label>
                            <input type="number" step="0.1" min="0" max="5"
                                   name="rating"
                                   value="{{ old('rating', 5) }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>

                        <div class="pt-6">
                            <label class="inline-flex items-center cursor-pointer relative">
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" name="is_active" value="1"
                                       class="sr-only peer"
                                       {{ old('is_active', true) ? 'checked' : '' }}>
                                <div
                                    class="w-10 h-5 bg-gray-200 rounded-full peer-checked:bg-indigo-600
                                           after:content-[''] after:absolute after:top-0.5 after:left-0.5
                                           after:bg-white after:rounded-full after:h-4 after:w-4
                                           after:transition-all peer-checked:after:translate-x-5">
                                </div>
                                <span class="ml-2 text-xs font-medium text-gray-700">Active</span>
                            </label>
                        </div>
                    </div>
                </div>

                {{-- LOCATION --}}
                <div class="space-y-4">
                    <h3 class="font-bold text-gray-700 border-b pb-1">
                        Location <span class="text-red-500">*</span>
                    </h3>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Address Line 1 *</label>
                        <input type="text" name="address_line1"
                               value="{{ old('address_line1') }}"
                               required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">City *</label>
                            <input type="text" name="city"
                                   value="{{ old('city') }}"
                                   required
                                   class="block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Postal Code *</label>
                            <input type="text" name="postal_code"
                                   value="{{ old('postal_code') }}"
                                   required
                                   class="block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Country *</label>
                        <input type="text" name="country"
                               value="{{ old('country', 'Philippines') }}"
                               required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>
                </div>
            </div>

            {{-- NOTES --}}
            <div class="mt-6">
                <label class="block text-sm font-medium text-gray-700">Notes</label>
                <textarea name="notes" rows="3"
                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">{{ old('notes') }}</textarea>
            </div>

            {{-- ACTIONS --}}
            <div class="mt-8 pt-6 border-t flex justify-end gap-3">
                <a href="{{ route('admin.suppliers.index') }}"
                   class="px-6 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition font-medium">
                    Cancel
                </a>
                <button type="submit"
                        class="px-10 py-2 bg-[#2d2d2d] text-white rounded-lg hover:bg-[#525252] transition font-bold">
                    Register Supplier
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
