/**
 * ============================================================================
 * resources/js/admin-pages/payment-status.js
 * ============================================================================
 *
 * Handles Payment Status AJAX CRUD operations.
 * Audit-safe revisions:
 * - Removes reliance on the browser global event object.
 * - Escapes modal values before injecting them into HTML.
 * - Keeps modal and toast behavior small, readable, and reusable.
 * ============================================================================
 */

const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

/**
 * Escape text before injecting inside modal HTML.
 */
function escapeHtml(value) {
    return String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

/**
 * Read JSON safely from a fetch response.
 */
async function readJsonResponse(response) {
    const text = await response.text();

    try {
        return text ? JSON.parse(text) : {};
    } catch (error) {
        return {
            success: false,
            message: 'Invalid server response.',
        };
    }
}

/**
 * Build modal shell to avoid duplicated modal structure.
 */
function setModalContent(content) {
    const modal = document.getElementById('status-modal');

    if (!modal) return;

    modal.innerHTML = content;
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

/**
 * Open modal to add new payment status.
 */
window.openAddModal = function () {
    setModalContent(`
        <div class="bg-white rounded-xl shadow-2xl max-w-md w-full p-5 animate-fade-in">
            <div class="flex items-center justify-between mb-5">
                <h2 class="text-xl font-bold text-primary-dark">Add Payment Status</h2>
                <button type="button" onclick="closeModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form id="add-status-form" onsubmit="submitAddForm(event)">
                <div class="mb-5">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Status Name <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        name="status_name"
                        id="status_name"
                        required
                        maxlength="50"
                        placeholder="e.g., Pending, Paid, Cancelled"
                        class="w-full px-3 py-2.5 border-2 border-gray-300 rounded-lg focus:border-secondary-blue focus:ring-2 focus:ring-secondary-blue transition-all"
                    >
                    <p class="text-xs text-gray-500 mt-2">This label will be used when manually recording payments.</p>
                    <div id="error-status_name" class="text-red-600 text-sm mt-2 hidden"></div>
                </div>

                <div class="flex gap-3">
                    <button
                        type="submit"
                        class="flex-1 bg-forest-green text-white px-4 py-2.5 rounded-lg text-sm font-semibold hover:bg-forest-green-dark transition-all shadow-sm"
                    >
                        Add Status
                    </button>
                    <button
                        type="button"
                        onclick="closeModal()"
                        class="px-4 py-2.5 bg-gray-200 text-gray-700 rounded-lg text-sm font-semibold hover:bg-gray-300 transition-all"
                    >
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    `);

    document.getElementById('status_name')?.focus();
};

/**
 * Submit add form via AJAX.
 */
window.submitAddForm = async function (event) {
    event.preventDefault();

    const form = event.target;
    const formData = new FormData(form);
    const submitBtn = form.querySelector('button[type="submit"]');

    formData.set('status_name', String(formData.get('status_name') || '').trim());

    submitBtn.disabled = true;
    submitBtn.textContent = 'Adding...';

    try {
        const response = await fetch('/admin/payment-statuses', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: formData,
        });

        const data = await readJsonResponse(response);

        if (response.ok && data.success) {
            showToast(data.message || 'Payment status added successfully.', 'success');
            closeModal();
            setTimeout(() => location.reload(), 800);
            return;
        }

        showFormErrors(data.errors);
        showToast(data.message || 'Failed to add payment status.', 'error');
    } catch (error) {
        showToast('An error occurred. Please try again.', 'error');
    } finally {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Add Status';
    }
};

/**
 * Open modal to edit payment status.
 */
window.editStatus = function (statusId, statusName) {
    const safeStatusName = escapeHtml(statusName);

    setModalContent(`
        <div class="bg-white rounded-xl shadow-2xl max-w-md w-full p-5 animate-fade-in">
            <div class="flex items-center justify-between mb-5">
                <h2 class="text-xl font-bold text-primary-dark">Edit Payment Status</h2>
                <button type="button" onclick="closeModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form id="edit-status-form" onsubmit="submitEditForm(event, ${Number(statusId)})">
                <div class="mb-5">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Status Name <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        name="status_name"
                        id="edit_status_name"
                        value="${safeStatusName}"
                        required
                        maxlength="50"
                        class="w-full px-3 py-2.5 border-2 border-gray-300 rounded-lg focus:border-secondary-blue focus:ring-2 focus:ring-secondary-blue transition-all"
                    >
                    <div id="error-status_name" class="text-red-600 text-sm mt-2 hidden"></div>
                </div>

                <div class="flex gap-3">
                    <button
                        type="submit"
                        class="flex-1 bg-secondary-blue text-white px-4 py-2.5 rounded-lg text-sm font-semibold hover:bg-secondary-blue-dark transition-all shadow-sm"
                    >
                        Update Status
                    </button>
                    <button
                        type="button"
                        onclick="closeModal()"
                        class="px-4 py-2.5 bg-gray-200 text-gray-700 rounded-lg text-sm font-semibold hover:bg-gray-300 transition-all"
                    >
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    `);

    document.getElementById('edit_status_name')?.focus();
};

/**
 * Submit edit form via AJAX.
 */
window.submitEditForm = async function (event, statusId) {
    event.preventDefault();

    const form = event.target;
    const formData = new FormData(form);
    const submitBtn = form.querySelector('button[type="submit"]');
    const statusName = String(formData.get('status_name') || '').trim();

    submitBtn.disabled = true;
    submitBtn.textContent = 'Updating...';

    try {
        const response = await fetch(`/admin/payment-statuses/${statusId}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                _method: 'PUT',
                status_name: statusName,
            }),
        });

        const data = await readJsonResponse(response);

        if (response.ok && data.success) {
            showToast(data.message || 'Payment status updated successfully.', 'success');
            closeModal();
            setTimeout(() => location.reload(), 800);
            return;
        }

        showFormErrors(data.errors);
        showToast(data.message || 'Failed to update payment status.', 'error');
    } catch (error) {
        showToast('An error occurred. Please try again.', 'error');
    } finally {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Update Status';
    }
};

/**
 * Toggle payment status active/inactive.
 */
window.toggleStatus = async function (statusId) {
    if (!confirm('Change this payment status availability?')) return;

    try {
        const response = await fetch(`/admin/payment-statuses/${statusId}/toggle-status`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
        });

        const data = await readJsonResponse(response);

        if (response.ok && data.success) {
            showToast(data.message || 'Payment status updated successfully.', 'success');
            setTimeout(() => location.reload(), 700);
            return;
        }

        showToast(data.message || 'Failed to update payment status.', 'error');
    } catch (error) {
        showToast('An error occurred. Please try again.', 'error');
    }
};

/**
 * Delete payment status after checking usage.
 */
window.deleteStatus = async function (statusId) {
    try {
        const usageResponse = await fetch(`/admin/payment-statuses/${statusId}/usage`, {
            headers: {
                'Accept': 'application/json',
            },
        });

        const usageData = await readJsonResponse(usageResponse);

        if ((usageData.usage_count || 0) > 0) {
            showToast(
                `Cannot delete. This status is used in ${usageData.usage_count} ${usageData.usage_count === 1 ? 'payment' : 'payments'}.`,
                'error'
            );
            return;
        }

        if (!confirm('Delete this payment status? This action cannot be undone.')) return;

        const response = await fetch(`/admin/payment-statuses/${statusId}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                _method: 'DELETE',
            }),
        });

        const data = await readJsonResponse(response);

        if (response.ok && data.success) {
            showToast(data.message || 'Payment status deleted successfully.', 'success');
            setTimeout(() => location.reload(), 800);
            return;
        }

        showToast(data.message || 'Failed to delete payment status.', 'error');
    } catch (error) {
        showToast('An error occurred. Please try again.', 'error');
    }
};

