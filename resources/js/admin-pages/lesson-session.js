/**
 * ============================================================================
 * LESSON SESSION PACKAGE MANAGEMENT - CLIENT-SIDE JAVASCRIPT
 * resources/js/admin-pages/lesson-session.js
 * ============================================================================
 * Handles:
 * - Modal rendering for create/edit forms (HTML generated in JS)
 * - AJAX form submissions (create/update)
 * - Toggle active/inactive status
 * - Delete with usage check (prevents deletion if used in enrollments)
 * - Toast notifications
 * - Form validation
 * ============================================================================
 */

// Get CSRF token from meta tag
const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

/**
 * ============================================================================
 * MODAL FUNCTIONS
 * ============================================================================
 */

/**
 * Open modal for adding a new lesson package
 */
function openAddModal() {
    const modalHTML = generateModalHTML('create', null);
    showModal(modalHTML);
}

/**
 * Open modal for editing an existing lesson package
 * Fetches package data via AJAX
 */
function editSession(sessionId) {
    fetch(`/admin/lesson-sessions/${sessionId}/edit`, {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const modalHTML = generateModalHTML('edit', data.session);
            showModal(modalHTML);
        } else {
            showToast(data.message || 'Failed to load package data', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred while loading package data', 'error');
    });
}

/**
 * Generate modal HTML for create or edit mode
 * @param {string} mode - 'create' or 'edit'
 * @param {object|null} session - Session data (null for create mode)
 * @returns {string} HTML string
 */
function generateModalHTML(mode, session) {
    const isEdit = mode === 'edit';
    const title = isEdit ? 'Edit lesson package' : 'Add new lesson package';
    const submitText = isEdit ? 'Update package' : 'Create package';
    
    // Extract session data if in edit mode
    const sessionCount = isEdit ? session.session_count : '';
    const sessionName = isEdit ? session.session_name : '';
    const durationMinutes = isEdit ? session.duration_minutes : 60;
    const price = isEdit ? session.price : '';
    const description = isEdit ? (session.description || '') : '';
    const usageCount = isEdit ? session.usage_count : 0;
    const sessionId = isEdit ? session.session_id : null;

    return `
        <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <!-- Modal Header -->
            <div class="bg-gradient-to-r from-secondary-blue to-forest-green px-6 py-4 flex items-center justify-between sticky top-0 z-10">
                <h2 class="text-2xl font-bold text-white">${title}</h2>
                <button onclick="closeModal()" class="text-white hover:bg-white hover:bg-opacity-20 rounded-full p-2 transition-all">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Modal Body -->
            <form id="session-form" class="p-6 space-y-6" onsubmit="handleSubmit(event, '${mode}', ${sessionId})">
                ${isEdit ? `<input type="hidden" name="session_id" value="${sessionId}">` : ''}

                <!-- Session Count Dropdown with Custom Option -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2 label-required">Session count</label>
                    <select id="session-count-select" name="session_count_dropdown" 
                            onchange="handleSessionCountChange(this.value, ${sessionCount})"
                            class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-secondary-blue focus:ring-2 focus:ring-secondary-blue transition-all">
                        <option value="">Select session count</option>
                        <option value="5" ${sessionCount == 5 ? 'selected' : ''}>5 sessions</option>
                        <option value="10" ${sessionCount == 10 ? 'selected' : ''}>10 sessions</option>
                        <option value="20" ${sessionCount == 20 ? 'selected' : ''}>20 sessions</option>
                        <option value="custom" ${(sessionCount && ![5, 10, 20].includes(parseInt(sessionCount))) ? 'selected' : ''}>Other (custom)</option>
                    </select>
                    
                    <!-- Hidden input for actual session_count value -->
                    <input type="hidden" id="session-count-value" name="session_count" value="${sessionCount}" required>
                    
                    <!-- Custom input (shown when "Other" is selected) -->
                    <div id="custom-session-input" class="mt-3 ${(sessionCount && ![5, 10, 20].includes(parseInt(sessionCount))) ? '' : 'hidden'}">
                        <input type="number" id="custom-session-count" 
                            value="${(sessionCount && ![5, 10, 20].includes(parseInt(sessionCount))) ? sessionCount : ''}" 
                            min="1" max="100" step="1"
                            oninput="updateCustomSessionCount(this.value)"
                            placeholder="Enter custom session count (1-100)"
                            class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-secondary-blue focus:ring-2 focus:ring-secondary-blue transition-all">
                    </div>
                    
                    <p class="text-xs text-gray-500 mt-1">Choose preset or enter custom session count</p>
                </div>

                <!-- Session Name (Auto-generated but editable) -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Package name</label>
                    <input type="text" name="session_name" value="${sessionName}" 
                           placeholder="e.g., Beginner 5 sessions (leave blank to auto-generate)"
                           class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-secondary-blue focus:ring-2 focus:ring-secondary-blue transition-all">
                    <p class="text-xs text-gray-500 mt-1">Optional — auto-generates if left blank (e.g., "5 session package")</p>
                </div>

                <!-- Duration per Session -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2 label-required">Duration per session (minutes)</label>
                    <input type="number" name="duration_minutes" value="${durationMinutes}" 
                        min="15" max="300" step="5" required
                        oninput="updateTotalHours(this.value, document.querySelector('[name=session_count]').value)"
                        class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-secondary-blue focus:ring-2 focus:ring-secondary-blue transition-all">
                    <p class="text-xs text-gray-500 mt-1" id="duration-help">Default is 60 minutes (1 hour per session)</p>
                </div>

                <!-- Total Hours Display -->
                <div class="bg-secondary-blue bg-opacity-10 border-l-4 border-secondary-blue px-4 py-3 rounded">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-semibold text-gray-900">Total package duration</p>
                            <p class="text-xs text-gray-900 mt-1">Session count × Duration per session</p>
                        </div>
                        <div class="text-right">
                            <p class="text-2xl font-bold text-gray-900" id="total-hours-display">
                                ${isEdit ? calculateTotalHours(sessionCount, durationMinutes) : '—'}
                            </p>
                            <p class="text-xs text-gray-900">hours</p>
                        </div>
                    </div>
                </div>

                <!-- Price -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2 label-required">Package price (₱)</label>
                    <input type="number" name="price" value="${price}" 
                           min="0" max="999999.99" step="0.01" required
                           placeholder="e.g., 5000.00"
                           class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-secondary-blue focus:ring-2 focus:ring-secondary-blue transition-all">
                    <p class="text-xs text-gray-500 mt-1">Total price for all sessions in this package</p>
                </div>

                <!-- Description (Optional) -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Description</label>
                    <textarea name="description" rows="3" 
                              placeholder="Optional notes (e.g., 'Includes free starter book')"
                              class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-secondary-blue focus:ring-2 focus:ring-secondary-blue transition-all resize-none">${description}</textarea>
                </div>

                <!-- Usage Warning (Edit Mode Only) -->
                ${isEdit && usageCount > 0 ? `
                <div class="bg-golden-yellow bg-opacity-20 border-l-4 border-golden-yellow px-4 py-3 rounded">
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-golden-yellow mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                        <div>
                            <p class="text-sm font-semibold text-gray-700">This package is used in ${usageCount} enrollment${usageCount > 1 ? 's' : ''}</p>
                            <p class="text-xs text-gray-600 mt-1">Changes will affect existing enrollments. Be careful when updating price or session count.</p>
                        </div>
                    </div>
                </div>
                ` : ''}

                <!-- Modal Actions -->
                <div class="flex gap-3 pt-4 border-t border-gray-200">
                    <button type="button" onclick="closeModal()" class="flex-1 bg-gray-200 text-gray-700 px-6 py-3 rounded-lg font-semibold hover:bg-gray-300 transition-all">
                        Cancel
                    </button>
                    <button type="submit" class="flex-1 bg-forest-green text-white px-6 py-3 rounded-lg font-semibold hover:bg-forest-green-dark transition-all">
                        ${submitText}
                    </button>
                </div>
            </form>
        </div>
    `;
}

/**
 * Calculate total hours from session count and duration
 * @param {number} sessionCount - Number of sessions
 * @param {number} durationMinutes - Duration per session in minutes
 * @returns {string} Formatted total hours
 */
function calculateTotalHours(sessionCount, durationMinutes) {
    if (!sessionCount || !durationMinutes) return '—';
    const totalMinutes = sessionCount * durationMinutes;
    const totalHours = (totalMinutes / 60).toFixed(1);
    return totalHours;
}

/**
 * Update total hours display when inputs change
 * Called via oninput on duration and session count fields
 */
function updateTotalHours(duration, sessionCount) {
    const display = document.getElementById('total-hours-display');
    if (display) {
        const total = calculateTotalHours(sessionCount, duration);
        display.textContent = total;
        
        // Update help text
        const helpText = document.getElementById('duration-help');
        if (helpText && sessionCount && duration) {
            const hoursPerSession = (duration / 60).toFixed(1);
            helpText.textContent = `${hoursPerSession} ${hoursPerSession == 1 ? 'hour' : 'hours'} per session × ${sessionCount} sessions = ${total} total hours`;
        }
    }
}

/**
 * Display modal with given HTML content
 */
function showModal(html) {
    const modal = document.getElementById('session-modal');
    modal.innerHTML = html;
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.style.overflow = 'hidden';
}

/**
 * Close and clear modal
 */
function closeModal() {
    const modal = document.getElementById('session-modal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    modal.innerHTML = '';
    document.body.style.overflow = 'auto';
}

/**
 * ============================================================================
 * FORM SUBMISSION
 * ============================================================================
 */

/**
 * Handle form submission for create/edit
 * @param {Event} event - Form submit event
 * @param {string} mode - 'create' or 'edit'
 * @param {number|null} sessionId - Session ID (null for create)
 */
function handleSubmit(event, mode, sessionId) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    
    // Determine URL and method
    const url = mode === 'create' 
        ? '/admin/lesson-sessions' 
        : `/admin/lesson-sessions/${sessionId}`;
    
    const method = mode === 'create' ? 'POST' : 'PUT';
    
    // Convert FormData to JSON
    const data = {};
    formData.forEach((value, key) => {
        data[key] = value;
    });
    
    // Send AJAX request
    fetch(url, {
        method: 'POST', // Always POST (Laravel handles PUT via _method)
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-HTTP-Method-Override': method
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            closeModal();
            // Reload page to reflect changes
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            // Handle validation errors
            if (data.errors) {
                const errorMessages = Object.values(data.errors).flat().join('<br>');
                showToast(errorMessages, 'error');
            } else {
                showToast(data.message || 'An error occurred', 'error');
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred while saving', 'error');
    });
}

/**
 * ============================================================================
 * TOGGLE STATUS
 * ============================================================================
 */

/**
 * Toggle active/inactive status of a lesson package
 * @param {number} sessionId - Session ID to toggle
 */
function toggleSession(sessionId) {
    if (!confirm('Are you sure you want to change the status of this package?')) {
        return;
    }
    
    fetch(`/admin/lesson-sessions/${sessionId}/toggle-status`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            // Reload page to reflect changes
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showToast(data.message || 'Failed to update status', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred while updating status', 'error');
    });
}

/**
 * ============================================================================
 * DELETE PACKAGE
 * ============================================================================
 */

/**
 * Delete a lesson package (checks for usage in enrollments first)
 * @param {number} sessionId - Session ID to delete
 */
function deleteSession(sessionId) {
    if (!confirm('Are you sure you want to delete this package? This action cannot be undone.')) {
        return;
    }
    
    fetch(`/admin/lesson-sessions/${sessionId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            // Reload page to reflect changes
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            // Show usage error (cannot delete if used in enrollments)
            showToast(data.message || 'Failed to delete package', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred while deleting', 'error');
    });
}

/**
 * ============================================================================
 * TOAST NOTIFICATIONS
 * ============================================================================
 */

/**
 * Display toast notification
 * @param {string} message - Message to display
 * @param {string} type - 'success' or 'error'
 */
function showToast(message, type = 'success') {
    const container = document.getElementById('toast-container');
    
    const toast = document.createElement('div');
    toast.className = `px-6 py-4 rounded-lg shadow-lg transform transition-all duration-300 translate-x-0 ${
        type === 'success' 
            ? 'bg-forest-green text-white' 
            : 'bg-red-600 text-white'
    }`;
    
    toast.innerHTML = `
        <div class="flex items-center gap-3">
            <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                ${type === 'success' 
                    ? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>'
                    : '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>'
                }
            </svg>
            <p class="font-semibold">${message}</p>
        </div>
    `;
    
    container.appendChild(toast);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        toast.style.transform = 'translateX(400px)';
        toast.style.opacity = '0';
        setTimeout(() => {
            container.removeChild(toast);
        }, 300);
    }, 5000);
}

/**
 * ============================================================================
 * CLOSE MODAL ON OUTSIDE CLICK
 * ============================================================================
 */
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('session-modal');
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeModal();
            }
        });
    }
});

/**
 * Handle session count dropdown change
 * Shows/hides custom input based on selection
 */
function handleSessionCountChange(value, currentSessionCount) {
    const customInput = document.getElementById('custom-session-input');
    const customField = document.getElementById('custom-session-count');
    const hiddenInput = document.getElementById('session-count-value');
    const durationField = document.querySelector('[name=duration_minutes]');
    
    if (value === 'custom') {
        // Show custom input
        customInput.classList.remove('hidden');
        customField.required = true;
        customField.focus();
        
        // If there's a current custom value, use it
        if (currentSessionCount && ![5, 10, 20].includes(parseInt(currentSessionCount))) {
            customField.value = currentSessionCount;
            hiddenInput.value = currentSessionCount;
        } else {
            hiddenInput.value = '';
        }
    } else {
        // Hide custom input and use selected preset
        customInput.classList.add('hidden');
        customField.required = false;
        hiddenInput.value = value;
        
        // Update total hours display
        if (durationField && value) {
            updateTotalHours(durationField.value, value);
        }
    }
}

/**
 * Update hidden session_count value when custom input changes
 */
function updateCustomSessionCount(value) {
    const hiddenInput = document.getElementById('session-count-value');
    const durationField = document.querySelector('[name=duration_minutes]');
    
    hiddenInput.value = value;
    
    // Update total hours display
    if (durationField && value) {
        updateTotalHours(durationField.value, value);
    }
}

/**
 * View enrollments using a specific lesson package
 * @param {number} sessionId - Session ID
 * @param {string} sessionName - Session package name
 */
function viewSessionEnrollments(sessionId, sessionName) {
    fetch(`/admin/lesson-sessions/${sessionId}/enrollments`, {
        headers: { 'Accept': 'application/json' }
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            throw new Error(data.message);
        }
        
        const modalHTML = `
            <div class="bg-white rounded-lg max-w-4xl w-full max-h-[90vh] overflow-hidden">
                <div class="flex items-center justify-between px-6 py-4 bg-secondary-blue">
                    <h2 class="text-2xl font-bold text-white">Enrollments using ${sessionName}</h2>
                    <button onclick="closeModal()" class="text-black hover:bg-white hover:bg-opacity-20 rounded-full p-2 transition-all">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                ${data.enrollments.length === 0 ? `
                    <div class="text-center py-12 px-6">
                        <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <p class="text-gray-600 font-semibold">No enrollments found</p>
                    </div>
                ` : `
                    <div class="overflow-y-auto max-h-[70vh] px-6 py-4">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50 sticky top-0">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Enrollment ID</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Student</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Instructor</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Status</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Enrolled</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                ${data.enrollments.map(e => `
                                    <tr class="hover:bg-blue-50 transition-colors">
                                        <td class="px-4 py-3 text-sm font-medium text-gray-900">${e.enrollment_id}</td>
                                        <td class="px-4 py-3">
                                            <div class="text-sm font-medium text-gray-900">${e.student_name}</div>
                                            <div class="text-xs text-gray-500">${e.user_email}</div>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-600">${e.instructor_name ? e.instructor_name : 'Not assigned'}</td>
                                        <td class="px-4 py-3">
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full ${
                                                e.status === 'active' ? 'bg-forest-green text-white' : 
                                                e.status === 'completed' ? 'bg-secondary-blue text-white' : 'bg-gray-400 text-white'
                                            }">${e.status}</span>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-600">${new Date(e.enrollment_date).toLocaleDateString()}</td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </div>
                `}
                
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                    <button onclick="closeModal()" class="w-full bg-gray-200 text-gray-700 px-6 py-3 rounded-lg font-semibold hover:bg-gray-300 transition-all">
                        Close
                    </button>
                </div>
            </div>
        `;
        
        showModal(modalHTML);
        
        })
        .catch(error => {
        showToast(error.message, 'error');
    });
}

/**
 * ============================================================================
 * EXPOSE FUNCTIONS TO WINDOW OBJECT (for inline onclick handlers)
 * ============================================================================
 */
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