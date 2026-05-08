{{-- resources/views/instructor/schedule/index.blade.php --}}
@extends('layouts.instructor')

@section('content')
@php
    $filter = $filter ?? 'all';
    $filters = [
        'all' => 'All',
        'today' => 'Today',
        'week' => 'This Week',
        'upcoming' => 'Upcoming',
        'past' => 'Past',
    ];
@endphp

<div class="space-y-6">
    <header class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.22em] text-[#B4833D]">Schedule</p>
            <h1 class="mt-2 text-3xl font-extrabold text-[#2F4F4F]" style="font-family: 'Sora', sans-serif;">My Schedule</h1>
            <p class="mt-2 max-w-2xl text-sm text-[#61677A]">View real lesson schedules connected to your instructor account, enrollment, student, instrument, room, and attendance records.</p>
        </div>

        <div class="flex flex-col gap-2 sm:flex-row">
            <form method="GET" action="{{ route('instructor.schedule.index') }}" class="flex gap-2">
                <input type="hidden" name="filter" value="{{ $filter }}">
                <input name="q" value="{{ $q ?? '' }}" placeholder="Search schedule..." class="w-full rounded-2xl border border-[#D8D9DA] bg-white px-4 py-2 text-sm focus:border-[#959D90] focus:ring-[#959D90] sm:w-64">
                <button class="rounded-2xl bg-[#2F4F4F] px-4 py-2 text-sm font-bold text-white hover:bg-[#B4833D]">Search</button>
            </form>
            <a href="{{ route('instructor.schedule.create') }}" class="rounded-2xl bg-[#3C4B33] px-4 py-2 text-center text-sm font-bold text-white hover:bg-[#B4833D]">Create Schedule</a>
        </div>
    </header>

    <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-[22px] border border-[#D8D9DA] bg-white p-4"><p class="text-xs font-bold uppercase text-[#61677A]">All</p><p class="mt-2 text-2xl font-black text-[#2F4F4F]" style="font-family: 'JetBrains Mono', monospace;">{{ $stats['all'] ?? 0 }}</p></div>
        <div class="rounded-[22px] border border-[#D8D9DA] bg-white p-4"><p class="text-xs font-bold uppercase text-[#61677A]">Today</p><p class="mt-2 text-2xl font-black text-[#2F4F4F]" style="font-family: 'JetBrains Mono', monospace;">{{ $stats['today'] ?? 0 }}</p></div>
        <div class="rounded-[22px] border border-[#D8D9DA] bg-white p-4"><p class="text-xs font-bold uppercase text-[#61677A]">Upcoming</p><p class="mt-2 text-2xl font-black text-[#2F4F4F]" style="font-family: 'JetBrains Mono', monospace;">{{ $stats['upcoming'] ?? 0 }}</p></div>
        <div class="rounded-[22px] border border-[#D8D9DA] bg-white p-4"><p class="text-xs font-bold uppercase text-[#61677A]">Past</p><p class="mt-2 text-2xl font-black text-[#2F4F4F]" style="font-family: 'JetBrains Mono', monospace;">{{ $stats['past'] ?? 0 }}</p></div>
    </section>

    <nav class="flex gap-2 overflow-x-auto rounded-[24px] border border-[#D8D9DA] bg-white p-3 shadow-sm">
        @foreach($filters as $key => $label)
            <a href="{{ route('instructor.schedule.index', ['filter' => $key, 'q' => $q ?? null]) }}" class="shrink-0 rounded-2xl px-4 py-2 text-sm font-bold transition {{ $filter === $key ? 'bg-[#2F4F4F] text-white' : 'bg-[#FFF6E0] text-[#523D35] hover:bg-[#D8D9DA]' }}">
                {{ $label }}
            </a>
        @endforeach
    </nav>

    <section class="grid grid-cols-1 gap-4 xl:grid-cols-2">
        @forelse($schedules as $schedule)
            @php
                $statusClass = match($schedule->status) {
                    'completed' => 'bg-[#2F4F4F] text-white',
                    'cancelled', 'no_class' => 'bg-[#523D35] text-white',
                    'in_progress' => 'bg-[#B4833D] text-white',
                    default => 'bg-[#FFF6E0] text-[#523D35]',
                };
            @endphp

            <article class="rounded-[26px] border border-[#D8D9DA] bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-[#B4833D] hover:shadow-md">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wide text-[#61677A]">{{ $schedule->instrument_name ?? 'No instrument' }}</p>
                        <h2 class="mt-1 text-xl font-bold text-[#272829]" style="font-family: 'Sora', sans-serif;">{{ $schedule->student_name }}</h2>
                        <p class="mt-1 text-sm text-[#61677A]">{{ $schedule->lesson_topic ?? 'No lesson topic yet' }}</p>
                    </div>
                    <span class="w-fit rounded-full px-3 py-1 text-xs font-bold {{ $statusClass }}">{{ ucfirst(str_replace('_', ' ', $schedule->status)) }}</span>
                </div>

                <div class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    <div><p class="text-xs font-bold uppercase text-[#61677A]">Date</p><p class="mt-1 font-bold text-[#2F4F4F]" style="font-family: 'JetBrains Mono', monospace;">{{ \Carbon\Carbon::parse($schedule->schedule_date)->format('M d, Y') }}</p></div>
                    <div><p class="text-xs font-bold uppercase text-[#61677A]">Time</p><p class="mt-1 font-bold text-[#2F4F4F]" style="font-family: 'JetBrains Mono', monospace;">{{ \Carbon\Carbon::parse($schedule->start_time)->format('h:i A') }}</p></div>
                    <div><p class="text-xs font-bold uppercase text-[#61677A]">Room</p><p class="mt-1 font-bold text-[#2F4F4F]">{{ $schedule->room_number ?? '—' }}</p></div>
                    <div><p class="text-xs font-bold uppercase text-[#61677A]">Attendance</p><p class="mt-1 font-bold text-[#2F4F4F]">{{ $schedule->attendance_status ? ucfirst($schedule->attendance_status) : 'Pending' }}</p></div>
                </div>

                <div class="mt-5 flex flex-col gap-2 sm:flex-row sm:justify-end">
                    <a href="{{ route('instructor.attendance.edit', $schedule->student_id) }}" class="rounded-2xl border border-[#959D90] px-4 py-2 text-center text-sm font-bold text-[#2F4F4F] hover:bg-[#FFF6E0]">Attendance</a>
                    <a href="{{ route('instructor.progress.create', ['student_id' => $schedule->student_id]) }}" class="rounded-2xl border border-[#959D90] px-4 py-2 text-center text-sm font-bold text-[#2F4F4F] hover:bg-[#FFF6E0]">Add Progress</a>
                    <a href="{{ route('instructor.schedule.edit', $schedule->schedule_id) }}" class="rounded-2xl bg-[#2F4F4F] px-4 py-2 text-center text-sm font-bold text-white hover:bg-[#B4833D]">Edit</a>
                </div>
            </article>
        @empty
            <div class="rounded-[28px] border border-dashed border-[#959D90] bg-white p-10 text-center xl:col-span-2">
                <h2 class="text-2xl font-bold text-[#2F4F4F]" style="font-family: 'Sora', sans-serif;">No schedules found</h2>
                <p class="mt-2 text-sm text-[#61677A]">Try the All filter, clear your search, or create a schedule from an active enrollment.</p>
                <a href="{{ route('instructor.schedule.create') }}" class="mt-5 inline-block rounded-2xl bg-[#2F4F4F] px-5 py-3 text-sm font-bold text-white hover:bg-[#B4833D]">Create Schedule</a>
            </div>
        @endforelse
    </section>

    @if($schedules->hasPages())
        <div>{{ $schedules->links() }}</div>
    @endif
</div>
@endsection