/**
 * Show validation messages beside form fields.
 */
function showFormErrors(errors = {}) {
    Object.keys(errors || {}).forEach((key) => {
        const errorDiv = document.getElementById(`error-${key}`);

        if (errorDiv) {
            errorDiv.textContent = errors[key][0] || 'Invalid value.';
            errorDiv.classList.remove('hidden');
        }
    });
}

/**
 * Close modal.
 */
window.closeModal = function () {
    const modal = document.getElementById('status-modal');

    if (!modal) return;

    modal.classList.add('hidden');
    modal.classList.remove('flex');
    modal.innerHTML = '';
};

/**
 * Show toast notification.
 */
window.showToast = function (message, type = 'success') {
    const container = document.getElementById('toast-container');

    if (!container) {
        alert(message);
        return;
    }

    const toast = document.createElement('div');

    toast.className = `px-4 py-3 rounded-lg shadow-lg text-white text-sm font-semibold animate-fade-in ${
        type === 'success' ? 'bg-forest-green' : 'bg-warm-coral'
    }`;

    toast.textContent = message;
    container.appendChild(toast);

    setTimeout(() => {
        toast.style.opacity = '0';
        setTimeout(() => toast.remove(), 300);
    }, 2800);
};

/**
 * Close modal when clicking outside.
 */
document.addEventListener('click', (event) => {
    const modal = document.getElementById('status-modal');

    if (event.target === modal) {
        closeModal();
    }
});