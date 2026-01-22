{{-- 
    
    resources/views/partials/topbar.blade.php
    FOR INSTRUCTOR 

    --}}
    
<header class="bg-gray-50 border-b border-gray-200 px-6 lg:px-10 py-4 flex items-center justify-between shadow-sm">
    {{-- Left: Page Title --}}
    <div class="flex flex-col">
        <h2 class="text-lg font-semibold text-gray-900">
            @yield('page_title', 'Instructor Dashboard')
        </h2>
        <span class="text-sm text-gray-500">
            @yield('page_subtitle', 'Welcome back')
        </span>
    </div>

    {{-- Right: User Info --}}
    <div class="flex items-center gap-3">
        <div class="text-right">
            <p class="text-sm font-medium text-gray-800">
                {{ auth()->user()->user_email ?? 'Instructor' }}
            </p>
            <p class="text-xs text-gray-500">
                Instructor
            </p>
        </div>

        {{-- Avatar --}}
        <div class="w-9 h-9 rounded-full bg-secondary-blue flex items-center justify-center text-white font-semibold shadow-sm">
            {{ strtoupper(substr(auth()->user()->user_email ?? 'IN', 0, 2)) }}
        </div>
    </div>
</header>