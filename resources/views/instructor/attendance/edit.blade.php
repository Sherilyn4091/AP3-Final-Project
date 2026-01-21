@extends('layouts.instructor')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <div class="mb-6 flex items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Manage Attendance</h1>
            <p class="mt-1 text-sm text-gray-600">
                {{ $student->last_name }}, {{ $student->first_name }} • ID: {{ $student->student_id }}
            </p>
        </div>

        <a href="{{ route('instructor.attendance.index') }}"
           class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200">
            Back
        </a>
    </div>

    @if (session('success'))
        <div class="mb-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-800">
            {{ session('success') }}
        </div>
    @endif

    @if($schedules->isEmpty())
        <div class="rounded-xl border border-gray-200 bg-white p-10 text-center shadow-sm">
            <p class="text-gray-700">No schedule records found for this student.</p>
        </div>
    @else
        <form method="POST" action="{{ route('instructor.attendance.update', $student->student_id) }}"
              class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
            @csrf
            @method('PUT')

            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Time</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Room</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">
                    @foreach($schedules as $i => $sc)
                        @php
                            $att = $attendanceBySchedule[$sc->schedule_id] ?? null;
                            $current = $att?->attendance_status ?? null;
                        @endphp

                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm text-gray-700">
                                {{ \Carbon\Carbon::parse($sc->schedule_date)->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700">
                                {{ \Carbon\Carbon::createFromFormat('H:i:s', $sc->start_time)->format('h:i A') }}
                                –
                                {{ \Carbon\Carbon::createFromFormat('H:i:s', $sc->end_time)->format('h:i A') }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700">
                                {{ $sc->room_number ?? '—' }}
                            </td>
                            <td class="px-6 py-4">
                                <input type="hidden" name="attendance[{{ $i }}][schedule_id]" value="{{ $sc->schedule_id }}">

                                <select name="attendance[{{ $i }}][status]"
                                       class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-gray-400 focus:ring-0">
                                    <option value="present"  {{ $current === 'present' ? 'selected' : '' }}>Present</option>
                                    <option value="absent"   {{ $current === 'absent' ? 'selected' : '' }}>Absent</option>
                                    <option value="late"     {{ $current === 'late' ? 'selected' : '' }}>Late</option>
                                    <option value="excused"  {{ $current === 'excused' ? 'selected' : '' }}>Excused</option>
                                </select>

                                @if($current)
                                    <div class="mt-1 text-xs text-gray-500">
                                        Currently: {{ ucfirst($current) }}
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="flex items-center justify-end gap-2 border-t border-gray-200 p-4">
                <button type="submit"
                        class="rounded-lg bg-gray-800 px-5 py-2 text-sm font-medium text-white hover:bg-gray-700">
                    Save Attendance
                </button>
            </div>

        </form>
    @endif

</div>
@endsection