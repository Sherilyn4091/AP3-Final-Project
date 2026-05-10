/**
 * ============================================================================
 * LESSON SESSION PACKAGE MANAGEMENT - CLIENT-SIDE JAVASCRIPT
 * resources/js/admin-pages/lesson-session.js
 * ============================================================================
 * Handles:
 * - Modal rendering for create/edit forms
 * - AJAX create/update/delete/toggle requests
 * - Safe enrollment usage modal rendering
 * - Toast notifications
 * - Small UI helpers for package duration and validation
 *
 * Safety improvements:
 * - Escapes dynamic text before placing it in HTML templates
 * - Uses centralized API helpers to read JSON errors cleanly
 * - Keeps used package core fields read-only in the edit modal
 * ============================================================================
 */

const csrfMeta = document.querySelector('meta[name="csrf-token"]');
const csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';

// ============================================================================
// SMALL UTILITIES
// ============================================================================

/**
 * Escape dynamic values before rendering them inside HTML strings.
 * This avoids stored HTML/script injection from database-driven values.
 */
function escapeHtml(value) {
    return String(value ?? '').replace(/[&<>"']/g, function (char) {
        return {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;',
        }[char];
    });
}

/**
 * Convert possibly string-based numbers from JSON/FormData to safe numbers.
 */
function toNumber(value, fallback = 0) {
    const number = Number(value);
    return Number.isFinite(number) ? number : fallback;
}

/**
 * Build standard headers for Laravel JSON requests.
 */
function getJsonHeaders() {
    return {
        'X-CSRF-TOKEN': csrfToken,
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    };
}

/**
 * Fetch JSON and throw a readable message if the response is not successful.
 */
async function fetchJson(url, options = {}) {
    const response = await fetch(url, options);
    const text = await response.text();
    let data = {};

    try {
        data = text ? JSON.parse(text) : {};
    } catch (error) {
        data = { message: text || 'Invalid server response.' };
    }

    if (!response.ok) {
        const message = data.errors
            ? Object.values(data.errors).flat().join('\n')
            : (data.message || 'Request failed.');

        throw new Error(message);
    }

    return data;
}

/**
 * Format currency values consistently for display.
 */
