@extends('layouts.student')

{{-- resources/views/student/profile.blade.php --}}

@section('content')
@php
    /*
    |--------------------------------------------------------------------------
    | Profile View Helpers
    |--------------------------------------------------------------------------
    |
    | These variables keep repeated Tailwind classes and lesson preference
    | options in one place. This helps reduce duplicated code and makes the
    | page easier to update later.
    |
    */

    $fieldClasses = 'w-full rounded-lg bg-white border border-gray-300 text-gray-900 px-3 py-2 text-sm focus:border-[#959D90] focus:ring-1 focus:ring-[#959D90]';
    $disabledFieldClasses = 'w-full rounded-lg bg-gray-100 border border-gray-300 text-gray-600 px-3 py-2 text-sm';

    $validDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

    $timeSlots = [
        'Morning (9:00 AM - 12:00 PM)',
        'Afternoon (12:00 PM - 3:00 PM)',
        'Late Afternoon (3:00 PM - 6:00 PM)',
        'Evening (6:00 PM - 8:00 PM)',
        'Sunday Window (10:00 AM - 6:00 PM)',
    ];

    /*
    |--------------------------------------------------------------------------
    | Selected Lesson Days
    |--------------------------------------------------------------------------
    |
    | Your current database column stores preferred_lesson_days as text.
    | To keep your existing ProfileController safe, the checkboxes update
    | one hidden text input instead of submitting an array.
    |
    */

    $selectedLessonDays = old('preferred_lesson_days', $student->preferred_lesson_days ?? '');

    if (is_string($selectedLessonDays)) {
        $selectedLessonDays = array_values(array_filter(array_map('trim', explode(',', $selectedLessonDays))));
    }

    if (!is_array($selectedLessonDays)) {
        $selectedLessonDays = [];
    }
@endphp

