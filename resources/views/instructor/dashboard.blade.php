{{-- resources/views/instructor/dashboard.blade.php --}}
@extends('layouts.instructor')

@section('content')
@php
    $stats = $stats ?? [];

    $statCards = [
        ['label' => 'Students', 'value' => $stats['total_students'] ?? 0, 'hint' => 'Assigned through enrollments'],
        ['label' => 'Active Enrollments', 'value' => $stats['active_enrollments'] ?? 0, 'hint' => 'Currently active packages'],
        ['label' => 'Remaining Sessions', 'value' => $stats['remaining_sessions'] ?? 0, 'hint' => 'Total sessions left'],
        ['label' => 'Today', 'value' => $stats['today_classes'] ?? 0, 'hint' => 'Classes scheduled today'],
        ['label' => 'Upcoming', 'value' => $stats['upcoming_classes'] ?? 0, 'hint' => 'Today and future classes'],
        ['label' => 'Completed', 'value' => $stats['completed_classes'] ?? 0, 'hint' => 'Present or late attendance'],
        ['label' => 'Pending Attendance', 'value' => $stats['pending_attendance'] ?? 0, 'hint' => 'Past/today schedules without attendance'],
        ['label' => 'Avg Rating', 'value' => number_format((float) ($stats['average_rating'] ?? 0), 1), 'hint' => 'From progress records'],
    ];
@endphp

