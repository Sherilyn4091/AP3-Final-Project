@extends('layouts.admin-default')

@section('title', 'Edit Supplier - ' . $supplier->supplier_name)

@section('headername')
    <h1 class="text-2xl font-bold text-gray-800">Edit Supplier</h1>
    <p class="text-gray-600 mt-1 text-sm">
        {{ $supplier->supplier_code }} • Last Update:
        {{ $supplier->updated_at?->diffForHumans() ?? 'No record' }}
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


        {{-- Header --}}
        <div class="bg-[#2d2d2d] px-6 py-5">
            <h2 class="text-xl font-bold text-white">
                Edit: {{ $supplier->supplier_name }}
            </h2>
        </div>
        {{-- Validation Errors --}}
        @if ($errors->any())
            <div class="m-6 p-4 rounded-lg border border-red-200 bg-red-50 text-red-700">
                <p class="font-bold text-sm mb-2">Please fix the following:</p>
                <ul class="list-disc list-inside text-xs space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Form --}}
        <form action="{{ route('admin.suppliers.update', $supplier->supplier_id) }}"
              method="POST"
              class="p-6 lg:p-8">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                {{-- IDENTIFICATION --}}
                <div class="space-y-4">
                    <h3 class="font-bold text-gray-700 border-b pb-1">Identification</h3>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Supplier Name *</label>
                        <input type="text" name="supplier_name"
                               value="{{ old('supplier_name', $supplier->supplier_name) }}"
                               required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Supplier Code *</label>
                        <input type="text" name="supplier_code"
                               value="{{ old('supplier_code', $supplier->supplier_code) }}"
                               required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Website</label>
                        <input type="text" name="website"
                               value="{{ old('website', $supplier->website) }}"
                               placeholder="example.com or https://example.com"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        <p class="text-xs text-gray-400 mt-1">Optional</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Rating (0–5)</label>
                        <input type="number" step="0.1" min="0" max="5"
                               name="rating"
                               value="{{ old('rating', $supplier->rating) }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>
                </div>

                {{-- CONTACT --}}
                <div class="space-y-4">
                    <h3 class="font-bold text-gray-700 border-b pb-1">Contact</h3>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Contact Person</label>
                        <input type="text" name="contact_person"
                               value="{{ old('contact_person', $supplier->contact_person) }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Contact Position</label>
                        <input type="text" name="contact_position"
                               value="{{ old('contact_position', $supplier->contact_position) }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" name="email"
                               value="{{ old('email', $supplier->email) }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Phone</label>
                        <input type="text" name="phone"
                               value="{{ old('phone', $supplier->phone) }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Payment Terms</label>
                        <input type="text" name="payment_terms"
                               value="{{ old('payment_terms', $supplier->payment_terms) }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Delivery Terms</label>
                        <input type="text" name="delivery_terms"
                               value="{{ old('delivery_terms', $supplier->delivery_terms) }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>
                </div>

                {{-- LOCATION & STATUS --}}
                <div class="space-y-4">
                    <h3 class="font-bold text-gray-700 border-b pb-1">Location & Status</h3>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Address Line 1</label>
                        <input type="text" name="address_line1"
                               value="{{ old('address_line1', $supplier->address_line1) }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Address Line 2</label>
                        <input type="text" name="address_line2"
                               value="{{ old('address_line2', $supplier->address_line2) }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">City *</label>
                        <input type="text" name="city"
                               value="{{ old('city', $supplier->city) }}"
                               required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Province</label>
                        <input type="text" name="province"
                               value="{{ old('province', $supplier->province) }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Postal Code *</label>
                        <input type="text" name="postal_code"
                               value="{{ old('postal_code', $supplier->postal_code) }}"
                               required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Country *</label>
                        <input type="text" name="country"
                               value="{{ old('country', $supplier->country) }}"
                               required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>

                    <div class="pt-2">
                        <label class="inline-flex items-center cursor-pointer relative">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1"
                                   class="sr-only peer"
                                   {{ old('is_active', $supplier->is_active) ? 'checked' : '' }}>
                            <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-indigo-600
                                        after:content-[''] after:absolute after:top-[2px] after:left-[2px]
                                        after:bg-white after:border after:border-gray-300 after:rounded-full
                                        after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full"></div>
                            <span class="ml-3 text-sm font-medium text-gray-700">Active Supplier</span>
                        </label>
                    </div>
                </div>
            </div>

            {{-- Notes --}}
            <div class="mt-6">
                <label class="block text-sm font-medium text-gray-700">Notes</label>
                <textarea name="notes" rows="3"
                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">{{ old('notes', $supplier->notes) }}</textarea>
            </div>

            {{-- Actions --}}
            <div class="mt-8 pt-6 border-t flex justify-between">
                <a href="{{ route('admin.suppliers.index') }}"
                   class="px-6 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition font-medium">
                    Cancel
                </a>
                <button type="submit"
                        class="px-10 py-2 bg-[#2d2d2d] text-white rounded-lg hover:bg-[#525252] transition font-bold shadow-sm">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
