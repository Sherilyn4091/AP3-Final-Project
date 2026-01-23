<!-- resources/views/student/enrollments.blade.php -->

@extends('layouts.student')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">My Packages & Enrollments</h1>
        <p class="mt-2 text-base text-gray-700">
            View your current and past lesson packages
        </p>
    </div>

    @if($enrollments->isEmpty())
        <div class="bg-white border border-gray-300 rounded-xl shadow p-12 text-center">
            <div class="text-6xl mb-4">🎓</div>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">No Enrollments Yet</h3>
            <p class="text-gray-600 mb-6">Enroll in a package to start your music lessons!</p>
            <a href="{{ route('student.packages') }}" 
               class="inline-block px-6 py-3 bg-gray-900 text-white rounded-lg font-semibold hover:bg-gray-800 transition shadow-sm">
                Browse Packages
            </a>
        </div>
    @else
        <div class="space-y-6">
            @foreach($enrollments as $enrollment)
                <div class="bg-white border border-gray-300 rounded-xl shadow overflow-hidden">
                    <div class="px-6 py-4 bg-gray-100 border-b border-gray-300">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="text-xl font-bold text-gray-900">
                                    {{ $enrollment->lessonSession->session_count }}-Session Package
                                </h3>
                                <p class="text-gray-600 mt-1 text-sm">
                                    Enrolled: {{ $enrollment->enrollment_date?->format('M d, Y') ?? '—' }}
                                </p>
                            </div>
                            <span class="px-4 py-2 
                                {{ $enrollment->status === 'active' ? 'bg-green-100 text-green-800 border-green-300' : 'bg-gray-200 text-gray-800 border-gray-400' }} 
                                rounded-lg text-sm font-bold border">
                                {{ ucfirst($enrollment->status) }}
                            </span>
                        </div>
                    </div>

                    <div class="p-6">
                        <!-- Progress Bar -->
                        <div class="mb-6">
                            <div class="flex justify-between text-sm mb-3">
                                <span class="text-gray-700 font-semibold">Progress</span>
                                <span class="font-bold text-indigo-700">
                                    {{ $enrollment->completed_sessions }} / {{ $enrollment->total_sessions }}
                                    ({{ round(($enrollment->completed_sessions / $enrollment->total_sessions) * 100, 1) }}%)
                                </span>
                            </div>
                            <div class="w-full bg-gray-300 rounded-full h-3 overflow-hidden">
                                <div class="bg-indigo-600 h-3 rounded-full transition-all duration-500"
                                     style="width: {{ round(($enrollment->completed_sessions / $enrollment->total_sessions) * 100, 1) }}%">
                                </div>
                            </div>
                        </div>

                        <!-- Details Grid -->
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-6 text-sm">
                            <div>
                                <p class="text-gray-600 font-medium">Remaining</p>
                                <p class="font-bold text-gray-900 text-lg">{{ $enrollment->remaining_sessions }}</p>
                            </div>
                            <div>
                                <p class="text-gray-600 font-medium">Paid</p>
                                <p class="font-semibold text-gray-900">₱{{ number_format($enrollment->amount_paid ?? 0, 2) }}</p>
                            </div>
                            <div>
                                <p class="text-gray-600 font-medium">Instructor</p>
                                <p class="font-semibold text-gray-900">{{ $enrollment->instructor->first_name ?? '—' }}</p>
                            </div>
                            <div>
                                <p class="text-gray-600 font-medium">Start Date</p>
                                <p class="font-semibold text-gray-900">{{ $enrollment->start_date?->format('M d, Y') ?? '—' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

</div>
@endsection