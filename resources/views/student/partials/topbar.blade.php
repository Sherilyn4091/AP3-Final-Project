<!-- resources/views/student/partials/topbar.blade.php -->

<header class="bg-[#272829] border-b border-[#61677A] px-8 py-4 flex items-center justify-between shadow-sm">
    <div class="flex items-center">
        <button data-mobile-menu class="lg:hidden mr-4 text-[#D8D9DA] hover:text-[#FFF6E0] focus:outline-none">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>
        <h2 class="text-xl font-semibold text-[#D8D9DA]">@yield('pageTitle', 'Student Dashboard')</h2>
    </div>

    <div class="flex items-center space-x-6">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-[#61677A] to-[#272829] flex items-center justify-center text-[#FFF6E0] font-bold">
                {{ substr(Auth::user()->student->first_name ?? 'S', 0, 1) }}
            </div>
            <div>
                <p class="font-medium text-[#D8D9DA]">{{ Auth::user()->student->first_name ?? 'Student' }}</p>
            </div>
        </div>
    </div>
</header>