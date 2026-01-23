@extends('layouts.instructor')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <!-- Header + Search -->
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">My Students</h1>
            <p class="mt-1 text-gray-600">Track progress and manage your assigned students</p>
        </div>

        {{-- IMPORTANT: action MUST point to students.index --}}
        <form method="GET"
              action="{{ route('instructor.students.index') }}"
              class="relative w-full sm:w-72">
            <input
                type="text"
                name="q"
                value="{{ $q ?? '' }}"
                placeholder="Search students..."
                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg
                       focus:outline-none focus:ring-2 focus:ring-gray-400"
            >
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
        </form>
    </div>

    <!-- Students Grid -->
    @if($students->isEmpty())
        <div class="bg-white rounded-xl border border-gray-100 p-12 text-center">
            <h3 class="text-xl font-medium text-gray-700">No students assigned yet</h3>
            <p class="mt-2 text-gray-500">
                Once students are enrolled under you, they will appear here.
            </p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($students as $student)
                @php
                    $enroll = $student->latestEnrollment;
                    $total = $enroll->total_sessions ?? 0;
                    $completed = $enroll->completed_sessions ?? 0;
                    $remaining = $enroll->remaining_sessions ?? 0;
                    $percentage = $total > 0 ? round(($completed / $total) * 100) : 0;
                @endphp

                <div class="bg-white rounded-xl border border-gray-100 shadow-sm hover:shadow-md transition">
                    <div class="p-6">

                        <!-- Student Header -->
                        <div class="flex items-center justify-between mb-5">
                            <div class="flex items-center gap-4">
                                <div class="w-14 h-14 rounded-full bg-gray-800
                                            flex items-center justify-center
                                            text-white font-bold text-xl">
                                    {{ strtoupper(substr($student->first_name, 0, 1)) }}
                                </div>

                                <div>
                                    <h3 class="font-semibold text-gray-900 text-lg">
                                        {{ $student->first_name }} {{ $student->last_name }}
                                    </h3>
                                    <p class="text-sm text-gray-600">
                                        {{ $student->instrument->instrument_name ?? 'No instrument' }}
                                        • {{ ucfirst($student->skill_level ?? 'Beginner') }}
                                    </p>
                                </div>
                            </div>

                            <span class="px-3 py-1 bg-gray-200 text-gray-800 text-xs font-medium rounded-full">
                                Active
                            </span>
                        </div>

                        <!-- Progress -->
                        <div class="space-y-4">
                            <div>
                                <div class="flex justify-between text-sm mb-1">
                                    <span class="text-gray-600">Sessions</span>
                                    <span class="font-medium">
                                        {{ $completed }} / {{ $total ?: '?' }}
                                    </span>
                                </div>

                                <div class="w-full bg-gray-200 rounded-full h-2.5">
                                    <div class="bg-gray-800 h-2.5 rounded-full transition-all"
                                         style="width: {{ $percentage }}%">
                                    </div>
                                </div>
                            </div>

                            <!-- Quick Info -->
                            <div class="grid grid-cols-2 gap-4 text-sm">
                                <div>
                                    <p class="text-gray-500">Remaining</p>
                                    <p class="font-medium text-gray-900">
                                        {{ $remaining }} sessions
                                    </p>
                                </div>
                                <div>
                                    <p class="text-gray-500">Last Lesson</p>
                                    <p class="font-medium text-gray-900">
                                        {{ $student->last_lesson_date ?? 'N/A' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex gap-3">
                        {{-- THIS is the only correct link --}}
                        <a href="{{ route('instructor.students.show', ['student' => $student->student_id]) }}"
                           class="flex-1 text-center py-2 bg-gray-800 text-white rounded-lg
                                  hover:bg-gray-700 transition text-sm">
                            View Details
                        </a>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-10">
            {{ $students->links() }}
        </div>
    @endif

</div>
@endsection
