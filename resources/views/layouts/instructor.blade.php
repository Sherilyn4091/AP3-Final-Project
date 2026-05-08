<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Instructor Portal - {{ config('app.name', 'Music Lab') }}</title>

    {{-- Main app CSS plus instructor-specific Vite CSS. --}}
    @vite(['resources/css/app.css', 'resources/css/instructor.css', 'resources/js/app.js'])

    {{-- Music Lab UI fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;600;700&family=Sora:wght@500;600;700;800&display=swap" rel="stylesheet">

    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    @stack('styles')
</head>

<body class="instructor-body h-full antialiased">
    <div class="flex h-full">
        @include('instructor.partials.sidebar')

        <div class="flex min-h-screen w-full flex-1 flex-col lg:ml-64">
            @include('instructor.partials.topbar')

            <main class="instructor-main flex-1 overflow-y-auto px-3 py-4 sm:px-5 lg:px-8 lg:py-6">
                @if (session('success') && !request()->routeIs('instructor.attendance.edit'))
                    <div class="mb-5 rounded-2xl border border-[#959D90] bg-white px-4 py-3 text-sm font-bold text-[#2F4F4F] shadow-sm">
                        {{ session('success') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-5 rounded-2xl border border-[#B4833D] bg-[#fcf3e3] px-4 py-3 text-sm font-bold text-[#523D35] shadow-sm">
                        {{ session('error') }}
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    @stack('scripts')
</body>
</html>