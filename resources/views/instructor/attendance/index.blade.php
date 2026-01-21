@extends('layouts.instructor')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <div class="mb-8 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Attendance</h1>
            <p class="mt-1 text-gray-600">Track lesson attendance for your students</p>
        </div>

        <form method="GET" action="{{ route('instructor.attendance.index') }}" class="relative w-full sm:w-80">
            <input
                type="text"
                name="q"
                value="{{ $q ?? '' }}"
                placeholder="Search student..."
                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-400"
            >
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
        </form>
    </div>

    @if($attendance->count() === 0)
        <div class="bg-white rounded-xl border border-gray-100 p-12 text-center">
            <h3 class="text-xl font-medium text-gray-700">No attendance records</h3>
            <p class="mt-2 text-gray-500">
                Once schedules are created and attendance is marked, records will show here.
            </p>
        </div>
    @else
        <div class="bg-white rounded-xl border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr class="text-left text-gray-600">
                            <th class="px-6 py-3 font-medium">Date</th>
                            <th class="px-6 py-3 font-medium">Scheduled Time</th>
                            <th class="px-6 py-3 font-medium">Student</th>
                            <th class="px-6 py-3 font-medium">Status</th>
                            <th class="px-6 py-3 font-medium text-right">Action</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100">
                        @foreach($attendance as $a)
                            @php
                                $status = $a->attendance_status ?? '—';

                                $badge = match($status) {
                                    'present' => 'bg-gray-800 text-white',
                                    'late' => 'bg-gray-200 text-gray-900',
                                    'absent' => 'bg-gray-100 text-gray-700',
                                    'excused' => 'bg-gray-50 text-gray-700',
                                    default => 'bg-gray-100 text-gray-700',
                                };

                                // Requires Attendance model relationship: schedule()
                                $start = $a->schedule?->start_time;
                                $end   = $a->schedule?->end_time;
                            @endphp

                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-gray-900 whitespace-nowrap">
                                    {{ $a->attendance_date ? \Carbon\Carbon::parse($a->attendance_date)->format('Y-m-d') : 'N/A' }}
                                </td>

                                <td class="px-6 py-4 text-gray-700 whitespace-nowrap">
                                    @if($start && $end)
                                        {{ \Carbon\Carbon::createFromFormat('H:i:s', $start)->format('h:i A') }}
                                        -
                                        {{ \Carbon\Carbon::createFromFormat('H:i:s', $end)->format('h:i A') }}
                                    @else
                                        —
                                    @endif
                                </td>

                                <td class="px-6 py-4 text-gray-900">
                                    {{ $a->student->first_name ?? '' }} {{ $a->student->last_name ?? '' }}
                                </td>

                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {{ $badge }}">
                                        {{ ucfirst($status) }}
                                    </span>
                                </td>

                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('instructor.attendance.edit', $a->student_id) }}"
                                       class="inline-flex items-center px-3 py-1.5 border border-gray-300 bg-gray-800 text-white rounded-lg hover:bg-gray-700 transition text-xs font-medium">
                                        Manage
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>

                </table>
            </div>
        </div>

        <div class="mt-8">
            {{ $attendance->links() }}
        </div>
    @endif

</div>
@endsection