function formatCurrency(value) {
    return '₱' + toNumber(value).toLocaleString(undefined, {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
}

/**
 * Format date values safely.
 */
function formatDate(value) {
    if (!value) return 'N/A';

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return escapeHtml(value);
    }

    return date.toLocaleDateString(undefined, {
        year: 'numeric',
        month: 'short',
        day: '2-digit',
    });
}

// ============================================================================
// MODAL FUNCTIONS
// ============================================================================

/**
 * Open modal for adding a new lesson package.
 */
function openAddModal() {
    const modalHTML = generateModalHTML('create', null);
    showModal(modalHTML);
}

/**
 * Open modal for editing an existing lesson package.
 */
async function editSession(sessionId) {
    try {
        const data = await fetchJson(`/admin/lesson-sessions/${encodeURIComponent(sessionId)}/edit`, {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
        });

        if (!data.success) {
            throw new Error(data.message || 'Failed to load package data.');
        }

        showModal(generateModalHTML('edit', data.session));
    } catch (error) {
        console.error('Load package error:', error);
        showToast(error.message || 'An error occurred while loading package data.', 'error');
    }
}

/**
 * Generate modal HTML for create or edit mode.
 */
function generateModalHTML(mode, session) {
    const isEdit = mode === 'edit';
    const usageCount = isEdit ? toNumber(session.usage_count) : 0;
    const isLocked = isEdit && usageCount > 0;

    const sessionId = isEdit ? toNumber(session.session_id) : null;
    const sessionCount = isEdit ? toNumber(session.session_count) : '';
    const sessionName = isEdit ? escapeHtml(session.session_name) : '';
    const durationMinutes = isEdit ? toNumber(session.duration_minutes, 60) : 60;
    const price = isEdit ? toNumber(session.price).toFixed(2) : '';
    const description = isEdit ? escapeHtml(session.description || '') : '';

    const title = isEdit ? 'Edit lesson package' : 'Add new lesson package';
    const submitText = isEdit ? 'Update package' : 'Create package';

    return `
        <div class="w-full max-w-3xl overflow-hidden rounded-2xl bg-white shadow-2xl">
            <div class="sticky top-0 z-10 flex items-center justify-between bg-gradient-to-r from-secondary-blue to-forest-green px-4 py-3">
                <div>
                    <h2 class="text-xl font-extrabold text-white">${title}</h2>
                    <p class="mt-1 text-sm text-white text-opacity-90">
                        ${isLocked ? 'Core values are locked because this package already has enrollments.' : 'Fill in package details used during student enrollment.'}
                    </p>
                </div>
                <button type="button" onclick="closeModal()" class="rounded-full p-2 text-white transition-all hover:bg-white hover:bg-opacity-20" aria-label="Close modal">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <form id="session-form" class="max-h-[78vh] space-y-6 overflow-y-auto p-4" onsubmit="handleSubmit(event, '${mode}', ${sessionId})">
                ${isEdit ? `<input type="hidden" name="session_id" value="${sessionId}">` : ''}

                ${isLocked ? generateLockedPackageFields(sessionCount, durationMinutes, price) : generateEditablePackageFields(sessionCount, durationMinutes, price)}

                <div>
                    <label class="label-required mb-2 block text-sm font-bold text-gray-700">Package name</label>
                    <input type="text"
                           name="session_name"
                           value="${sessionName}"
                           maxlength="200"
                           placeholder="Example: 5 Sessions Package"
                           class="w-full rounded-xl border-2 border-gray-300 px-4 py-3 transition-all focus:border-secondary-blue focus:ring-2 focus:ring-secondary-blue">
                    <p class="mt-1 text-xs text-gray-500">Leave blank to auto-generate based on session count.</p>
                </div>

                <div class="rounded-xl border-l-4 border-secondary-blue bg-secondary-blue bg-opacity-10 px-4 py-4">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-sm font-bold text-gray-900">Total package duration</p>
                            <p class="mt-1 text-xs text-gray-700">Session count × duration per session</p>
                        </div>
                        <div class="text-right">
                            <p id="total-hours-display" class="text-2xl font-extrabold text-gray-900">
                                ${calculateTotalHours(sessionCount, durationMinutes)}
                            </p>
                            <p class="text-xs font-semibold text-gray-700">hours</p>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-bold text-gray-700">Description</label>
                    <textarea name="description"
                              rows="4"
                              maxlength="2000"
                              placeholder="Optional notes, inclusions, or package description."
                              class="w-full resize-none rounded-xl border-2 border-gray-300 px-4 py-3 transition-all focus:border-secondary-blue focus:ring-2 focus:ring-secondary-blue">${description}</textarea>
                </div>

                ${isLocked ? `
                    <div class="rounded-xl border-l-4 border-golden-yellow bg-golden-yellow bg-opacity-20 px-4 py-4">
                        <div class="flex items-start gap-3">
                            <svg class="mt-0.5 h-5 w-5 flex-shrink-0 text-golden-yellow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                            <div>
                                <p class="text-sm font-bold text-gray-800">This package is used in ${usageCount} enrollment${usageCount === 1 ? '' : 's'}.</p>
                                <p class="mt-1 text-xs text-gray-700">For data safety, session count, duration, and price are read-only. You can still update the name and description.</p>
                            </div>
                        </div>
                    </div>
                ` : ''}

                <div class="flex flex-col gap-3 border-t border-gray-200 pt-5 sm:flex-row">
                    <button type="button" onclick="closeModal()" class="flex-1 rounded-xl bg-gray-200 px-4 py-3 font-bold text-gray-700 transition-all hover:bg-gray-300">
                        Cancel
                    </button>
                    <button type="submit" class="flex-1 rounded-xl bg-forest-green px-4 py-3 font-bold text-white transition-all hover:bg-forest-green-dark">
                        ${submitText}
                    </button>
                </div>
            </form>
        </div>
    `;
}

/**
 * Render editable fields for new or unused packages.
 */
function generateEditablePackageFields(sessionCount, durationMinutes, price) {
    const isPreset = [5, 10, 20].includes(toNumber(sessionCount));
    const selectedCustom = sessionCount && !isPreset;

    return `
        <div>
            <label class="label-required mb-2 block text-sm font-bold text-gray-700">Session count</label>
            <select id="session-count-select"
                    name="session_count_dropdown"
                    onchange="handleSessionCountChange(this.value, ${toNumber(sessionCount) || 0})"
                    class="w-full rounded-xl border-2 border-gray-300 px-4 py-3 transition-all focus:border-secondary-blue focus:ring-2 focus:ring-secondary-blue">
                <option value="">Select session count</option>
                <option value="5" ${toNumber(sessionCount) === 5 ? 'selected' : ''}>5 sessions</option>
                <option value="10" ${toNumber(sessionCount) === 10 ? 'selected' : ''}>10 sessions</option>
                <option value="20" ${toNumber(sessionCount) === 20 ? 'selected' : ''}>20 sessions</option>
                <option value="custom" ${selectedCustom ? 'selected' : ''}>Other custom package</option>
            </select>

            <input type="hidden" id="session-count-value" name="session_count" value="${escapeHtml(sessionCount)}" required>

            <div id="custom-session-input" class="mt-3 ${selectedCustom ? '' : 'hidden'}">
                <input type="number"
                       id="custom-session-count"
                       value="${selectedCustom ? escapeHtml(sessionCount) : ''}"
                       min="1"
                       max="100"
                       step="1"
                       oninput="updateCustomSessionCount(this.value)"
                       placeholder="Enter custom session count from 1 to 100"
                       class="w-full rounded-xl border-2 border-gray-300 px-4 py-3 transition-all focus:border-secondary-blue focus:ring-2 focus:ring-secondary-blue">
            </div>
            <p class="mt-1 text-xs text-gray-500">Choose 5, 10, 20, or create a custom package.</p>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
                <label class="label-required mb-2 block text-sm font-bold text-gray-700">Duration per session</label>
                <input type="number"
                       name="duration_minutes"
                       value="${escapeHtml(durationMinutes)}"
                       min="15"
                       max="300"
                       step="5"
                       required
                       oninput="updateTotalHours(this.value, document.querySelector('[name=session_count]').value)"
                       class="w-full rounded-xl border-2 border-gray-300 px-4 py-3 transition-all focus:border-secondary-blue focus:ring-2 focus:ring-secondary-blue">
                <p id="duration-help" class="mt-1 text-xs text-gray-500">Default is 60 minutes per session.</p>
            </div>

            <div>
                <label class="label-required mb-2 block text-sm font-bold text-gray-700">Package price</label>
                <input type="number"
                       name="price"
                       value="${escapeHtml(price)}"
                       min="0"
                       max="999999.99"
                       step="0.01"
                       required
                       placeholder="Example: 3500.00"
                       class="w-full rounded-xl border-2 border-gray-300 px-4 py-3 transition-all focus:border-secondary-blue focus:ring-2 focus:ring-secondary-blue">
                <p class="mt-1 text-xs text-gray-500">Total price for all sessions.</p>
            </div>
        </div>
    `;
}

/**
 * Render read-only package fields for packages already used by enrollments.
 */
function generateLockedPackageFields(sessionCount, durationMinutes, price) {
    return `
        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <div>
                <label class="mb-2 block text-sm font-bold text-gray-700">Session count</label>
                <input type="number" name="session_count" value="${escapeHtml(sessionCount)}" readonly class="w-full rounded-xl border-2 border-gray-200 bg-gray-100 px-4 py-3 text-gray-700">
            </div>
            <div>
                <label class="mb-2 block text-sm font-bold text-gray-700">Duration per session</label>
                <input type="number" name="duration_minutes" value="${escapeHtml(durationMinutes)}" readonly class="w-full rounded-xl border-2 border-gray-200 bg-gray-100 px-4 py-3 text-gray-700">
            </div>
            <div>
                <label class="mb-2 block text-sm font-bold text-gray-700">Package price</label>
                <input type="number" name="price" value="${escapeHtml(price)}" readonly class="w-full rounded-xl border-2 border-gray-200 bg-gray-100 px-4 py-3 text-gray-700">
            </div>
        </div>
    `;
}

/**
 * Display modal with generated HTML.
 */
function showModal(html) {
    const modal = document.getElementById('session-modal');

    if (!modal) return;

    modal.innerHTML = html;
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.style.overflow = 'hidden';
}

/**
 * Close and clear the modal.
 */
function closeModal() {
    const modal = document.getElementById('session-modal');

    if (!modal) return;

    modal.classList.add('hidden');
    modal.classList.remove('flex');
    modal.innerHTML = '';
    document.body.style.overflow = 'auto';
}

// ============================================================================
// PACKAGE DURATION HELPERS
// ============================================================================

/**
 * Calculate total hours from session count and duration.
 */
function calculateTotalHours(sessionCount, durationMinutes) {
    const count = toNumber(sessionCount);
    const duration = toNumber(durationMinutes);

    if (count <= 0 || duration <= 0) return '—';

    return ((count * duration) / 60).toFixed(1);
}

/**
 * Update total hours display when duration or session count changes.
 */
function updateTotalHours(duration, sessionCount) {
    const display = document.getElementById('total-hours-display');
    const helpText = document.getElementById('duration-help');
    const total = calculateTotalHours(sessionCount, duration);

    if (display) {
        display.textContent = total;
    }

    if (helpText && sessionCount && duration) {
        const hoursPerSession = (toNumber(duration) / 60).toFixed(1);
        helpText.textContent = `${hoursPerSession} ${hoursPerSession === '1.0' ? 'hour' : 'hours'} per session × ${sessionCount} sessions = ${total} total hours`;
    }
}

/**
 * Handle session count dropdown change.
 */
function handleSessionCountChange(value, currentSessionCount) {
    const customInput = document.getElementById('custom-session-input');
    const customField = document.getElementById('custom-session-count');
    const hiddenInput = document.getElementById('session-count-value');
    const durationField = document.querySelector('[name=duration_minutes]');

    if (!customInput || !customField || !hiddenInput) return;

    if (value === 'custom') {
        customInput.classList.remove('hidden');
        customField.required = true;
        customField.focus();

        if (currentSessionCount && ![5, 10, 20].includes(toNumber(currentSessionCount))) {
            customField.value = currentSessionCount;
            hiddenInput.value = currentSessionCount;
        } else {
            hiddenInput.value = '';
        }
    } else {
        customInput.classList.add('hidden');
        customField.required = false;
        customField.value = '';
        hiddenInput.value = value;

        if (durationField && value) {
            updateTotalHours(durationField.value, value);
        }
    }
}

/**
 * Update hidden session_count when custom input changes.
 */
function updateCustomSessionCount(value) {
    const hiddenInput = document.getElementById('session-count-value');
    const durationField = document.querySelector('[name=duration_minutes]');

    if (!hiddenInput) return;

    hiddenInput.value = value;

    if (durationField && value) {
        updateTotalHours(durationField.value, value);
    }
}

// ============================================================================
// FORM SUBMISSION
// ============================================================================

/**
 * Handle form submission for create/edit.
 */
async function handleSubmit(event, mode, sessionId) {
    event.preventDefault();

    const form = event.target;
    const formData = new FormData(form);
    const data = Object.fromEntries(formData.entries());

    const url = mode === 'create'
        ? '/admin/lesson-sessions'
        : `/admin/lesson-sessions/${encodeURIComponent(sessionId)}`;

    const methodOverride = mode === 'create' ? 'POST' : 'PUT';

    try {
        const response = await fetchJson(url, {
            method: 'POST',
            headers: {
                ...getJsonHeaders(),
                'X-HTTP-Method-Override': methodOverride,
            },
            body: JSON.stringify(data),
        });

        if (!response.success) {
            throw new Error(response.message || 'Unable to save lesson package.');
        }

        showToast(response.message || 'Lesson package saved successfully.', 'success');
        closeModal();

        setTimeout(() => window.location.reload(), 800);
    } catch (error) {
        console.error('Save package error:', error);
        showToast(error.message || 'An error occurred while saving.', 'error');
    }
}

// ============================================================================
// STATUS AND DELETE ACTIONS
// ============================================================================

/**
 * Toggle active/inactive status of a lesson package.
 */
async function toggleSession(sessionId) {
    if (!confirm('Are you sure you want to change the status of this lesson package?')) {
        return;
    }

    try {
        const data = await fetchJson(`/admin/lesson-sessions/${encodeURIComponent(sessionId)}/toggle-status`, {
            method: 'POST',
            headers: getJsonHeaders(),
            body: JSON.stringify({}),
        });

        showToast(data.message || 'Status updated successfully.', 'success');
        setTimeout(() => window.location.reload(), 800);
    } catch (error) {
        console.error('Toggle package error:', error);
        showToast(error.message || 'An error occurred while updating status.', 'error');
    }
}

/**
 * Delete a lesson package.
 */
async function deleteSession(sessionId) {
    if (!confirm('Are you sure you want to delete this package? This action cannot be undone.')) {
        return;
    }

    try {
        const data = await fetchJson(`/admin/lesson-sessions/${encodeURIComponent(sessionId)}`, {
            method: 'DELETE',
            headers: getJsonHeaders(),
        });

        showToast(data.message || 'Lesson package deleted successfully.', 'success');
        setTimeout(() => window.location.reload(), 800);
    } catch (error) {
        console.error('Delete package error:', error);
        showToast(error.message || 'An error occurred while deleting.', 'error');
    }
}

// ============================================================================
// ENROLLMENT USAGE MODAL
// ============================================================================

/**
 * Return a safe status badge class.
 */
function getStatusClass(status) {
    const normalized = String(status || '').toLowerCase();

    if (normalized === 'active') return 'bg-forest-green text-white';
    if (normalized === 'completed') return 'bg-secondary-blue text-white';
    if (normalized === 'cancelled' || normalized === 'withdrawn') return 'bg-warm-coral text-white';
    if (normalized === 'withdrawal_requested') return 'bg-golden-yellow text-primary-dark';

    return 'bg-gray-400 text-white';
}

/**
 * View enrollments using a specific lesson package.
 */
async function viewSessionEnrollments(sessionId) {
    try {
        const data = await fetchJson(`/admin/lesson-sessions/${encodeURIComponent(sessionId)}/enrollments`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
            },
        });

        if (!data.success) {
            throw new Error(data.message || 'Unable to load enrollments.');
        }

        showModal(generateEnrollmentsModal(data));
    } catch (error) {
        console.error('Enrollment usage error:', error);
        showToast(error.message || 'An error occurred while loading enrollments.', 'error');
    }
}

