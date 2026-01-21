@extends('layouts.instructor')

@section('content')
<div class="max-w-3xl mx-auto px-4 py-8">

    <h1 class="text-2xl font-bold text-gray-900 mb-6">Create Schedule</h1>

    <form method="POST" action="{{ route('instructor.schedule.store') }}"
          class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 space-y-5">
        @csrf

        {{-- Student --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Student</label>
            <select name="student_id" class="w-full rounded-lg border-gray-300" required>
                <option value="">Select student</option>
                @foreach($students as $student)
                    <option value="{{ $student->student_id }}">
                        {{ $student->first_name }} {{ $student->last_name }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Date --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Date</label>
            <input type="date" name="schedule_date" class="w-full rounded-lg border-gray-300" required>
        </div>

        {{-- Time --}}
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Start Time</label>
                <input type="time" name="start_time" class="w-full rounded-lg border-gray-300" required>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">End Time</label>
                <input type="time" name="end_time" class="w-full rounded-lg border-gray-300" required>
            </div>
        </div>

        {{-- Room --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Room</label>
            <input type="text" name="room_number" class="w-full rounded-lg border-gray-300">
        </div>

        {{-- Topic --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Lesson Topic</label>
            <input type="text" name="lesson_topic" class="w-full rounded-lg border-gray-300">
        </div>

        {{-- Notes --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
            <textarea name="notes" rows="3" class="w-full rounded-lg border-gray-300"></textarea>
        </div>

        {{-- Actions --}}
        <div class="flex justify-end gap-3">
            <a href="{{ route('instructor.schedule.index') }}"
               class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300">
                Cancel
            </a>

            <button type="submit"
                    class="px-5 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-700">
                Save Schedule
            </button>
        </div>
    </form>
</div>
@endsection
