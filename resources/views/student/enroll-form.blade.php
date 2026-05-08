{{-- resources/views/student/enroll-form.blade.php --}}
{{-- Purpose: Process new student enrollment --}}
{{-- Allows student to select instrument, genre, instructor, preferred days/time, and confirm package enrollment --}}

@extends('layouts.student')

@section('content')
<div class="min-h-full bg-[#F5F7F4] px-4 py-8 sm:px-6 lg:px-8" style="font-family: 'Inter', sans-serif;">
    <div class="mx-auto max-w-5xl">
        {{-- Header --}}
        <div class="mb-6">
            <p class="text-xs font-bold uppercase tracking-[0.25em] text-[#768A96]">Student Enrollment</p>
            <h1 class="mt-2 text-2xl font-bold text-[#223030] sm:text-3xl" style="font-family: 'Sora', sans-serif;">Enroll in package</h1>
            <p class="mt-2 text-sm text-[#44576D]">Complete your lesson package details. Your selected days and time are preferences for schedule confirmation.</p>
        </div>

        {{-- Validation / Session Messages --}}
        @if(session('error'))
            <div class="mb-6 rounded-2xl border border-[#C56B5F]/40 bg-[#F6EFEC] px-4 py-3 text-sm font-semibold text-[#523D35]">
                {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 rounded-2xl border border-[#C56B5F]/40 bg-[#F6EFEC] px-4 py-3 text-sm text-[#523D35]">
                <p class="font-bold">Please review the form.</p>
                <ul class="mt-2 list-disc space-y-1 pl-5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Selected Package Summary --}}
        <div class="mb-6 overflow-hidden rounded-[26px] border border-[#D8DDD8] bg-white shadow-sm">
            <div class="grid gap-4 p-5 sm:grid-cols-3 sm:items-center">
                <div class="sm:col-span-2">
                    <p class="text-xs font-bold uppercase tracking-wide text-[#768A96]">Selected package</p>
                    <h2 class="mt-1 text-xl font-bold text-[#223030]" style="font-family: 'Sora', sans-serif;">
                        {{ $package->session_name ?? $package->session_count . '-Session Package' }}
                    </h2>
                    <p class="mt-1 text-sm text-[#44576D]">
                        {{ $package->session_count }} sessions • {{ $package->duration_minutes }} minutes each
                    </p>
                </div>
                <div class="rounded-2xl bg-[#F5F7F4] p-4 text-left sm:text-right">
                    <p class="text-xs font-bold uppercase tracking-wide text-[#768A96]">Total price</p>
                    <p class="mt-1 text-2xl font-extrabold text-[#29353C]" style="font-family: 'JetBrains Mono', monospace;">₱{{ number_format($package->price, 2) }}</p>
                </div>
            </div>
        </div>

        {{-- Enrollment Form --}}
        <form action="{{ route('student.enroll.process') }}" method="POST" class="overflow-hidden rounded-[26px] border border-[#D8DDD8] bg-white shadow-sm">
            @csrf
            <input type="hidden" name="session_id" value="{{ $package->session_id }}">

            <div class="border-b border-[#D8DDD8] bg-[#FCFCFA] px-5 py-4">
                <h2 class="text-lg font-bold text-[#223030]" style="font-family: 'Sora', sans-serif;">Enrollment details</h2>
                <p class="mt-1 text-sm text-[#768A96]">Choose your instrument, qualified instructor, and preferred schedule.</p>
            </div>

            <div class="space-y-6 p-5 sm:p-6">
                {{-- Instrument and Genre --}}
                <div class="grid gap-5 md:grid-cols-2">
                    <div>
                        <label for="instrument_id" class="mb-2 block text-sm font-bold text-[#223030]">Primary instrument</label>
                        <select name="instrument_id" id="instrument_id" required class="w-full rounded-2xl border border-[#D8DDD8] bg-white px-4 py-3 text-sm text-[#223030] focus:border-[#44576D] focus:ring-[#44576D]">
                            <option value="">Select an instrument</option>
                            @foreach($instruments as $instrument)
                                <option value="{{ $instrument->instrument_id }}" {{ old('instrument_id') == $instrument->instrument_id ? 'selected' : '' }}>
                                    {{ $instrument->instrument_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="preferred_genre_id" class="mb-2 block text-sm font-bold text-[#223030]">Preferred genre</label>
                        <select name="preferred_genre_id" id="preferred_genre_id" class="w-full rounded-2xl border border-[#D8DDD8] bg-white px-4 py-3 text-sm text-[#223030] focus:border-[#44576D] focus:ring-[#44576D]">
                            <option value="">Select a genre (optional)</option>
                            @foreach($genres as $genre)
                                <option value="{{ $genre->genre_id }}" {{ old('preferred_genre_id', $student->preferred_genre_id ?? null) == $genre->genre_id ? 'selected' : '' }}>
                                    {{ $genre->genre_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Instructor --}}
                <div>
                    <label for="instructor_id" class="mb-2 block text-sm font-bold text-[#223030]">Preferred instructor</label>
                    <select name="instructor_id" id="instructor_id" required class="w-full rounded-2xl border border-[#D8DDD8] bg-white px-4 py-3 text-sm text-[#223030] focus:border-[#44576D] focus:ring-[#44576D]">
                        <option value="">Select an instrument first to load qualified instructors</option>
                    </select>
                    <p class="mt-2 text-xs text-[#768A96]">Only instructors with a matching specialization will be listed.</p>
                </div>

                {{-- Start Date and Time Slot --}}
                <div class="grid gap-5 md:grid-cols-2">
                    <div>
                        <label for="start_date" class="mb-2 block text-sm font-bold text-[#223030]">Start date</label>
                        <input type="date" name="start_date" id="start_date" required min="{{ date('Y-m-d') }}" value="{{ old('start_date') }}" class="w-full rounded-2xl border border-[#D8DDD8] bg-white px-4 py-3 text-sm text-[#223030] focus:border-[#44576D] focus:ring-[#44576D]">
                    </div>

                    <div>
                        <label for="preferred_lesson_time" class="mb-2 block text-sm font-bold text-[#223030]">Preferred time slot</label>
                        <select name="preferred_lesson_time" id="preferred_lesson_time" required class="w-full rounded-2xl border border-[#D8DDD8] bg-white px-4 py-3 text-sm text-[#223030] focus:border-[#44576D] focus:ring-[#44576D]">
                            <option value="">Select preferred time</option>
                            @foreach($timeSlots as $timeSlot)
                                <option value="{{ $timeSlot }}" {{ old('preferred_lesson_time', $student->preferred_lesson_time ?? null) === $timeSlot ? 'selected' : '' }}>{{ $timeSlot }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Preferred Lesson Days --}}
                <div>
                    <div class="mb-3 flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <label class="block text-sm font-bold text-[#223030]">Preferred lesson days</label>
                            <p class="mt-1 text-xs text-[#768A96]">Monday to Saturday: 9:00 AM - 8:00 PM • Sunday: 10:00 AM - 6:00 PM</p>
                        </div>
                    </div>

                    @php
                        $oldDays = old('preferred_lesson_days', []);
                        if (empty($oldDays) && !empty($student->preferred_lesson_days)) {
                            $oldDays = collect(explode(',', $student->preferred_lesson_days))->map(fn($day) => trim($day))->toArray();
                        }
                    @endphp

                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 lg:grid-cols-7">
                        @foreach($validDays as $day)
                            <label class="group flex cursor-pointer items-start gap-2 rounded-2xl border border-[#D8DDD8] bg-[#FCFCFA] p-3 transition hover:border-[#44576D] hover:bg-white">
                                <input type="checkbox" name="preferred_lesson_days[]" value="{{ $day }}" {{ in_array($day, $oldDays, true) ? 'checked' : '' }} class="mt-1 rounded border-[#959D90] text-[#29353C] focus:ring-[#29353C]">
                                <span>
                                    <span class="block text-sm font-bold text-[#223030]">{{ $day }}</span>
                                    <span class="block text-[11px] text-[#768A96]">{{ $day === 'Sunday' ? '10AM-6PM' : '9AM-8PM' }}</span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- Notes --}}
                <div>
                    <label for="notes" class="mb-2 block text-sm font-bold text-[#223030]">Notes or special request</label>
                    <textarea name="notes" id="notes" rows="4" class="w-full rounded-2xl border border-[#D8DDD8] bg-white px-4 py-3 text-sm text-[#223030] focus:border-[#44576D] focus:ring-[#44576D]" placeholder="Example: I prefer weekend lessons, beginner-friendly pacing, or a specific learning goal.">{{ old('notes') }}</textarea>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="flex flex-col gap-3 border-t border-[#D8DDD8] bg-[#FCFCFA] p-5 sm:flex-row sm:justify-end">
                <a href="{{ route('student.packages') }}" class="rounded-2xl border border-[#D8DDD8] bg-white px-5 py-3 text-center text-sm font-bold text-[#29353C] transition hover:bg-[#F5F7F4]">
                    Back to packages
                </a>
                <button type="submit" class="rounded-2xl bg-[#29353C] px-5 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-[#223030]">
                    Confirm enrollment
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('styles')
<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;600;700&family=Sora:wght@500;600;700;800&display=swap');
</style>
@endpush

@push('scripts')
@endpush