<div class="space-y-6">
    {{-- Header --}}
    <section class="overflow-hidden rounded-[28px] border border-[#D8D9DA] bg-[#272829] shadow-sm">
        <div class="grid gap-6 p-5 sm:p-6 lg:grid-cols-[1.5fr_1fr] lg:p-8">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.22em] text-[#B4833D]">Instructor Portal</p>
                <h1 class="mt-3 text-3xl font-extrabold tracking-tight text-[#FFF6E0] sm:text-4xl" style="font-family: 'Sora', sans-serif;">
                    Welcome back{{ $instructor ? ', ' . $instructor->first_name : '' }}
                </h1>
                <p class="mt-3 max-w-2xl text-sm leading-6 text-[#D8D9DA] sm:text-base">
                    Monitor today’s lessons, student progress, homework, attendance, and remaining sessions using real Music Lab records.
                </p>

                <div class="mt-5 flex flex-wrap gap-2">
                    <a href="{{ route('instructor.schedule.create') }}" class="rounded-2xl bg-[#2F4F4F] px-4 py-2.5 text-sm font-bold text-white transition hover:bg-[#B4833D]">
                        Create Schedule
                    </a>
                    <a href="{{ route('instructor.progress.create') }}" class="rounded-2xl bg-[#3C4B33] px-4 py-2.5 text-sm font-bold text-white transition hover:bg-[#B4833D]">
                        Add Progress
                    </a>
                    <a href="{{ route('instructor.attendance.index') }}" class="rounded-2xl border border-[#61677A] px-4 py-2.5 text-sm font-bold text-[#D8D9DA] transition hover:border-[#B4833D] hover:text-[#FFF6E0]">
                        Manage Attendance
                    </a>
                </div>
            </div>

            <div class="rounded-[24px] border border-[#61677A] bg-white/5 p-5">
                <p class="text-sm font-semibold text-[#D8D9DA]">Profile Summary</p>
                <div class="mt-4 space-y-3 text-sm text-[#D8D9DA]">
                    <div class="flex justify-between gap-3"><span>Name</span><strong class="text-right text-[#FFF6E0]">{{ $instructor ? trim($instructor->first_name . ' ' . $instructor->last_name) : 'Not found' }}</strong></div>
                    <div class="flex justify-between gap-3"><span>Status</span><strong class="text-right text-[#FFF6E0]">{{ ucfirst($instructor->employment_status ?? 'unknown') }}</strong></div>
                    <div class="flex justify-between gap-3"><span>Experience</span><strong class="text-right text-[#FFF6E0]">{{ $instructor->years_of_experience ?? 0 }} years</strong></div>
                    <div class="flex justify-between gap-3"><span>Availability</span><strong class="text-right text-[#FFF6E0]">{{ $instructor && $instructor->is_available ? 'Available' : 'Not set' }}</strong></div>
                </div>
            </div>
        </div>
    </section>

    {{-- Stats: compresses cleanly from 1 column to 4 columns --}}
    <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach($statCards as $card)
            <div class="rounded-[24px] border border-[#D8D9DA] bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                <p class="text-xs font-bold uppercase tracking-wide text-[#61677A]">{{ $card['label'] }}</p>
                <p class="mt-3 text-3xl font-extrabold text-[#2F4F4F]" style="font-family: 'JetBrains Mono', monospace;">{{ $card['value'] }}</p>
                <p class="mt-2 text-sm leading-5 text-[#523D35]">{{ $card['hint'] }}</p>
            </div>
        @endforeach
    </section>

    <section class="grid grid-cols-1 gap-5 xl:grid-cols-3">
        {{-- Today schedule --}}
        <div class="rounded-[28px] border border-[#D8D9DA] bg-white p-5 shadow-sm xl:col-span-2">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-xl font-bold text-[#2F4F4F]" style="font-family: 'Sora', sans-serif;">Today’s Classes</h2>
                    <p class="text-sm text-[#61677A]">Lessons scheduled for the current day.</p>
                </div>
                <a href="{{ route('instructor.schedule.index', ['filter' => 'today']) }}" class="rounded-2xl border border-[#959D90] px-4 py-2 text-sm font-bold text-[#2F4F4F] hover:bg-[#FFF6E0]">View Today</a>
            </div>

            <div class="mt-5 space-y-3">
                @forelse($todayClasses as $class)
                    <div class="rounded-2xl border border-[#D8D9DA] bg-[#fcf3e3] p-4">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p class="font-bold text-[#272829]" style="font-family: 'Sora', sans-serif;">{{ $class->student_name }}</p>
                                <p class="mt-1 text-sm text-[#61677A]">{{ $class->instrument_name ?? 'No instrument' }} • {{ $class->lesson_topic ?? 'No topic yet' }}</p>
                            </div>
                            <div class="text-left sm:text-right">
                                <p class="font-bold text-[#2F4F4F]" style="font-family: 'JetBrains Mono', monospace;">
                                    {{ \Carbon\Carbon::parse($class->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($class->end_time)->format('h:i A') }}
                                </p>
                                <p class="text-xs font-semibold uppercase tracking-wide text-[#523D35]">{{ $class->attendance_status ? ucfirst($class->attendance_status) : 'Attendance pending' }}</p>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="rounded-2xl border border-dashed border-[#959D90] bg-[#FFF6E0] p-6 text-center text-sm text-[#523D35]">
                        No classes scheduled today.
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Homework list --}}
        <div class="rounded-[28px] border border-[#D8D9DA] bg-white p-5 shadow-sm">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <h2 class="text-xl font-bold text-[#2F4F4F]" style="font-family: 'Sora', sans-serif;">Recent Homework</h2>
                    <p class="text-sm text-[#61677A]">Displayed from progress records, not a separate module.</p>
                </div>
                <a href="{{ route('instructor.progress.index') }}" class="text-sm font-bold text-[#B4833D] hover:text-[#523D35]">Open</a>
            </div>

            <div class="mt-5 space-y-3">
                @forelse($homeworkList as $item)
                    <a href="{{ route('instructor.progress.show', $item->progress_id) }}" class="block rounded-2xl border border-[#D8D9DA] bg-[#fcf3e3] p-4 transition hover:border-[#B4833D] hover:bg-[#FFF6E0]">
                        <p class="text-sm font-bold text-[#272829]" style="font-family: 'Sora', sans-serif;">{{ $item->student_name }}</p>
                        <p class="mt-1 line-clamp-2 text-sm text-[#523D35]">{{ $item->homework }}</p>
                        <p class="mt-2 text-xs font-semibold text-[#61677A]" style="font-family: 'JetBrains Mono', monospace;">{{ \Carbon\Carbon::parse($item->progress_date)->format('M d, Y') }}</p>
                    </a>
                @empty
                    <div class="rounded-2xl border border-dashed border-[#959D90] bg-[#FFF6E0] p-5 text-sm text-[#523D35]">
                        No homework has been recorded yet. Add it inside a progress record.
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <section class="grid grid-cols-1 gap-5 xl:grid-cols-2">
        {{-- Recent students --}}
        <div class="rounded-[28px] border border-[#D8D9DA] bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-xl font-bold text-[#2F4F4F]" style="font-family: 'Sora', sans-serif;">Student Monitoring</h2>
                    <p class="text-sm text-[#61677A]">Recently enrolled students under your account.</p>
                </div>
                <a href="{{ route('instructor.students.index') }}" class="text-sm font-bold text-[#B4833D] hover:text-[#523D35]">View All</a>
            </div>

            <div class="mt-5 space-y-3">
                @forelse($recentStudents as $student)
                    @php
                        $percent = $student->total_sessions > 0 ? round(($student->completed_sessions / $student->total_sessions) * 100) : 0;
                    @endphp
                    <a href="{{ route('instructor.students.show', $student->student_id) }}" class="block rounded-2xl border border-[#D8D9DA] p-4 transition hover:border-[#B4833D] hover:bg-[#FFF6E0]">
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p class="font-bold text-[#272829]" style="font-family: 'Sora', sans-serif;">{{ $student->student_name }}</p>
                                <p class="text-sm text-[#61677A]">{{ $student->instrument_name }} • {{ ucfirst($student->enrollment_status) }}</p>
                            </div>
                            <p class="text-sm font-bold text-[#2F4F4F]" style="font-family: 'JetBrains Mono', monospace;">{{ $student->remaining_sessions }} left</p>
                        </div>
                        <div class="mt-3 h-2 overflow-hidden rounded-full bg-[#D8D9DA]">
                            <div class="h-full rounded-full bg-[#2F4F4F]" style="width: {{ $percent }}%"></div>
                        </div>
                    </a>
                @empty
                    <p class="rounded-2xl border border-dashed border-[#959D90] bg-[#FFF6E0] p-5 text-sm text-[#523D35]">No assigned students yet.</p>
                @endforelse
            </div>
        </div>

        {{-- Recent progress --}}
        <div class="rounded-[28px] border border-[#D8D9DA] bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-xl font-bold text-[#2F4F4F]" style="font-family: 'Sora', sans-serif;">Recent Progress</h2>
                    <p class="text-sm text-[#61677A]">Latest lesson notes and ratings.</p>
                </div>
                <a href="{{ route('instructor.progress.create') }}" class="rounded-2xl bg-[#2F4F4F] px-4 py-2 text-sm font-bold text-white hover:bg-[#B4833D]">Add</a>
            </div>

            <div class="mt-5 space-y-3">
                @forelse($recentProgress as $record)
                    <a href="{{ route('instructor.progress.show', $record->progress_id) }}" class="block rounded-2xl border border-[#D8D9DA] p-4 transition hover:border-[#B4833D] hover:bg-[#FFF6E0]">
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p class="font-bold text-[#272829]" style="font-family: 'Sora', sans-serif;">{{ $record->student_name }}</p>
                                <p class="text-sm text-[#61677A]">{{ $record->lesson_topic ?? 'No lesson topic' }} • {{ $record->instrument_name ?? 'No instrument' }}</p>
                            </div>
                            <p class="text-sm font-bold text-[#2F4F4F]" style="font-family: 'JetBrains Mono', monospace;">{{ $record->performance_rating ?? '—' }}/10</p>
                        </div>
                    </a>
                @empty
                    <p class="rounded-2xl border border-dashed border-[#959D90] bg-[#FFF6E0] p-5 text-sm text-[#523D35]">No progress records yet.</p>
                @endforelse
            </div>
        </div>
    </section>
</div>
@endsection