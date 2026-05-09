<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title') - Music Lab</title>

    <!-- Main Vite assets -->
    @vite([
        'resources/css/app.css',
        'resources/js/app.js',
        'resources/js/admin-pages.js',
        'resources/js/admin-pages/reports-chart.js'
    ])

    <!-- Page-specific styles -->
    @stack('styles')
</head>

<body class="bg-gray-100">
    {{-- Sidebar --}}
    @include('layouts.admin-header')

    {{-- Main content wrapper --}}
    <div class="flex min-h-screen w-full flex-col lg:pl-64">

        {{-- Sticky top header --}}
        <header class="sticky top-0 z-40 w-full border-b border-gray-200 bg-white px-4 py-3 shadow-sm sm:px-6 lg:px-8">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    @yield('headername')
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    @yield('header_actions')
                </div>
            </div>
        </header>

        {{-- Page content --}}
        <main class="flex-1 w-full pb-8">
            @yield('maincontent')
        </main>

        {{-- Footer --}}
        <footer class="w-full border-t border-[#D8D9DA] bg-white px-4 py-3 text-center text-xs text-[#61677A]">
            &copy; {{ date('Y') }} Music Lab. All rights reserved.
        </footer>
    </div>

    {{-- Shared toast container --}}
    <div id="toast-container" class="fixed right-4 top-20 z-[100] space-y-2"></div>

    {{-- Shared reset password modal --}}
    <div id="reset-password-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-50 p-4"></div>

    {{-- Page-specific scripts --}}
    @stack('scripts')

    {{-- Load Livewire only if installed --}}
    @if (class_exists(\Livewire\Livewire::class))
        @livewireScripts
    @endif
</body>
</html>