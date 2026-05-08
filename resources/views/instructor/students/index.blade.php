{{-- resources/views/instructor/students/index.blade.php --}}
@extends('layouts.instructor')

@section('content')
<div class="space-y-6">
    <header class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.22em] text-[#B4833D]">Student Monitoring</p>
            <h1 class="mt-2 text-3xl font-extrabold text-[#2F4F4F]" style="font-family: 'Sora', sans-serif;">My Students</h1>
            <p class="mt-2 max-w-2xl text-sm text-[#61677A]">Students are shown through real enrollment records assigned to your instructor account.</p>
        </div>

        <form method="GET" action="{{ route('instructor.students.index') }}" class="flex gap-2">
            <input name="q" value="{{ $q ?? '' }}" placeholder="Search student..." class="w-full rounded-2xl border border-[#D8D9DA] bg-white px-4 py-2 text-sm focus:border-[#959D90] focus:ring-[#959D90] sm:w-72">
            <button class="rounded-2xl bg-[#2F4F4F] px-4 py-2 text-sm font-bold text-white hover:bg-[#B4833D]">Search</button>
        </form>
    </header>

    <section class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
        @forelse($students as $student)
            @php
                $percent = $student->total_sessions > 0 ? round(($student->completed_sessions / $student->total_sessions) * 100) : 0;
            @endphp
            <article class="rounded-[26px] border border-[#D8D9DA] bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-[#B4833D] hover:shadow-md">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wide text-[#61677A]">{{ $student->instrument_name }}</p>
                        <h2 class="mt-1 text-xl font-bold text-[#272829]" style="font-family: 'Sora', sans-serif;">{{ $student->student_name }}</h2>
                        <p class="mt-1 text-sm text-[#61677A]">{{ $student->email ?? 'No email' }}</p>
                    </div>
                    <span class="rounded-full bg-[#FFF6E0] px-3 py-1 text-xs font-bold text-[#523D35]">{{ ucfirst($student->enrollment_status) }}</span>
                </div>

                <div class="mt-5 grid grid-cols-2 gap-3 text-sm">
                    <div class="rounded-2xl bg-[#fcf3e3] p-3"><p class="text-xs font-bold uppercase text-[#61677A]">Remaining</p><p class="mt-1 font-bold text-[#2F4F4F]" style="font-family: 'JetBrains Mono', monospace;">{{ $student->remaining_sessions }}</p></div>
                    <div class="rounded-2xl bg-[#fcf3e3] p-3"><p class="text-xs font-bold uppercase text-[#61677A]">Progress</p><p class="mt-1 font-bold text-[#2F4F4F]" style="font-family: 'JetBrains Mono', monospace;">{{ $percent }}%</p></div>
                    <div class="rounded-2xl bg-[#fcf3e3] p-3"><p class="text-xs font-bold uppercase text-[#61677A]">Records</p><p class="mt-1 font-bold text-[#2F4F4F]" style="font-family: 'JetBrains Mono', monospace;">{{ $student->progress_count }}</p></div>
                    <div class="rounded-2xl bg-[#fcf3e3] p-3"><p class="text-xs font-bold uppercase text-[#61677A]">Last Lesson</p><p class="mt-1 font-bold text-[#2F4F4F]" style="font-family: 'JetBrains Mono', monospace;">{{ $student->last_lesson_date ? \Carbon\Carbon::parse($student->last_lesson_date)->format('M d') : '—' }}</p></div>
                </div>

                <div class="mt-5 h-2 overflow-hidden rounded-full bg-[#D8D9DA]">
                    <div class="h-full rounded-full bg-[#2F4F4F]" style="width: {{ $percent }}%"></div>
                </div>

                <div class="mt-5 grid grid-cols-1 gap-2 sm:grid-cols-3">
                    <a href="{{ route('instructor.students.show', $student->student_id) }}" class="rounded-2xl bg-[#2F4F4F] px-3 py-2 text-center text-xs font-bold text-white hover:bg-[#B4833D]">Details</a>
                    <a href="{{ route('instructor.attendance.edit', $student->student_id) }}" class="rounded-2xl border border-[#959D90] px-3 py-2 text-center text-xs font-bold text-[#2F4F4F] hover:bg-[#FFF6E0]">Attendance</a>
                    <a href="{{ route('instructor.progress.create', ['student_id' => $student->student_id]) }}" class="rounded-2xl border border-[#959D90] px-3 py-2 text-center text-xs font-bold text-[#2F4F4F] hover:bg-[#FFF6E0]">Progress</a>
                </div>
            </article>
        @empty
            <div class="rounded-[28px] border border-dashed border-[#959D90] bg-white p-10 text-center md:col-span-2 xl:col-span-3">
                <h2 class="text-2xl font-bold text-[#2F4F4F]" style="font-family: 'Sora', sans-serif;">No assigned students found</h2>
                <p class="mt-2 text-sm text-[#61677A]">Students appear here when their enrollment is assigned to your instructor ID.</p>
            </div>
        @endforelse
    </section>

    @if($students->hasPages())
        <div>{{ $students->links() }}</div>
    @endif
</div>
@endsection