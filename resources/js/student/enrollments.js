/*
|--------------------------------------------------------------------------
| Student Enrollments Page Scripts
|--------------------------------------------------------------------------
|
| Handles enrollment page interactions:
| - closeable session messages
| - reusable modal open/close behavior
| - instructor loading for edit enrollment modals
|
*/

document.addEventListener('DOMContentLoaded', function () {
    initializeEnrollmentAlerts();
    initializeEnrollmentModals();
    initializeEditEnrollmentInstructorFilters();
});

function initializeEnrollmentAlerts() {
    document.querySelectorAll('[data-close-alert]').forEach(function (button) {
        button.addEventListener('click', function () {
            button.closest('[data-alert]')?.remove();
        });
    });
}

function initializeEnrollmentModals() {
    document.querySelectorAll('[data-open-modal]').forEach(function (button) {
        button.addEventListener('click', function () {
            openModal(button.dataset.openModal);
        });
    });

    document.querySelectorAll('[data-close-modal]').forEach(function (button) {
        button.addEventListener('click', function () {
            closeModal(button.dataset.closeModal);
        });
    });

    document.querySelectorAll('.fixed[id]').forEach(function (modal) {
        modal.addEventListener('click', function (event) {
            if (event.target === modal) {
                closeModal(modal.id);
            }
        });
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            document.querySelectorAll('.fixed[id]').forEach(function (modal) {
                closeModal(modal.id);
            });
        }
    });
}

function openModal(id) {
    const modal = document.getElementById(id);

    if (!modal) {
        return;
    }

    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.classList.add('overflow-hidden');
}

function closeModal(id) {
    const modal = document.getElementById(id);

    if (!modal) {
        return;
    }

    modal.classList.add('hidden');
    modal.classList.remove('flex');
    document.body.classList.remove('overflow-hidden');
}

function initializeEditEnrollmentInstructorFilters() {
    document.querySelectorAll('.edit-instrument').forEach(function (select) {
        loadQualifiedInstructorsForEdit(select);

        select.addEventListener('change', function () {
            select.dataset.selectedInstructor = '';
            loadQualifiedInstructorsForEdit(select);
        });
    });
}

function loadQualifiedInstructorsForEdit(instrumentSelect) {
    const targetId = instrumentSelect.dataset.target;
    const selectedInstructorId = instrumentSelect.dataset.selectedInstructor;
    const instructorSelect = document.getElementById(targetId);

    if (!instructorSelect || !instrumentSelect.value) {
        return;
    }

    instructorSelect.innerHTML = '<option value="">Loading qualified instructors...</option>';

    fetch(`/student/api/instructors-by-instrument/${instrumentSelect.value}`)
        .then(function (response) {
            if (!response.ok) {
                throw new Error('Unable to load instructors.');
            }

            return response.json();
        })
        .then(function (instructors) {
            instructorSelect.innerHTML = '<option value="">Select instructor</option>';

            instructors.forEach(function (instructor) {
                const option = document.createElement('option');
                option.value = instructor.instructor_id;
                option.textContent = instructor.full_name || `${instructor.first_name} ${instructor.last_name}`;

                if (String(selectedInstructorId) === String(instructor.instructor_id)) {
                    option.selected = true;
                }

                instructorSelect.appendChild(option);
            });
        })
        .catch(function (error) {
            console.error('Unable to load instructors:', error);
            instructorSelect.innerHTML = '<option value="">Unable to load instructors</option>';
        });
}
