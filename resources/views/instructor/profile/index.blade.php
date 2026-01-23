{{-- resources/views/instructor/profile/index.blade.php --}}

@extends('layouts.instructor')

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

                <form method="POST" action="{{ route('instructor.profile.update') }}" class="p-4 sm:p-6 space-y-5">
                    @csrf
                    @method('PATCH')

                    {{-- Name Fields --}}
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">First name</label>
                            <input type="text" name="first_name" value="{{ old('first_name', $instructor->first_name) }}" required
                                   class="w-full rounded-lg bg-white border border-gray-300 text-gray-900 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                            @error('first_name')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">Middle name</label>
                            <input type="text" name="middle_name" value="{{ old('middle_name', $instructor->middle_name) }}"
                                   class="w-full rounded-lg bg-white border border-gray-300 text-gray-900 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                                   placeholder="Optional">
                            @error('middle_name')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">Last name</label>
                            <input type="text" name="last_name" value="{{ old('last_name', $instructor->last_name) }}" required
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
                            <input type="text" name="suffix" value="{{ old('suffix', $instructor->suffix) }}"
                                   class="w-full rounded-lg bg-white border border-gray-300 text-gray-900 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                                   placeholder="Jr., Sr., III (optional)">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">Gender</label>
                            <select name="gender" class="w-full rounded-lg bg-white border border-gray-300 text-gray-900 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                                <option value="">Select gender</option>
                                <option value="Male" {{ old('gender', $instructor->gender) == 'Male' ? 'selected' : '' }}>Male</option>
                                <option value="Female" {{ old('gender', $instructor->gender) == 'Female' ? 'selected' : '' }}>Female</option>
                                <option value="Other" {{ old('gender', $instructor->gender) == 'Other' ? 'selected' : '' }}>Other</option>
                                <option value="Prefer not to say" {{ old('gender', $instructor->gender) == 'Prefer not to say' ? 'selected' : '' }}>Prefer not to say</option>
                            </select>
                        </div>
                    </div>

                    {{-- Contact Information --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">Phone number</label>
                            <input type="text" name="phone" value="{{ old('phone', $instructor->phone) }}" 
                                   maxlength="11" placeholder="09123456789"
                                   class="w-full rounded-lg bg-white border border-gray-300 text-gray-900 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                            @error('phone')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">Email (read-only)</label>
                            <input type="email" value="{{ $instructor->email }}" disabled
                                   class="w-full rounded-lg bg-gray-100 border border-gray-300 text-gray-600 px-3 py-2 text-sm">
                        </div>
                    </div>

                    {{-- Date of Birth & Nationality --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">Date of birth</label>
                            <input type="date" name="date_of_birth" value="{{ old('date_of_birth', $instructor->date_of_birth) }}"
                                   class="w-full rounded-lg bg-white border border-gray-300 text-gray-900 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">Nationality</label>
                            <input type="text" name="nationality" value="{{ old('nationality', $instructor->nationality) }}"
                                   class="w-full rounded-lg bg-white border border-gray-300 text-gray-900 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                                   placeholder="Filipino">
                        </div>
                    </div>

                    {{-- Address --}}
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">Address line 1</label>
                            <input type="text" name="address_line1" value="{{ old('address_line1', $instructor->address_line1) }}"
                                   class="w-full rounded-lg bg-white border border-gray-300 text-gray-900 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                                   placeholder="Street, Barangay">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">Address line 2</label>
                            <input type="text" name="address_line2" value="{{ old('address_line2', $instructor->address_line2) }}"
                                   class="w-full rounded-lg bg-white border border-gray-300 text-gray-900 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                                   placeholder="Building, Unit (optional)">
                        </div>
                    </div>

                    {{-- City, Province, Postal --}}
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">City</label>
                            <input type="text" name="city" value="{{ old('city', $instructor->city) }}"
                                   class="w-full rounded-lg bg-white border border-gray-300 text-gray-900 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">Province</label>
                            <input type="text" name="province" value="{{ old('province', $instructor->province) }}"
                                   class="w-full rounded-lg bg-white border border-gray-300 text-gray-900 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">Postal code</label>
                            <input type="text" name="postal_code" value="{{ old('postal_code', $instructor->postal_code) }}"
                                   class="w-full rounded-lg bg-white border border-gray-300 text-gray-900 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                                   placeholder="6000">
                        </div>
                    </div>

                    {{-- Country --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1.5">Country</label>
                        <input type="text" name="country" value="{{ old('country', $instructor->country ?? 'Philippines') }}"
                               class="w-full rounded-lg bg-white border border-gray-300 text-gray-900 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                    </div>

                    {{-- Emergency Contact Section --}}
                    <div class="border-t border-gray-200 pt-5">
                        <h3 class="text-sm font-bold text-gray-900 mb-4">Emergency contact</h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">Contact name</label>
                                <input type="text" name="emergency_contact_name" value="{{ old('emergency_contact_name', $instructor->emergency_contact_name) }}"
                                       class="w-full rounded-lg bg-white border border-gray-300 text-gray-900 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Relationship</label>
                                    <input type="text" name="emergency_contact_relationship" value="{{ old('emergency_contact_relationship', $instructor->emergency_contact_relationship) }}"
                                           class="w-full rounded-lg bg-white border border-gray-300 text-gray-900 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                                           placeholder="Spouse, Parent, etc.">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Phone</label>
                                    <input type="text" name="emergency_contact_phone" value="{{ old('emergency_contact_phone', $instructor->emergency_contact_phone) }}"
                                           maxlength="11" placeholder="09123456789"
                                           class="w-full rounded-lg bg-white border border-gray-300 text-gray-900 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Professional Information Section --}}
                    <div class="border-t border-gray-200 pt-5">
                        <h3 class="text-sm font-bold text-gray-900 mb-4">Professional information</h3>
                        <div class="space-y-4">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Employee ID (read-only)</label>
                                    <input type="text" value="{{ $instructor->employee_id ?? '—' }}" disabled
                                           class="w-full rounded-lg bg-gray-100 border border-gray-300 text-gray-600 px-3 py-2 text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Hire date (read-only)</label>
                                    <input type="text" value="{{ $instructor->hire_date ? \Carbon\Carbon::parse($instructor->hire_date)->format('F d, Y') : '—' }}" disabled
                                           class="w-full rounded-lg bg-gray-100 border border-gray-300 text-gray-600 px-3 py-2 text-sm">
                                </div>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Employment status (read-only)</label>
                                    <input type="text" value="{{ ucfirst(str_replace('_', ' ', $instructor->employment_status ?? '—')) }}" disabled
                                           class="w-full rounded-lg bg-gray-100 border border-gray-300 text-gray-600 px-3 py-2 text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Contract type (read-only)</label>
                                    <input type="text" value="{{ ucfirst($instructor->contract_type ?? '—') }}" disabled
                                           class="w-full rounded-lg bg-gray-100 border border-gray-300 text-gray-600 px-3 py-2 text-sm">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Compensation Section (Read-only) --}}
                    <div class="border-t border-gray-200 pt-5">
                        <h3 class="text-sm font-bold text-gray-900 mb-4">Compensation (read-only)</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">Hourly rate</label>
                                <input type="text" value="{{ $instructor->hourly_rate ? '₱' . number_format($instructor->hourly_rate, 2) : '—' }}" disabled
                                       class="w-full rounded-lg bg-gray-100 border border-gray-300 text-gray-600 px-3 py-2 text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">Monthly salary</label>
                                <input type="text" value="{{ $instructor->monthly_salary ? '₱' . number_format($instructor->monthly_salary, 2) : '—' }}" disabled
                                       class="w-full rounded-lg bg-gray-100 border border-gray-300 text-gray-600 px-3 py-2 text-sm">
                            </div>
                        </div>
                    </div>

                    {{-- Qualifications Section --}}
                    <div class="border-t border-gray-200 pt-5">
                        <h3 class="text-sm font-bold text-gray-900 mb-4">Professional qualifications</h3>
                        <div class="space-y-4">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Education level</label>
                                    <input type="text" name="education_level" value="{{ old('education_level', $instructor->education_level) }}"
                                           class="w-full rounded-lg bg-white border border-gray-300 text-gray-900 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                                           placeholder="Bachelor's, Master's, etc.">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Music degree</label>
                                    <input type="text" name="music_degree" value="{{ old('music_degree', $instructor->music_degree) }}"
                                           class="w-full rounded-lg bg-white border border-gray-300 text-gray-900 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                                           placeholder="Bachelor of Music, etc.">
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">Certifications</label>
                                <textarea name="certifications" rows="2"
                                          class="w-full rounded-lg bg-white border border-gray-300 text-gray-900 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                                          placeholder="List your music certifications">{{ old('certifications', $instructor->certifications) }}</textarea>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">Years of experience</label>
                                <input type="number" name="years_of_experience" value="{{ old('years_of_experience', $instructor->years_of_experience) }}"
                                       min="0" max="50"
                                       class="w-full rounded-lg bg-white border border-gray-300 text-gray-900 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                            </div>
                        </div>
                    </div>

                    {{-- Teaching Details Section --}}
                    <div class="border-t border-gray-200 pt-5">
                        <h3 class="text-sm font-bold text-gray-900 mb-4">Teaching details</h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">Teaching style</label>
                                <textarea name="teaching_style" rows="3"
                                          class="w-full rounded-lg bg-white border border-gray-300 text-gray-900 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                                          placeholder="Describe your teaching approach">{{ old('teaching_style', $instructor->teaching_style) }}</textarea>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">Bio / About me</label>
                                <textarea name="bio" rows="4"
                                          class="w-full rounded-lg bg-white border border-gray-300 text-gray-900 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                                          placeholder="Tell students about yourself">{{ old('bio', $instructor->bio) }}</textarea>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">Languages spoken</label>
                                <input type="text" name="languages_spoken" value="{{ old('languages_spoken', $instructor->languages_spoken) }}"
                                       class="w-full rounded-lg bg-white border border-gray-300 text-gray-900 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                                       placeholder="English, Tagalog, Bisaya, etc.">
                            </div>
                        </div>
                    </div>

                    {{-- Availability Section --}}
                    <div class="border-t border-gray-200 pt-5">
                        <h3 class="text-sm font-bold text-gray-900 mb-4">Availability preferences</h3>
                        <div class="space-y-4">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Available days</label>
                                    <input type="text" name="available_days" value="{{ old('available_days', $instructor->available_days) }}"
                                           class="w-full rounded-lg bg-white border border-gray-300 text-gray-900 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                                           placeholder="Mon, Wed, Fri">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Preferred time slots</label>
                                    <input type="text" name="preferred_time_slots" value="{{ old('preferred_time_slots', $instructor->preferred_time_slots) }}"
                                           class="w-full rounded-lg bg-white border border-gray-300 text-gray-900 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                                           placeholder="9AM-12PM, 2PM-6PM">
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">Max students per day</label>
                                <input type="number" name="max_students_per_day" value="{{ old('max_students_per_day', $instructor->max_students_per_day ?? 8) }}"
                                       min="1" max="20"
                                       class="w-full rounded-lg bg-white border border-gray-300 text-gray-900 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                            </div>
                        </div>
                    </div>

                    {{-- Performance Metrics (Read-only) --}}
                    <div class="border-t border-gray-200 pt-5">
                        <h3 class="text-sm font-bold text-gray-900 mb-4">Performance metrics (read-only)</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">Total students taught</label>
                                <input type="text" value="{{ $instructor->total_students_taught ?? 0 }}" disabled
                                       class="w-full rounded-lg bg-gray-100 border border-gray-300 text-gray-600 px-3 py-2 text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">Average rating</label>
                                <input type="text" value="{{ $instructor->average_rating ? number_format($instructor->average_rating, 2) . ' / 5.00' : '—' }}" disabled
                                       class="w-full rounded-lg bg-gray-100 border border-gray-300 text-gray-600 px-3 py-2 text-sm">
                            </div>
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

                <form method="POST" action="{{ route('instructor.password.change') }}" class="p-4 sm:p-6 space-y-4">
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