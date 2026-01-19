{{-- resources/views/auth/create-account.blade.php --}}
{{-- Password Setup Page - FINAL STEP OF REGISTRATION --}}
{{-- Email is read-only (from session), user only enters password --}}

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Create Your Password - Music Lab</title>
    
    @vite(['resources/css/style.css', 'resources/js/script.js'])
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>

<body class="bg-light-gray min-h-screen flex items-center justify-center px-8 py-10">

    <div class="card max-w-md w-full animate-fade-in">
        <div class="p-6 md:p-8">

            {{-- Header with Logo --}}
            <div class="text-center mb-8">
                    {{-- Clickable Logo - Links to Home/Welcome Page --}}
                    <a href="{{ route('home') }}" class="inline-block hover:opacity-90 transition-opacity duration-300">
                        <img 
                            src="https://res.cloudinary.com/dibojpqg2/image/upload/v1766933637/music-lab-logo_1_lfcsqw.png" 
                            alt="Music Lab - Lessons & Instruments" 
                            class="mx-auto h-20 md:h-28 object-contain drop-shadow-2xl">
                    </a>
                <h1 class="text-xl font-bold text-primary-dark">Create your account</h1>
                <p class="text-sm text-secondary-blue mt-1">Final step to activate your account</p>
            </div>

            {{-- Success Message --}}
            @if(session('success'))
            <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6 rounded">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <p class="text-green-700 text-sm">{{ session('success') }}</p>
                </div>
            </div>
            @endif

            {{-- Error Messages --}}
            @if($errors->any())
            <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded">
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-red-500 mr-3 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <div>
                        @foreach($errors->all() as $error)
                            <p class="text-red-700 text-sm">{{ $error }}</p>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            {{-- Form --}}
            <form method="POST" action="{{ route('account.create.process') }}">
                @csrf
                
                {{-- Email (Read-only - from session) --}}
                <div class="mb-6">
                    <label for="user_email" class="block text-xs font-semibold text-primary-dark mb-1">
                        Your Email Address
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-secondary-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <input type="email" 
                               id="user_email" 
                               name="user_email" 
                               value="{{ $email }}" 
                               readonly 
                               class="input-field text-sm bg-gray-100 cursor-not-allowed font-semibold text-primary-dark">
                    </div>
                    <p class="text-xs text-secondary-blue mt-2">
                        ✓ This is the email you registered with. You'll use it to login.
                    </p>
                </div>

                {{-- Password Requirements Notice --}}
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-4">
                    <h3 class="text-sm font-semibold text-blue-900 mb-2">Password Requirements:</h3>
                    <ul class="text-xs text-blue-800 space-y-1">
                        <li class="flex items-center">
                            <svg class="w-4 h-4 mr-2 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            Minimum 8 characters
                        </li>
                        <li class="flex items-center">
                            <svg class="w-4 h-4 mr-2 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            Both passwords must match
                        </li>
                    </ul>
                </div>

                {{-- Password --}}
                <div class="mb-6">
                    <label for="user_password" class="block text-xs font-semibold text-primary-dark mb-1 label-required">
                        Create a Password
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-secondary-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </div>
                        <input type="password" 
                               id="user_password" 
                               name="user_password" 
                               required 
                               class="input-field text-sm @error('user_password') border-red-500 @enderror" 
                               placeholder="Minimum 8 characters"
                               autocomplete="new-password">
                        <button type="button" 
                                class="password-toggle absolute inset-y-0 right-0 pr-3 flex items-center z-10 cursor-pointer">
                            <svg class="h-5 w-5 text-secondary-blue hover:text-primary-dark transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </button>
                    </div>
                    @error('user_password')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Confirm Password --}}
                <div class="mb-8">
                    <label for="user_password_confirmation" class="block text-xs font-semibold text-primary-dark mb-1 label-required">
                        Confirm Your Password
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-secondary-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <input type="password" 
                               id="user_password_confirmation" 
                               name="user_password_confirmation" 
                               required 
                               class="input-field text-sm @error('user_password_confirmation') border-red-500 @enderror" 
                               placeholder="Re-enter your password"
                               autocomplete="new-password">
                    </div>
                    @error('user_password_confirmation')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Submit Button --}}
                <button type="submit" class="btn-primary w-full flex items-center justify-center group text-sm py-2">
                    <span>Create Account & Login</span>
                    <svg class="w-5 h-5 ml-2 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </button>
            </form>

            {{-- Divider --}}
            <div class="relative my-6">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-300"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="px-2 bg-white text-gray-500">or</span>
                </div>
            </div>

            {{-- Link to Start Over --}}
            <div class="text-center">
                <a href="{{ route('register') }}" class="text-sm text-secondary-blue hover:text-primary-dark font-medium underline">
                    ← Start registration over
                </a>
            </div>

        </div>
    </div>

    {{-- Session Timeout Warning (Optional) --}}
    <script>
        // Warn user if session is about to expire (25 minutes)
        setTimeout(() => {
            if (confirm('Your registration session will expire soon. Please complete your password setup now to avoid starting over.')) {
                document.getElementById('user_password').focus();
            }
        }, 25 * 60 * 1000); // 25 minutes
    </script>

</body>
</html>