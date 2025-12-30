{{-- ============================================================================
    STUDENT REGISTRATION FORM  |  resources/views/auth/register/student.blade.php
    multi-section registration form with validation
    ============================================================================ --}}

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Student Registration - Music Lab</title>
    
    {{-- Tailwind CSS via Vite --}}
    @vite(['resources/css/style.css', 'resources/js/script.js'])
    
    {{-- Google Fonts --}}
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>

<body class="min-h-screen bg-gradient-to-br from-gray-100 to-gray-200 py-8 px-4 font-poppins">
    
    <div class="max-w-4xl mx-auto">
        
        {{-- Header Section --}}
        <div class="text-center mb-8 animate-fade-in">
            <div class="flex justify-center mb-4">
                <div class="bg-gradient-to-br from-blue-500 to-blue-600 p-5 rounded-full shadow-xl">
                    <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                </div>
            </div>
            <h1 class="text-3xl font-bold text-primary-dark mb-2">Student Registration</h1>
            <p class="text-secondary-blue font-medium">Join Music Lab and start your musical journey</p>
        </div>

        {{-- Registration Form Card --}}
        <div class="card animate-fade-in">
            <div class="p-6 md:p-10">
                
                {{-- Error Summary Alert --}}
                @if($errors->any())
                <div class="mb-8 p-5 bg-red-50 border-l-4 border-red-500 rounded-r-lg">
                    <div class="flex items-start">
                        <svg class="w-6 h-6 text-red-500 mr-3 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        <div>
                            <p class="font-bold text-red-800 mb-2">Please fix the following errors:</p>
                            <ul class="list-disc list-inside text-sm text-red-700 space-y-1">
                                @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
                @endif

                {{-- Registration Form --}}
                <form method="POST" action="{{ route('register.student.process') }}" class="space-y-8">
                    @csrf

                    {{-- SECTION 1: Account Information --}}
                    <div class="section-divider">
                        <div class="flex items-center mb-6">
                            <div class="bg-primary-dark text-accent-yellow rounded-full w-10 h-10 flex items-center justify-center font-bold mr-3">
                                1
                            </div>
                            <h2 class="text-2xl font-bold text-primary-dark">Account Information</h2>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            
                            {{-- Email Address --}}
                            <div class="md:col-span-2">
                                <label for="user_email" class="block text-sm font-semibold text-primary-dark mb-2 label-required">
                                    Email Address
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-secondary-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                    <input type="email" id="user_email" name="user_email" 
                                           value="{{ old('user_email') }}" required
                                           class="input-field" placeholder="your.email@example.com">
                                </div>
                                @error('user_email')
                                <p class="mt-2 text-sm text-red-600 flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $message }}
                                </p>
                                @enderror
                            </div>

                            {{-- Password --}}
                            <div>
                                <label for="user_password" class="block text-sm font-semibold text-primary-dark mb-2 label-required">
                                    Password
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-secondary-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                        </svg>
                                    </div>
                                    <input type="password" id="user_password" name="user_password" required
                                           class="input-field" placeholder="Minimum 8 characters">
                                    <button type="button" onclick="togglePassword()"
                                            class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                        <svg id="eye-icon" class="h-5 w-5 text-secondary-blue hover:text-primary-dark transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </button>
                                </div>
                                @error('user_password')
                                <p class="mt-2 text-sm text-red-600 flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $message }}
                                </p>
                                @enderror
                            </div>

                            {{-- Confirm Password --}}
                            <div>
                                <label for="user_password_confirmation" class="block text-sm font-semibold text-primary-dark mb-2 label-required">
                                    Confirm Password
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-secondary-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </div>
                                    <input type="password" id="user_password_confirmation" name="user_password_confirmation" required
                                           class="input-field" placeholder="Re-enter your password">
                                </div>
                            </div>

                        </div>
                    </div>

                    {{-- SECTION 2: Personal Information --}}
                    <div class="section-divider">
                        <div class="flex items-center mb-6">
                            <div class="bg-primary-dark text-accent-yellow rounded-full w-10 h-10 flex items-center justify-center font-bold mr-3">
                                2
                            </div>
                            <h2 class="text-2xl font-bold text-primary-dark">Personal Information</h2>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            
                            {{-- First Name --}}
                            <div>
                                <label for="first_name" class="block text-sm font-semibold text-primary-dark mb-2 label-required">
                                    First Name
                                </label>
                                <input type="text" id="first_name" name="first_name" 
                                       value="{{ old('first_name') }}" required
                                       class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-secondary-blue focus:border-secondary-blue transition-all">
                                @error('first_name')
                                <p class="mt-2 text-sm text-red-600 flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $message }}
                                </p>
                                @enderror
                            </div>

                            {{-- Last Name --}}
                            <div>
                                <label for="last_name" class="block text-sm font-semibold text-primary-dark mb-2 label-required">
                                    Last Name
                                </label>
                                <input type="text" id="last_name" name="last_name" 
                                       value="{{ old('last_name') }}" required
                                       class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-secondary-blue focus:border-secondary-blue transition-all">
                                @error('last_name')
                                <p class="mt-2 text-sm text-red-600 flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $message }}
                                </p>
                                @enderror
                            </div>

                            {{-- Middle Name (Optional) --}}
                            <div>
                                <label for="middle_name" class="block text-sm font-semibold text-primary-dark mb-2">
                                    Middle Name <span class="text-gray-500 text-xs">(Optional)</span>
                                </label>
                                <input type="text" id="middle_name" name="middle_name" 
                                       value="{{ old('middle_name') }}"
                                       class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-secondary-blue focus:border-secondary-blue transition-all">
                            </div>

                            {{-- Phone Number --}}
                            <div>
                                <label for="phone" class="block text-sm font-semibold text-primary-dark mb-2 label-required">
                                    Phone Number
                                </label>
                                <input type="tel" id="phone" name="phone" 
                                       value="{{ old('phone') }}" required pattern="[0-9]{11}" maxlength="11"
                                       class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-secondary-blue focus:border-secondary-blue transition-all"
                                       placeholder="09123456789">
                                <p class="mt-1 text-xs text-gray-500">11 digits (e.g., 09123456789)</p>
                                @error('phone')
                                <p class="mt-2 text-sm text-red-600 flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $message }}
                                </p>
                                @enderror
                            </div>

                            {{-- Date of Birth --}}
                            <div>
                                <label for="date_of_birth" class="block text-sm font-semibold text-primary-dark mb-2">
                                    Date of Birth
                                </label>
                                <input type="date" id="date_of_birth" name="date_of_birth" 
                                       value="{{ old('date_of_birth') }}"
                                       class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-secondary-blue focus:border-secondary-blue transition-all">
                            </div>

                            {{-- Gender --}}
                            <div>
                                <label for="gender" class="block text-sm font-semibold text-primary-dark mb-2">
                                    Gender
                                </label>
                                <select id="gender" name="gender" 
                                        class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-secondary-blue focus:border-secondary-blue transition-all cursor-pointer">
                                    <option value="">Select gender</option>
                                    <option value="Male" {{ old('gender') == 'Male' ? 'selected' : '' }}>Male</option>
                                    <option value="Female" {{ old('gender') == 'Female' ? 'selected' : '' }}>Female</option>
                                    <option value="Other" {{ old('gender') == 'Other' ? 'selected' : '' }}>Other</option>
                                    <option value="Prefer not to say" {{ old('gender') == 'Prefer not to say' ? 'selected' : '' }}>Prefer not to say</option>
                                </select>
                            </div>

                        </div>
                    </div>

                    {{-- SECTION 3: Musical Information --}}
                    <div>
                        <div class="flex items-center mb-6">
                            <div class="bg-primary-dark text-accent-yellow rounded-full w-10 h-10 flex items-center justify-center font-bold mr-3">
                                3
                            </div>
                            <h2 class="text-2xl font-bold text-primary-dark">Musical Information</h2>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            
                            {{-- Primary Instrument --}}
                            <div>
                                <label for="instrument_id" class="block text-sm font-semibold text-primary-dark mb-2">
                                    Primary Instrument
                                </label>
                                <select id="instrument_id" name="instrument_id" 
                                        class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-secondary-blue focus:border-secondary-blue transition-all cursor-pointer">
                                    <option value="">Select instrument</option>
                                    @foreach($instruments as $instrument)
                                    <option value="{{ $instrument->instrument_id }}" {{ old('instrument_id') == $instrument->instrument_id ? 'selected' : '' }}>
                                        {{ $instrument->instrument_name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Skill Level --}}
                            <div>
                                <label for="skill_level" class="block text-sm font-semibold text-primary-dark mb-2">
                                    Skill Level
                                </label>
                                <select id="skill_level" name="skill_level" 
                                        class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-secondary-blue focus:border-secondary-blue transition-all cursor-pointer">
                                    <option value="">Select skill level</option>
                                    <option value="beginner" {{ old('skill_level') == 'beginner' ? 'selected' : '' }}>Beginner</option>
                                    <option value="intermediate" {{ old('skill_level') == 'intermediate' ? 'selected' : '' }}>Intermediate</option>
                                    <option value="advanced" {{ old('skill_level') == 'advanced' ? 'selected' : '' }}>Advanced</option>
                                    <option value="expert" {{ old('skill_level') == 'expert' ? 'selected' : '' }}>Expert</option>
                                </select>
                            </div>

                            {{-- Music Goals --}}
                            <div class="md:col-span-2">
                                <label for="music_goals" class="block text-sm font-semibold text-primary-dark mb-2">
                                    Music Goals
                                </label>
                                <textarea id="music_goals" name="music_goals" rows="4" 
                                          class="textarea-field"
                                          placeholder="What do you hope to achieve through music lessons?">{{ old('music_goals') }}</textarea>
                            </div>

                        </div>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="flex flex-col md:flex-row gap-4 pt-6">
                        <button type="submit" class="btn-primary flex-1">
                            <span class="flex items-center justify-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                                </svg>
                                Create Student Account
                            </span>
                        </button>
                        <a href="{{ route('register') }}" class="btn-secondary flex-1 text-center flex items-center justify-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                            </svg>
                            Back to Role Selection
                        </a>
                    </div>

                </form>

            </div>
        </div>

        {{-- Login Link --}}
        <div class="text-center mt-8">
            <p class="text-sm text-secondary-blue">
                Already have an account? 
                <a href="{{ route('login') }}" class="font-bold text-primary-dark hover:text-secondary-blue underline transition-colors">Sign in</a>
            </p>
        </div>

    </div>

</body>
</html>