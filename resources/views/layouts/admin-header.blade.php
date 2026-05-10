{{-- resources/views/layouts/admin-header.blade.php --}} 
{{--  
    ============================================================================ 
    ADMIN HEADER WITH SIDEBAR NAVIGATION
    - Logo clicks to dashboard 
    - All Quick Actions included in sidebar 
    ============================================================================ 
--}} 
 
<!-- Sidebar for Desktop & Mobile --> 
<aside class="fixed top-0 left-0 h-full w-64 bg-primary-dark text-accent-yellow transition-transform duration-300 ease-in-out -translate-x-full lg:translate-x-0 z-50 flex flex-col" id="admin-sidebar"> 
     
    {{-- Logo Section - Clicks to Dashboard --}} 
    <a href="{{ route('admin.dashboard') }}" class="block p-6 border-b border-secondary-blue hover:bg-primary-darker transition-colors"> 
        <img src="https://res.cloudinary.com/dibojpqg2/image/upload/v1766933637/music-lab-logo_1_lfcsqw.png"  
             alt="Music Lab Logo"  
             class="h-16 object-contain mx-auto"> 
    </a> 
     
    {{-- Navigation Links --}} 
    <nav class="mt-4 flex-grow overflow-y-auto scrollbar-custom"> 
        <ul class="space-y-1 px-3"> 
             
            {{-- Dashboard --}} 
            <li> 
                <a href="{{ route('admin.dashboard') }}"  
                   class="flex items-center px-4 py-3 rounded-lg hover:bg-primary-darker transition-all duration-200 {{ request()->routeIs('admin.dashboard') ? 'bg-secondary-blue text-white' : '' }}"> 
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"> 
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/> 
                    </svg> 
                    <span class="font-medium">Dashboard</span> 
                </a> 
            </li> 
             
            {{-- Users Dropdown --}} 
            <li> 
                <button class="w-full flex items-center justify-between px-4 py-3 rounded-lg hover:bg-primary-darker transition-all duration-200 focus:outline-none"  
                        onclick="toggleDropdown(this)"> 
                    <div class="flex items-center"> 
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"> 
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/> 
                        </svg> 
                        <span class="font-medium">Users</span> 
                    </div> 
                    <svg class="w-4 h-4 transform transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"> 
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/> 
                    </svg> 
                </button> 
                {{-- Scrollable Dropdown with max-height 300px --}} 
                <ul class="hidden mt-1 ml-4 space-y-1 border-l-2 border-secondary-blue pl-4 max-h-[300px] overflow-y-auto scrollbar-custom"> 
                    <li><a href="{{ route('admin.users.index') }}" class="block px-4 py-2 rounded hover:bg-secondary-blue transition-colors text-sm">All Users</a></li> 
                    <li><a href="{{ route('admin.users.students') }}" class="block px-4 py-2 rounded hover:bg-secondary-blue transition-colors text-sm">Students</a></li>
                    <li><a href="{{ route('admin.instructors.index') }}" class="block px-4 py-2 rounded hover:bg-secondary-blue transition-colors text-sm">Instructors</a></li>
                    <li class="border-t border-secondary-blue pt-1 mt-1"> 
                        <a href="{{ route('admin.users.create') }}" class="block px-4 py-2 rounded hover:bg-secondary-blue transition-colors text-sm font-semibold"> 
                            + Add New User 
                        </a> 
                    </li> 
                </ul> 
            </li> 
             
            {{-- Schedule Management --}} 
            <li> 
                <a href="{{ route('admin.schedules.index') }}"  
                   class="flex items-center px-4 py-3 rounded-lg hover:bg-primary-darker transition-all duration-200"> 
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"> 
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/> 
                    </svg> 
                    <span class="font-medium">Schedule</span> 
                </a> 
            </li> 

            {{-- Lesson Packages --}}
            <li>
                <a href="{{ route('admin.lesson-sessions.index') }}"
                class="flex items-center px-4 py-3 rounded-lg hover:bg-primary-darker transition-all duration-200 {{ request()->routeIs('admin.lesson-sessions.*') ? 'bg-secondary-blue text-white' : '' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                    <span class="font-medium">Lesson packages</span>
                </a>
            </li>
             
            {{-- Instruments --}} 
            <li> 
                <a href="{{ route('admin.instruments.index') }}"  
                   class="flex items-center px-4 py-3 rounded-lg hover:bg-primary-darker transition-all duration-200"> 
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"> 
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/> 
                    </svg> 
                    <span class="font-medium">Instruments</span> 
                </a> 
            </li> 

            {{-- Specializations --}} 
            <li> 
                <a href="{{ route('admin.specializations.index') }}"  
                class="flex items-center px-4 py-3 rounded-lg hover:bg-primary-darker transition-all duration-200 {{ request()->routeIs('admin.specializations.*') ? 'bg-secondary-blue text-white' : '' }}"> 
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"> 
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/> 
                    </svg> 
                    <span class="font-medium">Specializations</span> 
                </a> 
            </li>

            {{-- Genres --}} 
            <li> 
                <a href="{{ route('admin.genres.index') }}"  
                class="flex items-center px-4 py-3 rounded-lg hover:bg-primary-darker transition-all duration-200 {{ request()->routeIs('admin.genres.*') ? 'bg-secondary-blue text-white' : '' }}"> 
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"> 
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"></path> 
                    </svg> 
                    <span class="font-medium">Genres</span> 
                </a> 
            </li>

            {{-- Payment Methods --}}
            <li>
                <a href="{{ route('admin.payment-methods.index') }}"
                class="flex items-center px-4 py-3 rounded-lg hover:bg-primary-darker transition-all duration-200 {{ request()->routeIs('admin.payment-methods.*') ? 'bg-secondary-blue text-white' : '' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <span class="font-medium">Payment Methods</span>
                </a>
            </li>

            {{-- Payment Statuses --}}
            <li>
                <a href="{{ route('admin.payment-statuses.index') }}"
                class="flex items-center px-4 py-3 rounded-lg hover:bg-primary-darker transition-all duration-200 {{ request()->routeIs('admin.payment-statuses.*') ? 'bg-secondary-blue text-white' : '' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="font-medium">Payment statuses</span>
                </a>
            </li>
             
            {{-- Inventory --}} 
            <li> 
                <a href="{{ route('admin.inventory.index') }}"  
                   class="flex items-center px-4 py-3 rounded-lg hover:bg-primary-darker transition-all duration-200"> 
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"> 
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/> 
                    </svg> 
                    <span class="font-medium">Inventory</span> 
                </a> 
            </li> 

            {{-- Suppliers --}}
            <li>
                <a href="{{ route('admin.suppliers.index') }}"
                class="flex items-center px-4 py-3 rounded-lg hover:bg-primary-darker transition-all duration-200 {{ request()->routeIs('admin.suppliers.*') ? 'bg-secondary-blue text-white' : '' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <span class="font-medium">Suppliers</span>
                </a>
            </li>

            {{-- Reports --}}
            <li>
                <a href="{{ route('admin.reports.index') }}"
                class="flex items-center px-4 py-3 rounded-lg hover:bg-primary-darker transition-all duration-200 {{ request()->routeIs('admin.reports.*') ? 'bg-secondary-blue text-white' : '' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span class="font-medium">Reports</span>
                </a>
            </li>
             
        </ul> 
    
        {{-- Student Risk Analytics - Python Decision Tree Classification --}}
        <div class="px-3 pb-3">
            <a href="{{ route('admin.student-risk-analytics.index') }}"
               class="flex items-center px-4 py-3 rounded-lg hover:bg-primary-darker transition-all duration-200 {{ request()->routeIs('admin.student-risk-analytics.*') ? 'bg-secondary-blue text-white' : '' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-6a2 2 0 012-2h8M9 17H5a2 2 0 01-2-2V7a2 2 0 012-2h4m0 12l3 3m0 0l3-3m-3 3V9" />
                </svg>
                <span class="font-medium">Student Risk Analytics</span>
            </a>
        </div>
