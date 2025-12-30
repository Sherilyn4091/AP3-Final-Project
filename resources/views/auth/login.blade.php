{{-- LOGIN PAGE --}}

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - Music Lab</title>
    
    {{-- Tailwind CSS via Vite --}}
    @vite(['resources/css/style.css', 'resources/js/script.js'])
    
    {{-- Google Fonts --}}
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>

<body class="min-h-screen bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center p-10 font-poppins">
    
    <div class="w-full max-w-md">
        
        {{-- Login Card --}}
        <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">
            
            {{-- Header Section with Official Music Lab Logo --}}
            <div class="bg-gradient-to-r from-primary-dark to-primary-darker p-8 text-center">
                {{-- Clickable Logo - Links to Home/Welcome Page --}}
                <a href="{{ route('home') }}" class="inline-block hover:opacity-90 transition-opacity duration-300">
                    <img 
                        src="https://res.cloudinary.com/dibojpqg2/image/upload/v1766933637/music-lab-logo_1_lfcsqw.png" 
                        alt="Music Lab - Lessons & Instruments" 
                        class="mx-auto h-28 md:h-40 object-contain drop-shadow-2xl"
                    >
                </a>
            </div>

            {{-- Form Section --}}
            <div class="p-8">
                
                {{-- Welcome Message --}}
                <div class="text-center mb-8">
                    <h2 class="text-2xl font-bold text-primary-dark mb-2">Welcome Back</h2>
                    <p class="text-secondary-blue text-sm">Sign in to access your dashboard</p>
                </div>

                {{-- Success Message (after registration) --}}
                @if(session('success'))
                <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 rounded-r-lg">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <p class="text-green-700 font-medium text-sm">{{ session('success') }}</p>
                    </div>
                </div>
                @endif

                {{-- Error Messages --}}
                @if($errors->any())
                <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-r-lg">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-red-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        <p class="text-red-700 font-medium text-sm">{{ $errors->first() }}</p>
                    </div>
                </div>
                @endif

                {{-- Login Form --}}
                <form method="POST" action="{{ route('login.process') }}" class="space-y-6">
                    @csrf

                    {{-- Email Address Field --}}
                    <div>
                        <label for="user_email" class="block text-sm font-semibold text-primary-dark mb-2">
                            Email Address
                        </label>
                        <div class="relative">
                            {{-- Email Icon --}}
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-secondary-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/>
                                </svg>
                            </div>
                            <input 
                                type="email" 
                                id="user_email" 
                                name="user_email" 
                                value="{{ old('user_email') }}"
                                placeholder="your.email@example.com"
                                required
                                class="input-field"
                            >
                        </div>
                        @error('user_email')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Password Field --}}
                    <div>
                        <label for="user_password" class="block text-sm font-semibold text-primary-dark mb-2">
                            Password
                        </label>
                        <div class="relative">
                            {{-- Lock Icon --}}
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-secondary-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            </div>
                            <input 
                                type="password" 
                                id="user_password" 
                                name="user_password" 
                                placeholder="Enter your password"
                                required
                                class="input-field"
                            >
                            {{-- Toggle Password Visibility Button --}}
                            <button 
                                type="button" 
                                onclick="togglePassword()"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center"
                            >
                                <svg id="eye-icon" class="h-5 w-5 text-secondary-blue hover:text-primary-dark transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </button>
                        </div>
                        @error('user_password')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Sign In Button --}}
                    <button 
                        type="submit"
                        class="btn-primary"
                    >
                        <span class="flex items-center justify-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                            </svg>
                            Sign In
                        </span>
                    </button>

                </form>

                {{-- Register Link --}}
                <div class="mt-8 text-center">
                    <div class="inline-flex items-center justify-center p-4 border-2 border-secondary-blue rounded-xl hover:bg-accent-yellow-light transition-all duration-300">
                        <p class="text-sm">
                            <span class="text-secondary-blue font-medium">Don't have an account?</span>
                            <a href="{{ route('register') }}" class="font-bold text-primary-dark hover:text-secondary-blue underline ml-2 transition-colors">
                                Register
                            </a>
                        </p>
                    </div>
                </div>

                {{-- Forgot Password Link --}}
                <div class="mt-4 text-center">
                    <a href="#" class="text-sm text-secondary-blue hover:text-primary-dark font-medium transition-colors inline-flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Forgot your password?
                    </a>
                </div>

            </div>
        </div>

    </div>

</body>
</html>