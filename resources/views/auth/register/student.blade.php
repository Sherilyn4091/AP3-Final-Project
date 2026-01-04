{{-- resources/views/auth/register/student.blade.php --}}
{{-- Multi-Step Student Registration - Session-Based (Matches Instructor Design) --}}

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Student Registration - Music Lab</title>

    @vite(['resources/css/style.css', 'resources/js/script.js'])
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="bg-light-gray min-h-screen py-8 px-4">

    <div id="blurOverlay" class="blur-overlay">
        <p id="motivationText" class="motivation-text"></p>
    </div>

    <div class="max-w-5xl mx-auto">
        {{-- Header with Official Logo --}}
        <div class="text-center mb-10 animate-fade-in">
            <a href="{{ route('home') }}" class="inline-block hover:opacity-90 transition-opacity">
                <img src="https://res.cloudinary.com/dibojpqg2/image/upload/v1766933637/music-lab-logo_1_lfcsqw.png" 
                     alt="Music Lab - Lessons & Instruments" 
                     class="mx-auto h-28 md:h-36 object-contain drop-shadow-2xl">
            </a>
            <h1 class="text-3xl font-bold text-primary-dark mt-6">Student Registration</h1>
            <p class="text-secondary-blue mt-2 text-base">Begin your musical journey with us</p>
        </div>

        <div class="card relative overflow-hidden py-8 px-8">
            <form method="POST" action="{{ route('register.student.process') }}" id="studentForm">
                @csrf

                {{-- Step Progress Indicator --}}
                <div class="flex justify-center mb-8 step-indicator">
                    <div class="flex items-center space-x-8">
                        <div class="step-item active">
                            <div class="step-circle">1</div>
                            <span class="step-label text-sm">Personal</span>
                        </div>
                        <div class="step-line"></div>
                        <div class="step-item">
                            <div class="step-circle">2</div>
                            <span class="step-label text-sm">Contacts</span>
                        </div>
                        <div class="step-line"></div>
                        <div class="step-item">
                            <div class="step-circle">3</div>
                            <span class="step-label text-sm">Musical Info</span>
                        </div>
                    </div>
                </div>

                {{-- Step 1: Personal Information --}}
                <div id="step1" class="step-panel active">
                    <h2 class="text-2xl font-bold text-primary-dark mb-8 text-center">Personal Information</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Name Fields --}}
                        <div>
                            <label for="first_name" class="label-required text-sm">First Name</label>
                            <input type="text" id="first_name" name="first_name" value="{{ old('first_name') }}" required class="input-field text-sm capitalize-words">
                            @error('first_name')<p class="error-text text-xs">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="last_name" class="label-required text-sm">Last Name</label>
                            <input type="text" id="last_name" name="last_name" value="{{ old('last_name') }}" required class="input-field text-sm capitalize-words">
                            @error('last_name')<p class="error-text text-xs">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="middle_name" class="text-sm">Middle Name</label>
                            <input type="text" id="middle_name" name="middle_name" value="{{ old('middle_name') }}" class="input-field text-sm capitalize-words">
                        </div>
                        <div>
                            <label for="suffix" class="text-sm">Suffix</label>
                            <input type="text" id="suffix" name="suffix" value="{{ old('suffix') }}" class="input-field text-sm" placeholder="e.g., Jr., III">
                        </div>

                        {{-- Contact --}}
                        <div>
                            <label for="phone" class="label-required text-sm">Phone Number</label>
                            <input type="tel" id="phone" name="phone" value="{{ old('phone') }}" required class="input-field text-sm" placeholder="09123456789">
                            @error('phone')<p class="error-text text-xs">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="user_email" class="label-required text-sm">Email Address</label>
                            <input type="email" id="user_email" name="user_email" value="{{ old('user_email') }}" required class="input-field text-sm lowercase-email">
                            @error('user_email')<p class="error-text text-xs">{{ $message }}</p>@enderror
                        </div>

                        {{-- Address --}}
                        <div class="md:col-span-2">
                            <label for="address_line1" class="label-required text-sm">Address Line 1</label>
                            <input type="text" id="address_line1" name="address_line1" value="{{ old('address_line1') }}" required class="input-field text-sm capitalize-words">
                        </div>
                        <div class="md:col-span-2">
                            <label for="address_line2" class="text-sm">Address Line 2</label>
                            <input type="text" id="address_line2" name="address_line2" value="{{ old('address_line2') }}" class="input-field text-sm capitalize-words">
                        </div>
                        <div>
                            <label for="city" class="label-required text-sm">City/Municipality</label>
                            <input type="text" id="city" name="city" value="{{ old('city') }}" required class="input-field text-sm capitalize-words">
                        </div>
                        <div>
                            <label for="province" class="label-required text-sm">Province</label>
                            <input type="text" id="province" name="province" value="{{ old('province') }}" required class="input-field text-sm capitalize-words">
                        </div>
                        <div>
                            <label for="postal_code" class="label-required text-sm">Postal Code</label>
                            <input type="text" id="postal_code" name="postal_code" value="{{ old('postal_code') }}" required class="input-field text-sm">
                        </div>
                        <div>
                            <label for="country" class="label-required text-sm">Country</label>
                            <input type="text" id="country" name="country" value="{{ old('country', 'Philippines') }}" required class="input-field text-sm capitalize-words">
                        </div>

                        {{-- Personal Details --}}
                        <div>
                            <label for="date_of_birth" class="label-required text-sm">Date of Birth</label>
                            <input type="date" id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth') }}" required class="input-field text-sm">
                            <p class="text-xs text-secondary-blue mt-2">Age: <span id="age-display" class="font-semibold">—</span></p>
                        </div>
                        <div>
                            <label for="gender" class="label-required text-sm">Gender</label>
                            <select id="gender" name="gender" required class="select-field text-sm">
                                <option value="">Select gender</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Other">Other</option>
                                <option value="Prefer not to say">Prefer not to say</option>
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label for="nationality" class="text-sm">Nationality</label>
                            <input type="text" id="nationality" name="nationality" value="{{ old('nationality') }}" class="input-field text-sm capitalize-words" placeholder="e.g., Filipino">
                        </div>

                        {{-- Medical Information --}}
                        <div class="md:col-span-2 section-divider pt-6">
                            <h3 class="text-lg font-bold text-primary-dark mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-warm-coral" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                Medical Information (Optional)
                            </h3>
                        </div>

                        <div class="md:col-span-2">
                            <label for="medical_conditions" class="text-sm">Medical Conditions</label>
                            <textarea id="medical_conditions" name="medical_conditions" rows="3" class="textarea-field text-sm" placeholder="Any existing medical conditions we should know about">{{ old('medical_conditions') }}</textarea>
                        </div>

                        <div>
                            <label for="allergies" class="text-sm">Allergies</label>
                            <textarea id="allergies" name="allergies" rows="2" class="textarea-field text-sm" placeholder="Food, medication, or environmental allergies">{{ old('allergies') }}</textarea>
                        </div>

                        <div>
                            <label for="special_needs" class="text-sm">Special Needs</label>
                            <textarea id="special_needs" name="special_needs" rows="2" class="textarea-field text-sm" placeholder="Any accommodations needed">{{ old('special_needs') }}</textarea>
                        </div>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="flex flex-col md:flex-row gap-6 pt-8 mt-auto">
                        <a href="{{ route('register') }}" class="btn-secondary flex-1 text-center flex items-center justify-center py-3 text-sm">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                            </svg>
                            Back to Role Selection
                        </a>
                        <button type="button" id="nextStepBtn1" class="btn-primary flex-1 flex items-center justify-center py-3 text-sm">
                            Next
                            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Step 2: Emergency & Guardian Contacts --}}
                <div id="step2" class="step-panel hidden">
                    <h2 class="text-2xl font-bold text-primary-dark mb-8 text-center">Emergency & Guardian Contacts</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Emergency Contact Section --}}
                        <div class="md:col-span-2">
                            <h3 class="text-lg font-bold text-forest-green mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                </svg>
                                Emergency Contact
                            </h3>
                        </div>

                        <div>
                            <label for="emergency_contact_name" class="label-required text-sm">Name</label>
                            <input type="text" id="emergency_contact_name" name="emergency_contact_name" value="{{ old('emergency_contact_name') }}" required class="input-field text-sm capitalize-words">
                        </div>

                        <div>
                            <label for="emergency_contact_relationship" class="label-required text-sm">Relationship</label>
                            <input type="text" id="emergency_contact_relationship" name="emergency_contact_relationship" value="{{ old('emergency_contact_relationship') }}" required class="input-field text-sm capitalize-words">
                        </div>

                        <div class="md:col-span-2">
                            <label for="emergency_contact_phone" class="label-required text-sm">Phone Number</label>
                            <input type="tel" id="emergency_contact_phone" name="emergency_contact_phone" value="{{ old('emergency_contact_phone') }}" required class="input-field text-sm" placeholder="09123456789">
                        </div>

                        {{-- Parent/Guardian Section --}}
                        <div class="md:col-span-2 section-divider pt-6">
                            <h3 class="text-lg font-bold text-golden-yellow mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                Parent/Guardian Information
                            </h3>
                        </div>

                        <div>
                            <label for="parent_guardian_name" class="label-required text-sm">Name</label>
                            <input type="text" id="parent_guardian_name" name="parent_guardian_name" value="{{ old('parent_guardian_name') }}" required class="input-field text-sm capitalize-words">
                        </div>

                        <div>
                            <label for="parent_guardian_relationship" class="label-required text-sm">Relationship</label>
                            <input type="text" id="parent_guardian_relationship" name="parent_guardian_relationship" value="{{ old('parent_guardian_relationship') }}" required class="input-field text-sm capitalize-words" placeholder="e.g., Mother, Father">
                        </div>

                        <div>
                            <label for="parent_guardian_phone" class="label-required text-sm">Phone Number</label>
                            <input type="tel" id="parent_guardian_phone" name="parent_guardian_phone" value="{{ old('parent_guardian_phone') }}" required class="input-field text-sm" placeholder="09123456789">
                        </div>

                        <div>
                            <label for="parent_guardian_email" class="text-sm">Email Address</label>
                            <input type="email" id="parent_guardian_email" name="parent_guardian_email" value="{{ old('parent_guardian_email') }}" class="input-field text-sm lowercase-email">
                        </div>

                        <div class="md:col-span-2">
                            <label for="parent_guardian_address" class="text-sm">Address</label>
                            <textarea id="parent_guardian_address" name="parent_guardian_address" rows="3" class="textarea-field text-sm capitalize-words" placeholder="Complete address">{{ old('parent_guardian_address') }}</textarea>
                        </div>
                    </div>

                    <div class="flex justify-between mt-10">
                        <button type="button" id="prevStepBtn2" class="btn-secondary px-8 text-sm">← Previous</button>
                        <button type="button" id="nextStepBtn2" class="btn-primary px-8 text-sm">Next →</button>
                    </div>
                </div>

                {{-- Step 3: Musical & Educational Background --}}
                <div id="step3" class="step-panel hidden">
                    <h2 class="text-2xl font-bold text-primary-dark mb-8 text-center">Musical & Educational Background</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Musical Background Section --}}
                        <div class="md:col-span-2">
                            <h3 class="text-lg font-bold text-warm-coral mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/>
                                </svg>
                                Musical Background
                            </h3>
                        </div>

                        <div>
                            <label for="instrument_id" class="label-required text-sm">Primary Instrument</label>
                            <select id="instrument_id" name="instrument_id" required class="select-field text-sm">
                                <option value="">Select instrument</option>
                                @foreach($instruments as $instrument)
                                <option value="{{ $instrument->instrument_id }}" {{ old('instrument_id') == $instrument->instrument_id ? 'selected' : '' }}>
                                    {{ $instrument->instrument_name }}
                                </option>
                                @endforeach
                            </select>
                            @error('instrument_id')<p class="error-text text-xs">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="skill_level" class="label-required text-sm">Skill Level</label>
                            <select id="skill_level" name="skill_level" required class="select-field text-sm">
                                <option value="">Select skill level</option>
                                <option value="beginner" {{ old('skill_level') == 'beginner' ? 'selected' : '' }}>Beginner</option>
                                <option value="intermediate" {{ old('skill_level') == 'intermediate' ? 'selected' : '' }}>Intermediate</option>
                                <option value="advanced" {{ old('skill_level') == 'advanced' ? 'selected' : '' }}>Advanced</option>
                                <option value="expert" {{ old('skill_level') == 'expert' ? 'selected' : '' }}>Expert</option>
                            </select>
                            @error('skill_level')<p class="error-text text-xs">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="preferred_genre_id" class="text-sm">Preferred Genre</label>
                            <select id="preferred_genre_id" name="preferred_genre_id" class="select-field text-sm">
                                <option value="">Select genre</option>
                                @foreach($genres as $genre)
                                <option value="{{ $genre->genre_id }}" {{ old('preferred_genre_id') == $genre->genre_id ? 'selected' : '' }}>
                                    {{ $genre->genre_name }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Dynamic Secondary Instruments --}}
                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-primary-dark mb-4">Secondary Instruments (Optional)</label>
                            <div id="secondary-instruments-container" class="space-y-4">
                                <!-- Dynamic fields added by JS -->
                            </div>
                            <button type="button" id="add-instrument-btn" class="mt-4 text-forest-green hover:text-primary-dark font-medium flex items-center gap-2 text-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                Add Instrument
                            </button>
                        </div>

                        <div class="md:col-span-2">
                            <label for="previous_music_experience" class="text-sm">Previous Music Experience</label>
                            <textarea id="previous_music_experience" name="previous_music_experience" rows="4" class="textarea-field text-sm" placeholder="Tell us about your previous music learning experience">{{ old('previous_music_experience') }}</textarea>
                        </div>

                        <div class="md:col-span-2">
                            <label for="music_goals" class="text-sm">Music Goals</label>
                            <textarea id="music_goals" name="music_goals" rows="4" class="textarea-field text-sm" placeholder="What do you hope to achieve through music lessons?">{{ old('music_goals') }}</textarea>
                        </div>

                        {{-- Educational Background Section --}}
                        <div class="md:col-span-2 section-divider pt-6">
                            <h3 class="text-lg font-bold text-secondary-blue mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                </svg>
                                Educational Background
                            </h3>
                        </div>

                        <div>
                            <label for="school_name" class="text-sm">School Name</label>
                            <input type="text" id="school_name" name="school_name" value="{{ old('school_name') }}" class="input-field text-sm capitalize-words">
                        </div>

                        <div>
                            <label for="grade_level" class="text-sm">Grade Level</label>
                            <select id="grade_level" name="grade_level" class="select-field text-sm">
                                <option value="">Select grade level</option>
                                <option value="Kindergarten">Kindergarten</option>
                                <option value="Grade 1">Grade 1</option>
                                <option value="Grade 2">Grade 2</option>
                                <option value="Grade 3">Grade 3</option>
                                <option value="Grade 4">Grade 4</option>
                                <option value="Grade 5">Grade 5</option>
                                <option value="Grade 6">Grade 6</option>
                                <option value="Grade 7">Grade 7</option>
                                <option value="Grade 8">Grade 8</option>
                                <option value="Grade 9">Grade 9</option>
                                <option value="Grade 10">Grade 10</option>
                                <option value="Grade 11">Grade 11</option>
                                <option value="Grade 12">Grade 12</option>
                                <option value="1st Year College">1st Year College</option>
                                <option value="2nd Year College">2nd Year College</option>
                                <option value="3rd Year College">3rd Year College</option>
                                <option value="4th Year College">4th Year College</option>
                                <option value="Graduate">Graduate</option>
                                <option value="Not in School">Not in School</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex justify-between mt-10">
                        <button type="button" id="prevStepBtn3" class="btn-secondary px-8 text-sm">← Previous</button>
                        <button type="submit" class="btn-primary px-8 text-sm">Complete Registration</button>
                    </div>
                </div>
            </form>
        </div>

        <div class="text-center mt-8">
            <p class="text-sm text-secondary-blue">
                Already have an account? 
                <a href="{{ route('login') }}" class="font-bold text-primary-dark hover:text-secondary-blue underline">Sign in</a>
            </p>
        </div>
    </div>
</body>
</html>