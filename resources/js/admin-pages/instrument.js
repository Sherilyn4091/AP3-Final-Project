/*
|--------------------------------------------------------------------------
| Admin Instruments Page
|--------------------------------------------------------------------------
|
| Handles:
| - Add / edit instrument modal
| - View instrument details and connected students
| - Activate / deactivate instrument
| - Relationship-aware error messages
| - Smooth count-up animation for statistics
|
*/

(function () {
    'use strict';

    const CONFIG = window.adminInstrumentConfig || {};

    const state = {
        mode: 'create',
        currentInstrumentId: null,
        isSystemInstrument: false,
    };

    const DOM = {
        modal: document.getElementById('instrumentModal'),
        viewModal: document.getElementById('instrumentViewModal'),
        form: document.getElementById('instrumentForm'),
        submitButton: document.getElementById('instrumentSubmitButton'),

        modalTitle: document.getElementById('instrumentModalTitle'),
        modalSubtitle: document.getElementById('instrumentModalSubtitle'),
        systemNotice: document.getElementById('systemNotice'),

        instrumentId: document.getElementById('instrumentId'),
        instrumentName: document.getElementById('instrumentName'),
        instrumentCategory: document.getElementById('instrumentCategory'),
        instrumentDescription: document.getElementById('instrumentDescription'),

        viewInstrumentName: document.getElementById('viewInstrumentName'),
        viewInstrumentMeta: document.getElementById('viewInstrumentMeta'),
        viewActiveStudents: document.getElementById('viewActiveStudents'),
        viewTotalStudents: document.getElementById('viewTotalStudents'),
        viewActiveEnrollments: document.getElementById('viewActiveEnrollments'),
        viewTotalEnrollments: document.getElementById('viewTotalEnrollments'),
        connectedStudentsContainer: document.getElementById('connectedStudentsContainer'),

        toast: document.getElementById('instrumentToast'),
    };

    /*
    |--------------------------------------------------------------------------
    | Utility Helpers
    |--------------------------------------------------------------------------
    */

    function buildUrl(template, id) {
        return String(template || '').replace('__ID__', encodeURIComponent(id));
    }

    function openFlexModal(modal) {
        if (!modal) return;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.classList.add('overflow-hidden');
    }

    function closeFlexModal(modal) {
        if (!modal) return;
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.classList.remove('overflow-hidden');
    }

    function showToast(message, type = 'info') {
        if (!DOM.toast) {
            alert(message);
            return;
        }

        DOM.toast.textContent = message;
        DOM.toast.classList.remove('hidden');

        if (type === 'error') {
            DOM.toast.style.backgroundColor = '#F6EFEC';
            DOM.toast.style.color = '#523D35';
            DOM.toast.style.borderColor = '#E7D6CE';
        } else {
            DOM.toast.style.backgroundColor = '#FFFFFF';
            DOM.toast.style.color = '#223030';
            DOM.toast.style.borderColor = '#D8DDD8';
        }

        window.clearTimeout(DOM.toast.dataset.timer);

        DOM.toast.dataset.timer = window.setTimeout(() => {
            DOM.toast.classList.add('hidden');
        }, 3800);
    }

    function setSubmitLoading(isLoading) {
        if (!DOM.submitButton) return;

        DOM.submitButton.disabled = isLoading;
        DOM.submitButton.textContent = isLoading ? 'Saving...' : 'Save Instrument';
    }

    async function parseJsonResponse(response) {
        const text = await response.text();

        try {
            return text ? JSON.parse(text) : {};
        } catch (error) {
            return {
                success: false,
                message: text || 'Invalid server response.',
            };
        }
    }

    function clearErrors() {
        document.querySelectorAll('[data-error-for]').forEach((errorElement) => {
            errorElement.textContent = '';
            errorElement.classList.add('hidden');
        });
    }

    function showValidationErrors(errors) {
        clearErrors();

        Object.entries(errors || {}).forEach(([field, messages]) => {
            const errorElement = document.querySelector(`[data-error-for="${field}"]`);

            if (!errorElement) return;

            errorElement.textContent = Array.isArray(messages) ? messages[0] : messages;
            errorElement.classList.remove('hidden');
        });
    }

    function resetForm() {
        if (!DOM.form) return;

        DOM.form.reset();
        clearErrors();

        state.mode = 'create';
        state.currentInstrumentId = null;
        state.isSystemInstrument = false;

        if (DOM.instrumentId) DOM.instrumentId.value = '';
        if (DOM.instrumentName) DOM.instrumentName.disabled = false;
        if (DOM.instrumentCategory) DOM.instrumentCategory.disabled = false;
        if (DOM.systemNotice) DOM.systemNotice.classList.add('hidden');

        if (DOM.modalTitle) DOM.modalTitle.textContent = 'Add Instrument';
        if (DOM.modalSubtitle) {
            DOM.modalSubtitle.textContent = 'Create a new custom instrument record.';
        }

        if (DOM.submitButton) DOM.submitButton.textContent = 'Save Instrument';
    }

    /*
    |--------------------------------------------------------------------------
    | Add / Edit Modal
    |--------------------------------------------------------------------------
    */

    window.openAddInstrumentModal = function () {
        resetForm();
        openFlexModal(DOM.modal);
    };

    window.closeInstrumentModal = function () {
        closeFlexModal(DOM.modal);
        resetForm();
    };

    window.editInstrument = async function (instrumentId) {
        resetForm();

        try {
            const response = await fetch(buildUrl(CONFIG.showUrlTemplate, instrumentId), {
                method: 'GET',
                headers: {
                    Accept: 'application/json',
                },
            });

            const data = await parseJsonResponse(response);

            if (!response.ok || !data.success) {
                throw new Error(data.message || 'Unable to load instrument details.');
            }

            const instrument = data.instrument;

            state.mode = 'edit';
            state.currentInstrumentId = instrument.instrument_id;
            state.isSystemInstrument = Boolean(instrument.is_system);

            if (DOM.modalTitle) DOM.modalTitle.textContent = 'Edit Instrument';
            if (DOM.modalSubtitle) {
                DOM.modalSubtitle.textContent = state.isSystemInstrument
                    ? 'Protected system instrument. Only description can be updated.'
                    : 'Update this custom instrument record.';
            }

            if (DOM.instrumentId) DOM.instrumentId.value = instrument.instrument_id;
            if (DOM.instrumentName) DOM.instrumentName.value = instrument.instrument_name || '';
            if (DOM.instrumentCategory) DOM.instrumentCategory.value = instrument.category || '';
            if (DOM.instrumentDescription) DOM.instrumentDescription.value = instrument.description || '';

            if (state.isSystemInstrument) {
                if (DOM.instrumentName) DOM.instrumentName.disabled = true;
                if (DOM.instrumentCategory) DOM.instrumentCategory.disabled = true;
                if (DOM.systemNotice) DOM.systemNotice.classList.remove('hidden');
            }

            openFlexModal(DOM.modal);
        } catch (error) {
            showToast(error.message, 'error');
        }
    };

    /*
    |--------------------------------------------------------------------------
    | Form Submit
    |--------------------------------------------------------------------------
    */

    DOM.form?.addEventListener('submit', async function (event) {
        event.preventDefault();

        clearErrors();
        setSubmitLoading(true);

        const formData = new FormData(DOM.form);

        /*
        |--------------------------------------------------------------------------
        | System Instrument Update
        |--------------------------------------------------------------------------
        |
        | Disabled inputs are not included in FormData. For system instruments,
        | that is okay because the backend only accepts description updates.
        |
        */
        const isEdit = state.mode === 'edit' && state.currentInstrumentId;
        const url = isEdit
            ? buildUrl(CONFIG.updateUrlTemplate, state.currentInstrumentId)
            : CONFIG.storeUrl;

        const method = isEdit ? 'PUT' : 'POST';

        try {
            const response = await fetch(url, {
                method,
                headers: {
                    'X-CSRF-TOKEN': CONFIG.csrfToken,
                    Accept: 'application/json',
                },
                body: formData,
            });

            const data = await parseJsonResponse(response);

            if (response.status === 422) {
                showValidationErrors(data.errors || {});
                throw new Error(data.message || 'Please check the form fields.');
            }

            if (!response.ok || !data.success) {
                throw new Error(data.details || data.message || 'Unable to save instrument.');
            }

            showToast(data.message || 'Instrument saved successfully.');
            closeFlexModal(DOM.modal);

            window.setTimeout(() => {
                window.location.reload();
            }, 650);
        } catch (error) {
            showToast(error.message, 'error');
        } finally {
            setSubmitLoading(false);
        }
    });

    /*
    |--------------------------------------------------------------------------
    | View Instrument Details
    |--------------------------------------------------------------------------
    */

    window.viewInstrument = async function (instrumentId) {
        openFlexModal(DOM.viewModal);

        if (DOM.viewInstrumentName) DOM.viewInstrumentName.textContent = 'Loading...';
        if (DOM.viewInstrumentMeta) DOM.viewInstrumentMeta.textContent = 'Fetching instrument details.';
        if (DOM.connectedStudentsContainer) {
            DOM.connectedStudentsContainer.innerHTML = '<p class="text-sm text-[#768A96]">Loading students...</p>';
        }

        try {
            const [detailsResponse, studentsResponse] = await Promise.all([
                fetch(buildUrl(CONFIG.showUrlTemplate, instrumentId), {
                    method: 'GET',
                    headers: { Accept: 'application/json' },
                }),
                fetch(buildUrl(CONFIG.studentsUrlTemplate, instrumentId), {
                    method: 'GET',
                    headers: { Accept: 'application/json' },
                }),
            ]);

            const details = await parseJsonResponse(detailsResponse);
            const studentsData = await parseJsonResponse(studentsResponse);

            if (!detailsResponse.ok || !details.success) {
                throw new Error(details.message || 'Unable to load instrument details.');
            }

            if (!studentsResponse.ok || !studentsData.success) {
                throw new Error(studentsData.message || 'Unable to load connected students.');
            }

            const instrument = details.instrument;
            const usage = details.usage || {};
            const counts = usage.counts || {};

            if (DOM.viewInstrumentName) {
                DOM.viewInstrumentName.textContent = instrument.instrument_name || 'Instrument Details';
            }

            if (DOM.viewInstrumentMeta) {
                const typeLabel = instrument.is_system ? 'System instrument' : 'Custom instrument';
                const statusLabel = instrument.is_active ? 'Active' : 'Inactive';
                DOM.viewInstrumentMeta.textContent = `${instrument.category || 'Uncategorized'} • ${typeLabel} • ${statusLabel}`;
            }

            if (DOM.viewActiveStudents) DOM.viewActiveStudents.textContent = counts.active_students || 0;
            if (DOM.viewTotalStudents) DOM.viewTotalStudents.textContent = counts.total_students || 0;
            if (DOM.viewActiveEnrollments) DOM.viewActiveEnrollments.textContent = counts.active_enrollments || 0;
            if (DOM.viewTotalEnrollments) DOM.viewTotalEnrollments.textContent = counts.total_enrollments || 0;

            renderConnectedStudents(studentsData.students || []);
        } catch (error) {
            showToast(error.message, 'error');

            if (DOM.connectedStudentsContainer) {
                DOM.connectedStudentsContainer.innerHTML = `
                    <div class="rounded-2xl border border-[#E7D6CE] bg-[#F6EFEC] px-4 py-3 text-sm font-semibold text-[#523D35]">
                        ${escapeHtml(error.message)}
                    </div>
                `;
            }
        }
    };

    window.closeInstrumentViewModal = function () {
        closeFlexModal(DOM.viewModal);
    };

    function renderConnectedStudents(students) {
        if (!DOM.connectedStudentsContainer) return;

        if (!students.length) {
            DOM.connectedStudentsContainer.innerHTML = `
                <div class="rounded-2xl border border-[#D8DDD8] bg-[#FCFCFA] px-4 py-6 text-center">
                    <p class="text-sm font-semibold text-[#768A96]">No connected students found for this instrument.</p>
                </div>
            `;
            return;
        }

        const rows = students.map((student) => {
            const activeBadge = student.is_active
                ? '<span class="rounded-full bg-[#F1F3EF] px-2 py-1 text-xs font-bold text-[#223030]">Active</span>'
                : '<span class="rounded-full bg-[#F6EFEC] px-2 py-1 text-xs font-bold text-[#523D35]">Inactive</span>';

            return `
                <tr class="border-b border-[#EEF1EC] last:border-0">
                    <td class="px-3 py-3">
                        <p class="font-bold text-[#223030]">${escapeHtml(student.student_name || 'Unnamed Student')}</p>
                        <p class="text-xs text-[#768A96]" style="font-family: 'JetBrains Mono', monospace;">ID: ${escapeHtml(student.student_id)}</p>
                    </td>
                    <td class="px-3 py-3 text-sm text-[#44576D]">
                        ${escapeHtml(student.contact_email || 'No email')}
                    </td>
                    <td class="px-3 py-3 text-sm text-[#44576D]">
                        ${escapeHtml(student.phone || 'No phone')}
                    </td>
                    <td class="px-3 py-3 text-sm text-[#44576D]">
                        ${escapeHtml(student.active_enrollments_count || 0)} active / ${escapeHtml(student.enrollments_count || 0)} total
                    </td>
                    <td class="px-3 py-3">
                        ${activeBadge}
                    </td>
                </tr>
            `;
        }).join('');

        DOM.connectedStudentsContainer.innerHTML = `
            <table class="min-w-[760px] w-full text-left">
                <thead>
                    <tr class="border-b border-[#D8DDD8]">
                        <th class="px-3 py-3 text-xs font-bold uppercase tracking-wide text-[#768A96]">Student</th>
                        <th class="px-3 py-3 text-xs font-bold uppercase tracking-wide text-[#768A96]">Email</th>
                        <th class="px-3 py-3 text-xs font-bold uppercase tracking-wide text-[#768A96]">Phone</th>
                        <th class="px-3 py-3 text-xs font-bold uppercase tracking-wide text-[#768A96]">Enrollments</th>
                        <th class="px-3 py-3 text-xs font-bold uppercase tracking-wide text-[#768A96]">Status</th>
                    </tr>
                </thead>
                <tbody>
                    ${rows}
                </tbody>
            </table>
        `;
    }

    /*
    |--------------------------------------------------------------------------
    | Activate / Deactivate
    |--------------------------------------------------------------------------
    */

    window.toggleInstrumentStatus = async function (instrumentId, isCurrentlyActive) {
        const actionText = isCurrentlyActive ? 'deactivate' : 'activate';

        if (!confirm(`Are you sure you want to ${actionText} this instrument?`)) {
            return;
        }

        try {
            const response = await fetch(buildUrl(CONFIG.toggleUrlTemplate, instrumentId), {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': CONFIG.csrfToken,
                    Accept: 'application/json',
                },
            });

            const data = await parseJsonResponse(response);

            if (!response.ok || !data.success) {
                throw new Error(data.details || data.message || `Unable to ${actionText} instrument.`);
            }

            showToast(data.message || 'Instrument status updated.');

            window.setTimeout(() => {
                window.location.reload();
            }, 650);
        } catch (error) {
            showToast(error.message, 'error');
        }
    };

    /*
    |--------------------------------------------------------------------------
    | Optional Direct Delete Function
    |--------------------------------------------------------------------------
    |
    | Kept for compatibility in case other existing buttons call deleteInstrument().
    | Backend still performs a safe soft-deactivate only.
    |
    */

    window.deleteInstrument = async function (instrumentId) {
        if (!confirm('Deactivate this instrument? This action will be blocked if it is connected to active records.')) {
            return;
        }

        try {
            const response = await fetch(buildUrl(CONFIG.destroyUrlTemplate, instrumentId), {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': CONFIG.csrfToken,
                    Accept: 'application/json',
                },
            });

            const data = await parseJsonResponse(response);

            if (!response.ok || !data.success) {
                throw new Error(data.details || data.message || 'Unable to deactivate instrument.');
            }

            showToast(data.message || 'Instrument deactivated.');

            window.setTimeout(() => {
                window.location.reload();
            }, 650);
        } catch (error) {
            showToast(error.message, 'error');
        }
    };

    /*
    |--------------------------------------------------------------------------
    | Count-Up Animation
    |--------------------------------------------------------------------------
    */

    function animateCountUp(element) {
        const target = Number(element.dataset.count || 0);
        const duration = 900;
        const startTime = performance.now();

        function tick(now) {
            const progress = Math.min((now - startTime) / duration, 1);
            const easedProgress = 1 - Math.pow(1 - progress, 3);
            const value = Math.round(target * easedProgress);

            element.textContent = value.toLocaleString();

            if (progress < 1) {
                requestAnimationFrame(tick);
            }
        }

        requestAnimationFrame(tick);
    }

    function initCountAnimations() {
        const counters = document.querySelectorAll('.js-count-up');

        if (!counters.length) return;

        if ('IntersectionObserver' in window) {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach((entry) => {
                    if (!entry.isIntersecting) return;

                    animateCountUp(entry.target);
                    observer.unobserve(entry.target);
                });
            }, { threshold: 0.25 });

            counters.forEach((counter) => observer.observe(counter));
            return;
        }

        counters.forEach(animateCountUp);
    }

    /*
    |--------------------------------------------------------------------------
    | Keyboard / Outside Click Handling
    |--------------------------------------------------------------------------
    */

    document.addEventListener('keydown', function (event) {
        if (event.key !== 'Escape') return;

        closeFlexModal(DOM.modal);
        closeFlexModal(DOM.viewModal);
    });

    DOM.modal?.addEventListener('click', function (event) {
        if (event.target === DOM.modal) {
            window.closeInstrumentModal();
        }
    });

    DOM.viewModal?.addEventListener('click', function (event) {
        if (event.target === DOM.viewModal) {
            window.closeInstrumentViewModal();
        }
    });

    /*
    |--------------------------------------------------------------------------
    | HTML Escape Helper
    |--------------------------------------------------------------------------
    */

    function escapeHtml(value) {
        return String(value ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    /*
    |--------------------------------------------------------------------------
    | Initialize
    |--------------------------------------------------------------------------
    */

    document.addEventListener('DOMContentLoaded', function () {
        initCountAnimations();
    });
})();