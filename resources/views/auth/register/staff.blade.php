{{-- ============================================================================ --}}
{{-- FILE: resources/views/auth/register/staff.blade.php --}}
{{-- ALL-AROUND STAFF REGISTRATION FORM --}}
{{-- ============================================================================ --}}

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>All-Around Staff Registration - Music Lab</title>
    @vite(['resources/css/style.css', 'resources/js/script.js'])
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="bg-light-gray min-h-screen py-8 px-4">

    <div id="blurOverlay" class="blur-overlay">
        <p id="motivationText" class="motivation-text"></p>
    </div>

    <div class="max-w-5xl mx-auto">
        <div class="text-center mb-10 animate-fade-in">
            <a href="{{ route('home') }}" class="inline-block hover:opacity-90 transition-opacity">
                <img src="https://res.cloudinary.com/dibojpqg2/image/upload/v1766933637/music-lab-logo_1_lfcsqw.png" 
                     alt="Music Lab" class="mx-auto h-28 md:h-36 object-contain drop-shadow-2xl">
            </a>
            <h1 class="text-3xl font-bold text-primary-dark mt-6">All-Around Staff Registration</h1>
            <p class="text-secondary-blue mt-2 text-sm">Join our team and keep Music Lab running smoothly</p>
        </div>

        <div class="card relative overflow-hidden py-8 px-8">
            <form method="POST" action="{{ route('register.staff.process') }}" id="allAroundStaffForm">
                @csrf

                <div class="flex justify-center mb-8 step-indicator">
                    <div class="flex items-center space-x-8">
                        <div class="step-item active">
                            <div class="step-circle">1</div>
                            <span class="step-label text-sm">Personal</span>
                        </div>
                        <div class="step-line"></div>
                        <div class="step-item">
                            <div class="step-circle">2</div>
                            <span class="step-label text-sm">Emergency</span>
                        </div>
                        <div class="step-line"></div>
                        <div class="step-item">
                            <div class="step-circle">3</div>
                            <span class="step-label text-sm">Professional</span>
                        </div>
                    </div>
                </div>

                {{-- STEP 1: PERSONAL --}}
                <div id="step1" class="step-panel active">
                    <h2 class="text-2xl font-bold text-primary-dark mb-8 text-center">Personal Information</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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
                            <label for="nationality" class="label-required text-sm">Nationality</label>
                            <input type="text" id="nationality" name="nationality" value="{{ old('nationality') }}" required class="input-field text-sm capitalize-words" placeholder="e.g., Filipino">
                        </div>
                    </div>
                    <div class="flex flex-col md:flex-row gap-6 pt-8">
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

                {{-- STEP 2: EMERGENCY --}}
                <div id="step2" class="step-panel hidden">
                    <h2 class="text-2xl font-bold text-primary-dark mb-8 text-center">Emergency Contact</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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
                    </div>
                    <div class="flex justify-between mt-10">
                        <button type="button" id="prevStepBtn2" class="btn-secondary px-8 text-sm">← Previous</button>
                        <button type="button" id="nextStepBtn2" class="btn-primary px-8 text-sm">Next →</button>
                    </div>
                </div>

                {{-- STEP 3: PROFESSIONAL --}}
                <div id="step3" class="step-panel hidden">
                    <h2 class="text-2xl font-bold text-primary-dark mb-8 text-center">Professional Information</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label for="position" class="label-required text-sm">Position</label>
                            <input type="text" id="position" name="position" value="{{ old('position') }}" required class="input-field text-sm capitalize-words" placeholder="e.g., Office Assistant">
                        </div>
                        <div class="md:col-span-2">
                            <label for="education_level" class="label-required text-sm">Highest Education Level</label>
                            <select id="education_level" name="education_level" required class="select-field text-sm">
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