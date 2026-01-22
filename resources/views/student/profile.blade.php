@extends('layouts.student')

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-6">

    {{-- Header --}}
    <div class="mb-6">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">My profile</h1>
        <p class="mt-1 text-sm text-gray-600">Update your personal information and password</p>
    </div>

    {{-- Success Message --}}
    @if(session('success'))
        <div class="mb-6 bg-green-50 border border-green-300 text-green-800 px-4 py-3 rounded-lg flex items-center gap-3">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <span class="text-sm font-medium">{{ session('success') }}</span>
        </div>
    @endif

    {{-- Error Message --}}
    @if(session('error'))
        <div class="mb-6 bg-red-50 border border-red-300 text-red-800 px-4 py-3 rounded-lg flex items-center gap-3">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
            <span class="text-sm font-medium">{{ session('error') }}</span>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        {{-- Personal Information Form (2 columns) --}}
        <div class="lg:col-span-2">
            <div class="bg-white border border-gray-300 rounded-xl shadow overflow-hidden">
                <div class="px-4 sm:px-6 py-4 bg-gray-100 border-b border-gray-300">
                    <h2 class="text-lg font-bold text-gray-900">Personal information</h2>
                </div>

                <form method="POST" action="{{ route('student.profile.update') }}" class="p-4 sm:p-6 space-y-5">
                    @csrf
                    @method('PATCH')

                    {{-- Name Fields --}}
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">First name</label>
                            <input type="text" name="first_name" value="{{ old('first_name', $student->first_name) }}" required
                                   class="w-full rounded-lg bg-white border border-gray-300 text-gray-900 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                            @error('first_name')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">Middle name</label>
                            <input type="text" name="middle_name" value="{{ old('middle_name', $student->middle_name) }}"
                                   class="w-full rounded-lg bg-white border border-gray-300 text-gray-900 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                                   placeholder="Optional">
                            @error('middle_name')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">Last name</label>
                            <input type="text" name="last_name" value="{{ old('last_name', $student->last_name) }}" required
                                   class="w-full rounded-lg bg-white border border-gray-300 text-gray-900 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                            @error('last_name')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Suffix & Gender --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">Suffix</label>
                            <input type="text" name="suffix" value="{{ old('suffix', $student->suffix) }}"
                                   class="w-full rounded-lg bg-white border border-gray-300 text-gray-900 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                                   placeholder="Jr., Sr., III (optional)">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">Gender</label>
                            <select name="gender" class="w-full rounded-lg bg-white border border-gray-300 text-gray-900 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                                <option value="">Select gender</option>
                                <option value="Male" {{ old('gender', $student->gender) == 'Male' ? 'selected' : '' }}>Male</option>
                                <option value="Female" {{ old('gender', $student->gender) == 'Female' ? 'selected' : '' }}>Female</option>
                                <option value="Other" {{ old('gender', $student->gender) == 'Other' ? 'selected' : '' }}>Other</option>
                                <option value="Prefer not to say" {{ old('gender', $student->gender) == 'Prefer not to say' ? 'selected' : '' }}>Prefer not to say</option>
                            </select>
                        </div>
                    </div>

                    {{-- Contact Information --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">Phone number</label>
                            <input type="text" name="phone" value="{{ old('phone', $student->phone) }}" 
                                   maxlength="11" placeholder="09123456789"
                                   class="w-full rounded-lg bg-white border border-gray-300 text-gray-900 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                            @error('phone')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">Email (read-only)</label>
                            <input type="email" value="{{ $student->email }}" disabled
                                   class="w-full rounded-lg bg-gray-100 border border-gray-300 text-gray-600 px-3 py-2 text-sm">
                        </div>
                    </div>

                    {{-- Date of Birth & Nationality --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">Date of birth</label>
                            <input type="date" name="date_of_birth" value="{{ old('date_of_birth', $student->date_of_birth) }}"
                                   class="w-full rounded-lg bg-white border border-gray-300 text-gray-900 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">Nationality</label>
                            <input type="text" name="nationality" value="{{ old('nationality', $student->nationality) }}"
                                   class="w-full rounded-lg bg-white border border-gray-300 text-gray-900 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                                   placeholder="Filipino">
                        </div>
                    </div>

                    {{-- Address --}}
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">Address line 1</label>
                            <input type="text" name="address_line1" value="{{ old('address_line1', $student->address_line1) }}"
                                   class="w-full rounded-lg bg-white border border-gray-300 text-gray-900 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                                   placeholder="Street, Barangay">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">Address line 2</label>
                            <input type="text" name="address_line2" value="{{ old('address_line2', $student->address_line2) }}"
                                   class="w-full rounded-lg bg-white border border-gray-300 text-gray-900 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                                   placeholder="Building, Unit (optional)">
                        </div>
                    </div>

                    {{-- City, Province, Postal --}}
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">City</label>
                            <input type="text" name="city" value="{{ old('city', $student->city) }}"
                                   class="w-full rounded-lg bg-white border border-gray-300 text-gray-900 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">Province</label>
                            <input type="text" name="province" value="{{ old('province', $student->province) }}"
                                   class="w-full rounded-lg bg-white border border-gray-300 text-gray-900 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">Postal code</label>
                            <input type="text" name="postal_code" value="{{ old('postal_code', $student->postal_code) }}"
                                   class="w-full rounded-lg bg-white border border-gray-300 text-gray-900 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                                   placeholder="6000">
                        </div>
                    </div>

                    {{-- Country --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1.5">Country</label>
                        <input type="text" name="country" value="{{ old('country', $student->country ?? 'Philippines') }}"
                               class="w-full rounded-lg bg-white border border-gray-300 text-gray-900 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                    </div>

                    {{-- Emergency Contact Section --}}
                    <div class="border-t border-gray-200 pt-5">
                        <h3 class="text-sm font-bold text-gray-900 mb-4">Emergency contact</h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">Contact name</label>
                                <input type="text" name="emergency_contact_name" value="{{ old('emergency_contact_name', $student->emergency_contact_name) }}"
                                       class="w-full rounded-lg bg-white border border-gray-300 text-gray-900 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Relationship</label>
                                    <input type="text" name="emergency_contact_relationship" value="{{ old('emergency_contact_relationship', $student->emergency_contact_relationship) }}"
                                           class="w-full rounded-lg bg-white border border-gray-300 text-gray-900 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                                           placeholder="Mother, Father, etc.">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Phone</label>
                                    <input type="text" name="emergency_contact_phone" value="{{ old('emergency_contact_phone', $student->emergency_contact_phone) }}"
                                           maxlength="11" placeholder="09123456789"
                                           class="w-full rounded-lg bg-white border border-gray-300 text-gray-900 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
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
                                    <input type="text" name="parent_guardian_name" value="{{ old('parent_guardian_name', $student->parent_guardian_name) }}"
                                           class="w-full rounded-lg bg-white border border-gray-300 text-gray-900 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Relationship</label>
                                    <input type="text" name="parent_guardian_relationship" value="{{ old('parent_guardian_relationship', $student->parent_guardian_relationship) }}"
                                           class="w-full rounded-lg bg-white border border-gray-300 text-gray-900 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                                </div>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Phone</label>
                                    <input type="text" name="parent_guardian_phone" value="{{ old('parent_guardian_phone', $student->parent_guardian_phone) }}"
                                           maxlength="11" placeholder="09123456789"
                                           class="w-full rounded-lg bg-white border border-gray-300 text-gray-900 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Email</label>
                                    <input type="email" name="parent_guardian_email" value="{{ old('parent_guardian_email', $student->parent_guardian_email) }}"
                                           class="w-full rounded-lg bg-white border border-gray-300 text-gray-900 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">Address</label>
                                <textarea name="parent_guardian_address" rows="2"
                                          class="w-full rounded-lg bg-white border border-gray-300 text-gray-900 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">{{ old('parent_guardian_address', $student->parent_guardian_address) }}</textarea>
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
                                    <select name="instrument_id" class="w-full rounded-lg bg-white border border-gray-300 text-gray-900 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                                        <option value="">Select instrument</option>
                                        @foreach($instruments as $instrument)
                                            <option value="{{ $instrument->instrument_id }}" 
                                                {{ old('instrument_id', $student->instrument_id) == $instrument->instrument_id ? 'selected' : '' }}>
                                                {{ $instrument->instrument_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Skill level</label>
                                    <select name="skill_level" class="w-full rounded-lg bg-white border border-gray-300 text-gray-900 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                                        <option value="">Select level</option>
                                        <option value="beginner" {{ old('skill_level', $student->skill_level) == 'beginner' ? 'selected' : '' }}>Beginner</option>
                                        <option value="intermediate" {{ old('skill_level', $student->skill_level) == 'intermediate' ? 'selected' : '' }}>Intermediate</option>
                                        <option value="advanced" {{ old('skill_level', $student->skill_level) == 'advanced' ? 'selected' : '' }}>Advanced</option>
                                        <option value="expert" {{ old('skill_level', $student->skill_level) == 'expert' ? 'selected' : '' }}>Expert</option>
                                    </select>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">Secondary instruments</label>
                                <input type="text" name="secondary_instruments" value="{{ old('secondary_instruments', $student->secondary_instruments) }}"
                                       class="w-full rounded-lg bg-white border border-gray-300 text-gray-900 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                                       placeholder="Piano, Drums, etc.">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">Previous music experience</label>
                                <textarea name="previous_music_experience" rows="2"
                                          class="w-full rounded-lg bg-white border border-gray-300 text-gray-900 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                                          placeholder="Describe your prior training or experience">{{ old('previous_music_experience', $student->previous_music_experience) }}</textarea>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">Music goals</label>
                                <textarea name="music_goals" rows="2"
                                          class="w-full rounded-lg bg-white border border-gray-300 text-gray-900 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                                          placeholder="What do you hope to achieve?">{{ old('music_goals', $student->music_goals) }}</textarea>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">Preferred genre</label>
                                <select name="preferred_genre_id" class="w-full rounded-lg bg-white border border-gray-300 text-gray-900 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                                    <option value="">Select genre</option>
                                    @foreach($genres as $genre)
                                        <option value="{{ $genre->genre_id }}" 
                                            {{ old('preferred_genre_id', $student->preferred_genre_id) == $genre->genre_id ? 'selected' : '' }}>
                                            {{ $genre->genre_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Educational Background --}}
                    <div class="border-t border-gray-200 pt-5">
                        <h3 class="text-sm font-bold text-gray-900 mb-4">Educational background</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">School name</label>
                                <input type="text" name="school_name" value="{{ old('school_name', $student->school_name) }}"
                                       class="w-full rounded-lg bg-white border border-gray-300 text-gray-900 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">Grade level</label>
                                <input type="text" name="grade_level" value="{{ old('grade_level', $student->grade_level) }}"
                                       class="w-full rounded-lg bg-white border border-gray-300 text-gray-900 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                                       placeholder="Grade 10, College 2nd Year, etc.">
                            </div>
                        </div>
                    </div>

                    {{-- Medical Information --}}
                    <div class="border-t border-gray-200 pt-5">
                        <h3 class="text-sm font-bold text-gray-900 mb-4">Medical information</h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">Medical conditions</label>
                                <textarea name="medical_conditions" rows="2"
                                          class="w-full rounded-lg bg-white border border-gray-300 text-gray-900 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                                          placeholder="Any medical conditions we should know about">{{ old('medical_conditions', $student->medical_conditions) }}</textarea>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">Allergies</label>
                                <textarea name="allergies" rows="2"
                                          class="w-full rounded-lg bg-white border border-gray-300 text-gray-900 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                                          placeholder="Any allergies">{{ old('allergies', $student->allergies) }}</textarea>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">Special needs</label>
                                <textarea name="special_needs" rows="2"
                                          class="w-full rounded-lg bg-white border border-gray-300 text-gray-900 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                                          placeholder="Any special accommodations needed">{{ old('special_needs', $student->special_needs) }}</textarea>
                            </div>
                        </div>
                    </div>

                    {{-- Lesson Preferences --}}
                    <div class="border-t border-gray-200 pt-5">
                        <h3 class="text-sm font-bold text-gray-900 mb-4">Lesson preferences</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">Preferred lesson days</label>
                                <input type="text" name="preferred_lesson_days" value="{{ old('preferred_lesson_days', $student->preferred_lesson_days) }}" 
                                       class="w-full rounded-lg bg-white border border-gray-300 text-gray-900 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                                       placeholder="Mon, Wed, Fri">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">Preferred time slots</label>
                                <input type="text" name="preferred_lesson_time" value="{{ old('preferred_lesson_time', $student->preferred_lesson_time) }}" 
                                       class="w-full rounded-lg bg-white border border-gray-300 text-gray-900 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                                       placeholder="Afternoon (2PM-6PM)">
                            </div>
                        </div>
                    </div>

                    {{-- Enrollment Date (Read-only) --}}
                    <div class="border-t border-gray-200 pt-5">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">Enrollment date (read-only)</label>
                            <input type="text" value="{{ $student->enrollment_date ? \Carbon\Carbon::parse($student->enrollment_date)->format('F d, Y') : '—' }}" disabled
                                   class="w-full rounded-lg bg-gray-100 border border-gray-300 text-gray-600 px-3 py-2 text-sm">
                        </div>
                    </div>

                    {{-- Submit Button --}}
                    <div class="flex justify-end pt-4 border-t border-gray-200">
                        <button type="submit" class="px-6 py-2.5 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition font-medium text-sm flex items-center gap-2 shadow-sm">
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
                        <input type="password" name="current_password" required
                               class="w-full rounded-lg bg-white border border-gray-300 text-gray-900 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                               placeholder="Enter current password">
                        @error('current_password')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- New Password --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1.5">New password</label>
                        <input type="password" name="password" required
                               class="w-full rounded-lg bg-white border border-gray-300 text-gray-900 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                               placeholder="Enter new password">
                        @error('password')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">Must be at least 8 characters</p>
                    </div>

                    {{-- Confirm Password --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1.5">Confirm new password</label>
                        <input type="password" name="password_confirmation" required
                               class="w-full rounded-lg bg-white border border-gray-300 text-gray-900 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                               placeholder="Re-enter new password">
                    </div>

                    {{-- Submit Button --}}
                    <div class="pt-2">
                        <button type="submit" class="w-full px-4 py-2.5 bg-gray-900 text-white rounded-lg hover:bg-gray-800 transition font-medium text-sm flex items-center justify-center gap-2 shadow-sm">
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
                                    <li>Don't reuse old passwords</li>
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