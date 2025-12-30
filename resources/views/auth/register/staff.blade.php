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
    <title>Staff Registration - Music Lab</title>

    {{-- External CSS and JS via Vite --}}
    @vite(['resources/css/style.css', 'resources/js/script.js'])

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="bg-light-gray min-h-screen py-8 px-4">

    <div class="max-w-4xl mx-auto">
        
        {{-- Header with SVG Icon --}}
        <div class="text-center mb-8 animate-fade-in">
            <div class="inline-flex items-center justify-center w-20 h-20 bg-primary-dark rounded-full mb-4 shadow-xl">
                <svg class="w-10 h-10 text-accent-yellow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
            </div>
            <h1 class="text-3xl font-bold text-primary-dark mb-2">Staff Registration</h1>
            <p class="text-secondary-blue font-medium">Join our team and keep Music Lab running smoothly</p>
        </div>

        {{-- Main Card --}}
        <div class="card animate-fade-in">
            <div class="p-6 md:p-10">
                
                {{-- Validation Errors --}}
                @if($errors->any())
                <div class="mb-8 p-5 bg-red-50 border-l-4 border-red-500 rounded-r-lg">
                    <div class="flex items-start">
                        <svg class="w-6 h-6 text-red-500 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
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

                <form method="POST" action="{{ route('register.staff.process') }}" class="space-y-8">
                    @csrf

                    {{-- Section 1: Account Information --}}
                    <div class="section-divider">
                        <div class="flex items-center mb-6">
                            <div class="bg-primary-dark text-accent-yellow rounded-full w-10 h-10 flex items-center justify-center font-bold mr-3">1</div>
                            <h2 class="text-2xl font-bold text-primary-dark">Account Information</h2>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="md:col-span-2">
                                <label for="user_email" class="block text-sm font-semibold text-primary-dark mb-2 label-required">Email Address</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-secondary-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                    <input type="email" id="user_email" name="user_email" value="{{ old('user_email') }}" required class="input-field" placeholder="your.email@example.com">
                                </div>
                                @error('user_email')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label for="user_password" class="block text-sm font-semibold text-primary-dark mb-2 label-required">Password</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-secondary-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                        </svg>
                                    </div>
                                    <input type="password" id="user_password" name="user_password" required class="input-field" placeholder="Minimum 8 characters">
                                    <button type="button" onclick="togglePassword()" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                        <svg id="eye-icon" class="h-5 w-5 text-secondary-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </button>
                                </div>
                                @error('user_password')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label for="user_password_confirmation" class="block text-sm font-semibold text-primary-dark mb-2 label-required">Confirm Password</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-secondary-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </div>
                                    <input type="password" id="user_password_confirmation" name="user_password_confirmation" required class="input-field">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Section 2: Personal Information --}}
                    <div class="section-divider">
                        <div class="flex items-center mb-6">
                            <div class="bg-primary-dark text-accent-yellow rounded-full w-10 h-10 flex items-center justify-center font-bold mr-3">2</div>
                            <h2 class="text-2xl font-bold text-primary-dark">Personal Information</h2>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="first_name" class="block text-sm font-semibold text-primary-dark mb-2 label-required">First Name</label>
                                <input type="text" id="first_name" name="first_name" value="{{ old('first_name') }}" required class="input-field">
                                @error('first_name')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label for="last_name" class="block text-sm font-semibold text-primary-dark mb-2 label-required">Last Name</label>
                                <input type="text" id="last_name" name="last_name" value="{{ old('last_name') }}" required class="input-field">
                                @error('last_name')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label for="middle_name" class="block text-sm font-semibold text-primary-dark mb-2">Middle Name</label>
                                <input type="text" id="middle_name" name="middle_name" value="{{ old('middle_name') }}" class="input-field">
                            </div>

                            <div>
                                <label for="phone" class="block text-sm font-semibold text-primary-dark mb-2 label-required">Phone Number</label>
                                <input type="tel" id="phone" name="phone" value="{{ old('phone') }}" required pattern="[0-9]{11}" maxlength="11" class="input-field" placeholder="09123456789">
                                @error('phone')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label for="date_of_birth" class="block text-sm font-semibold text-primary-dark mb-2">Date of Birth</label>
                                <input type="date" id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth') }}" class="input-field">
                            </div>

                            <div>
                                <label for="gender" class="block text-sm font-semibold text-primary-dark mb-2">Gender</label>
                                <select id="gender" name="gender" class="select-field">
                                    <option value="">Select gender</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                    <option value="Other">Other</option>
                                    <option value="Prefer not to say">Prefer not to say</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Submit Buttons --}}
                    <div class="flex flex-col md:flex-row gap-4 pt-6">
                        <button type="submit" class="btn-primary flex-1">Create Staff Account</button>
                        <a href="{{ route('register') }}" class="btn-secondary flex-1 text-center flex items-center justify-center">Back to Role Selection</a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Footer Link --}}
        <div class="text-center mt-8">
            <p class="text-sm text-secondary-blue">
                Already have an account? 
                <a href="{{ route('login') }}" class="font-bold text-primary-dark hover:text-secondary-blue underline">Sign in</a>
            </p>
        </div>
    </div>
</body>
</html>