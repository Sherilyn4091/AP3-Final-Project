{{-- resources/views/instructor/attendance/edit.blade.php --}}
@extends('layouts.instructor')

@section('content')
<div class="space-y-6">
    <header class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.22em] text-[#B4833D]">Attendance</p>
            <h1 class="mt-2 text-3xl font-extrabold text-[#2F4F4F]" style="font-family: 'Sora', sans-serif;">{{ $student->student_name }}</h1>
            <p class="mt-2 text-sm text-[#61677A]">Mark attendance. Present and late consume one session; absent/excused/on leave do not.</p>
        </div>
        <a href="{{ route('instructor.attendance.index') }}" class="rounded-2xl border border-[#959D90] bg-white px-4 py-2 text-sm font-bold text-[#2F4F4F] hover:bg-[#FFF6E0]">Back</a>
    </header>

    @if(session('success'))
        <div class="rounded-2xl border border-[#959D90] bg-white p-4 text-sm font-bold text-[#2F4F4F]">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('instructor.attendance.update', $student->student_id) }}" class="rounded-[28px] border border-[#D8D9DA] bg-white p-5 shadow-sm">
        @csrf
        @method('PUT')

        <div class="space-y-3">
            @forelse($schedules as $index => $schedule)
                @php
                    $current = old("attendance.$index.status", $schedule->attendance_status ?? 'absent');
                @endphp
                <div class="rounded-2xl border border-[#D8D9DA] bg-[#fcf3e3] p-4">
                    <input type="hidden" name="attendance[{{ $index }}][schedule_id]" value="{{ $schedule->schedule_id }}">

                    <div class="grid grid-cols-1 gap-4 lg:grid-cols-[1fr_220px] lg:items-center">
                        <div>
                            <p class="font-bold text-[#272829]" style="font-family: 'Sora', sans-serif;">{{ $schedule->instrument_name ?? 'Lesson' }} â€¢ {{ $schedule->lesson_topic ?? 'No topic' }}</p>
                            <p class="mt-1 text-sm text-[#61677A]">
                                <span style="font-family: 'JetBrains Mono', monospace;">{{ \Carbon\Carbon::parse($schedule->schedule_date)->format('M d, Y') }} â€¢ {{ \Carbon\Carbon::parse($schedule->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($schedule->end_time)->format('h:i A') }}</span>
                                â€¢ Room {{ $schedule->room_number ?? 'â€”' }}
                            </p>
                            <p class="mt-1 text-xs font-semibold text-[#523D35]">Enrollment: {{ $schedule->completed_sessions ?? 0 }}/{{ $schedule->total_sessions ?? 0 }} completed, {{ $schedule->remaining_sessions ?? 0 }} remaining</p>
                        </div>

                        <select name="attendance[{{ $index }}][status]" class="w-full rounded-2xl border border-[#D8D9DA] bg-white px-4 py-3 text-sm font-bold focus:border-[#959D90] focus:ring-[#959D90]">
                            @foreach(['present', 'late', 'absent', 'excused', 'half_day', 'on_leave'] as $status)
                                <option value="{{ $status }}" @selected($current === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            @empty
                <div class="rounded-2xl border border-dashed border-[#959D90] bg-[#FFF6E0] p-8 text-center text-sm text-[#523D35]">
                    No schedules found for this student.
                </div>
            @endforelse
        </div>

        @if($schedules->count())
            <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:justify-end">
                <a href="{{ route('instructor.attendance.index') }}" class="rounded-2xl border border-[#959D90] px-5 py-3 text-center text-sm font-bold text-[#2F4F4F] hover:bg-[#FFF6E0]">Cancel</a>
                <button type="submit" class="rounded-2xl bg-[#2F4F4F] px-5 py-3 text-sm font-bold text-white hover:bg-[#B4833D]">Save Attendance</button>
            </div>
        @endif
    </form>
</div>
@endsection