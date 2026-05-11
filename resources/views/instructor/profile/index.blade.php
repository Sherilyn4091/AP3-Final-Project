{{-- resources/views/instructor/profile/index.blade.php --}}
@extends('layouts.instructor')

@section('content')
@php
    $inputClass = 'instructor-input';
    $labelClass = 'instructor-label';
@endphp

<div class="mx-auto max-w-6xl space-y-6">
    <header class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.22em] text-[#B4833D]">Instructor Profile</p>
            <h1 class="instructor-heading mt-2 text-3xl font-extrabold text-[#2F4F4F]">My Profile</h1>
            <p class="mt-2 max-w-2xl text-sm text-[#61677A]">Update your instructor information, teaching details, availability, and password.</p>
        </div>
    </header>

    @if(session('success'))
        <div class="rounded-2xl border border-[#959D90] bg-white p-4 text-sm font-bold text-[#2F4F4F] shadow-sm">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="rounded-2xl border border-[#B4833D] bg-[#fcf3e3] p-4 text-sm font-bold text-[#523D35] shadow-sm">
            {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="rounded-2xl border border-[#B4833D] bg-[#fcf3e3] p-4 text-sm text-[#523D35] shadow-sm">
            <strong>Please check the form.</strong>
            <ul class="mt-2 list-disc pl-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 gap-5 xl:grid-cols-3">
        <form method="POST" action="{{ route('instructor.profile.update') }}" class="instructor-card xl:col-span-2">
            @csrf
            @method('PATCH')

            <div class="border-b border-[#D8D9DA] px-5 py-4">
                <h2 class="instructor-heading text-xl font-bold text-[#2F4F4F]">Personal and Teaching Information</h2>
                <p class="mt-1 text-sm text-[#61677A]">Editable profile details are saved to your instructor record.</p>
            </div>

            <div class="space-y-6 p-5">
                <section>
                    <h3 class="instructor-heading mb-4 text-base font-bold text-[#523D35]">Basic Information</h3>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <div><label class="{{ $labelClass }}">First name</label><input class="{{ $inputClass }}" name="first_name" value="{{ old('first_name', $instructor->first_name) }}" required></div>
                        <div><label class="{{ $labelClass }}">Middle name</label><input class="{{ $inputClass }}" name="middle_name" value="{{ old('middle_name', $instructor->middle_name) }}"></div>
                        <div><label class="{{ $labelClass }}">Last name</label><input class="{{ $inputClass }}" name="last_name" value="{{ old('last_name', $instructor->last_name) }}" required></div>
                        <div><label class="{{ $labelClass }}">Suffix</label><input class="{{ $inputClass }}" name="suffix" value="{{ old('suffix', $instructor->suffix) }}" placeholder="Optional"></div>
                        <div><label class="{{ $labelClass }}">Gender</label><select class="{{ $inputClass }}" name="gender"><option value="">Select gender</option>@foreach(['Male','Female','Other','Prefer not to say'] as $gender)<option value="{{ $gender }}" @selected(old('gender', $instructor->gender) === $gender)>{{ $gender }}</option>@endforeach</select></div>
                        <div><label class="{{ $labelClass }}">Date of birth</label><input type="date" class="{{ $inputClass }}" name="date_of_birth" value="{{ old('date_of_birth', $instructor->date_of_birth) }}"></div>
                        <div><label class="{{ $labelClass }}">Nationality</label><input class="{{ $inputClass }}" name="nationality" value="{{ old('nationality', $instructor->nationality) }}" placeholder="Filipino"></div>
                        <div><label class="{{ $labelClass }}">Phone number</label><input class="{{ $inputClass }}" name="phone" maxlength="11" value="{{ old('phone', $instructor->phone) }}" placeholder="09123456789"></div>
                        <div><label class="{{ $labelClass }}">Email</label><input class="{{ $inputClass }} bg-[#D8D9DA]/40" value="{{ $instructor->email }}" disabled></div>
                    </div>
                </section>

                <section class="instructor-divider pt-5">
                    <h3 class="instructor-heading mb-4 text-base font-bold text-[#523D35]">Address</h3>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2"><label class="{{ $labelClass }}">Address line 1</label><input class="{{ $inputClass }}" name="address_line1" value="{{ old('address_line1', $instructor->address_line1) }}"></div>
                        <div class="sm:col-span-2"><label class="{{ $labelClass }}">Address line 2</label><input class="{{ $inputClass }}" name="address_line2" value="{{ old('address_line2', $instructor->address_line2) }}"></div>
                        <div><label class="{{ $labelClass }}">City</label><input class="{{ $inputClass }}" name="city" value="{{ old('city', $instructor->city) }}"></div>
                        <div><label class="{{ $labelClass }}">Province</label><input class="{{ $inputClass }}" name="province" value="{{ old('province', $instructor->province) }}"></div>
                        <div><label class="{{ $labelClass }}">Postal code</label><input class="{{ $inputClass }}" name="postal_code" value="{{ old('postal_code', $instructor->postal_code) }}"></div>
                        <div><label class="{{ $labelClass }}">Country</label><input class="{{ $inputClass }}" name="country" value="{{ old('country', $instructor->country ?? 'Philippines') }}"></div>
                    </div>
                </section>

                <section class="instructor-divider pt-5">
                    <h3 class="instructor-heading mb-4 text-base font-bold text-[#523D35]">Emergency Contact</h3>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <div><label class="{{ $labelClass }}">Contact name</label><input class="{{ $inputClass }}" name="emergency_contact_name" value="{{ old('emergency_contact_name', $instructor->emergency_contact_name) }}"></div>
                        <div><label class="{{ $labelClass }}">Relationship</label><input class="{{ $inputClass }}" name="emergency_contact_relationship" value="{{ old('emergency_contact_relationship', $instructor->emergency_contact_relationship) }}"></div>
                        <div><label class="{{ $labelClass }}">Phone</label><input class="{{ $inputClass }}" name="emergency_contact_phone" maxlength="11" value="{{ old('emergency_contact_phone', $instructor->emergency_contact_phone) }}"></div>
                    </div>
                </section>

                <section class="instructor-divider pt-5">
                    <h3 class="instructor-heading mb-4 text-base font-bold text-[#523D35]">Professional Qualifications</h3>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div><label class="{{ $labelClass }}">Education level</label><input class="{{ $inputClass }}" name="education_level" value="{{ old('education_level', $instructor->education_level) }}"></div>
                        <div><label class="{{ $labelClass }}">Music degree</label><input class="{{ $inputClass }}" name="music_degree" value="{{ old('music_degree', $instructor->music_degree) }}"></div>
                        <div><label class="{{ $labelClass }}">Years of experience</label><input type="number" min="0" max="50" class="{{ $inputClass }} instructor-mono" name="years_of_experience" value="{{ old('years_of_experience', $instructor->years_of_experience) }}"></div>
                        <div><label class="{{ $labelClass }}">Languages spoken</label><input class="{{ $inputClass }}" name="languages_spoken" value="{{ old('languages_spoken', $instructor->languages_spoken) }}" placeholder="English, Tagalog, Bisaya"></div>
                        <div class="sm:col-span-2"><label class="{{ $labelClass }}">Certifications</label><textarea class="{{ $inputClass }}" name="certifications" rows="3">{{ old('certifications', $instructor->certifications) }}</textarea></div>
                    </div>
                </section>

                <section class="instructor-divider pt-5">
                    <h3 class="instructor-heading mb-4 text-base font-bold text-[#523D35]">Teaching Details</h3>
                    <div class="grid grid-cols-1 gap-4">
                        <div><label class="{{ $labelClass }}">Teaching style</label><textarea class="{{ $inputClass }}" name="teaching_style" rows="3">{{ old('teaching_style', $instructor->teaching_style) }}</textarea></div>
                        <div><label class="{{ $labelClass }}">Bio / About me</label><textarea class="{{ $inputClass }}" name="bio" rows="4">{{ old('bio', $instructor->bio) }}</textarea></div>
                    </div>
                </section>

                <section class="instructor-divider pt-5">
                    <h3 class="instructor-heading mb-4 text-base font-bold text-[#523D35]">Availability</h3>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <div><label class="{{ $labelClass }}">Available days</label><input class="{{ $inputClass }}" name="available_days" value="{{ old('available_days', $instructor->available_days) }}" placeholder="Mon, Wed, Fri"></div>
                        <div><label class="{{ $labelClass }}">Preferred time slots</label><input class="{{ $inputClass }}" name="preferred_time_slots" value="{{ old('preferred_time_slots', $instructor->preferred_time_slots) }}" placeholder="9AM-12PM"></div>
                        <div><label class="{{ $labelClass }}">Max students per day</label><input type="number" min="1" max="20" class="{{ $inputClass }} instructor-mono" name="max_students_per_day" value="{{ old('max_students_per_day', $instructor->max_students_per_day ?? 8) }}"></div>
                    </div>
                </section>

                <div class="instructor-divider flex flex-col gap-3 pt-5 sm:flex-row sm:justify-end">
                    <button type="submit" class="instructor-btn-primary px-5 py-3 text-sm">Save Profile Changes</button>
                </div>
            </div>
        </form>

        <aside class="space-y-5">
            <section class="instructor-card p-5">
                <h2 class="instructor-heading text-xl font-bold text-[#2F4F4F]">Employment Summary</h2>
                <div class="mt-5 space-y-3 text-sm">
                    <div class="flex justify-between gap-3 border-b border-[#D8D9DA] pb-2"><span class="text-[#61677A]">Employee ID</span><strong class="text-right text-[#2F4F4F]">{{ $instructor->employee_id ?? '—' }}</strong></div>
                    <div class="flex justify-between gap-3 border-b border-[#D8D9DA] pb-2"><span class="text-[#61677A]">Hire date</span><strong class="text-right text-[#2F4F4F]">{{ $instructor->hire_date ? \Carbon\Carbon::parse($instructor->hire_date)->format('M d, Y') : '—' }}</strong></div>
                    <div class="flex justify-between gap-3 border-b border-[#D8D9DA] pb-2"><span class="text-[#61677A]">Status</span><strong class="text-right text-[#2F4F4F]">{{ ucfirst(str_replace('_', ' ', $instructor->employment_status ?? '—')) }}</strong></div>
                    <div class="flex justify-between gap-3 border-b border-[#D8D9DA] pb-2"><span class="text-[#61677A]">Contract</span><strong class="text-right text-[#2F4F4F]">{{ ucfirst($instructor->contract_type ?? '—') }}</strong></div>
                    <div class="flex justify-between gap-3 border-b border-[#D8D9DA] pb-2"><span class="text-[#61677A]">Hourly rate</span><strong class="instructor-mono text-right text-[#2F4F4F]">{{ $instructor->hourly_rate ? '₱' . number_format($instructor->hourly_rate, 2) : '—' }}</strong></div>
                    <div class="flex justify-between gap-3"><span class="text-[#61677A]">Monthly salary</span><strong class="instructor-mono text-right text-[#2F4F4F]">{{ $instructor->monthly_salary ? '₱' . number_format($instructor->monthly_salary, 2) : '—' }}</strong></div>
                </div>
            </section>

            <section class="instructor-card p-5">
                <h2 class="instructor-heading text-xl font-bold text-[#2F4F4F]">Performance</h2>
                <div class="mt-5 grid grid-cols-2 gap-3">
                    <div class="rounded-2xl border border-[#D8D9DA] bg-[#fcf3e3] p-3"><p class="text-xs font-bold uppercase text-[#61677A]">Students</p><p class="instructor-mono mt-1 text-2xl font-black text-[#2F4F4F]">{{ $instructor->total_students_taught ?? 0 }}</p></div>
                    <div class="rounded-2xl border border-[#D8D9DA] bg-[#fcf3e3] p-3"><p class="text-xs font-bold uppercase text-[#61677A]">Rating</p><p class="instructor-mono mt-1 text-2xl font-black text-[#2F4F4F]">{{ $instructor->average_rating ? number_format($instructor->average_rating, 2) : '—' }}</p></div>
                </div>
            </section>

            <section class="instructor-card p-5">
                <h2 class="instructor-heading text-xl font-bold text-[#2F4F4F]">Change Password</h2>
                <form method="POST" action="{{ route('instructor.password.change') }}" class="mt-5 space-y-4">
                    @csrf
                    <div><label class="{{ $labelClass }}">Current password</label><input type="password" name="current_password" required class="{{ $inputClass }}"></div>
                    <div><label class="{{ $labelClass }}">New password</label><input type="password" name="password" required class="{{ $inputClass }}"><p class="mt-1 text-xs text-[#61677A]">Must be at least 8 characters.</p></div>
                    <div><label class="{{ $labelClass }}">Confirm password</label><input type="password" name="password_confirmation" required class="{{ $inputClass }}"></div>
                    <button type="submit" class="instructor-btn-primary w-full px-5 py-3 text-sm">Update Password</button>
                </form>
            </section>
        </aside>
    </div>
</div>
@endsection