{{-- resources/views/instructor/students/show.blade.php --}}
@extends('layouts.instructor')

@section('content')
@php
    $progressPercent = $enrollment->total_sessions > 0 ? round(($enrollment->completed_sessions / $enrollment->total_sessions) * 100) : 0;
@endphp

<div class="space-y-6">
    <header class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.22em] text-[#B4833D]">Student Monitoring</p>
            <h1 class="mt-2 text-3xl font-extrabold text-[#2F4F4F]" style="font-family: 'Sora', sans-serif;">{{ $student->student_name }}</h1>
            <p class="mt-2 text-sm text-[#61677A]">{{ $enrollment->instrument_name }} • {{ ucfirst($enrollment->status) }} enrollment • {{ $student->email ?? 'No email' }}</p>
        </div>
        <div class="flex flex-col gap-2 sm:flex-row">
            <a href="{{ route('instructor.attendance.edit', $student->student_id) }}" class="rounded-2xl border border-[#959D90] bg-white px-4 py-2 text-center text-sm font-bold text-[#2F4F4F] hover:bg-[#FFF6E0]">Manage Attendance</a>
            <a href="{{ route('instructor.progress.create', ['student_id' => $student->student_id]) }}" class="rounded-2xl bg-[#2F4F4F] px-4 py-2 text-center text-sm font-bold text-white hover:bg-[#B4833D]">Add Progress</a>
        </div>
    </header>

    <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-[24px] border border-[#D8D9DA] bg-white p-5"><p class="text-xs font-bold uppercase text-[#61677A]">Total Sessions</p><p class="mt-2 text-3xl font-black text-[#2F4F4F]" style="font-family: 'JetBrains Mono', monospace;">{{ $enrollment->total_sessions }}</p></div>
        <div class="rounded-[24px] border border-[#D8D9DA] bg-white p-5"><p class="text-xs font-bold uppercase text-[#61677A]">Completed</p><p class="mt-2 text-3xl font-black text-[#2F4F4F]" style="font-family: 'JetBrains Mono', monospace;">{{ $enrollment->completed_sessions }}</p></div>
        <div class="rounded-[24px] border border-[#D8D9DA] bg-white p-5"><p class="text-xs font-bold uppercase text-[#61677A]">Remaining</p><p class="mt-2 text-3xl font-black text-[#2F4F4F]" style="font-family: 'JetBrains Mono', monospace;">{{ $enrollment->remaining_sessions }}</p></div>
        <div class="rounded-[24px] border border-[#D8D9DA] bg-white p-5"><p class="text-xs font-bold uppercase text-[#61677A]">Progress</p><p class="mt-2 text-3xl font-black text-[#2F4F4F]" style="font-family: 'JetBrains Mono', monospace;">{{ $progressPercent }}%</p></div>
    </section>

    <section class="grid grid-cols-1 gap-5 xl:grid-cols-3">
        <div class="rounded-[28px] border border-[#D8D9DA] bg-white p-5 shadow-sm xl:col-span-2">
            <h2 class="text-xl font-bold text-[#2F4F4F]" style="font-family: 'Sora', sans-serif;">Recent Schedules</h2>
            <div class="mt-5 space-y-3">
                @forelse($recentSchedules as $schedule)
                    <div class="rounded-2xl border border-[#D8D9DA] bg-[#fcf3e3] p-4">
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p class="font-bold text-[#272829]">{{ $schedule->lesson_topic ?? 'No topic' }}</p>
                                <p class="text-sm text-[#61677A]">{{ $schedule->room_number ?? 'No room' }} • {{ ucfirst(str_replace('_', ' ', $schedule->schedule_status)) }}</p>
                            </div>
                            <div class="text-left sm:text-right">
                                <p class="font-bold text-[#2F4F4F]" style="font-family: 'JetBrains Mono', monospace;">{{ \Carbon\Carbon::parse($schedule->schedule_date)->format('M d, Y') }}</p>
                                <p class="text-sm text-[#61677A]" style="font-family: 'JetBrains Mono', monospace;">{{ \Carbon\Carbon::parse($schedule->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($schedule->end_time)->format('h:i A') }}</p>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="rounded-2xl border border-dashed border-[#959D90] bg-[#FFF6E0] p-5 text-sm text-[#523D35]">No schedules found for this student.</p>
                @endforelse
            </div>
        </div>

        <aside class="space-y-5">
            <div class="rounded-[28px] border border-[#D8D9DA] bg-white p-5 shadow-sm">
                <h2 class="text-xl font-bold text-[#2F4F4F]" style="font-family: 'Sora', sans-serif;">Next Class</h2>
                @if($nextClass)
                    <p class="mt-4 text-2xl font-black text-[#2F4F4F]" style="font-family: 'JetBrains Mono', monospace;">{{ \Carbon\Carbon::parse($nextClass->schedule_date)->format('M d') }}</p>
                    <p class="mt-1 text-sm text-[#61677A]" style="font-family: 'JetBrains Mono', monospace;">{{ \Carbon\Carbon::parse($nextClass->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($nextClass->end_time)->format('h:i A') }}</p>
                    <p class="mt-2 text-sm text-[#523D35]">{{ $nextClass->lesson_topic ?? 'No topic yet' }}</p>
                @else
                    <p class="mt-4 text-sm text-[#61677A]">No upcoming class found.</p>
                @endif
            </div>

            <div class="rounded-[28px] border border-[#D8D9DA] bg-white p-5 shadow-sm">
                <h2 class="text-xl font-bold text-[#2F4F4F]" style="font-family: 'Sora', sans-serif;">Latest Homework</h2>
                @if($latestHomework)
                    <p class="mt-4 text-sm leading-6 text-[#523D35]">{{ $latestHomework->homework }}</p>
                    <a href="{{ route('instructor.progress.show', $latestHomework->progress_id) }}" class="mt-4 inline-block text-sm font-bold text-[#B4833D] hover:text-[#523D35]">Open progress record</a>
                @else
                    <p class="mt-4 text-sm text-[#61677A]">No homework recorded yet.</p>
                @endif
            </div>
        </aside>
    </section>

    <section class="rounded-[28px] border border-[#D8D9DA] bg-white p-5 shadow-sm">
        <div class="flex items-center justify-between gap-3">
            <div>
                <h2 class="text-xl font-bold text-[#2F4F4F]" style="font-family: 'Sora', sans-serif;">Progress Records</h2>
                <p class="text-sm text-[#61677A]">Latest progress notes, homework, ratings, and next lesson focus.</p>
            </div>
        </div>

        <div class="mt-5 grid grid-cols-1 gap-3 lg:grid-cols-2">
            @forelse($progressRecords as $record)
                <a href="{{ route('instructor.progress.show', $record->progress_id) }}" class="rounded-2xl border border-[#D8D9DA] bg-[#fcf3e3] p-4 hover:border-[#B4833D] hover:bg-[#FFF6E0]">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="font-bold text-[#272829]">{{ $record->lesson_topic ?? 'No topic' }}</p>
                            <p class="mt-1 line-clamp-2 text-sm text-[#61677A]">{{ $record->instructor_notes ?? $record->homework ?? 'No detailed note' }}</p>
                        </div>
                        <p class="font-bold text-[#2F4F4F]" style="font-family: 'JetBrains Mono', monospace;">{{ $record->performance_rating ?? '—' }}/10</p>
                    </div>
                </a>
            @empty
                <p class="rounded-2xl border border-dashed border-[#959D90] bg-[#FFF6E0] p-5 text-sm text-[#523D35] lg:col-span-2">No progress records yet.</p>
            @endforelse
        </div>
    </section>
</div>
@endsection