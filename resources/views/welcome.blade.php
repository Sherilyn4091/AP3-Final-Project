<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Music Lab | Home</title>
    @vite(['resources/css/style.css', 'resources/js/script.js'])
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="bg-light-gray min-h-screen flex items-center justify-center">

    <div class="max-w-4xl mx-auto text-center px-6">
        <!-- Main Logo/Icon -->
        <div class="mb-8 animate-fade-in">
            <div class="inline-flex items-center justify-center w-32 h-32 bg-primary-dark rounded-full shadow-2xl mb-6">
                <svg class="w-16 h-16 text-accent-yellow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/>
                </svg>
            </div>
            <h1 class="text-5xl font-bold text-primary-dark mb-4">Welcome to Music Lab</h1>
            <p class="text-xl text-secondary-blue mb-12">Discover your musical potential with expert instructors and personalized lessons</p>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row gap-6 justify-center animate-fade-in">
            <a href="{{ route('login') }}" class="btn-primary px-12 py-4 text-lg">Log In</a>
            <a href="{{ route('register') }}" class="btn-secondary px-12 py-4 text-lg">Register</a>
        </div>

        <!-- Footer Note -->
        <p class="mt-16 text-secondary-blue">
            Join thousands of students mastering their instruments today.
        </p>
    </div>

</body>
</html>