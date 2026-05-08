/*
|--------------------------------------------------------------------------
| resources/js/student/enroll-form.js
|
| Student Enrollment Form Scripts
|--------------------------------------------------------------------------
|
| Handles the enrollment form only:
| - loads qualified instructors when the selected instrument changes
|
*/

document.addEventListener('DOMContentLoaded', function () {
    initializeEnrollmentFormInstructorFilter();
});

/**
 * Loads instructors that match the selected instrument specialization.
 */
function initializeEnrollmentFormInstructorFilter() {
    const instrumentSelect = document.getElementById('instrument_id');
    const instructorSelect = document.getElementById('instructor_id');

    if (!instrumentSelect || !instructorSelect) {
        return;
    }

    instrumentSelect.addEventListener('change', function () {
        loadQualifiedInstructorsForEnrollment(instrumentSelect, instructorSelect);
    });
}

function loadQualifiedInstructorsForEnrollment(instrumentSelect, instructorSelect) {
    const instrumentId = instrumentSelect.value;

    instructorSelect.innerHTML = '<option value="" class="text-gray-500">Select an instructor</option>';

    if (!instrumentId) {
        return;
    }

    instructorSelect.innerHTML = '<option value="" class="text-gray-500">Loading qualified instructors...</option>';

    fetch(`/student/api/instructors-by-instrument/${instrumentId}`)
        .then(function (response) {
            if (!response.ok) {
                throw new Error('Unable to load instructors.');
            }

            return response.json();
        })
        .then(function (instructors) {
            instructorSelect.innerHTML = '<option value="" class="text-gray-500">Select an instructor</option>';

            instructors.forEach(function (instructor) {
                const option = document.createElement('option');
                option.value = instructor.instructor_id;
                option.className = 'text-gray-900';
                option.textContent = instructor.full_name || `${instructor.first_name} ${instructor.last_name}`;

                instructorSelect.appendChild(option);
            });
        })
        .catch(function (error) {
            console.error('Unable to load instructors:', error);
            instructorSelect.innerHTML = '<option value="">Unable to load instructors</option>';
        });
}
