/*
|--------------------------------------------------------------------------
| Student Module Imports
|--------------------------------------------------------------------------
|
| Page-specific scripts are imported here because this file is already loaded
| by resources/views/layouts/student.blade.php through Vite.
|
*/

import './student';

/*
|--------------------------------------------------------------------------
| resources/js/student-instructor.js
|--------------------------------------------------------------------------
|
| Handles shared Student/Instructor portal interactions.
|
| Current responsibilities:
| - Toggle lesson/instrument sections
| - Clear student registration localStorage draft only after successful
|   Create Account & Login
|
*/

document.addEventListener('DOMContentLoaded', () => {
    initializePortalSectionToggles();
    clearStudentRegistrationDraftAfterSuccessfulAccountCreation();
});

/*
|--------------------------------------------------------------------------
| Student Registration Draft Cleanup
|--------------------------------------------------------------------------
|
| The student registration draft must NOT be cleared when:
| - the user clicks Complete Registration
| - the user clicks Back to registration
| - the user refreshes or closes the browser accidentally
|
| It is cleared ONLY after Laravel confirms successful account creation
| by sending the temporary cookie below.
|
*/

const STUDENT_REGISTRATION_DRAFT_KEY = 'musiclab.student.registration.draft.v1';
const STUDENT_REGISTRATION_CLEAR_COOKIE = 'musiclab_clear_student_registration_draft';

function clearStudentRegistrationDraftAfterSuccessfulAccountCreation() {
    if (!hasCookie(STUDENT_REGISTRATION_CLEAR_COOKIE)) {
        return;
    }

    removeStorageItem(STUDENT_REGISTRATION_DRAFT_KEY);
    deleteCookie(STUDENT_REGISTRATION_CLEAR_COOKIE);
}

function removeStorageItem(key) {
    try {
        localStorage.removeItem(key);
    } catch (error) {
        console.warn('Unable to remove stored student registration draft:', error);
    }
}

function hasCookie(cookieName) {
    return document.cookie
        .split(';')
        .some(cookie => cookie.trim().startsWith(cookieName + '='));
}

function deleteCookie(cookieName) {
    document.cookie = cookieName + '=; Max-Age=0; path=/; SameSite=Lax';
}

/*
|--------------------------------------------------------------------------
| Portal Section Toggles
|--------------------------------------------------------------------------
|
| Keeps the portal page interactions grouped in one place.
| Small functions are used to avoid long methods and duplicated logic.
|
*/

function initializePortalSectionToggles() {
    bindToggleButton('lessons-trigger', toggleLessons);
    bindToggleButtonByClass('lessons-btn', toggleLessons);

    bindToggleButton('instruments-trigger', toggleInstruments);
    bindToggleButtonByClass('instruments-btn', toggleInstruments);
}

function bindToggleButton(elementId, callback) {
    document.getElementById(elementId)?.addEventListener('click', callback);
}

function bindToggleButtonByClass(className, callback) {
    document.querySelector(`.${className}`)?.addEventListener('click', event => {
        event.preventDefault();
        callback();
    });
}

function toggleLessons() {
    toggleSection('lessons-content', 'instruments-content');
}

function toggleInstruments() {
    toggleSection('instruments-content', 'lessons-content');
}

function toggleSection(activeSectionId, inactiveSectionId) {
    const activeSection = document.getElementById(activeSectionId);
    const inactiveSection = document.getElementById(inactiveSectionId);

    activeSection?.classList.toggle('active');
    inactiveSection?.classList.remove('active');

    scrollToSection(activeSectionId);
}

function scrollToSection(sectionId) {
    const section = document.getElementById(sectionId);

    if (!section) {
        return;
    }

    window.scrollTo({
        top: section.offsetTop - 80,
        behavior: 'smooth',
    });
}