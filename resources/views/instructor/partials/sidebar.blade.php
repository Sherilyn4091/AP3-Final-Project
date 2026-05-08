{{-- resources/views/instructor/partials/sidebar.blade.php --}}
@php
    $links = [
        [
            'label' => 'Overview',
            'route' => 'instructor.dashboard',
            'active' => 'instructor.dashboard',
            'icon' => 'M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z',
        ],
        [
            'label' => 'Schedule',
            'route' => 'instructor.schedule.index',
            'active' => 'instructor.schedule.*',
            'icon' => 'M8 7V3m8 4V3M5 11h14M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
        ],
        [
            'label' => 'Students',
            'route' => 'instructor.students.index',
            'active' => 'instructor.students.*',
            'icon' => 'M17 20h5v-2a4 4 0 00-4-4h-1M9 20H4v-2a4 4 0 014-4h1m4-5a4 4 0 11-8 0 4 4 0 018 0zm8 0a3 3 0 11-6 0 3 3 0 016 0z',
        ],
        [
            'label' => 'Progress',
            'route' => 'instructor.progress.index',
            'active' => 'instructor.progress.*',
            'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5l5 5v11a2 2 0 01-2 2z',
        ],
        [
            'label' => 'Attendance',
            'route' => 'instructor.attendance.index',
            'active' => 'instructor.attendance.*',
            'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 14l2 2 4-4',
        ],
        [
            'label' => 'Profile',
            'route' => 'instructor.profile.index',
            'active' => 'instructor.profile.*',
            'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
        ],
    ];
@endphp

<button id="mobile-menu-btn" type="button" class="fixed left-4 top-4 z-50 rounded-xl bg-[#272829] p-2 text-[#D8D9DA] shadow-lg lg:hidden" aria-label="Open instructor menu">
    <svg id="menu-icon" class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
    </svg>
    <svg id="close-icon" class="hidden h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
    </svg>
</button>

<div id="mobile-overlay" class="fixed inset-0 z-40 hidden bg-black/50 lg:hidden"></div>

<aside id="sidebar" class="fixed inset-y-0 left-0 z-50 flex w-64 -translate-x-full flex-col border-r border-[#61677A] bg-[#272829] text-[#D8D9DA] shadow-xl transition-transform duration-300 ease-in-out lg:translate-x-0">
    <div class="border-b border-[#61677A] p-5">
        <p class="text-xs font-bold uppercase tracking-[0.22em] text-[#B4833D]">Music Lab</p>
        <h1 class="instructor-sidebar-title mt-2 text-xl font-extrabold text-[#FFF6E0]">Instructor Portal</h1>
    </div>

    <nav class="flex-1 overflow-y-auto px-3 py-5">
        <ul class="space-y-1.5">
            @foreach($links as $link)
                @php($isActive = request()->routeIs($link['active']))
                <li>
                    <a href="{{ route($link['route']) }}"
                       class="group flex items-center rounded-2xl px-4 py-3 text-sm font-bold transition {{ $isActive ? 'bg-[#2F4F4F] text-white shadow-sm' : 'text-[#D8D9DA] hover:bg-[#394a56] hover:text-white' }}">
                        <svg class="mr-3 h-5 w-5 shrink-0 {{ $isActive ? 'text-[#FFF6E0]' : 'text-[#959D90] group-hover:text-[#FFF6E0]' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $link['icon'] }}" />
                        </svg>
                        <span class="instructor-nav-label">{{ $link['label'] }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>

    <div class="border-t border-[#61677A] p-4">
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="flex w-full items-center rounded-2xl px-4 py-3 text-sm font-bold text-[#D8D9DA] transition hover:bg-[#523D35] hover:text-white">
                <svg class="mr-3 h-5 w-5 text-[#959D90]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                Logout
            </button>
        </form>
    </div>
</aside>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('mobile-overlay');
        const menuBtn = document.getElementById('mobile-menu-btn');
        const menuIcon = document.getElementById('menu-icon');
        const closeIcon = document.getElementById('close-icon');

        if (!sidebar || !overlay || !menuBtn || !menuIcon || !closeIcon) {
            return;
        }

        const setMenuState = (isOpen) => {
            sidebar.classList.toggle('-translate-x-full', !isOpen);
            overlay.classList.toggle('hidden', !isOpen);
            menuIcon.classList.toggle('hidden', isOpen);
            closeIcon.classList.toggle('hidden', !isOpen);
        };

        menuBtn.addEventListener('click', () => {
            setMenuState(sidebar.classList.contains('-translate-x-full'));
        });

        overlay.addEventListener('click', () => setMenuState(false));
    });
</script>