{{-- resources/views/instructor/attendance/index.blade.php --}}
@extends('layouts.instructor')

@section('content')
<div class="space-y-6">
    <header class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.22em] text-[#B4833D]">Attendance</p>
            <h1 class="mt-2 text-3xl font-extrabold text-[#2F4F4F]" style="font-family: 'Sora', sans-serif;">Lesson Attendance</h1>
            <p class="mt-2 max-w-2xl text-sm text-[#61677A]">Attendance is connected through schedule records, so it still works even if older attendance rows have missing instructor_id.</p>
        </div>

        <form method="GET" action="{{ route('instructor.attendance.index') }}" class="flex gap-2">
            <input name="q" value="{{ $q ?? '' }}" placeholder="Search attendance..." class="w-full rounded-2xl border border-[#D8D9DA] bg-white px-4 py-2 text-sm focus:border-[#959D90] focus:ring-[#959D90] sm:w-72">
            <button class="rounded-2xl bg-[#2F4F4F] px-4 py-2 text-sm font-bold text-white hover:bg-[#B4833D]">Search</button>
        </form>
    </header>

    <section class="overflow-hidden rounded-[28px] border border-[#D8D9DA] bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-[#D8D9DA] text-sm">
                <thead class="bg-[#272829] text-[#D8D9DA]">
                    <tr>
                        <th class="px-5 py-4 text-left font-bold">Date</th>
                        <th class="px-5 py-4 text-left font-bold">Student</th>
                        <th class="px-5 py-4 text-left font-bold">Instrument</th>
                        <th class="px-5 py-4 text-left font-bold">Time</th>
                        <th class="px-5 py-4 text-left font-bold">Status</th>
                        <th class="px-5 py-4 text-right font-bold">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#D8D9DA]">
                    @forelse($attendance as $row)
                        @php
                            $badge = match($row->attendance_status) {
                                'present' => 'bg-[#2F4F4F] text-white',
                                'late' => 'bg-[#B4833D] text-white',
                                'absent' => 'bg-[#523D35] text-white',
                                default => 'bg-[#FFF6E0] text-[#523D35]',
                            };
                        @endphp
                        <tr class="hover:bg-[#FFF6E0]">
                            <td class="px-5 py-4 font-bold text-[#2F4F4F]" style="font-family: 'JetBrains Mono', monospace;">{{ \Carbon\Carbon::parse($row->attendance_date)->format('M d, Y') }}</td>
                            <td class="px-5 py-4 font-bold text-[#272829]">{{ $row->student_name }}</td>
                            <td class="px-5 py-4 text-[#61677A]">{{ $row->instrument_name ?? '—' }}</td>
                            <td class="px-5 py-4 text-[#2F4F4F]" style="font-family: 'JetBrains Mono', monospace;">{{ \Carbon\Carbon::parse($row->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($row->end_time)->format('h:i A') }}</td>
                            <td class="px-5 py-4"><span class="rounded-full px-3 py-1 text-xs font-bold {{ $badge }}">{{ ucfirst($row->attendance_status) }}</span></td>
                            <td class="px-5 py-4 text-right"><a href="{{ route('instructor.attendance.edit', $row->student_id) }}" class="rounded-2xl bg-[#2F4F4F] px-4 py-2 text-xs font-bold text-white hover:bg-[#B4833D]">Manage</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-10 text-center text-[#61677A]">No attendance records found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    @if($attendance->hasPages())
        <div>{{ $attendance->links() }}</div>
    @endif
</div>
@endsection