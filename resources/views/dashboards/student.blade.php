@extends('layouts.student')

@section('content')
<div class="max-w-7xl mx-auto">

    <!-- Welcome Header -->
    <div class="mb-10">
        <h1 class="text-3xl sm:text-4xl font-bold text-[#D8D9DA]">
            Welcome back, {{ $student->first_name }}!
        </h1>
        <p class="mt-2 text-lg text-[#61677A]">
            Here's your music journey at a glance
        </p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        <!-- Current Package -->
        <div class="lg:col-span-2">
            @if($currentEnrollment)
                <div class="bg-[#272829] rounded-2xl shadow-lg border border-[#61677A] overflow-hidden">
                    <div class="px-6 py-6 bg-[#61677A]/20 border-b border-[#61677A]">
                        <h2 class="text-2xl font-bold text-[#D8D9DA]">Current Package</h2>
                    </div>
                    <div class="p-6">
                        <div class="flex flex-col md:flex-row justify-between items-start gap-6 mb-8">
                            <div>
                                <h3 class="text-xl font-semibold text-[#D8D9DA]">
                                    {{ $currentEnrollment->lessonSession->session_count }}-Session Package
                                </h3>
                                <p class="text-[#61677A] mt-2">
                                    Started: {{ $currentEnrollment->enrollment_date?->format('M d, Y') ?? '—' }}
                                </p>
                            </div>
                            <span class="px-5 py-2 bg-[#61677A]/30 text-[#FFF6E0] rounded-full font-medium">
                                Active
                            </span>
                        </div>

                        <!-- Progress Bar -->
                        <div class="mb-8">
                            <div class="flex justify-between text-sm mb-3">
                                <span class="text-[#61677A] font-medium">Progress</span>
                                <span class="font-bold text-[#FFF6E0]">{{ $progressPercentage }}%</span>
                            </div>
                            <div class="w-full bg-[#61677A]/30 rounded-full h-4 overflow-hidden">
                                <div class="bg-gradient-to-r from-[#61677A] to-[#FFF6E0] h-4 rounded-full transition-all duration-1000"
                                     style="width: {{ $progressPercentage }}%">
                                </div>
                            </div>
                            <p class="mt-2 text-sm text-[#61677A] text-center">
                                {{ $currentEnrollment->completed_sessions }} completed • 
                                {{ $currentEnrollment->remaining_sessions }} remaining
                            </p>
                        </div>
                    </div>
                </div>
            @else
                <div class="bg-[#FFF6E0]/10 border-l-4 border-[#FFF6E0] p-8 rounded-xl">
                    <h3 class="text-xl font-semibold text-[#FFF6E0]">No Active Package</h3>
                    <p class="mt-3 text-[#D8D9DA]">Enroll in a package to begin your lessons!</p>
                </div>
            @endif
        </div>

        <!-- Next Lesson -->
        <div>
            @if($nextLesson)
                <div class="bg-gradient-to-br from-[#61677A] to-[#272829] text-[#D8D9DA] rounded-2xl shadow-lg p-8">
                    <h2 class="text-2xl font-bold mb-4">Next Lesson</h2>
                    <p class="text-xl mb-2 font-medium">
                        {{ $nextLesson->schedule_date->format('l, F d, Y') }}
                    </p>
                    <p class="text-lg mb-4">
                        {{ $nextLesson->start_time->format('h:i A') }} – {{ $nextLesson->end_time->format('h:i A') }}
                    </p>
                    <p class="text-[#FFF6E0]">
                        With: {{ $nextLesson->instructor->first_name }} {{ $nextLesson->instructor->last_name }}
                    </p>
                    <p class="mt-4 text-sm opacity-90">Room: {{ $nextLesson->room_number ?? 'TBA' }}</p>
                </div>
            @else
                <div class="bg-[#272829] rounded-2xl shadow-sm border border-[#61677A] p-8 text-center">
                    <h3 class="text-xl font-semibold text-[#D8D9DA]">No Upcoming Lessons</h3>
                    <p class="mt-3 text-[#61677A]">Your next lesson will appear here soon!</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Recent Progress -->
    <div class="mt-12">
        <h2 class="text-2xl font-bold text-[#D8D9DA] mb-6">Recent Progress</h2>
        @if($recentProgress->isEmpty())
            <div class="bg-[#272829] rounded-xl shadow-sm border border-[#61677A] p-12 text-center">
                <p class="text-[#61677A] text-lg">No progress updates yet — attend your next lesson!</p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($recentProgress as $progress)
                    <div class="bg-[#272829] rounded-xl shadow-sm border border-[#61677A] p-6 hover:shadow-md transition">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <p class="font-semibold text-[#D8D9DA]">
                                    {{ $progress->progress_date->format('M d, Y') }}
                                </p>
                                <p class="text-sm text-[#61677A] mt-1">
                                    {{ $progress->lesson_topic ?? 'Regular Lesson' }}
                                </p>
                            </div>
                            <span class="px-3 py-1 bg-[#61677A]/30 text-[#FFF6E0] text-sm font-medium rounded-full">
                                {{ $progress->performance_rating ?? '—' }}/10
                            </span>
                        </div>
                        @if($progress->homework)
                            <p class="text-sm text-[#FFF6E0] mt-2">
                                <strong>Homework:</strong> {{ Str::limit($progress->homework, 80) }}
                            </p>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>

</div>
@endsection
