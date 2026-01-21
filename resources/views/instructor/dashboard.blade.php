{{-- resources/views/instructor/dashboard.blade.php --}}
@extends('layouts.instructor')

@section('page_title', 'Dashboard')
@section('page_subtitle', 'Overview of your teaching activities')

@section('content')
@php
    // Make the Blade not crash even if you don't pass variables yet
    $instructor   = isset($instructor) ? $instructor : null;
    $stats        = isset($stats) && is_array($stats) ? $stats : [];
    $todayClasses = isset($todayClasses) ? $todayClasses : collect();

    // Default stats
    $totalStudents    = $stats['total_students'] ?? 0;
    $upcomingClasses  = $stats['upcoming_classes'] ?? 0;
    $completedClasses = $stats['completed_classes'] ?? 0;
@endphp

<div class="space-y-8">

    {{-- Welcome Section --}}
    <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Instructor Dashboard</h1>

                @if($instructor)
                    <p class="mt-2 text-gray-600">
                        Welcome back,
                        <span class="font-medium text-gray-900">
                            {{ $instructor->first_name }} {{ $instructor->last_name }}
                        </span>
                    </p>
                @else
                    <p class="mt-2 text-sm text-amber-700 bg-amber-50 border border-amber-200 rounded-lg px-4 py-2 inline-block">
                        Instructor profile not loaded yet (no controller data). The page is still okay.
                    </p>
                @endif
            </div>

            <div class="flex flex-wrap gap-2">
                <a href="{{ route('instructor.schedule.index') }}"
                   class="px-4 py-2 rounded-lg text-sm font-medium bg-slate-900 text-white hover:bg-slate-800 transition">
                    View Schedule
                </a>

                <a href="{{ route('instructor.attendance.index') }}"
                   class="px-4 py-2 rounded-lg text-sm font-medium bg-gray-200 text-gray-900 hover:bg-gray-300 transition">
                    Attendance
                </a>

                <a href="{{ route('instructor.progress.index') }}"
                   class="px-4 py-2 rounded-lg text-sm font-medium bg-gray-200 text-gray-900 hover:bg-gray-300 transition">
                    Progress Notes
                </a>
            </div>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        {{-- Total Students --}}
        <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-sm text-gray-500">Total Students</p>
                <span class="w-2.5 h-2.5 rounded-full bg-slate-500"></span>
            </div>
            <p class="mt-2 text-3xl font-semibold text-gray-900">{{ $totalStudents }}</p>
            <p class="mt-1 text-xs text-gray-500">Students currently assigned to you</p>
        </div>

        {{-- Upcoming Classes --}}
        <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-sm text-gray-500">Upcoming Classes</p>
                <span class="w-2.5 h-2.5 rounded-full bg-indigo-500"></span>
            </div>
            <p class="mt-2 text-3xl font-semibold text-gray-900">{{ $upcomingClasses }}</p>
            <p class="mt-1 text-xs text-gray-500">Scheduled lessons from today onwards</p>
        </div>

        {{-- Completed Classes --}}
        <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-sm text-gray-500">Completed Classes</p>
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
            </div>
            <p class="mt-2 text-3xl font-semibold text-gray-900">{{ $completedClasses }}</p>
            <p class="mt-1 text-xs text-gray-500">Lessons marked completed</p>
        </div>
    </div>

    {{-- Today's Schedule --}}
    <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
        <div class="flex items-center justify-between gap-3 mb-4">
            <h2 class="text-lg font-semibold text-gray-900">Today’s Schedule</h2>

            <a href="{{ route('instructor.schedule.index', ['filter' => 'today']) }}"
               class="text-sm font-medium text-gray-700 hover:text-gray-900 underline">
                Open Today View
            </a>
        </div>

        @if($todayClasses && count($todayClasses) > 0)
            <ul class="divide-y divide-gray-200">
                @foreach($todayClasses as $class)
                    @php
                        // Supports either DB raw objects or Eloquent
                        $studentName = $class->student_name
                            ?? trim(($class->student->first_name ?? '').' '.($class->student->last_name ?? ''))
                            ?? 'Student';

                        $lessonName = $class->lesson_name
                            ?? $class->lesson_topic
                            ?? 'Lesson';

                        $startTime = $class->start_time ?? null;
                        $endTime   = $class->end_time ?? null;

                        // If start_time/end_time are TIME strings ("10:00:00") this will format nicely:
                        $fmtStart = $startTime ? \Carbon\Carbon::createFromFormat('H:i:s', $startTime)->format('h:i A') : '—';
                        $fmtEnd   = $endTime ? \Carbon\Carbon::createFromFormat('H:i:s', $endTime)->format('h:i A') : '—';
                    @endphp

                    <li class="py-3 flex items-center justify-between text-sm">
                        <div>
                            <p class="font-medium text-gray-900">{{ $studentName }}</p>
                            <p class="text-gray-500">{{ $lessonName }}</p>
                        </div>

                        <span class="text-gray-600 whitespace-nowrap">
                            {{ $fmtStart }} – {{ $fmtEnd }}
                        </span>
                    </li>
                @endforeach
            </ul>
        @else
            <p class="text-sm text-gray-500">
                No classes scheduled for today.
            </p>
        @endif
    </div>

</div>
@endsection
