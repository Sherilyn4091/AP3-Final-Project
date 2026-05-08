{{-- resources/views/instructor/partials/topbar.blade.php --}}
<header class="sticky top-0 z-30 border-b border-[#D8D9DA] bg-white/90 px-4 py-3 shadow-sm backdrop-blur lg:px-8">
    <div class="flex items-center justify-between gap-4 pl-12 lg:pl-0">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-[#61677A]">Instructor Workspace</p>
            <h2 class="instructor-heading text-base font-extrabold text-[#2F4F4F] sm:text-lg">
                {{ request()->routeIs('instructor.dashboard') ? 'Dashboard' : ucwords(str_replace(['instructor.', '.', '-'], ['', ' / ', ' '], request()->route()->getName() ?? 'Instructor Portal')) }}
            </h2>
        </div>

        <div class="hidden items-center gap-3 sm:flex">
            <span class="rounded-full border border-[#959D90] bg-[#fcf3e3] px-3 py-1 text-xs font-bold text-[#523D35]">
                Instructor
            </span>
        </div>
    </div>
</header>