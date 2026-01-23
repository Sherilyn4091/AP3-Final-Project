<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') - Music Lab</title>

    <!-- ONLY ONE VITE CALL – put everything you need here -->
    @vite([
        'resources/css/app.css',
        'resources/js/app.js',           // ← should contain Alpine + other global JS
        'resources/js/admin-pages.js',
        'resources/js/admin-pages/reports-chart.js'    // ← your custom admin scripts
    ])

    <!-- Livewire styles – only once -->
</head>

<body class="bg-gray-100 flex"> {{-- Gray background for the whole site --}}

    {{-- 1. The Sidebar (admin-header.blade.php) --}}
    @include('layouts.admin-header')

    {{-- 2. The Main Content Area (Pushed right by ml-64) --}}
    <div class="flex-1 lg:ml-64 min-h-screen flex flex-col">
        
        {{-- Fixed/Shared Top Header --}}
    <header class="sticky top-0 z-40 bg-white shadow-sm border-b border-gray-200 p-6 lg:p-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                @yield('headername')
            </div>
            <div class="mt-4 sm:mt-0">
                @yield('header_actions')
            </div>
        </div>
    </header>

        {{-- The Page Content --}}
        <main class="flex-grow pb-12"> 
             @yield('maincontent')  
        </main>

        <footer class="p-6 text-center text-xs text-gray-500 bg-white border-t">
            © {{ date('Y') }} Music Lab. All rights reserved.
        </footer>
    </div>

<!-- Important: Livewire scripts should be at the END of body -->
    @livewireScripts

    <!-- Remove any extra @stack('scripts') if it includes app.js again -->
    @stack('scripts')

    <!-- Toast container & other shared modals -->
    <div id="toast-container" class="fixed top-20 right-4 z-[100] space-y-2"></div>
    <div id="reset-password-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-4"></div>

</body>
</html>
