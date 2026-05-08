/*
|--------------------------------------------------------------------------
| Student Progress Page Scripts
|--------------------------------------------------------------------------
|
| Handles progress page interactions:
| - switching between progress and attendance tabs
|
*/

document.addEventListener('DOMContentLoaded', function () {
    initializeProgressTabs();
});

function initializeProgressTabs() {
    document.querySelectorAll('[data-progress-tab-button]').forEach(function (button) {
        button.addEventListener('click', function () {
            switchProgressTab(button.dataset.tab);
        });
    });
}

function switchProgressTab(tab) {
    if (!tab) {
        return;
    }

    document.querySelectorAll('.tab-content').forEach(function (content) {
        content.classList.add('hidden');
    });

    document.querySelectorAll('.tab-btn').forEach(function (button) {
        button.classList.remove('active');
    });

    document.getElementById('content-' + tab)?.classList.remove('hidden');
    document.getElementById('tab-' + tab)?.classList.add('active');
}

/*
|--------------------------------------------------------------------------
| Temporary Backward Compatibility
|--------------------------------------------------------------------------
| Keeps older onclick="switchTab('progress')" markup from breaking while you
| replace inline handlers with data-progress-tab-button and data-tab.
*/
window.switchTab = switchProgressTab;