</nav> 
     
    {{-- Logout Button (Fixed at Bottom) --}} 
    <div class="p-4 border-t border-secondary-blue bg-primary-dark"> 
        <form action="{{ route('logout') }}" method="POST"> 
            @csrf 
            <button type="submit" class="w-full flex items-center justify-center px-4 py-3 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-all duration-200 font-medium shadow-lg hover:shadow-xl"> 
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"> 
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/> 
                </svg> 
                Logout 
            </button> 
        </form> 
    </div> 
</aside> 
 
{{-- Mobile Sidebar Toggle Button --}} 
<button class="lg:hidden fixed top-4 left-4 z-50 p-3 bg-primary-dark text-accent-yellow rounded-lg shadow-lg hover:bg-primary-darker transition-all"  
        onclick="toggleSidebar()"  
        aria-label="Toggle Menu"> 
    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"> 
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/> 
    </svg> 
</button> 
 
{{-- Backdrop for Mobile Sidebar --}} 
<div id="sidebar-backdrop"  
     class="lg:hidden fixed inset-0 bg-black bg-opacity-50 z-40 hidden transition-opacity duration-300"  
     onclick="toggleSidebar()"></div> 
 
<script> 
/** 
 * ============================================================================ 
 * SIDEBAR JAVASCRIPT FUNCTIONS 
 * ============================================================================ 
 */ 
 
/** 
 * Toggle sidebar visibility on mobile devices 
 */ 
function toggleSidebar() { 
    const sidebar = document.getElementById('admin-sidebar'); 
    const backdrop = document.getElementById('sidebar-backdrop'); 
     
    sidebar.classList.toggle('-translate-x-full'); 
    backdrop.classList.toggle('hidden'); 
} 
 
/** 
 * Toggle dropdown menu in sidebar 
 * @param {HTMLElement} button - The dropdown button element 
 */ 
function toggleDropdown(button) { 
    const dropdown = button.nextElementSibling; 
    const arrow = button.querySelector('svg:last-child'); 
     
    dropdown.classList.toggle('hidden'); 
    arrow.classList.toggle('rotate-180'); 
} 
 
/** 
 * Close sidebar when clicking on a link (mobile only) 
 */ 
document.addEventListener('DOMContentLoaded', function() { 
    const sidebarLinks = document.querySelectorAll('#admin-sidebar a'); 
    const isMobile = window.innerWidth < 1024; 
     
    if (isMobile) { 
        sidebarLinks.forEach(link => { 
            link.addEventListener('click', function() { 
                toggleSidebar(); 
            }); 
        }); 
    } 
}); 
</script>
