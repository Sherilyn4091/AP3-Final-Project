{{-- resources/views/instructor/schedule/edit.blade.php --}}
@extends('layouts.instructor')

@section('content')
<div class="mx-auto max-w-4xl space-y-6">
    <header class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.22em] text-[#B4833D]">Schedule</p>
            <h1 class="mt-2 text-3xl font-extrabold text-[#2F4F4F]" style="font-family: 'Sora', sans-serif;">Edit Schedule</h1>
            <p class="mt-2 text-sm text-[#61677A]">{{ $schedule->student_name }} • {{ $schedule->instrument_name ?? 'No instrument' }}</p>
        </div>
        <a href="{{ route('instructor.schedule.index') }}" class="rounded-2xl border border-[#959D90] bg-white px-4 py-2 text-sm font-bold text-[#2F4F4F] hover:bg-[#FFF6E0]">Back</a>
    </header>

    <form method="POST" action="{{ route('instructor.schedule.update', $schedule->schedule_id) }}" class="rounded-[28px] border border-[#D8D9DA] bg-white p-5 shadow-sm sm:p-6">
        @csrf
        @method('PUT')

        @if($errors->any())
            <div class="mb-5 rounded-2xl border border-[#B4833D] bg-[#FFF6E0] p-4 text-sm text-[#523D35]">
                <strong>Please check the form.</strong>
                <ul class="mt-2 list-disc pl-5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <label class="mb-1 block text-sm font-bold text-[#2F4F4F]">Date</label>
                <input type="date" name="schedule_date" value="{{ old('schedule_date', \Carbon\Carbon::parse($schedule->schedule_date)->format('Y-m-d')) }}" required class="w-full rounded-2xl border border-[#D8D9DA] px-4 py-3 text-sm focus:border-[#959D90] focus:ring-[#959D90]">
            </div>

            <div>
                <label class="mb-1 block text-sm font-bold text-[#2F4F4F]">Status</label>
                <select name="status" required class="w-full rounded-2xl border border-[#D8D9DA] px-4 py-3 text-sm focus:border-[#959D90] focus:ring-[#959D90]">
                    @foreach(['scheduled', 'in_progress', 'completed', 'cancelled', 'no_class', 'rescheduled'] as $status)
                        <option value="{{ $status }}" @selected(old('status', $schedule->status) === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1 block text-sm font-bold text-[#2F4F4F]">Start Time</label>
                <input type="time" name="start_time" value="{{ old('start_time', \Carbon\Carbon::parse($schedule->start_time)->format('H:i')) }}" required class="w-full rounded-2xl border border-[#D8D9DA] px-4 py-3 text-sm focus:border-[#959D90] focus:ring-[#959D90]" style="font-family: 'JetBrains Mono', monospace;">
            </div>

            <div>
                <label class="mb-1 block text-sm font-bold text-[#2F4F4F]">End Time</label>
                <input type="time" name="end_time" value="{{ old('end_time', \Carbon\Carbon::parse($schedule->end_time)->format('H:i')) }}" required class="w-full rounded-2xl border border-[#D8D9DA] px-4 py-3 text-sm focus:border-[#959D90] focus:ring-[#959D90]" style="font-family: 'JetBrains Mono', monospace;">
            </div>

            <div class="sm:col-span-2">
                <label class="mb-1 block text-sm font-bold text-[#2F4F4F]">Room</label>
                <select name="room_number" class="w-full rounded-2xl border border-[#D8D9DA] px-4 py-3 text-sm focus:border-[#959D90] focus:ring-[#959D90]">
                    <option value="">No room yet</option>
                    @foreach($rooms as $room)
                        <option value="{{ $room->room_number }}" @selected(old('room_number', $schedule->room_number) == $room->room_number)>
                            {{ $room->room_number }}{{ $room->room_name ? ' - ' . $room->room_name : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-2">
                <label class="mb-1 block text-sm font-bold text-[#2F4F4F]">Lesson Topic</label>
                <input type="text" name="lesson_topic" value="{{ old('lesson_topic', $schedule->lesson_topic) }}" class="w-full rounded-2xl border border-[#D8D9DA] px-4 py-3 text-sm focus:border-[#959D90] focus:ring-[#959D90]">
            </div>

            <div class="sm:col-span-2">
                <label class="mb-1 block text-sm font-bold text-[#2F4F4F]">Notes</label>
                <textarea name="notes" rows="4" class="w-full rounded-2xl border border-[#D8D9DA] px-4 py-3 text-sm focus:border-[#959D90] focus:ring-[#959D90]">{{ old('notes', $schedule->notes) }}</textarea>
            </div>
        </div>

        <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:justify-end">
            <a href="{{ route('instructor.schedule.index') }}" class="rounded-2xl border border-[#959D90] px-5 py-3 text-center text-sm font-bold text-[#2F4F4F] hover:bg-[#FFF6E0]">Cancel</a>
            <button type="submit" class="rounded-2xl bg-[#2F4F4F] px-5 py-3 text-sm font-bold text-white hover:bg-[#B4833D]">Update Schedule</button>
        </div>
    </form>
</div>
@endsection