/**
 * Generate enrollment usage modal HTML.
 */
function generateEnrollmentsModal(data) {
    const enrollments = Array.isArray(data.enrollments) ? data.enrollments : [];
    const sessionName = escapeHtml(data.session_name || 'Selected package');

    return `
        <div class="w-full max-w-3xl overflow-hidden rounded-2xl bg-white shadow-2xl">
            <div class="flex items-center justify-between bg-secondary-blue px-4 py-3">
                <div>
                    <h2 class="text-xl font-extrabold text-white">Enrollments using ${sessionName}</h2>
                    <p class="mt-1 text-sm text-white text-opacity-90">Review connected students, instructors, instruments, and enrollment dates.</p>
                </div>
                <button type="button" onclick="closeModal()" class="rounded-full p-2 text-white transition-all hover:bg-white hover:bg-opacity-20" aria-label="Close modal">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            ${enrollments.length === 0 ? `
                <div class="px-4 py-3 text-center">
                    <svg class="mx-auto mb-4 h-10 w-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <p class="text-base font-bold text-gray-700">No enrollments found</p>
                    <p class="mt-2 text-sm text-gray-500">This package is not connected to any enrollment yet.</p>
                </div>
            ` : `
                <div class="max-h-[70vh] overflow-auto px-4 py-4">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="sticky top-0 bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wide text-gray-700">Enrollment ID</th>
                                <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wide text-gray-700">Student</th>
                                <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wide text-gray-700">Instrument</th>
                                <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wide text-gray-700">Instructor</th>
                                <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wide text-gray-700">Status</th>
                                <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wide text-gray-700">Enrolled</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            ${enrollments.map((enrollment) => generateEnrollmentRow(enrollment)).join('')}
                        </tbody>
                    </table>
                </div>
            `}

            <div class="border-t border-gray-200 bg-gray-50 px-4 py-4">
                <button type="button" onclick="closeModal()" class="w-full rounded-xl bg-gray-200 px-4 py-3 font-bold text-gray-700 transition-all hover:bg-gray-300">
                    Close
                </button>
            </div>
        </div>
    `;
}

