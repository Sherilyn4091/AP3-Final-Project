{{--
    
    resources/views/partials/sidebar.blade.php 
    FOR toggleInstructorSidebar
    
    --}}
<aside
    id="instructor-sidebar"
    class="fixed top-0 left-0 h-full w-64 bg-primary-dark text-accent-yellow transition-transform duration-300 ease-in-out -translate-x-full lg:translate-x-0 z-50 flex flex-col"
>
    {{-- Logo / Header --}}
    <a href="{{ route('instructor.dashboard') }}"
       class="block p-6 border-b border-secondary-blue hover:bg-primary-darker transition-colors">
        <div class="text-center">
            <div class="text-lg font-semibold tracking-tight">Instructor Portal</div>
            <div class="text-sm opacity-80 mt-1">Music Lab</div>
        </div>
    </a>

    {{-- Navigation --}}
    <nav class="mt-4 flex-grow overflow-y-auto scrollbar-custom">
        <ul class="space-y-1 px-3">

            <li>
                <a href="{{ route('instructor.dashboard') }}"
                   class="flex items-center px-4 py-3 rounded-lg hover:bg-primary-darker transition-all duration-200
                          {{ request()->routeIs('instructor.dashboard') ? 'bg-secondary-blue text-white' : '' }}">
                    <span class="font-medium">Dashboard</span>
                </a>
            </li>

            <li>
                <a href="{{ route('instructor.schedule.index') }}"
                   class="flex items-center px-4 py-3 rounded-lg hover:bg-primary-darker transition-all duration-200
                          {{ request()->routeIs('instructor.schedule.index') ? 'bg-secondary-blue text-white' : '' }}">
                    <span class="font-medium">Schedule</span>
                </a>
            </li>

<li>
    <a href="{{ route('instructor.students.index') }}"
       class="flex items-center px-4 py-3 rounded-lg hover:bg-primary-darker transition-all duration-200
              {{ request()->routeIs('instructor.students.*') ? 'bg-secondary-blue text-white' : '' }}">
        <span class="font-medium">Students</span>
    </a>
</li>

<li>
    <a href="{{ route('instructor.attendance.index') }}"
       class="flex items-center px-4 py-3 rounded-lg hover:bg-primary-darker transition-all duration-200
              {{ request()->routeIs('instructor.attendance.*') ? 'bg-secondary-blue text-white' : '' }}">
        <span class="font-medium">Attendance</span>
    </a>
</li>

            <li>
                <a href="{{ route('instructor.progress.index') }}"
                   class="flex items-center px-4 py-3 rounded-lg hover:bg-primary-darker transition-all duration-200
                          {{ request()->routeIs('instructor.progress.*') ? 'bg-secondary-blue text-white' : '' }}">
                    <span class="font-medium">Progress</span>
                </a>
            </li>

            <li>
                <a href="{{ route('instructor.profile.index') }}"
                class="flex items-center px-4 py-3 rounded-lg hover:bg-primary-darker transition-all duration-200
                        {{ request()->routeIs('instructor.profile.*') ? 'bg-secondary-blue text-white' : '' }}">
                    <span class="font-medium">Profile</span>
                </a>
            </li>

        </ul>
    </nav>

    {{-- Logout --}}
    <div class="p-4 border-t border-secondary-blue bg-primary-dark">
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit"
                class="w-full flex items-center justify-center px-4 py-3 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-all duration-200 font-medium shadow-lg hover:shadow-xl">
                Logout
            </button>
        </form>
    </div>
</aside>

{{-- Mobile Sidebar Toggle Button --}}
<button
    class="lg:hidden fixed top-4 left-4 z-50 p-3 bg-primary-dark text-accent-yellow rounded-lg shadow-lg hover:bg-primary-darker transition-all"
    onclick="toggleInstructorSidebar()"
    aria-label="Toggle Menu"
    type="button"
>
    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
    </svg>
</button>

{{-- Backdrop for Mobile Sidebar --}}
<div
    id="instructor-sidebar-backdrop"
    class="lg:hidden fixed inset-0 bg-black bg-opacity-50 z-40 hidden transition-opacity duration-300"
    onclick="toggleInstructorSidebar()"
></div>

<script>
function toggleInstructorSidebar() {
    const sidebar = document.getElementById('instructor-sidebar');
    const backdrop = document.getElementById('instructor-sidebar-backdrop');

    sidebar.classList.toggle('-translate-x-full');
    backdrop.classList.toggle('hidden');
}

document.addEventListener('DOMContentLoaded', function () {
    const sidebarLinks = document.querySelectorAll('#instructor-sidebar a');
    const isMobile = window.innerWidth < 1024;

    if (isMobile) {
        sidebarLinks.forEach(link => {
            link.addEventListener('click', function () {
                toggleInstructorSidebar();
            });
        });
    }
});
</script>
