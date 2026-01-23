<!-- resources/views/layouts/student.blade.php -->

<!DOCTYPE html>
<html lang="en" class="bg-[#272829]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Portal - {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/css/student-instructor.css', 'resources/js/student-instructor.js'])
</head>
<body class="font-sans text-[#D8D9DA] bg-[#272829] min-h-screen antialiased">

    <div id="sidebar-overlay" class="fixed inset-0 bg-black/50 z-30 hidden"></div>

    <div class="flex h-screen">
        <aside id="sidebar"
            class="fixed lg:static inset-y-0 left-0 w-64 bg-[#272829] border-r border-[#61677A]
                flex flex-col h-screen
                transform -translate-x-full lg:translate-x-0
                transition-transform duration-300
                z-40">
            @include('student.partials.sidebar')
        </aside>

        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            @include('student.partials.topbar')
            <main class="flex-1 overflow-y-auto bg-gray-100">
                @yield('content')
            </main>
        </div>
    </div>

    <script>
        const menuBtn = document.querySelector('[data-mobile-menu]');
        const closeBtn = document.querySelector('[data-close-sidebar]');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebar-overlay');

        function openSidebar() {
            if (window.innerWidth < 1024) {
                sidebar.classList.remove('-translate-x-full');
                overlay.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');
            }
        }

        function closeSidebar() {
            sidebar.classList.add('-translate-x-full');
            overlay.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }


        menuBtn?.addEventListener('click', openSidebar);
        closeBtn?.addEventListener('click', closeSidebar);
        overlay?.addEventListener('click', closeSidebar);

        window.addEventListener('resize', () => {
            if (window.innerWidth >= 1024) {
                document.body.classList.remove('overflow-hidden');
                overlay.classList.add('hidden');
            }
        });
    </script>

</body>
</html>