/**
 * Generate one enrollment table row.
 */
function generateEnrollmentRow(enrollment) {
    const status = String(enrollment.status || 'unknown');

    return `
        <tr class="transition-colors hover:bg-blue-50">
            <td class="px-4 py-3 text-sm font-bold text-gray-900">${escapeHtml(enrollment.enrollment_id)}</td>
            <td class="px-4 py-3">
                <p class="text-sm font-bold text-gray-900">${escapeHtml(enrollment.student_name || 'Unnamed student')}</p>
                <p class="mt-1 text-xs text-gray-500">${escapeHtml(enrollment.user_email || 'No email')}</p>
            </td>
            <td class="px-4 py-3 text-sm text-gray-700">${escapeHtml(enrollment.instrument_name || 'Not selected')}</td>
            <td class="px-4 py-3 text-sm text-gray-700">${escapeHtml(enrollment.instructor_name || 'Not assigned')}</td>
            <td class="px-4 py-3">
                <span class="rounded-full px-3 py-1 text-xs font-bold ${getStatusClass(status)}">${escapeHtml(status.replaceAll('_', ' '))}</span>
            </td>
            <td class="px-4 py-3 text-sm text-gray-600">${formatDate(enrollment.enrollment_date)}</td>
        </tr>
    `;
}

