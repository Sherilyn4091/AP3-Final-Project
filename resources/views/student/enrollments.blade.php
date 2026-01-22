<!-- resources/views/student/enrollments.blade.php -->

@extends('layouts.student')

@section('content')
<div class="max-w-7xl mx-auto">

    <!-- Header -->
    <div class="mb-10">
        <h1 class="text-3xl font-bold text-[#D8D9DA]">My Packages & Enrollments</h1>
        <p class="mt-2 text-lg text-[#61677A]">
            View your current and past lesson packages
        </p>
    </div>

    @if($enrollments->isEmpty())
        <div class="bg-[#272829] rounded-2xl shadow-lg border border-[#61677A] p-12 text-center">
            <div class="text-6xl mb-4">🎓</div>
            <h3 class="text-xl font-semibold text-[#D8D9DA] mb-2">No Enrollments Yet</h3>
            <p class="text-[#61677A]">Enroll in a package to start your music lessons!</p>
        </div>
    @else
        <div class="space-y-8">
            @foreach($enrollments as $enrollment)
                <div class="bg-[#272829] rounded-2xl shadow-lg border border-[#61677A] overflow-hidden">
                    <div class="px-6 py-6 bg-[#61677A]/20 border-b border-[#61677A]">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="text-xl font-bold text-[#D8D9DA]">
                                    {{ $enrollment->lessonSession->session_count }}-Session Package
                                </h3>
                                <p class="text-[#61677A] mt-2">
                                    Enrolled: {{ $enrollment->enrollment_date?->format('M d, Y') ?? '—' }}
                                </p>
                            </div>
                            <span class="px-4 py-2 bg-[#61677A]/30 text-[#FFF6E0] rounded-full font-medium">
                                {{ ucfirst($enrollment->status) }}
                            </span>
                        </div>
                    </div>

                    <div class="p-6">
                        <!-- Progress Bar -->
                        <div class="mb-6">
                            <div class="flex justify-between text-sm mb-3">
                                <span class="text-[#61677A] font-medium">Progress</span>
                                <span class="font-bold text-[#FFF6E0]">
                                    {{ $enrollment->completed_sessions }} / {{ $enrollment->total_sessions }}
                                    ({{ round(($enrollment->completed_sessions / $enrollment->total_sessions) * 100, 1) }}%)
                                </span>
                            </div>
                            <div class="w-full bg-[#61677A]/30 rounded-full h-4 overflow-hidden">
                                <div class="bg-gradient-to-r from-[#61677A] to-[#FFF6E0] h-4 rounded-full transition-all duration-1000"
                                     style="width: {{ round(($enrollment->completed_sessions / $enrollment->total_sessions) * 100, 1) }}%">
                                </div>
                            </div>
                        </div>

                        <!-- Details Grid -->
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-6 text-sm">
                            <div>
                                <p class="text-[#61677A]">Remaining</p>
                                <p class="font-bold text-[#FFF6E0]">{{ $enrollment->remaining_sessions }}</p>
                            </div>
                            <div>
                                <p class="text-[#61677A]">Paid</p>
                                <p class="font-medium">₱{{ number_format($enrollment->amount_paid ?? 0, 2) }}</p>
                            </div>
                            <div>
                                <p class="text-[#61677A]">Instructor</p>
                                <p class="font-medium">{{ $enrollment->instructor->first_name ?? '—' }}</p>
                            </div>
                            <div>
                                <p class="text-[#61677A]">Start Date</p>
                                <p class="font-medium">{{ $enrollment->start_date?->format('M d, Y') ?? '—' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

</div>
@endsection
