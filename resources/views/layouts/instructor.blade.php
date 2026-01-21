<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Instructor Portal</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
</head>

<body class="font-inter h-full antialiased bg-gray-100 text-gray-800">
    {{-- Container for sidebar + main content --}}
    <div class="flex h-full">
        {{-- Sidebar (fixed on desktop, hidden on mobile by default) --}}
        @include('instructor.partials.sidebar')

        {{-- Main content wrapper --}}
        <div class="flex-1 flex flex-col min-h-screen w-full lg:ml-64">
            {{-- Topbar --}}
            @include('instructor.partials.topbar')

            {{-- Content --}}
            <main class="flex-1 overflow-y-auto px-4 py-6 lg:px-10 lg:py-8 bg-gray-100">
                {{-- Global flash messages --}}
                @if (session('success') && !request()->routeIs('instructor.attendance.edit'))
                    <div class="mb-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-800">
                        {{ session('success') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-800">
                        {{ session('error') }}
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>