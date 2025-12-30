{{-- resources/views/auth/register/instructor.blade.php --}}
{{-- Multi-Step Instructor Registration - Clean Version (No Inline CSS/JS) --}}

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Instructor Registration - Music Lab</title>

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
            <h1 class="text-3xl font-bold text-primary-dark mt-6">Instructor Registration</h1>
            <p class="text-secondary-blue mt-2">Share your musical expertise with our students</p>
        </div>

        <div class="card relative overflow-hidden py-8 px-8">
            <form method="POST" action="{{ route('register.instructor.process') }}" id="instructorForm">
                @csrf

                {{-- Step Progress Indicator --}}
                <div class="flex justify-center mb-8 step-indicator">
                    <div class="flex items-center space-x-8">
                        <div class="step-item active">
                            <div class="step-circle">1</div>
                            <span class="step-label">Personal</span>
                        </div>
                        <div class="step-line"></div>
                        <div class="step-item">
                            <div class="step-circle">2</div>
                            <span class="step-label">Emergency</span>
                        </div>
                        <div class="step-line"></div>
                        <div class="step-item">
                            <div class="step-circle">3</div>
                            <span class="step-label">Professional</span>
                        </div>
                    </div>
                </div>

                {{-- Step 1: Personal Information --}}
                <div id="step1" class="step-panel active">
                    <h2 class="text-2xl font-bold text-primary-dark mb-8 text-center">Personal Information</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Name Fields --}}
                        <div>
                            <label for="first_name" class="label-required">First Name</label>
                            <input type="text" id="first_name" name="first_name" value="{{ old('first_name') }}" required class="input-field capitalize-words">
                            @error('first_name')<p class="error-text">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="last_name" class="label-required">Last Name</label>
                            <input type="text" id="last_name" name="last_name" value="{{ old('last_name') }}" required class="input-field capitalize-words">
                            @error('last_name')<p class="error-text">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="middle_name">Middle Name</label>
                            <input type="text" id="middle_name" name="middle_name" value="{{ old('middle_name') }}" class="input-field capitalize-words">
                        </div>
                        <div>
                            <label for="suffix">Suffix</label>
                            <input type="text" id="suffix" name="suffix" value="{{ old('suffix') }}" class="input-field" placeholder="e.g., Jr., III">
                        </div>

                        {{-- Contact --}}
                        <div>
                            <label for="phone" class="label-required">Phone Number</label>
                            <input type="tel" id="phone" name="phone" value="{{ old('phone') }}" required class="input-field" placeholder="09123456789">
                            @error('phone')<p class="error-text">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="user_email" class="label-required">Email Address</label>
                            <input type="email" id="user_email" name="user_email" value="{{ old('user_email') }}" required class="input-field lowercase-email">
                            @error('user_email')<p class="error-text">{{ $message }}</p>@enderror
                        </div>

                        {{-- Address --}}
                        <div class="md:col-span-2">
                            <label for="address_line1" class="label-required">Address Line 1</label>
                            <input type="text" id="address_line1" name="address_line1" value="{{ old('address_line1') }}" required class="input-field capitalize-words">
                        </div>
                        <div class="md:col-span-2">
                            <label for="address_line2">Address Line 2</label>
                            <input type="text" id="address_line2" name="address_line2" value="{{ old('address_line2') }}" class="input-field capitalize-words">
                        </div>
                        <div>
                            <label for="city" class="label-required">City/Municipality</label>
                            <input type="text" id="city" name="city" value="{{ old('city') }}" required class="input-field capitalize-words">
                        </div>
                        <div>
                            <label for="province" class="label-required">Province</label>
                            <input type="text" id="province" name="province" value="{{ old('province') }}" required class="input-field capitalize-words">
                        </div>
                        <div>
                            <label for="postal_code" class="label-required">Postal Code</label>
                            <input type="text" id="postal_code" name="postal_code" value="{{ old('postal_code') }}" required class="input-field">
                        </div>
                        <div>
                            <label for="country" class="label-required">Country</label>
                            <input type="text" id="country" name="country" value="{{ old('country', 'Philippines') }}" required class="input-field capitalize-words">
                        </div>

                        {{-- Personal Details --}}
                        <div>
                            <label for="date_of_birth" class="label-required">Date of Birth</label>
                            <input type="date" id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth') }}" required class="input-field">
                            <p class="text-sm text-secondary-blue mt-2">Age: <span id="age-display" class="font-semibold">—</span></p>
                        </div>
                        <div>
                            <label for="gender" class="label-required">Gender</label>
                            <select id="gender" name="gender" required class="select-field">
                                <option value="">Select gender</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Other">Other</option>
                                <option value="Prefer not to say">Prefer not to say</option>
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label for="nationality">Nationality</label>
                            <input type="text" id="nationality" name="nationality" value="{{ old('nationality') }}" class="input-field capitalize-words" placeholder="e.g., Filipino">
                        </div>
                    </div>

                    {{-- Action Buttons - Clean & Professional --}}
                    <div class="flex flex-col md:flex-row gap-6 pt-8 mt-auto">
                        {{-- Back Button --}}
                        <a href="{{ route('register') }}" 
                        class="btn-secondary flex-1 text-center flex items-center justify-center py-4">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                    d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                            </svg>
                            Back to Role Selection
                        </a>

                        {{-- Next Button (Step 1) --}}
                        <button type="button" 
                                id="nextStepBtn1" 
                                class="btn-primary flex-1 flex items-center justify-center py-4">
                            Next
                            <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                    d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Step 2: Emergency Contact --}}
                <div id="step2" class="step-panel hidden">
                    <h2 class="text-2xl font-bold text-primary-dark mb-8 text-center">Emergency Contact</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="emergency_contact_name" class="label-required">Name</label>
                            <input type="text" id="emergency_contact_name" name="emergency_contact_name" value="{{ old('emergency_contact_name') }}" required class="input-field capitalize-words">
                        </div>

                        <div>
                            <label for="emergency_contact_relationship" class="label-required">Relationship</label>
                            <input type="text" id="emergency_contact_relationship" name="emergency_contact_relationship" value="{{ old('emergency_contact_relationship') }}" required class="input-field capitalize-words">
                        </div>

                        <div class="md:col-span-2">
                            <label for="emergency_contact_phone" class="label-required">Phone Number</label>
                            <input type="tel" id="emergency_contact_phone" name="emergency_contact_phone" value="{{ old('emergency_contact_phone') }}" required class="input-field" placeholder="09123456789">
                        </div>
                    </div>

                    <div class="flex justify-between mt-10">
                        <button type="button" id="prevStepBtn2" class="btn-secondary px-8">← Previous</button>
                        <button type="button" id="nextStepBtn2" class="btn-primary px-8">Next →</button>
                    </div>
                </div>             

                {{-- Step 3: Professional Information --}}
                <div id="step3" class="step-panel hidden">
                    <h2 class="text-2xl font-bold text-primary-dark mb-8 text-center">Professional Information</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="education_level" class="label-required">Highest Education Level</label>
                            <select id="education_level" name="education_level" required class="select-field">
                                <option value="">Select level</option>
                                <option value="Elementary Graduate">Elementary Graduate</option>
                                <option value="High School Graduate">High School Graduate</option>
                                <option value="Senior High School Graduate">Senior High School Graduate</option>
                                <option value="College Undergraduate">College Undergraduate</option>
                                <option value="College Graduate">College Graduate</option>
                                <option value="Master's Degree">Master's Degree</option>
                                <option value="Doctorate Degree">Doctorate Degree</option>
                                <option value="Vocational/Technical Certificate">Vocational/Technical Certificate</option>
                            </select>
                        </div>

                        <div>
                            <label for="music_degree">Music Degree</label>
                            <input type="text" id="music_degree" name="music_degree" value="{{ old('music_degree') }}" class="input-field" placeholder="e.g., Bachelor of Music">
                        </div>

                        <div>
                            <label for="years_of_experience" class="label-required">Years of Teaching Experience</label>
                            <input type="number" id="years_of_experience" name="years_of_experience" value="{{ old('years_of_experience') }}" min="0" required class="input-field">
                        </div>

                        <div>
                            <label for="languages_spoken">Languages Spoken</label>
                            <input type="text" id="languages_spoken" name="languages_spoken" value="{{ old('languages_spoken') }}" class="input-field capitalize-words" placeholder="English, Filipino, Spanish">
                        </div>

                        <div class="md:col-span-2">
                            <label for="teaching_style">Teaching Style / Philosophy</label>
                            <textarea id="teaching_style" name="teaching_style" rows="4" class="textarea-field">{{ old('teaching_style') }}</textarea>
                        </div>

                        <div class="md:col-span-2">
                            <label for="bio">Professional Bio</label>
                            <textarea id="bio" name="bio" rows="5" class="textarea-field">{{ old('bio') }}</textarea>
                        </div>

                        {{-- Certifications --}}
                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-primary-dark mb-4">Certifications (Optional)</label>
                            <div id="certifications-container" class="space-y-4">
                                <!-- Dynamic fields added by JS -->
                            </div>
                            <button type="button" id="add-cert-btn" class="mt-4 text-secondary-blue hover:text-primary-dark font-medium flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                Add Certificate
                            </button>
                        </div>

                        {{-- Specializations --}}
                        <div class="md:col-span-2 section-divider pt-8">
                            <h3 class="text-xl font-bold text-primary-dark mb-6">Specializations</h3>
                            <p class="text-sm text-secondary-blue mb-6">Select all you can teach. Choose one as primary.</p>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                                @foreach($specializations as $spec)
                                <label class="checkbox-label">
                                    <input type="checkbox" name="specializations[]" value="{{ $spec->specialization_id }}" class="checkbox-custom" {{ in_array($spec->specialization_id, old('specializations', [])) ? 'checked' : '' }}>
                                    <span>{{ $spec->specialization_name }}</span>
                                </label>
                                @endforeach
                            </div>

                            <div>
                                <label for="primary_specialization" class="label-required">Primary Specialization</label>
                                <select name="primary_specialization" id="primary_specialization" required class="select-field">
                                    <option value="">Select primary</option>
                                    @foreach($specializations as $spec)
                                    <option value="{{ $spec->specialization_id }}" {{ old('primary_specialization') == $spec->specialization_id ? 'selected' : '' }}>
                                        {{ $spec->specialization_name }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('primary_specialization')<p class="error-text">{{ $message }}</p>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-between mt-10">
                        <button type="button" id="prevStepBtn3" class="btn-secondary px-8">← Previous</button>
                        <button type="submit" class="btn-primary px-8">Complete Registration</button>
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