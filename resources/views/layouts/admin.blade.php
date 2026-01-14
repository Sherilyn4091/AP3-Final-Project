{{-- 
    ============================================================================
    ADMIN LAYOUT - Master Template for All Admin Pages
    resources/views/layouts/admin.blade.php
    ============================================================================
    
    PURPOSE:
    This is the main wrapper layout for all admin pages. It provides:
    - HTML document structure (head, body tags)
    - Sidebar navigation (via admin-header include)
    - Main content area where page content is injected
    - Asset loading (CSS/JS via Vite)
    - Custom scrollbar styling
    
    USAGE:
    All admin pages extend this layout using:
    @extends('layouts.admin')
    
    Then inject their content using:
    @section('content')
        <!-- Page-specific content here -->
    @endsection
    
    STRUCTURE:
    1. HEAD - Meta tags, title, CSS assets
    2. SIDEBAR - Navigation (from admin-header.blade.php)
    3. MAIN CONTENT - Dynamic page content area
    4. SCRIPTS - JavaScript assets
    
    ============================================================================
--}}

<!DOCTYPE html>
<html lang="en">
<head>
    {{-- ============================================
         META TAGS & CHARACTER ENCODING
         ============================================ --}}
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    {{-- CSRF Token for Laravel form security --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    {{-- ============================================
         PAGE TITLE
         Each page can set its own title via @section('title')
         Falls back to "Music Lab" if not set
         ============================================ --}}
    <title>@yield('title', 'Music Lab') - Admin Panel</title>
    
    {{-- ============================================
         VITE ASSET LOADING
         Loads Tailwind CSS and JavaScript
         ============================================ --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    {{-- ============================================
         CUSTOM SCROLLBAR STYLING
         Provides consistent scrollbar appearance
         across admin interface
         ============================================ --}}
    <style>
        /* Custom Scrollbar for Sidebar Dropdowns */
        .scrollbar-custom::-webkit-scrollbar { 
            width: 6px; 
        }
        .scrollbar-custom::-webkit-scrollbar-track { 
            background: #2D2F31; /* Dark background */
        }
        .scrollbar-custom::-webkit-scrollbar-thumb { 
            background: #61677A; /* Slate blue thumb */
            border-radius: 3px; 
        }
        .scrollbar-custom::-webkit-scrollbar-thumb:hover { 
            background: #4F5566; /* Darker on hover */
        }
    </style>
    
    {{-- ============================================
         ADDITIONAL PAGE-SPECIFIC STYLES
         Pages can add extra CSS using @push('styles')
         ============================================ --}}
    @stack('styles')
</head>

<body class="bg-gray-100">
    
    {{-- ============================================
         SIDEBAR NAVIGATION
         Includes the admin header with:
         - Logo
         - Navigation menu
         - Dropdown menus
         - Logout button
         Located at: resources/views/layouts/admin-header.blade.php
         ============================================ --}}
    @include('layouts.admin-header')
    
    {{-- ============================================
         MAIN CONTENT AREA
         - lg:ml-64 = Left margin on large screens (sidebar width)
         - Content from child pages injected via @yield('content')
         - Responsive padding for all screen sizes
         ============================================ --}}
    <main class="lg:ml-64 min-h-screen">
        <div class="p-6">
            {{-- 
                CONTENT INJECTION POINT
                Child pages define content using:
                @section('content')
                    <!-- Page content here -->
                @endsection
            --}}
            @yield('content')
        </div>
    </main>
    
    {{-- ============================================
         JAVASCRIPT SECTION
         - @stack('scripts') = Additional scripts from pages
         - @yield('scripts') = Main script section from pages
         
         Pages can add scripts using:
         @section('scripts')
             <script>
                 // Page-specific JavaScript
             </script>
         @endsection
         ============================================ --}}
    @stack('scripts')
    @yield('scripts')
    
</body>
</html>