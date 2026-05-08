@extends('layouts.student')

{{-- resources/views/student/progress.blade.php --}}

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    {{-- Page Header --}}
    <div class="mb-6">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">My Progress & Attendance</h1>
        <p class="mt-2 text-base text-gray-700">
            Track your learning journey, attendance history, and instructor feedback.
        </p>
    </div>

    {{-- Compact Stat Cards --}}
    @php
        /*
        |--------------------------------------------------------------------------
        | Progress Page Stats
        |--------------------------------------------------------------------------
        |
        | These cards summarize the student's learning records.
        | The layout is responsive, so it will not force 5 tiny cards on mobile.
        |
        */

        $statCards = [
            ['label' => 'Progress', 'value' => $stats['progress_records'] ?? 0, 'hint' => 'Records'],
            ['label' => 'Attendance', 'value' => $stats['attendance_records'] ?? 0, 'hint' => 'Records'],
            ['label' => 'Packages', 'value' => $stats['active_packages'] ?? 0, 'hint' => 'Active'],
            ['label' => 'Completed', 'value' => $stats['completed_sessions'] ?? 0, 'hint' => 'Sessions'],
            ['label' => 'Remaining', 'value' => $stats['remaining_sessions'] ?? 0, 'hint' => 'Sessions'],
        ];
    @endphp

    <section class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-5">
        @foreach($statCards as $card)
            <div class="rounded-2xl border border-gray-300 bg-white p-3 text-center shadow-sm transition hover:-translate-y-0.5 hover:shadow-md sm:p-4">
                <p class="text-[10px] font-bold uppercase tracking-wide text-gray-500 sm:text-xs">
                    {{ $card['label'] }}
                </p>
                <p class="mt-2 text-xl font-extrabold text-gray-900 sm:text-2xl">
                    {{ $card['value'] }}
                </p>
                <p class="mt-1 text-xs font-medium text-gray-600">
                    {{ $card['hint'] }}
                </p>
            </div>
        @endforeach
    </section>

    {{-- Tab Navigation --}}
    <div class="mb-6 flex gap-2 border-b border-gray-300">
        <button type="button"
                id="tab-progress"
                data-progress-tab-button
                data-tab="progress"
                class="tab-btn active inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold transition-colors sm:px-6">
            <svg class="h-4 w-4 sm:h-5 sm:w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
            Progress
        </button>

        <button type="button"
                id="tab-attendance"
                data-progress-tab-button
                data-tab="attendance"
                class="tab-btn inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold transition-colors sm:px-6">
            <svg class="h-4 w-4 sm:h-5 sm:w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
            </svg>
            Attendance
        </button>
    </div>

    {{-- Progress Tab Content --}}
    <div id="content-progress" class="tab-content">
        @forelse($progressHistory as $progress)
            @php
                /*
                |--------------------------------------------------------------------------
                | Safe Display Values
                |--------------------------------------------------------------------------
                |
                | These helpers allow the view to work whether the controller provides
                | instructor_full_name directly or first_name/last_name separately.
                |
                */

                $progressInstructorName = $progress->instructor_full_name
                    ?? trim(($progress->instructor_first_name ?? '') . ' ' . ($progress->instructor_last_name ?? ''));

                $progressInstrumentName = $progress->instrument_name ?? null;
            @endphp

            <article class="mb-4 overflow-hidden rounded-xl border border-gray-300 bg-white shadow transition-all hover:shadow-md">
                {{-- Progress Record Header --}}
                <div class="border-b border-gray-300 bg-gray-100 px-6 py-4">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <svg class="h-5 w-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>

                                <p class="text-lg font-bold text-gray-900">
                                    {{ $progress->progress_date->format('F d, Y') }}
                                </p>
                            </div>

                            @if($progressInstrumentName)
                                <p class="mt-1 text-xs font-bold uppercase tracking-wide text-gray-500">
                                    {{ $progressInstrumentName }}
                                </p>
                            @endif

                            <p class="mt-1 text-sm font-medium text-gray-600">
                                {{ $progress->lesson_topic ?? 'Regular lesson' }}
                            </p>

                            @if($progressInstructorName)
                                <p class="mt-0.5 text-sm text-gray-600">
                                    Instructor: {{ $progressInstructorName }}
                                </p>
                            @endif
                        </div>

                        @if($progress->performance_rating)
                            <div class="flex items-center gap-2 rounded-lg border border-indigo-300 bg-indigo-100 px-4 py-2">
                                <svg class="h-5 w-5 text-indigo-700" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                                <span class="text-base font-bold text-indigo-900">
                                    {{ $progress->performance_rating }}/10
                                </span>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="p-6">
                    {{-- Ratings Grid --}}
                    @if($progress->technical_skills_rating || $progress->musicality_rating || $progress->effort_rating)
                        <div class="mb-6 grid grid-cols-1 gap-4 border-b border-gray-200 pb-6 sm:grid-cols-3">
                            @foreach([
                                'Technical skills' => ['value' => $progress->technical_skills_rating, 'bar' => 'from-indigo-500 to-indigo-700'],
                                'Musicality' => ['value' => $progress->musicality_rating, 'bar' => 'from-purple-500 to-purple-700'],
                                'Effort' => ['value' => $progress->effort_rating, 'bar' => 'from-green-500 to-green-700'],
                            ] as $label => $rating)
                                @if($rating['value'])
                                    <div class="rounded-lg border border-gray-300 bg-gray-50 p-4">
                                        <p class="mb-2 text-xs font-bold uppercase tracking-wide text-gray-600">
                                            {{ $label }}
                                        </p>

                                        <div class="flex items-center gap-2">
                                            <div class="h-2.5 flex-1 rounded-full bg-gray-300">
                                                <div class="h-2.5 rounded-full bg-gradient-to-r {{ $rating['bar'] }} transition-all"
                                                     style="width: {{ $rating['value'] * 10 }}%">
                                                </div>
                                            </div>

                                            <span class="text-sm font-bold text-gray-900">
                                                {{ $rating['value'] }}/10
                                            </span>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @endif

                    {{-- Skills And Lesson Content --}}
                    @if($progress->skills_covered || $progress->techniques_learned || $progress->songs_practiced)
                        <div class="mb-6 grid grid-cols-1 gap-4 border-b border-gray-200 pb-6 md:grid-cols-3">
                            @foreach([
                                'Skills covered' => ['value' => $progress->skills_covered, 'iconColor' => 'text-green-600'],
                                'Techniques learned' => ['value' => $progress->techniques_learned, 'iconColor' => 'text-blue-600'],
                                'Songs practiced' => ['value' => $progress->songs_practiced, 'iconColor' => 'text-purple-600'],
                            ] as $label => $content)
                                @if($content['value'])
                                    <div>
                                        <p class="mb-2 flex items-center gap-1 text-sm font-bold text-gray-900">
                                            <svg class="h-4 w-4 {{ $content['iconColor'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            {{ $label }}
                                        </p>

                                        <ul class="ml-5 space-y-1 text-sm text-gray-700">
                                            @foreach(explode(',', $content['value']) as $item)
                                                <li class="list-disc">{{ trim($item) }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @endif

                    {{-- Feedback Section --}}
                    <div class="grid grid-cols-1 gap-4 text-sm md:grid-cols-2">
                        @if($progress->strengths)
                            <div class="rounded-lg border border-green-300 bg-green-50 p-4">
                                <p class="mb-2 flex items-center gap-1 font-bold text-green-900">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Strengths
                                </p>
                                <p class="text-gray-800">{{ $progress->strengths }}</p>
                            </div>
                        @endif

                        @if($progress->areas_for_improvement)
                            <div class="rounded-lg border border-yellow-300 bg-yellow-50 p-4">
                                <p class="mb-2 flex items-center gap-1 font-bold text-yellow-900">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                                    </svg>
                                    Areas to improve
                                </p>
                                <p class="text-gray-800">{{ $progress->areas_for_improvement }}</p>
                            </div>
                        @endif
                    </div>

                    {{-- Instructor Notes --}}
                    @if($progress->instructor_notes)
                        <div class="mt-6 border-t border-gray-200 pt-6">
                            <p class="mb-2 flex items-center gap-1 text-sm font-bold text-gray-900">
                                <svg class="h-4 w-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                                </svg>
                                Instructor notes
                            </p>
                            <p class="whitespace-pre-wrap text-sm text-gray-700">
                                {{ $progress->instructor_notes }}
                            </p>
                        </div>
                    @endif

                    {{-- Homework --}}
                    @if($progress->homework)
                        <div class="mt-6 border-t border-gray-200 pt-6">
                            <p class="mb-2 flex items-center gap-1 text-sm font-bold text-gray-900">
                                <svg class="h-4 w-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                Homework
                            </p>
                            <p class="whitespace-pre-wrap text-sm text-gray-700">
                                {{ $progress->homework }}
                            </p>
                        </div>
                    @endif
                </div>
            </article>
        @empty
            {{-- Empty State For Progress --}}
            <div class="rounded-xl border border-gray-300 bg-white p-8 text-center shadow sm:p-12">
                <svg class="mx-auto mb-4 h-20 w-20 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>

                <h3 class="mb-2 text-xl font-bold text-gray-900">No progress updates yet</h3>
                <p class="text-base font-medium text-gray-600">
                    Your instructor will add feedback after each lesson.
                </p>

                @if(isset($activeEnrollments) && $activeEnrollments->isNotEmpty())
                    <div class="mt-6 grid gap-3 text-left md:grid-cols-2">
                        @foreach($activeEnrollments as $enrollment)
                            <div class="rounded-lg border border-gray-300 bg-gray-50 p-4">
                                <p class="text-sm font-bold text-gray-900">
                                    {{ $enrollment->instrument_name ?? 'Instrument' }}
                                    @if(isset($enrollment->session_count))
                                        • {{ $enrollment->session_count }} sessions
                                    @endif
                                </p>

                                <p class="mt-1 text-xs text-gray-600">
                                    Instructor: {{ $enrollment->instructor_full_name ?? 'TBA' }}
                                </p>

                                <p class="mt-1 text-xs text-gray-500">
                                    Waiting for instructor progress records.
                                </p>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @endforelse
    </div>

    {{-- Attendance Tab Content --}}
    <div id="content-attendance" class="tab-content hidden">
        @forelse($attendanceHistory as $attendance)
            @php
                $attendanceInstructorName = $attendance->instructor_full_name
                    ?? trim(($attendance->instructor_first_name ?? '') . ' ' . ($attendance->instructor_last_name ?? ''));

                $attendanceInstrumentName = $attendance->instrument_name ?? null;

                $statusConfig = [
                    'present' => ['bg' => 'bg-green-100', 'border' => 'border-green-300', 'text' => 'text-green-800', 'label' => 'Present', 'dot' => 'bg-green-500'],
                    'absent' => ['bg' => 'bg-red-100', 'border' => 'border-red-300', 'text' => 'text-red-800', 'label' => 'Absent', 'dot' => 'bg-red-500'],
                    'late' => ['bg' => 'bg-yellow-100', 'border' => 'border-yellow-300', 'text' => 'text-yellow-800', 'label' => 'Late', 'dot' => 'bg-yellow-500'],
                    'excused' => ['bg' => 'bg-blue-100', 'border' => 'border-blue-300', 'text' => 'text-blue-800', 'label' => 'Excused', 'dot' => 'bg-blue-500'],
                ];

                $status = $statusConfig[$attendance->attendance_status] ?? [
                    'bg' => 'bg-gray-100',
                    'border' => 'border-gray-300',
                    'text' => 'text-gray-800',
                    'label' => ucfirst(str_replace('_', ' ', $attendance->attendance_status ?? 'Recorded')),
                    'dot' => 'bg-gray-500',
                ];
            @endphp

            <article class="mb-4 rounded-xl border border-gray-300 bg-white p-6 shadow transition-all hover:shadow-md">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    {{-- Left: Date and time info --}}
                    <div class="flex-1">
                        <div class="mb-2 flex items-center gap-2">
                            <svg class="h-5 w-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>

                            <p class="text-lg font-bold text-gray-900">
                                {{ $attendance->attendance_date->format('F d, Y') }}
                            </p>
                        </div>

                        <div class="ml-7 space-y-1">
                            @if($attendanceInstrumentName)
                                <p class="text-xs font-bold uppercase tracking-wide text-gray-500">
                                    {{ $attendanceInstrumentName }}
                                </p>
                            @endif

                            <p class="text-sm font-medium text-gray-600">
                                <svg class="mr-1 inline-block h-4 w-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>

                                {{ \Carbon\Carbon::parse($attendance->start_time)->format('g:i A') }}

                                @if(isset($attendance->end_time) && $attendance->end_time)
                                    - {{ \Carbon\Carbon::parse($attendance->end_time)->format('g:i A') }}
                                @endif
                            </p>

                            @if($attendance->lesson_topic)
                                <p class="text-sm text-gray-600">
                                    {{ $attendance->lesson_topic }}
                                </p>
                            @endif

                            @if($attendanceInstructorName)
                                <p class="text-sm text-gray-600">
                                    Instructor: {{ $attendanceInstructorName }}
                                </p>
                            @endif
                        </div>
                    </div>

                    {{-- Right: Status Badge --}}
                    <div>
                        <div class="inline-flex items-center gap-2 rounded-lg border px-4 py-2 {{ $status['bg'] }} {{ $status['border'] }}">
                            <div class="h-2.5 w-2.5 rounded-full {{ $status['dot'] }}"></div>
                            <span class="text-sm font-bold {{ $status['text'] }}">
                                {{ $status['label'] }}
                            </span>
                        </div>
                    </div>
                </div>
            </article>
        @empty
            {{-- Empty State For Attendance --}}
            <div class="rounded-xl border border-gray-300 bg-white p-8 text-center shadow sm:p-12">
                <svg class="mx-auto mb-4 h-20 w-20 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 00-2-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>

                <h3 class="mb-2 text-xl font-bold text-gray-900">No attendance records yet</h3>
                <p class="text-base font-medium text-gray-600">
                    Your attendance will appear here once your instructor records your lesson attendance.
                </p>

                @if(isset($activeEnrollments) && $activeEnrollments->isNotEmpty())
                    <div class="mt-6 grid gap-3 text-left md:grid-cols-2">
                        @foreach($activeEnrollments as $enrollment)
                            <div class="rounded-lg border border-gray-300 bg-gray-50 p-4">
                                <p class="text-sm font-bold text-gray-900">
                                    {{ $enrollment->instrument_name ?? 'Instrument' }}
                                    @if(isset($enrollment->session_count))
                                        • {{ $enrollment->session_count }} sessions
                                    @endif
                                </p>

                                <p class="mt-1 text-xs text-gray-600">
                                    Instructor: {{ $enrollment->instructor_full_name ?? 'TBA' }}
                                </p>

                                <p class="mt-1 text-xs text-gray-500">
                                    Attendance records will appear after lessons are scheduled and checked.
                                </p>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @endforelse
    </div>

</div>
@endsection

@push('styles')
<style>
/*
|--------------------------------------------------------------------------
| Progress Page Tab Styling
|--------------------------------------------------------------------------
|
| This is only the simple old-style tab design.
| It does not use the new student palette classes.
|
*/

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
@endpush