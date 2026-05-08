/*
|--------------------------------------------------------------------------
| Student Layout Scripts
|--------------------------------------------------------------------------
|
| Handles student layout-only interactions:
| - mobile sidebar open/close
| - overlay click close
| - responsive reset on desktop width
|
*/

document.addEventListener('DOMContentLoaded', function () {
    initializeStudentSidebar();
});

/**
 * Handles the mobile sidebar without keeping JavaScript inside Blade.
 */
function initializeStudentSidebar() {
    const menuButton = document.querySelector('[data-mobile-menu]');
    const closeButton = document.querySelector('[data-close-sidebar]');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebar-overlay');

    if (!sidebar || !overlay) {
        return;
    }

    function openSidebar() {
        if (window.innerWidth >= 1024) {
            return;
        }

        sidebar.classList.remove('-translate-x-full');
        overlay.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }

    function closeSidebar() {
        sidebar.classList.add('-translate-x-full');
        overlay.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }

    menuButton?.addEventListener('click', openSidebar);
    closeButton?.addEventListener('click', closeSidebar);
    overlay.addEventListener('click', closeSidebar);

    window.addEventListener('resize', function () {
        if (window.innerWidth >= 1024) {
            document.body.classList.remove('overflow-hidden');
            overlay.classList.add('hidden');
        }
    });
}
