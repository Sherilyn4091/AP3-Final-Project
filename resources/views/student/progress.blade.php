@extends('layouts.student')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    {{-- Page header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">My progress & attendance</h1>
        <p class="mt-2 text-base text-gray-700">Track your learning journey and lesson history</p>
    </div>

    {{-- Tab navigation --}}
    <div class="flex gap-2 mb-6 border-b border-gray-300">
        <button onclick="switchTab('progress')" id="tab-progress" class="tab-btn active px-4 sm:px-6 py-2.5 text-sm font-semibold transition-colors">
            <svg class="w-4 h-4 sm:w-5 sm:h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
            Progress
        </button>
        <button onclick="switchTab('attendance')" id="tab-attendance" class="tab-btn px-4 sm:px-6 py-2.5 text-sm font-semibold transition-colors">
            <svg class="w-4 h-4 sm:w-5 sm:h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
            </svg>
            Attendance
        </button>
    </div>

    {{-- Progress tab content --}}
    <div id="content-progress" class="tab-content">
        @forelse($progressHistory as $progress)
            <div class="bg-white border border-gray-300 rounded-xl shadow mb-4 overflow-hidden hover:shadow-md transition-all">
                {{-- Header --}}
                <div class="px-6 py-4 bg-gray-100 border-b border-gray-300">
                    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-3">
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <p class="text-lg font-bold text-gray-900">
                                    {{ $progress->progress_date->format('F d, Y') }}
                                </p>
                            </div>
                            <p class="text-sm text-gray-600 mt-1 font-medium">
                                {{ $progress->lesson_topic ?? 'Regular lesson' }}
                            </p>
                            @if($progress->instructor_first_name)
                                <p class="text-sm text-gray-600 mt-0.5">
                                    Instructor: {{ trim($progress->instructor_first_name . ' ' . $progress->instructor_last_name) }}
                                </p>
                            @endif
                        </div>
                        @if($progress->performance_rating)
                            <div class="flex items-center gap-2 px-4 py-2 bg-indigo-100 border border-indigo-300 rounded-lg">
                                <svg class="w-5 h-5 text-indigo-700" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                                <span class="text-base font-bold text-indigo-900">{{ $progress->performance_rating }}/10</span>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="p-6">
                    {{-- Ratings grid --}}
                    @if($progress->technical_skills_rating || $progress->musicality_rating || $progress->effort_rating)
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6 pb-6 border-b border-gray-200">
                            @if($progress->technical_skills_rating)
                                <div class="bg-gray-50 border border-gray-300 rounded-lg p-4">
                                    <p class="text-xs font-bold text-gray-600 mb-2 uppercase tracking-wide">Technical skills</p>
                                    <div class="flex items-center gap-2">
                                        <div class="flex-1 bg-gray-300 rounded-full h-2.5">
                                            <div class="bg-gradient-to-r from-indigo-500 to-indigo-700 h-2.5 rounded-full transition-all" style="width: {{ $progress->technical_skills_rating * 10 }}%"></div>
                                        </div>
                                        <span class="text-sm font-bold text-gray-900">{{ $progress->technical_skills_rating }}/10</span>
                                    </div>
                                </div>
                            @endif
                            @if($progress->musicality_rating)
                                <div class="bg-gray-50 border border-gray-300 rounded-lg p-4">
                                    <p class="text-xs font-bold text-gray-600 mb-2 uppercase tracking-wide">Musicality</p>
                                    <div class="flex items-center gap-2">
                                        <div class="flex-1 bg-gray-300 rounded-full h-2.5">
                                            <div class="bg-gradient-to-r from-purple-500 to-purple-700 h-2.5 rounded-full transition-all" style="width: {{ $progress->musicality_rating * 10 }}%"></div>
                                        </div>
                                        <span class="text-sm font-bold text-gray-900">{{ $progress->musicality_rating }}/10</span>
                                    </div>
                                </div>
                            @endif
                            @if($progress->effort_rating)
                                <div class="bg-gray-50 border border-gray-300 rounded-lg p-4">
                                    <p class="text-xs font-bold text-gray-600 mb-2 uppercase tracking-wide">Effort</p>
                                    <div class="flex items-center gap-2">
                                        <div class="flex-1 bg-gray-300 rounded-full h-2.5">
                                            <div class="bg-gradient-to-r from-green-500 to-green-700 h-2.5 rounded-full transition-all" style="width: {{ $progress->effort_rating * 10 }}%"></div>
                                        </div>
                                        <span class="text-sm font-bold text-gray-900">{{ $progress->effort_rating }}/10</span>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif

                    {{-- Skills & content --}}
                    @if($progress->skills_covered || $progress->techniques_learned || $progress->songs_practiced)
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6 pb-6 border-b border-gray-200">
                            @if($progress->skills_covered)
                                <div>
                                    <p class="text-sm font-bold text-gray-900 mb-2 flex items-center gap-1">
                                        <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        Skills covered
                                    </p>
                                    <ul class="text-sm text-gray-700 space-y-1 ml-5">
                                        @foreach(explode(',', $progress->skills_covered) as $skill)
                                            <li class="list-disc">{{ trim($skill) }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                            @if($progress->techniques_learned)
                                <div>
                                    <p class="text-sm font-bold text-gray-900 mb-2 flex items-center gap-1">
                                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                        </svg>
                                        Techniques learned
                                    </p>
                                    <ul class="text-sm text-gray-700 space-y-1 ml-5">
                                        @foreach(explode(',', $progress->techniques_learned) as $technique)
                                            <li class="list-disc">{{ trim($technique) }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                            @if($progress->songs_practiced)
                                <div>
                                    <p class="text-sm font-bold text-gray-900 mb-2 flex items-center gap-1">
                                        <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/>
                                        </svg>
                                        Songs practiced
                                    </p>
                                    <ul class="text-sm text-gray-700 space-y-1 ml-5">
                                        @foreach(explode(',', $progress->songs_practiced) as $song)
                                            <li class="list-disc">{{ trim($song) }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </div>
                    @endif

                    {{-- Feedback section --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        @if($progress->strengths)
                            <div class="bg-green-50 border border-green-300 rounded-lg p-4">
                                <p class="font-bold text-green-900 mb-2 flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Strengths
                                </p>
                                <p class="text-gray-800">{{ $progress->strengths }}</p>
                            </div>
                        @endif
                        @if($progress->areas_for_improvement)
                            <div class="bg-yellow-50 border border-yellow-300 rounded-lg p-4">
                                <p class="font-bold text-yellow-900 mb-2 flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                                    </svg>
                                    Areas to improve
                                </p>
                                <p class="text-gray-800">{{ $progress->areas_for_improvement }}</p>
                            </div>
                        @endif
                    </div>

                    {{-- Instructor notes --}}
                    @if($progress->instructor_notes)
                        <div class="mt-6 pt-6 border-t border-gray-200">
                            <p class="font-bold text-gray-900 mb-2 text-sm flex items-center gap-1">
                                <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                                </svg>
                                Instructor notes
                            </p>
                            <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ $progress->instructor_notes }}</p>
                        </div>
                    @endif

                    {{-- Homework --}}
                    @if($progress->homework)
                        <div class="mt-6 pt-6 border-t border-gray-200">
                            <p class="font-bold text-gray-900 mb-2 text-sm flex items-center gap-1">
                                <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                Homework
                            </p>
                            <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ $progress->homework }}</p>
                        </div>
                    @endif
                </div>
            </div>
        @empty
            {{-- Empty state for progress --}}
            <div class="bg-white border border-gray-300 rounded-xl shadow p-12 text-center">
                <svg class="w-20 h-20 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                <h3 class="text-xl font-bold text-gray-900 mb-2">No progress updates yet</h3>
                <p class="text-base text-gray-600 font-medium">Your instructor will add feedback after each lesson</p>
            </div>
        @endforelse
    </div>

    {{-- Attendance tab content --}}
    <div id="content-attendance" class="tab-content hidden">
        @forelse($attendanceHistory as $attendance)
            <div class="bg-white border border-gray-300 rounded-xl shadow mb-4 p-6 hover:shadow-md transition-all">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    {{-- Left: Date and time info --}}
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-2">
                            <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <p class="text-lg font-bold text-gray-900">
                                {{ $attendance->attendance_date->format('F d, Y') }}
                            </p>
                        </div>
                        <div class="space-y-1 ml-7">
                            <p class="text-sm text-gray-600 font-medium">
                                <svg class="w-4 h-4 inline-block mr-1 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                {{ \Carbon\Carbon::parse($attendance->start_time)->format('g:i A') }}
                            </p>
                            @if($attendance->lesson_topic)
                                <p class="text-sm text-gray-600">{{ $attendance->lesson_topic }}</p>
                            @endif
                            @if($attendance->instructor_first_name)
                                <p class="text-sm text-gray-600">
                                    Instructor: {{ trim($attendance->instructor_first_name . ' ' . $attendance->instructor_last_name) }}
                                </p>
                            @endif
                        </div>
                    </div>

                    {{-- Right: Status badge --}}
                    <div>
                        @php
                            $statusConfig = [
                                'present' => ['bg' => 'bg-green-100', 'border' => 'border-green-300', 'text' => 'text-green-800', 'label' => 'Present', 'dot' => 'bg-green-500'],
                                'absent' => ['bg' => 'bg-red-100', 'border' => 'border-red-300', 'text' => 'text-red-800', 'label' => 'Absent', 'dot' => 'bg-red-500'],
                                'late' => ['bg' => 'bg-yellow-100', 'border' => 'border-yellow-300', 'text' => 'text-yellow-800', 'label' => 'Late', 'dot' => 'bg-yellow-500'],
                                'excused' => ['bg' => 'bg-blue-100', 'border' => 'border-blue-300', 'text' => 'text-blue-800', 'label' => 'Excused', 'dot' => 'bg-blue-500'],
                            ];
                            $status = $statusConfig[$attendance->attendance_status] ?? $statusConfig['present'];
                        @endphp
                        <div class="inline-flex items-center gap-2 px-4 py-2 {{ $status['bg'] }} border {{ $status['border'] }} rounded-lg">
                            <div class="w-2.5 h-2.5 rounded-full {{ $status['dot'] }}"></div>
                            <span class="text-sm font-bold {{ $status['text'] }}">{{ $status['label'] }}</span>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            {{-- Empty state for attendance --}}
            <div class="bg-white border border-gray-300 rounded-xl shadow p-12 text-center">
                <svg class="w-20 h-20 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>
                <h3 class="text-xl font-bold text-gray-900 mb-2">No attendance records yet</h3>
                <p class="text-base text-gray-600 font-medium">Your attendance will appear here once you start attending lessons</p>
            </div>
        @endforelse
    </div>

</div>

{{-- Tab switching script --}}
<script>
function switchTab(tab) {
    // Hide all content
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.add('hidden');
    });
    
    // Remove active state from all buttons
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    
    // Show selected content
    document.getElementById('content-' + tab).classList.remove('hidden');
    
    // Add active state to selected button
    document.getElementById('tab-' + tab).classList.add('active');
}
</script>

<style>
/* Tab button styles */
.tab-btn {
    color: #6B7280;
    border-bottom: 3px solid transparent;
}

.tab-btn:hover {
    color: #111827;
}

.tab-btn.active {
    color: #111827;
    border-bottom-color: #4F46E5;
}
</style>
@endsection