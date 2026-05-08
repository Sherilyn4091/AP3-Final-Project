/*
|--------------------------------------------------------------------------
| Student Schedule Page Scripts
|--------------------------------------------------------------------------
|
| Handles schedule page interactions:
| - lesson details modal
| - JSON loading through /student/schedule/{id}/details
|
*/

document.addEventListener('DOMContentLoaded', function () {
    initializeLessonDetailsModal();
});

function initializeLessonDetailsModal() {
    const modal = document.getElementById('lessonModal');

    if (!modal) {
        return;
    }

    document.querySelectorAll('[data-schedule-details-button]').forEach(function (button) {
        button.addEventListener('click', function () {
            openLessonDetails(button.dataset.scheduleId);
        });
    });

    document.querySelectorAll('[data-close-lesson-modal]').forEach(function (button) {
        button.addEventListener('click', closeLessonModal);
    });

    modal.addEventListener('click', function (event) {
        if (event.target === modal) {
            closeLessonModal();
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeLessonModal();
        }
    });
}

function openLessonDetails(scheduleId) {
    const modal = document.getElementById('lessonModal');
    const content = document.getElementById('lessonDetailsContent');

    if (!modal || !content || !scheduleId) {
        return;
    }

    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.classList.add('overflow-hidden');

    content.innerHTML = '<p class="text-sm font-semibold text-[#44576D]">Loading lesson details...</p>';

    fetch(`/student/schedule/${scheduleId}/details`)
        .then(function (response) {
            if (!response.ok) {
                throw new Error('Unable to load lesson details.');
            }

            return response.json();
        })
        .then(function (data) {
            if (data.error) {
                throw new Error(data.error);
            }

            content.innerHTML = `
                <div class="grid gap-4 sm:grid-cols-2">
                    ${detailCard('Date', data.schedule_date)}
                    ${detailCard('Time', `${data.start_time} – ${data.end_time}`)}
                    ${detailCard('Instrument', data.instrument)}
                    ${detailCard('Instructor', data.instructor)}
                    ${detailCard('Room', data.room_number)}
                    ${detailCard('Status', data.status)}
                    ${wideDetailCard('Lesson Topic', data.lesson_topic || 'Regular Lesson')}
                    ${wideDetailCard('Lesson Content', data.lesson_content || 'No lesson content added yet.')}
                    ${wideDetailCard('Notes', data.notes || 'No notes added yet.')}
                </div>
            `;
        })
        .catch(function (error) {
            console.error(error);
            content.innerHTML = '<p class="rounded-2xl border border-[#C56B5F]/40 bg-[#F6EFEC] p-4 text-sm font-semibold text-[#523D35]">Unable to load lesson details.</p>';
        });
}

function closeLessonModal() {
    const modal = document.getElementById('lessonModal');

    if (!modal) {
        return;
    }

    modal.classList.add('hidden');
    modal.classList.remove('flex');
    document.body.classList.remove('overflow-hidden');
}

function detailCard(label, value) {
    return `
        <div class="rounded-2xl border border-[#D8DDD8] bg-[#FCFCFA] p-4">
            <p class="text-xs font-bold uppercase tracking-wide text-[#768A96]">${escapeHtml(label)}</p>
            <p class="mt-1 text-sm font-bold text-[#223030]">${escapeHtml(value || '—')}</p>
        </div>
    `;
}

function wideDetailCard(label, value) {
    return `
        <div class="rounded-2xl border border-[#D8DDD8] bg-[#FCFCFA] p-4 sm:col-span-2">
            <p class="text-xs font-bold uppercase tracking-wide text-[#768A96]">${escapeHtml(label)}</p>
            <p class="mt-1 whitespace-pre-line text-sm font-semibold leading-relaxed text-[#223030]">${escapeHtml(value || '—')}</p>
        </div>
    `;
}

function escapeHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

/*
|--------------------------------------------------------------------------
| Temporary Backward Compatibility
|--------------------------------------------------------------------------
| These keep older onclick markup from breaking while you replace it with
| data-schedule-details-button and data-close-lesson-modal.
*/
window.showLessonDetails = openLessonDetails;
window.closeLessonModal = closeLessonModal;
