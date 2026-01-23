{{-- resources/views/instructor/partials/sidebar.blade.php --}}
{{-- Mobile menu button (visible only on mobile) --}}
<button id="mobile-menu-btn" class="fixed top-4 left-4 z-50 lg:hidden bg-gray-900 text-white p-2 rounded-lg">
    <svg id="menu-icon" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
    </svg>
    <svg id="close-icon" class="w-6 h-6 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
    </svg>
</button>

{{-- Overlay for mobile (click to close sidebar) --}}
<div id="mobile-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden lg:hidden"></div>

{{-- Sidebar --}}
<aside id="sidebar" class="fixed inset-y-0 left-0 z-50 w-64 bg-gray-900 text-white flex flex-col transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out">
    <!-- Header -->
    <div class="p-6 border-b border-gray-800">
        <h1 class="text-xl font-bold tracking-tight">Instructor Portal</h1>
    </div>

    <!-- Navigation Links -->
    <nav class="flex-1 px-3 py-6 overflow-y-auto">
        <ul class="space-y-1">
            {{-- Dashboard --}}
            <li>
                <a href="{{ route('instructor.dashboard') }}"
                   class="flex items-center px-4 py-3 rounded-lg transition-colors
                          {{ request()->routeIs('instructor.dashboard') 
                             ? 'bg-gray-800 text-white' 
                             : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                    <span>Overview</span>
                </a>
            </li>

            {{-- Schedule --}}
            <li>
                <a href="{{ route('instructor.schedule.index') }}"
                   class="flex items-center px-4 py-3 rounded-lg transition-colors
                          {{ request()->routeIs('instructor.schedule.*') 
                             ? 'bg-gray-800 text-white' 
                             : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <span>Schedule</span>
                </a>
            </li>

            {{-- Students --}}
            <li>
                <a href="{{ route('instructor.students.index') }}"
                   class="flex items-center px-4 py-3 rounded-lg transition-colors
                          {{ request()->routeIs('instructor.students.*') 
                             ? 'bg-gray-800 text-white' 
                             : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                    <span>Students</span>
                </a>
            </li>

            {{-- Progress Updates --}}
            <li>
                <a href="{{ route('instructor.progress.index') }}"
                   class="flex items-center px-4 py-3 rounded-lg transition-colors
                          {{ request()->routeIs('instructor.progress.*') 
                             ? 'bg-gray-800 text-white' 
                             : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <span>Progress Updates</span>
                </a>
            </li>

            {{-- Attendance --}}
            <li>
                <a href="{{ route('instructor.attendance.index') }}"
                   class="flex items-center px-4 py-3 rounded-lg transition-colors
                          {{ request()->routeIs('instructor.attendance.*') 
                             ? 'bg-gray-800 text-white' 
                             : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                    </svg>
                    <span>Attendance</span>
                </a>
            </li>

            {{-- Profile --}}
            <li>
                <a href="{{ route('instructor.profile.index') }}"
                class="flex items-center px-4 py-3 rounded-lg transition-colors
                        {{ request()->routeIs('instructor.profile.*') 
                            ? 'bg-gray-800 text-white' 
                            : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    <span>Profile</span>
                </a>
            </li>
        </ul>
    </nav>

    <!-- Logout Section -->
    <div class="p-4 border-t border-gray-800 mt-auto">
        <a href="{{ route('logout') }}"
           onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
           class="flex items-center px-4 py-3 rounded-lg transition-colors text-gray-300 hover:bg-gray-800 hover:text-white">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
            </svg>
            <span>Logout</span>
        </a>

        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
            @csrf
        </form>
    </div>
</aside>

{{-- JavaScript for toggle functionality --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('mobile-overlay');
        const menuBtn = document.getElementById('mobile-menu-btn');
        const menuIcon = document.getElementById('menu-icon');
        const closeIcon = document.getElementById('close-icon');

        // Toggle sidebar on mobile
        menuBtn.addEventListener('click', function() {
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
            menuIcon.classList.toggle('hidden');
            closeIcon.classList.toggle('hidden');
        });

        // Close sidebar when clicking overlay
        overlay.addEventListener('click', function() {
            sidebar.classList.add('-translate-x-full');
            overlay.classList.add('hidden');
            menuIcon.classList.remove('hidden');
            closeIcon.classList.add('hidden');
        });
    });
</script>