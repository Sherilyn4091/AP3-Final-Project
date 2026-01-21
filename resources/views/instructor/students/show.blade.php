@extends('layouts.instructor')

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <div class="flex items-start justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                {{ $student->first_name }} {{ $student->last_name }}
            </h1>
            <p class="text-gray-600">
                {{ $student->instrument->instrument_name ?? 'N/A' }} •
                {{ ucfirst($student->skill_level ?? 'Beginner') }}
                @if($student->status?->status_name)
                    • {{ $student->status->status_name }}
                @endif
            </p>
        </div>

        <a href="{{ route('instructor.students.index') }}"
           class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition text-sm">
            Back
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Basic Info --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Student Info</h2>

            @php $enroll = $student->latestEnrollment; @endphp

            <div class="space-y-3 text-sm">
                <div>
                    <p class="text-gray-500">Email</p>
                    <p class="font-medium text-gray-900">{{ $student->email ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Phone</p>
                    <p class="font-medium text-gray-900">{{ $student->phone ?? 'N/A' }}</p>
                </div>

                <div class="pt-2 border-t border-gray-100">
                    <p class="text-gray-500">Sessions</p>
                    <p class="font-medium text-gray-900">
                        {{ $enroll->completed_sessions ?? 0 }} / {{ $enroll->total_sessions ?? 0 }}
                        (Remaining: {{ $enroll->remaining_sessions ?? 0 }})
                    </p>
                </div>
            </div>
        </div>

        {{-- Next Class --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Next Class</h2>

            @if($nextClass)
                <div class="space-y-2 text-sm">
                    <div>
                        <p class="text-gray-500">Date</p>
                        <p class="font-medium text-gray-900">{{ $nextClass->schedule_date }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Time</p>
                        <p class="font-medium text-gray-900">{{ $nextClass->start_time }} - {{ $nextClass->end_time }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Topic</p>
                        <p class="font-medium text-gray-900">{{ $nextClass->lesson_topic ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Room</p>
                        <p class="font-medium text-gray-900">{{ $nextClass->room_number ?? 'N/A' }}</p>
                    </div>
                </div>
            @else
                <p class="text-gray-600 text-sm">No upcoming class scheduled.</p>
            @endif
        </div>

        {{-- Attendance --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Recent Attendance</h2>

            @if($attendance->isEmpty())
                <p class="text-gray-600 text-sm">No attendance records yet.</p>
            @else
                <div class="space-y-3">
                    @foreach($attendance as $a)
                        <div class="flex items-center justify-between text-sm border-b border-gray-100 pb-2">
                            <div>
                                <p class="font-medium text-gray-900">{{ $a->attendance_date }}</p>
                                <p class="text-gray-500">Schedule ID: {{ $a->schedule_id ?? 'N/A' }}</p>
                            </div>
                            <span class="px-2 py-1 rounded bg-gray-200 text-gray-800 text-xs font-medium">
                                {{ strtoupper($a->attendance_status) }}
                            </span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

    </div>
</div>
@endsection