<div class="profile-page max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-6">

    {{-- Header --}}
    <div class="mb-6">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">My profile</h1>
        <p class="mt-1 text-sm text-gray-600">Update your personal information, lesson preferences, and password</p>
    </div>

    {{-- Closeable Success Message --}}
    @if(session('success'))
        <div data-profile-alert class="mb-6 flex items-start justify-between gap-3 rounded-2xl border border-[#A7DDB5] bg-[#EAF8EE] px-4 py-3 text-[#23613B] shadow-sm">
            <div class="flex items-center gap-3">
                <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <span class="text-sm font-bold">{{ session('success') }}</span>
            </div>

            <button type="button" data-close-profile-alert class="rounded-lg p-1 opacity-70 transition hover:bg-white/60 hover:opacity-100" aria-label="Close notification">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    @endif

    {{-- Closeable Error Message --}}
    @if(session('error'))
        <div data-profile-alert class="mb-6 flex items-start justify-between gap-3 rounded-2xl border border-[#C56B5F]/40 bg-[#F6EFEC] px-4 py-3 text-[#523D35] shadow-sm">
            <div class="flex items-center gap-3">
                <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                <span class="text-sm font-bold">{{ session('error') }}</span>
            </div>

            <button type="button" data-close-profile-alert class="rounded-lg p-1 opacity-70 transition hover:bg-white/60 hover:opacity-100" aria-label="Close notification">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Personal Information Form (2 columns) --}}
        <div class="lg:col-span-2">
            <div class="bg-white border border-gray-300 rounded-xl shadow overflow-hidden">
                <div class="px-4 sm:px-6 py-4 bg-gray-100 border-b border-gray-300">
                    <h2 class="text-lg font-bold text-gray-900">Personal information</h2>
                </div>

                <form method="POST" action="{{ route('student.profile.update') }}" class="p-4 sm:p-6 space-y-5" id="profileForm">
                    @csrf
                    @method('PATCH')

                    {{-- Name Fields --}}
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">First name</label>
                            <input type="text" name="first_name" value="{{ old('first_name', $student->first_name) }}" required class="{{ $fieldClasses }}">
                            @error('first_name')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">Middle name</label>
                            <input type="text" name="middle_name" value="{{ old('middle_name', $student->middle_name) }}" class="{{ $fieldClasses }}" placeholder="Optional">
                            @error('middle_name')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">Last name</label>
                            <input type="text" name="last_name" value="{{ old('last_name', $student->last_name) }}" required class="{{ $fieldClasses }}">
                            @error('last_name')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Suffix & Gender --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">Suffix</label>
                            <input type="text" name="suffix" value="{{ old('suffix', $student->suffix) }}" class="{{ $fieldClasses }}" placeholder="Jr., Sr., III (optional)">
                            @error('suffix')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">Gender</label>
                            <select name="gender" class="{{ $fieldClasses }}">
                                <option value="">Select gender</option>
                                <option value="Male" {{ old('gender', $student->gender) == 'Male' ? 'selected' : '' }}>Male</option>
                                <option value="Female" {{ old('gender', $student->gender) == 'Female' ? 'selected' : '' }}>Female</option>
                                <option value="Other" {{ old('gender', $student->gender) == 'Other' ? 'selected' : '' }}>Other</option>
                                <option value="Prefer not to say" {{ old('gender', $student->gender) == 'Prefer not to say' ? 'selected' : '' }}>Prefer not to say</option>
                            </select>
                            @error('gender')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Contact Information --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">Phone number</label>
                            <input type="text" name="phone" value="{{ old('phone', $student->phone) }}" maxlength="11" placeholder="09123456789" class="{{ $fieldClasses }}">
                            @error('phone')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">Email (read-only)</label>
                            <input type="email" value="{{ $student->email }}" disabled class="{{ $disabledFieldClasses }}">
                        </div>
                    </div>

                    {{-- Date of Birth & Nationality --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">Date of birth</label>
                            <input type="date" name="date_of_birth" value="{{ old('date_of_birth', $student->date_of_birth ? \Carbon\Carbon::parse($student->date_of_birth)->format('Y-m-d') : '') }}" class="{{ $fieldClasses }}">
                            @error('date_of_birth')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">Nationality</label>
                            <input type="text" name="nationality" value="{{ old('nationality', $student->nationality) }}" class="{{ $fieldClasses }}" placeholder="Filipino">
                            @error('nationality')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Address --}}
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">Address line 1</label>
                            <input type="text" name="address_line1" value="{{ old('address_line1', $student->address_line1) }}" class="{{ $fieldClasses }}" placeholder="Street, Barangay">
                            @error('address_line1')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">Address line 2</label>
                            <input type="text" name="address_line2" value="{{ old('address_line2', $student->address_line2) }}" class="{{ $fieldClasses }}" placeholder="Building, Unit (optional)">
                            @error('address_line2')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- City, Province, Postal --}}
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">City</label>
                            <input type="text" name="city" value="{{ old('city', $student->city) }}" class="{{ $fieldClasses }}">
                            @error('city')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">Province</label>
                            <input type="text" name="province" value="{{ old('province', $student->province) }}" class="{{ $fieldClasses }}">
                            @error('province')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">Postal code</label>
                            <input type="text" name="postal_code" value="{{ old('postal_code', $student->postal_code) }}" class="{{ $fieldClasses }}" placeholder="6000">
                            @error('postal_code')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Country --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1.5">Country</label>
                        <input type="text" name="country" value="{{ old('country', $student->country ?? 'Philippines') }}" class="{{ $fieldClasses }}">
                        @error('country')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Emergency Contact Section --}}
                    <div class="border-t border-gray-200 pt-5">
                        <h3 class="text-sm font-bold text-gray-900 mb-4">Emergency contact</h3>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">Contact name</label>
                                <input type="text" name="emergency_contact_name" value="{{ old('emergency_contact_name', $student->emergency_contact_name) }}" class="{{ $fieldClasses }}">
                                @error('emergency_contact_name')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Relationship</label>
                                    <input type="text" name="emergency_contact_relationship" value="{{ old('emergency_contact_relationship', $student->emergency_contact_relationship) }}" class="{{ $fieldClasses }}" placeholder="Mother, Father, etc.">
                                    @error('emergency_contact_relationship')
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Phone</label>
                                    <input type="text" name="emergency_contact_phone" value="{{ old('emergency_contact_phone', $student->emergency_contact_phone) }}" maxlength="11" placeholder="09123456789" class="{{ $fieldClasses }}">
                                    @error('emergency_contact_phone')
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Parent/Guardian Section --}}
                    <div class="border-t border-gray-200 pt-5">
                        <h3 class="text-sm font-bold text-gray-900 mb-4">Parent/Guardian information</h3>

                        <div class="space-y-4">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Name</label>
                                    <input type="text" name="parent_guardian_name" value="{{ old('parent_guardian_name', $student->parent_guardian_name) }}" class="{{ $fieldClasses }}">
                                    @error('parent_guardian_name')
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Relationship</label>
                                    <input type="text" name="parent_guardian_relationship" value="{{ old('parent_guardian_relationship', $student->parent_guardian_relationship) }}" class="{{ $fieldClasses }}">
                                    @error('parent_guardian_relationship')
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Phone</label>
                                    <input type="text" name="parent_guardian_phone" value="{{ old('parent_guardian_phone', $student->parent_guardian_phone) }}" maxlength="11" placeholder="09123456789" class="{{ $fieldClasses }}">
                                    @error('parent_guardian_phone')
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Email</label>
                                    <input type="email" name="parent_guardian_email" value="{{ old('parent_guardian_email', $student->parent_guardian_email) }}" class="{{ $fieldClasses }}">
                                    @error('parent_guardian_email')
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">Address</label>
                                <textarea name="parent_guardian_address" rows="2" class="{{ $fieldClasses }}">{{ old('parent_guardian_address', $student->parent_guardian_address) }}</textarea>
                                @error('parent_guardian_address')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Musical Background Section --}}
                    <div class="border-t border-gray-200 pt-5">
                        <h3 class="text-sm font-bold text-gray-900 mb-4">Musical background</h3>

                        <div class="space-y-4">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Primary instrument</label>
                                    <select name="instrument_id" class="{{ $fieldClasses }}">
                                        <option value="">Select instrument</option>
                                        @foreach($instruments as $instrument)
                                            <option value="{{ $instrument->instrument_id }}" {{ old('instrument_id', $student->instrument_id) == $instrument->instrument_id ? 'selected' : '' }}>
                                                {{ $instrument->instrument_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('instrument_id')
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Skill level</label>
                                    <select name="skill_level" class="{{ $fieldClasses }}">
                                        <option value="">Select level</option>
                                        <option value="beginner" {{ old('skill_level', $student->skill_level) == 'beginner' ? 'selected' : '' }}>Beginner</option>
                                        <option value="intermediate" {{ old('skill_level', $student->skill_level) == 'intermediate' ? 'selected' : '' }}>Intermediate</option>
                                        <option value="advanced" {{ old('skill_level', $student->skill_level) == 'advanced' ? 'selected' : '' }}>Advanced</option>
                                        <option value="expert" {{ old('skill_level', $student->skill_level) == 'expert' ? 'selected' : '' }}>Expert</option>
                                    </select>
                                    @error('skill_level')
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">Secondary instruments</label>
                                <input type="text" name="secondary_instruments" value="{{ old('secondary_instruments', $student->secondary_instruments) }}" class="{{ $fieldClasses }}" placeholder="Piano, Drums, etc.">
                                @error('secondary_instruments')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">Previous music experience</label>
                                <textarea name="previous_music_experience" rows="2" class="{{ $fieldClasses }}" placeholder="Describe your prior training or experience">{{ old('previous_music_experience', $student->previous_music_experience) }}</textarea>
                                @error('previous_music_experience')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">Music goals</label>
                                <textarea name="music_goals" rows="2" class="{{ $fieldClasses }}" placeholder="What do you hope to achieve?">{{ old('music_goals', $student->music_goals) }}</textarea>
                                @error('music_goals')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">Preferred genre</label>
                                <select name="preferred_genre_id" class="{{ $fieldClasses }}">
                                    <option value="">Select genre</option>
                                    @foreach($genres as $genre)
                                        <option value="{{ $genre->genre_id }}" {{ old('preferred_genre_id', $student->preferred_genre_id) == $genre->genre_id ? 'selected' : '' }}>
                                            {{ $genre->genre_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('preferred_genre_id')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Educational Background --}}
                    <div class="border-t border-gray-200 pt-5">
                        <h3 class="text-sm font-bold text-gray-900 mb-4">Educational background</h3>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">School name</label>
                                <input type="text" name="school_name" value="{{ old('school_name', $student->school_name) }}" class="{{ $fieldClasses }}">
                                @error('school_name')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">Grade level</label>
                                <input type="text" name="grade_level" value="{{ old('grade_level', $student->grade_level) }}" class="{{ $fieldClasses }}" placeholder="Grade 10, College 2nd Year, etc.">
                                @error('grade_level')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Medical Information --}}
                    <div class="border-t border-gray-200 pt-5">
                        <h3 class="text-sm font-bold text-gray-900 mb-4">Medical information</h3>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">Medical conditions</label>
                                <textarea name="medical_conditions" rows="2" class="{{ $fieldClasses }}" placeholder="Any medical conditions we should know about">{{ old('medical_conditions', $student->medical_conditions) }}</textarea>
                                @error('medical_conditions')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">Allergies</label>
                                <textarea name="allergies" rows="2" class="{{ $fieldClasses }}" placeholder="Any allergies">{{ old('allergies', $student->allergies) }}</textarea>
                                @error('allergies')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">Special needs</label>
                                <textarea name="special_needs" rows="2" class="{{ $fieldClasses }}" placeholder="Any special accommodations needed">{{ old('special_needs', $student->special_needs) }}</textarea>
                                @error('special_needs')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Lesson Preferences --}}
                    <div class="border-t border-gray-200 pt-5">
                        <h3 class="text-sm font-bold text-gray-900 mb-4">Lesson preferences</h3>

                        {{-- Hidden input keeps your existing ProfileController validation safe because it submits text, not an array. --}}
                        <input type="hidden" name="preferred_lesson_days" id="preferred_lesson_days" value="{{ implode(', ', $selectedLessonDays) }}">

                        <div class="space-y-5">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-2">Preferred lesson days</label>
                                <p class="mb-3 text-xs text-gray-500">
                                    Monday to Saturday: 9:00 AM - 8:00 PM • Sunday: 10:00 AM - 6:00 PM
                                </p>

                                <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 lg:grid-cols-7">
                                    @foreach($validDays as $day)
                                        <label class="flex cursor-pointer items-start gap-2 rounded-xl border border-gray-300 bg-gray-50 p-3 transition hover:bg-white hover:shadow-sm">
                                            <input type="checkbox"
                                                   value="{{ $day }}"
                                                   data-lesson-day
                                                   {{ in_array($day, $selectedLessonDays, true) ? 'checked' : '' }}
                                                   class="mt-1 rounded border-gray-400 text-[#223030] focus:ring-[#959D90]">

                                            <span>
                                                <span class="block text-xs font-bold text-gray-900">{{ $day }}</span>
                                                <span class="block text-[11px] text-gray-500">
                                                    {{ $day === 'Sunday' ? '10AM-6PM' : '9AM-8PM' }}
                                                </span>
                                            </span>
                                        </label>
                                    @endforeach
                                </div>

                                @error('preferred_lesson_days')
                                    <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">Preferred time slots</label>
                                <select name="preferred_lesson_time" class="{{ $fieldClasses }}">
                                    <option value="">Select preferred time</option>
                                    @foreach($timeSlots as $slot)
                                        <option value="{{ $slot }}" {{ old('preferred_lesson_time', $student->preferred_lesson_time) === $slot ? 'selected' : '' }}>
                                            {{ $slot }}
                                        </option>
                                    @endforeach
                                </select>

                                @error('preferred_lesson_time')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Enrollment Date (Read-only) --}}
                    <div class="border-t border-gray-200 pt-5">
                        <label class="block text-xs font-semibold text-gray-700 mb-1.5">Enrollment date (read-only)</label>
                        <input type="text" value="{{ $student->enrollment_date ? \Carbon\Carbon::parse($student->enrollment_date)->format('F d, Y') : '—' }}" disabled class="{{ $disabledFieldClasses }}">
                    </div>

                    {{-- Submit Button --}}
                    <div class="flex justify-end pt-4 border-t border-gray-200">
                        <button type="submit" class="px-6 py-2.5 bg-[#223030] text-white rounded-lg hover:bg-[#523D35] transition font-medium text-sm flex items-center gap-2 shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Save changes
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Password Change Section (1 column) --}}
        <div class="lg:col-span-1">
            <div class="bg-white border border-gray-300 rounded-xl shadow overflow-hidden">
                <div class="px-4 sm:px-6 py-4 bg-gray-100 border-b border-gray-300">
                    <h2 class="text-lg font-bold text-gray-900">Change password</h2>
                </div>

                <form method="POST" action="{{ route('student.password.change') }}" class="p-4 sm:p-6 space-y-4">
                    @csrf

                    {{-- Current Password --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1.5">Current password</label>
                        <input type="password" name="current_password" required class="{{ $fieldClasses }}" placeholder="Enter current password">
                        @error('current_password')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- New Password --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1.5">New password</label>
                        <input type="password" name="password" required class="{{ $fieldClasses }}" placeholder="Enter new password">
                        @error('password')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">Must be at least 8 characters</p>
                    </div>

                    {{-- Confirm Password --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1.5">Confirm new password</label>
                        <input type="password" name="password_confirmation" required class="{{ $fieldClasses }}" placeholder="Re-enter new password">
                    </div>

                    {{-- Submit Button --}}
                    <div class="pt-2">
                        <button type="submit" class="w-full px-4 py-2.5 bg-[#223030] text-white rounded-lg hover:bg-[#523D35] transition font-medium text-sm flex items-center justify-center gap-2 shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                            </svg>
                            Update password
                        </button>
                    </div>

                    {{-- Security Info --}}
                    <div class="pt-4 border-t border-gray-200">
                        <div class="flex gap-2">
                            <svg class="w-5 h-5 text-gray-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>

                            <div>
                                <p class="text-xs font-semibold text-gray-700">Password tips:</p>
                                <ul class="mt-1 text-xs text-gray-600 space-y-0.5 list-disc list-inside">
                                    <li>Use a mix of letters and numbers</li>
                                    <li>Avoid common words</li>
                                    <li>Do not reuse old passwords</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
@endpush