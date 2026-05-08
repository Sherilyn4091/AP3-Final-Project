/*
|--------------------------------------------------------------------------
| Student Profile Page Scripts
|
| resources/js/student/profile.js
|--------------------------------------------------------------------------
|
| Handles profile-only interactions:
| - closeable success/error notifications
| - preferred lesson day checkbox syncing
|
*/

document.addEventListener('DOMContentLoaded', function () {
    initializeProfileAlerts();
    initializeLessonDayCheckboxes();
});

/**
 * Lets students close password/profile success or error messages manually.
 */
function initializeProfileAlerts() {
    document.querySelectorAll('[data-close-profile-alert]').forEach(function (button) {
        button.addEventListener('click', function () {
            button.closest('[data-profile-alert]')?.remove();
        });
    });
}

/**
 * Converts checked lesson-day boxes into one comma-separated text value.
 */
function initializeLessonDayCheckboxes() {
    const profileForm = document.getElementById('profileForm');
    const hiddenLessonDaysInput = document.getElementById('preferred_lesson_days');

    if (!profileForm || !hiddenLessonDaysInput) {
        return;
    }

    function syncSelectedLessonDays() {
        const selectedDays = Array.from(document.querySelectorAll('[data-lesson-day]:checked'))
            .map(function (checkbox) {
                return checkbox.value;
            });

        hiddenLessonDaysInput.value = selectedDays.join(', ');
    }

    document.querySelectorAll('[data-lesson-day]').forEach(function (checkbox) {
        checkbox.addEventListener('change', syncSelectedLessonDays);
    });

    profileForm.addEventListener('submit', syncSelectedLessonDays);

    syncSelectedLessonDays();
}