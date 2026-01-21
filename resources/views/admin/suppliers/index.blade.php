@extends('layouts.admin-default')

@section('title', 'Supplier Management')

@section('headername')
    <h1 class="text-2xl font-bold text-gray-800">Supplier Management</h1>
    <p class="text-gray-600 mt-1 text-sm">
        Total: <span class="font-semibold">{{ $stats['total'] }}</span> • 
        Active: <span class="text-green-600 font-semibold">{{ $stats['active'] }}</span> • 
        Inactive: <span class="text-gray-500 font-semibold">{{ $stats['inactive'] }}</span>
    </p>
@endsection

@section('header_actions')
    <a href="{{ route('admin.suppliers.create') }}" 
       class="inline-flex items-center px-5 py-2.5 bg-[#2d2d2d] text-white rounded-lg shadow-sm hover:bg-[#525252] transition-all duration-200 font-medium">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
        </svg>
        Add New Supplier
    </a>
@endsection

@section('maincontent')
<div class="mt-4 px-6 lg:px-8">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8">
        <form method="GET" class="flex gap-4">
            <div class="flex-1">
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="Search by name, code, or contact person..." 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <button type="submit" class="bg-[#2d2d2d] text-white px-8 py-2 rounded-lg hover:bg-[#525252] transition">
                Search
            </button>
            <a href="{{ route('admin.suppliers.index') }}" class="px-6 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition">
                Reset
            </a>
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full divide-y divide-gray-200 text-sm">
                <thead>
                    <tr class="bg-[#2d2d2d] text-white">
                        <th class="px-4 py-4 text-left text-xs font-bold uppercase tracking-wider">Code</th>
                        <th class="px-4 py-4 text-left text-xs font-bold uppercase tracking-wider">Supplier Name</th>
                        <th class="px-4 py-4 text-left text-xs font-bold uppercase tracking-wider">Contact Person</th>
                        <th class="px-4 py-4 text-left text-xs font-bold uppercase tracking-wider">Email/Phone</th>
                        <th class="px-4 py-4 text-center text-xs font-bold uppercase tracking-wider">Rating</th>
                        <th class="px-4 py-4 text-center text-xs font-bold uppercase tracking-wider">Status</th>
                        <th class="px-4 py-4 text-right text-xs font-bold uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($suppliers as $supplier)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-4 font-medium text-gray-900">{{ $supplier->supplier_code }}</td>
                            <td class="px-4 py-4">
                                <div class="font-bold text-gray-900">{{ $supplier->supplier_name }}</div>
                                <div class="text-xs text-gray-500">{{ $supplier->city }}, {{ $supplier->country }}</div>
                            </td>
                            <td class="px-4 py-4 text-gray-600">
                                <div>{{ $supplier->contact_person }}</div>
                                <div class="text-xs italic">{{ $supplier->contact_position }}</div>
                            </td>
                            <td class="px-4 py-4 text-gray-600">
                                <div>{{ $supplier->email }}</div>
                                <div class="text-xs">{{ $supplier->phone }}</div>
                            </td>
                            <td class="px-4 py-4 text-center">
                                <span class="px-2 py-1 bg-amber-50 text-amber-700 rounded border border-amber-200 font-semibold">
                                    {{ number_format($supplier->rating, 1) }} ★
                                </span>
                            </td>
                            <td class="px-4 py-4 text-center">
                                @if($supplier->is_active)
                                    <span class="inline-flex px-2.5 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">Active</span>
                                @else
                                    <span class="inline-flex px-2.5 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-500">Inactive</span>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-right space-x-3">
                                <a href="{{ route('admin.suppliers.edit', $supplier->supplier_id) }}" class="text-indigo-600 hover:text-indigo-900" title="Edit">
                                    <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                </a>
                                
                                <form action="{{ route('admin.suppliers.destroy', $supplier->supplier_id) }}" method="POST" class="inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" onclick="return confirm('Delete this supplier?')" class="text-red-600 hover:text-red-900">
                                        <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-500">No suppliers found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $suppliers->links() }}
        </div>
    </div>
</div>
@endsection