// ============================================================================
// TOAST NOTIFICATIONS
// ============================================================================

/**
 * Display toast notification.
 */
function showToast(message, type = 'success') {
    const container = document.getElementById('toast-container');

    if (!container) return;

    const toast = document.createElement('div');
    toast.className = `max-w-md rounded-xl px-5 py-4 shadow-lg transition-all duration-300 ${
        type === 'success' ? 'bg-forest-green text-white' : 'bg-red-600 text-white'
    }`;

    toast.innerHTML = `
        <div class="flex items-start gap-3">
            <svg class="mt-0.5 h-6 w-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                ${type === 'success'
                    ? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>'
                    : '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>'
                }
            </svg>
            <p class="toast-message whitespace-pre-line text-sm font-bold"></p>
        </div>
    `;

    const messageElement = toast.querySelector('.toast-message');
    if (messageElement) {
        messageElement.textContent = String(message || 'Done.');
    }

    container.appendChild(toast);

    setTimeout(() => {
        toast.style.transform = 'translateX(420px)';
        toast.style.opacity = '0';

        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 300);
    }, 5000);
}

// ============================================================================
// EVENT LISTENERS
// ============================================================================

document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('session-modal');

    if (modal) {
        modal.addEventListener('click', function (event) {
            if (event.target === modal) {
                closeModal();
            }
        });
    }

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeModal();
        }
    });
});

// ============================================================================
// EXPOSE FUNCTIONS TO WINDOW OBJECT FOR INLINE BLADE HANDLERS
// ============================================================================

window.openAddModal = openAddModal;
window.editSession = editSession;
window.toggleSession = toggleSession;
window.deleteSession = deleteSession;
window.closeModal = closeModal;
window.handleSubmit = handleSubmit;
window.updateTotalHours = updateTotalHours;
window.calculateTotalHours = calculateTotalHours;
window.handleSessionCountChange = handleSessionCountChange;
window.updateCustomSessionCount = updateCustomSessionCount;
window.viewSessionEnrollments = viewSessionEnrollments;
