{{-- ============================================================================
    ROLE SELECTION PAGE  |  resources/views/auth/register/select-role.blade.php
    Allows users to choose their role before registration
    ============================================================================ --}}

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Music Lab</title>
    
    {{-- Tailwind CSS via Vite --}}
    @vite(['resources/css/style.css', 'resources/js/script.js'])
    
    {{-- Google Fonts --}}
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>

<body class="min-h-screen bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center p-4 font-poppins">
    
    <div class="w-full max-w-6xl">
        
        {{-- Header Section --}}
        <div class="text-center mb-12 animate-fade-in">
            {{-- Professional Music Icon --}}
            <a href="{{ route('home') }}" class="inline-block hover:opacity-90 transition-opacity duration-300">
                <div class="flex justify-center mb-6">
                    <div class="bg-gradient-to-r from-primary-dark to-primary-darker p-6 rounded-full shadow-2xl">
                        <svg class="w-20 h-20 text-accent-yellow" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 3v10.55c-.59-.34-1.27-.55-2-.55-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4V7h4V3h-6z"/>
                        </svg>
                    </div>
                </div>
            </a>
            <h1 class="text-5xl font-bold text-primary-dark mb-4">Join Music Lab</h1>
            <p class="text-xl text-secondary-blue font-medium">Choose your role to get started</p>
        </div>

        {{-- Role Cards Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-10">
            
            {{-- Student Card --}}
            <a href="{{ route('register.student.form') }}" class="role-card group">
                <div class="text-center">
                    {{-- Icon --}}
                    <div class="flex justify-center mb-6">
                        <div class="bg-[#61677A] p-5 rounded-2xl shadow-lg group-hover:shadow-2xl transition-all duration-300">
                            <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                            </svg>
                        </div>
                    </div>
                    
                    <h2 class="text-2xl font-bold text-primary-dark mb-3 group-hover:text-secondary-blue transition-colors">Student</h2>
                    <p class="text-secondary-blue text-sm leading-relaxed mb-6">
                        Learn music from expert instructors. Book lessons, track your progress, and achieve your musical goals.
                    </p>
                    
                    <div class="inline-flex items-center bg-accent-yellow text-primary-dark px-6 py-3 rounded-lg font-semibold text-sm group-hover:bg-primary-dark group-hover:text-accent-yellow transition-all duration-300">
                        Register as Student
                        <svg class="w-5 h-5 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    </div>
                </div>
            </a>

            {{-- Instructor Card --}}
            <a href="{{ route('register.instructor.form') }}" class="role-card group">
                <div class="text-center">
                    {{-- Icon --}}
                    <div class="flex justify-center mb-6">
                        <div class="bg-[#E07A5F] p-5 rounded-2xl shadow-lg group-hover:shadow-2xl transition-all duration-300">
                            <svg class="w-12 h-12 text-white" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 3v10.55c-.59-.34-1.27-.55-2-.55-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4V7h4V3h-6z"/>
                            </svg>
                        </div>
                    </div>
                    
                    <h2 class="text-2xl font-bold text-primary-dark mb-3 group-hover:text-secondary-blue transition-colors">Instructor</h2>
                    <p class="text-secondary-blue text-sm leading-relaxed mb-6">
                        Share your musical expertise. Teach students, manage schedules, and inspire the next generation of musicians.
                    </p>
                    
                    <div class="inline-flex items-center bg-accent-yellow text-primary-dark px-6 py-3 rounded-lg font-semibold text-sm group-hover:bg-primary-dark group-hover:text-accent-yellow transition-all duration-300">
                        Register as Instructor
                        <svg class="w-5 h-5 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    </div>
                </div>
            </a>

            {{-- Sales Staff Card --}}
            <a href="{{ route('register.sales.form') }}" class="role-card group">
                <div class="text-center">
                    {{-- Icon --}}
                    <div class="flex justify-center mb-6">
                        <div class="bg-[#377357] p-5 rounded-2xl shadow-lg group-hover:shadow-2xl transition-all duration-300">
                            <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </div>
                    </div>
                    
                    <h2 class="text-2xl font-bold text-primary-dark mb-3 group-hover:text-secondary-blue transition-colors">Sales Staff</h2>
                    <p class="text-secondary-blue text-sm leading-relaxed mb-6">
                        Help students find the perfect lessons. Manage enrollments, process payments, and grow the music community.
                    </p>
                    
                    <div class="inline-flex items-center bg-accent-yellow text-primary-dark px-6 py-3 rounded-lg font-semibold text-sm group-hover:bg-primary-dark group-hover:text-accent-yellow transition-all duration-300">
                        Register as Sales Staff
                        <svg class="w-5 h-5 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    </div>
                </div>
            </a>

            {{-- All-Around Staff Card --}}
            <a href="{{ route('register.staff.form') }}" class="role-card group">
                <div class="text-center">
                    {{-- Icon --}}
                    <div class="flex justify-center mb-6">
                        <div class="bg-[#C2922F] p-5 rounded-2xl shadow-lg group-hover:shadow-2xl transition-all duration-300">
                            <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                    </div>
                    
                    <h2 class="text-2xl font-bold text-primary-dark mb-3 group-hover:text-secondary-blue transition-colors">Staff</h2>
                    <p class="text-secondary-blue text-sm leading-relaxed mb-6">
                        Keep everything running smoothly. Manage facilities, inventory, bookings, and support all operations.
                    </p>
                    
                    <div class="inline-flex items-center bg-accent-yellow text-primary-dark px-6 py-3 rounded-lg font-semibold text-sm group-hover:bg-primary-dark group-hover:text-accent-yellow transition-all duration-300">
                        Register as Staff
                        <svg class="w-5 h-5 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    </div>
                </div>
            </a>

        </div>

        {{-- Already Have Account Section --}}
        <div class="text-center animate-fade-in">
            <div class="inline-flex items-center justify-center p-5 bg-white border-2 border-secondary-blue rounded-xl shadow-lg hover:shadow-2xl hover:bg-accent-yellow-light transition-all duration-300">
                <svg class="w-5 h-5 text-secondary-blue mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                <p class="text-sm">
                    <span class="text-secondary-blue font-medium">Already have an account?</span>
                    <a href="{{ route('login') }}" class="font-bold text-primary-dark hover:text-secondary-blue underline ml-2 transition-colors">
                        Sign in
                    </a>
                </p>
            </div>
        </div>
    </div>

</body>
</html>