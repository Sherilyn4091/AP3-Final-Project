@extends('layouts.student')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    
    <div class="space-y-6">

        {{-- Welcome Section --}}
        <div class="bg-white border border-gray-300 rounded-xl p-6 shadow">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Student Dashboard</h1>
                    <p class="mt-2 text-base text-gray-700">
                        Welcome back,
                        <span class="font-semibold text-gray-900">
                            {{ $student->first_name }} {{ $student->last_name }}
                        </span>
                    </p>
                </div>

                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('student.schedule') }}"
                       class="px-4 py-2 rounded-lg text-sm font-semibold bg-gray-900 text-white hover:bg-gray-800 transition shadow-sm">
                        My Schedule
                    </a>
                    <a href="{{ route('student.progress') }}"
                       class="px-4 py-2 rounded-lg text-sm font-semibold bg-gray-300 text-gray-900 hover:bg-gray-400 transition shadow-sm">
                        My Progress
                    </a>
                    <a href="{{ route('student.enrollments') }}"
                       class="px-4 py-2 rounded-lg text-sm font-semibold bg-gray-300 text-gray-900 hover:bg-gray-400 transition shadow-sm">
                        Enroll Now
                    </a>
                </div>
            </div>
        </div>

        {{-- Current Package & Next Lesson Row --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Current Package (2 columns) --}}
            <div class="lg:col-span-2">
                @if($currentEnrollment)
                    <div class="bg-white border border-gray-300 rounded-xl shadow overflow-hidden">
                        <div class="px-6 py-4 bg-gray-100 border-b border-gray-300">
                            <h2 class="text-lg font-bold text-gray-900">Current Package</h2>
                        </div>
                        <div class="p-6">
                            <div class="flex flex-col sm:flex-row justify-between items-start gap-4 mb-6">
                                <div>
                                    <h3 class="text-xl font-bold text-gray-900">
                                        {{ $currentEnrollment->lessonSession->session_count }}-Session Package
                                    </h3>
                                    <p class="text-sm text-gray-600 mt-1">
                                        Started: {{ $currentEnrollment->enrollment_date?->format('M d, Y') ?? '—' }}
                                    </p>
                                </div>
                                <span class="px-4 py-2 bg-green-100 text-green-800 rounded-lg text-sm font-bold border border-green-300">
                                    Active
                                </span>
                            </div>

                            {{-- Progress Bar --}}
                            <div class="space-y-2">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-700 font-semibold">Progress</span>
                                    <span class="font-bold text-indigo-700">{{ $progressPercentage }}%</span>
                                </div>
                                <div class="w-full bg-gray-300 rounded-full h-3 overflow-hidden">
                                    <div class="bg-indigo-600 h-3 rounded-full transition-all duration-500"
                                         style="width: {{ $progressPercentage }}%">
                                    </div>
                                </div>
                                <p class="text-xs text-gray-600 text-center font-medium">
                                    {{ $currentEnrollment->completed_sessions }} completed • 
                                    {{ $currentEnrollment->remaining_sessions }} remaining
                                </p>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="bg-yellow-200 border-2 border-yellow-600 rounded-xl p-6 shadow-lg">
                        <h3 class="text-xl font-bold text-gray-900">No Active Package</h3>
                        <p class="mt-2 text-base text-gray-800 font-semibold">Enroll in a package to begin your lessons!</p>
                    </div>
                @endif
            </div>

            {{-- Next Lesson Card (1 column) --}}
            <div>
                @if($nextLesson)
                    <div class="bg-gradient-to-br from-indigo-600 to-indigo-700 text-white rounded-xl shadow-lg p-6">
                        <h2 class="text-lg font-bold mb-3">Next Lesson</h2>
                        <p class="text-base font-semibold mb-1">
                            {{ $nextLesson->schedule_date->format('l, F d') }}
                        </p>
                        <p class="text-sm mb-3">
                            {{ $nextLesson->start_time->format('g:i A') }} – {{ $nextLesson->end_time->format('g:i A') }}
                        </p>
                        <p class="text-sm font-medium">
                            Instructor: {{ $nextLesson->instructor->first_name }} {{ $nextLesson->instructor->last_name }}
                        </p>
                        <p class="text-xs mt-2 opacity-90">Room: {{ $nextLesson->room_number ?? 'TBA' }}</p>
                    </div>
                @else
                    <div class="bg-white border border-gray-300 rounded-xl shadow p-6 text-center">
                        <h3 class="text-lg font-bold text-gray-900">No Upcoming Lessons</h3>
                        <p class="mt-2 text-sm text-gray-600 font-medium">Schedule will appear here soon!</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Recent Progress --}}
        <div class="bg-white border border-gray-300 rounded-xl p-6 shadow">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-bold text-gray-900">Recent Progress</h2>
                <a href="{{ route('student.progress') }}" 
                   class="text-sm font-semibold text-indigo-700 hover:text-indigo-900 underline">
                    View All
                </a>
            </div>

            @if($recentProgress->isEmpty())
                <p class="text-sm text-gray-600 font-medium">
                    No progress updates yet — attend your next lesson!
                </p>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($recentProgress as $progress)
                        <div class="bg-gray-50 border border-gray-300 rounded-lg p-4 hover:shadow-md transition">
                            <div class="flex justify-between items-start mb-2">
                                <div>
                                    <p class="text-sm font-bold text-gray-900">
                                        {{ $progress->progress_date->format('M d, Y') }}
                                    </p>
                                    <p class="text-xs text-gray-600 mt-0.5 font-medium">
                                        {{ $progress->lesson_topic ?? 'Regular Lesson' }}
                                    </p>
                                </div>
                                @if($progress->performance_rating)
                                    <span class="px-2 py-1 bg-indigo-100 text-indigo-900 text-xs font-bold rounded border border-indigo-300">
                                        {{ $progress->performance_rating }}/10
                                    </span>
                                @endif
                            </div>
                            @if($progress->homework)
                                <p class="text-xs text-gray-700 mt-2 font-medium">
                                    <strong>Homework:</strong> {{ Str::limit($progress->homework, 60) }}
                                </p>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

    </div>
</div>
@endsection