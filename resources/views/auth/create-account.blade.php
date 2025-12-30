{{-- resources/views/auth/create-account.blade.php --}}
{{-- Password Setup Page After Registration --}}

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Create Account - Music Lab</title>
    
    @vite(['resources/css/style.css', 'resources/js/script.js'])
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>

<body class="bg-light-gray min-h-screen flex items-center justify-center px-4">

    <div class="card max-w-md w-full animate-fade-in">
        <div class="p-8 md:p-10">

            {{-- Header with Logo --}}
            <div class="text-center mb-8">
                <img src="https://res.cloudinary.com/dibojpqg2/image/upload/v1766933637/music-lab-logo_1_lfcsqw.png" 
                     alt="Music Lab Logo" 
                     class="mx-auto h-24 md:h-32 object-contain mb-4">
                <h1 class="text-2xl font-bold text-primary-dark">Create Your Password</h1>
                <p class="text-secondary-blue mt-2">One last step to access your dashboard</p>
            </div>

            {{-- Form --}}
            <form method="POST" action="{{ route('account.create.process') }}">
                @csrf
                
                {{-- Hidden Role Field - CRITICAL FOR REDIRECT --}}
                <input type="hidden" name="role" value="{{ session('role', 'student') }}">
                
                {{-- Email (Auto-fetched, Read-only) --}}
                <div class="mb-6">
                    <label for="user_email" class="block text-sm font-semibold text-primary-dark mb-2">Email Address</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-secondary-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <input type="email" id="user_email" name="user_email" value="{{ $email ?? session('email') }}" readonly class="input-field bg-gray-100 cursor-not-allowed">
                    </div>
                </div>

                {{-- Password --}}
                <div class="mb-6">
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
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </button>
                    </div>
                    @error('user_password')<p class="error-text">{{ $message }}</p>@enderror
                </div>

                {{-- Confirm Password --}}
                <div class="mb-8">
                    <label for="user_password_confirmation" class="block text-sm font-semibold text-primary-dark mb-2 label-required">Confirm Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-secondary-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <input type="password" id="user_password_confirmation" name="user_password_confirmation" required class="input-field" placeholder="Re-enter password">
                    </div>
                    @error('user_password_confirmation')<p class="error-text">{{ $message }}</p>@enderror
                </div>

                {{-- Submit Button --}}
                <button type="submit" class="btn-primary w-full flex items-center justify-center">
                    Create Account
                    <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </button>
            </form>

        </div>
    </div>

</body>
</html>