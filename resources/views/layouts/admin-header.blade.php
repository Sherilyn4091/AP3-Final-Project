{{-- resources/views/layouts/admin-header.blade.php --}} 
{{--  
    ============================================================================ 
    ADMIN HEADER WITH SIDEBAR NAVIGATION
    - Logo clicks to dashboard 
    - All Quick Actions included in sidebar 
    - Scrollable dropdowns (max 300px) 
    - Organized navigation hierarchy 
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
                    <li><a href="{{ route('admin.users.instructors') }}" class="block px-4 py-2 rounded hover:bg-secondary-blue transition-colors text-sm">Instructors</a></li> 
                    <li><a href="{{ route('admin.users.sales-staff') }}" class="block px-4 py-2 rounded hover:bg-secondary-blue transition-colors text-sm">Sales Staff</a></li> 
                    <li><a href="{{ route('admin.users.all-around-staff') }}" class="block px-4 py-2 rounded hover:bg-secondary-blue transition-colors text-sm">All-Around Staff</a></li> 
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
             
            {{-- Payments --}} 
            <li> 
                <a href="{{ route('admin.payments.index') }}"  
                   class="flex items-center px-4 py-3 rounded-lg hover:bg-primary-darker transition-all duration-200"> 
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"> 
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/> 
                    </svg> 
                    <span class="font-medium">Payments</span> 
                </a> 
            </li> 
             
            {{-- Lessons --}} 
            <li> 
                <a href="{{ route('admin.lessons.index') }}"  
                   class="flex items-center px-4 py-3 rounded-lg hover:bg-primary-darker transition-all duration-200"> 
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"> 
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/> 
                    </svg> 
                    <span class="font-medium">Lessons</span> 
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
             
            {{-- Reports --}} 
            <li> 
                <button class="w-full flex items-center justify-between px-4 py-3 rounded-lg hover:bg-primary-darker transition-all duration-200 focus:outline-none"  
                        onclick="toggleDropdown(this)"> 
                    <div class="flex items-center"> 
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"> 
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/> 
                        </svg> 
                        <span class="font-medium">Reports</span> 
                    </div> 
                    <svg class="w-4 h-4 transform transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"> 
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/> 
                    </svg> 
                </button> 
                <ul class="hidden mt-1 ml-4 space-y-1 border-l-2 border-secondary-blue pl-4 max-h-[300px] overflow-y-auto scrollbar-custom"> 
                    <li><a href="{{ route('admin.reports.index') }}" class="block px-4 py-2 rounded hover:bg-secondary-blue transition-colors text-sm">All Reports</a></li> 
                    <li><a href="{{ route('admin.reports.financial') }}" class="block px-4 py-2 rounded hover:bg-secondary-blue transition-colors text-sm">Financial Reports</a></li> 
                </ul> 
            </li> 
             
            {{-- Settings --}} 
            <li> 
                <a href="{{ route('admin.settings.index') }}"  
                   class="flex items-center px-4 py-3 rounded-lg hover:bg-primary-darker transition-all duration-200"> 
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"> 
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/> 
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/> 
                    </svg> 
                    <span class="font-medium">Settings</span> 
                </a> 
            </li> 
             
        </ul> 
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