@extends('layouts.instructor')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    {{-- Page Header --}}
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-semibold text-gray-900">
                My Schedule
            </h1>
            <p class="mt-1 text-sm text-gray-600">
                View and manage your upcoming and past lessons
            </p>
        </div>
            <a href="{{ route('instructor.schedule.create') }}"
            class="px-4 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-700 transition text-sm">
                Create Schedule
            </a>
    </div>


    {{-- Filters --}}
<div class="mb-8 rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
    <div class="flex flex-wrap gap-3">

        <a href="{{ route('instructor.schedule.index', ['filter' => 'today']) }}"
           class="px-4 py-2 rounded-lg text-sm font-medium
           {{ ($filter ?? 'upcoming') === 'today' ? 'bg-gray-800 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
            Today
        </a>

        <a href="{{ route('instructor.schedule.index', ['filter' => 'week']) }}"
           class="px-4 py-2 rounded-lg text-sm font-medium
           {{ ($filter ?? 'upcoming') === 'week' ? 'bg-gray-800 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
            This Week
        </a>

        <a href="{{ route('instructor.schedule.index', ['filter' => 'upcoming']) }}"
           class="px-4 py-2 rounded-lg text-sm font-medium
           {{ ($filter ?? 'upcoming') === 'upcoming' ? 'bg-gray-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
            Upcoming
        </a>

        <a href="{{ route('instructor.schedule.index', ['filter' => 'past']) }}"
           class="px-4 py-2 rounded-lg text-sm font-medium
           {{ ($filter ?? 'upcoming') === 'past' ? 'bg-gray-800 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
            Past
        </a>

    </div>
</div>


    {{-- Empty State --}}
    @if($schedules->isEmpty())
        <div class="rounded-xl border border-gray-200 bg-white p-12 text-center shadow-sm">
            <h3 class="text-lg font-medium text-gray-700">
                No lessons scheduled
            </h3>
            <p class="mt-2 text-sm text-gray-500">
                When students are assigned to you, your schedule will appear here.
            </p>
        </div>
    @else

        {{-- Grouped by Date --}}
        @foreach($schedules->groupBy('schedule_date') as $date => $dailySchedules)
            <div class="mb-12">

                {{-- Date Header --}}
                <div class="mb-4 flex items-center gap-3">
                    <h3 class="text-lg font-semibold text-gray-900">
                        {{ \Carbon\Carbon::parse($date)->format('l, F d, Y') }}
                    </h3>

                    @if($date === now()->toDateString())
                        <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-800">
                            Today
                        </span>
                    @elseif($date > now()->toDateString())
                        <span class="rounded-full bg-blue-100 px-3 py-1 text-xs font-medium text-blue-800">
                            Upcoming
                        </span>
                    @endif
                </div>

                {{-- Schedule Cards --}}
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($dailySchedules as $schedule)

                        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm hover:shadow-md transition">

                            {{-- Card Header --}}
                            <div class="border-b border-gray-100 p-5">
                                <div class="flex justify-between items-start gap-4">
                                    <div>
                                        <h4 class="text-lg font-semibold text-gray-900">
                                            {{ $schedule->student->first_name }} {{ $schedule->student->last_name }}
                                        </h4>
                                        <p class="mt-1 text-sm text-gray-600">
                                            {{ $schedule->start_time->format('h:i A') }}
                                            –
                                            {{ $schedule->end_time->format('h:i A') }}
                                        </p>
                                    </div>

                                    {{-- Status Badge --}}
                                    <span class="rounded-full px-3 py-1 text-xs font-medium
                                        {{ $schedule->status === 'scheduled' ? 'bg-blue-100 text-blue-800' : '' }}
                                        {{ $schedule->status === 'in_progress' ? 'bg-gray-100 text-gray-800' : '' }}
                                        {{ $schedule->status === 'completed' ? 'bg-green-100 text-green-800' : '' }}
                                        {{ $schedule->status === 'cancelled' ? 'bg-red-100 text-red-800' : '' }}
                                        {{ $schedule->status === 'no_show' ? 'bg-orange-100 text-orange-800' : '' }}">
                                        {{ ucfirst(str_replace('_', ' ', $schedule->status)) }}
                                    </span>
                                </div>
                            </div>

                            {{-- Card Body --}}
                            <div class="p-5 space-y-5">

                                {{-- Student Info --}}
                                <div class="flex items-center gap-4">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-200 text-sm font-medium text-gray-700">
                                        {{ strtoupper(substr($schedule->student->first_name, 0, 1)) }}
                                    </div>
                                    <div class="text-sm">
                                        <p class="font-medium text-gray-800">
                                            Remaining sessions:
                                            <span class="font-semibold">
                                                {{ $schedule->enrollment->remaining_sessions ?? '—' }}
                                            </span>
                                        </p>
                                        <p class="text-gray-600">
                                            Room: {{ $schedule->room_number ?? 'N/A' }}
                                        </p>
                                    </div>
                                </div>

                                {{-- Actions --}}
                                <div class="flex flex-wrap gap-3">
                                    <a href="{{ route('instructor.progress.create', $schedule->schedule_id) }}"
                                       class="flex-1 rounded-lg bg-gray-700 py-2.5 text-center text-sm font-medium text-white hover:bg-gray-600">
                                        Add Progress
                                    </a>

                                    <a href="{{ route('instructor.attendance.edit', $schedule->student->student_id) }}"
                                    class="flex-1 rounded-lg bg-gray-800 py-2.5 text-center text-sm font-medium text-white hover:bg-gray-700">
                                        Mark Attendance
                                    </a>
                                </div>

                            </div>
                        </div>

                    @endforeach
                </div>
            </div>
        @endforeach
    @endif
</div>
@endsection
