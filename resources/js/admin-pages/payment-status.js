/**
 * ============================================================================
 * resources/js/admin-pages/payment-status.js
 * 
 * PAYMENT STATUS MANAGEMENT - JavaScript Functions
 * Handles CRUD operations for payment status labels
 * ============================================================================
 */

// Get CSRF token
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

/**
 * Open modal to add new payment status
 */
window.openAddModal = function() {
    const modalHTML = `
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 animate-fade-in">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold text-primary-dark">Add payment status</h2>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            <form id="add-status-form" onsubmit="submitAddForm(event)">
                <div class="mb-6">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Status name <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        name="status_name" 
                        id="status_name" 
                        required 
                        placeholder="e.g., Pending, Paid, Cancelled..."
                        class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-secondary-blue focus:ring-2 focus:ring-secondary-blue transition-all"
                    >
                    <p class="text-xs text-gray-500 mt-2">This label will be used when manually recording payments</p>
                    <div id="error-status_name" class="text-red-600 text-sm mt-2 hidden"></div>
                </div>
                
                <div class="flex gap-3">
                    <button 
                        type="submit" 
                        class="flex-1 bg-forest-green text-white px-6 py-3 rounded-lg font-semibold hover:bg-forest-green-dark transition-all shadow-lg"
                    >
                        Add status
                    </button>
                    <button 
                        type="button" 
                        onclick="closeModal()"
                        class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg font-semibold hover:bg-gray-300 transition-all"
                    >
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    `;

    const modal = document.getElementById('status-modal');
    modal.innerHTML = modalHTML;
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.getElementById('status_name').focus();
}

/**
 * Submit add form via AJAX
 */
window.submitAddForm = async function(event) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);
    const submitBtn = form.querySelector('button[type="submit"]');

    // Trim whitespace from status name
    const statusName = formData.get('status_name').trim();
    formData.set('status_name', statusName);

    submitBtn.disabled = true;
    submitBtn.textContent = 'Adding...';

    try {
        const response = await fetch('/admin/payment-statuses', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            showToast(data.message, 'success');
            closeModal();
            setTimeout(() => location.reload(), 1000);
        } else {
            if (data.errors) {
                Object.keys(data.errors).forEach(key => {
                    const errorDiv = document.getElementById(`error-${key}`);
                    if (errorDiv) {
                        errorDiv.textContent = data.errors[key][0];
                        errorDiv.classList.remove('hidden');
                    }
                });
            }
            showToast(data.message || 'Failed to add payment status', 'error');
        }
    } catch (error) {
        showToast('An error occurred. Please try again.', 'error');
    } finally {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Add status';
    }
}

/**
 * Open modal to edit payment status
 */
window.editStatus = async function(statusId) {
    try {
        // Fetch status data from the table row
        const row = event.target.closest('tr');
        const statusName = row.querySelector('td:first-child .text-sm.font-bold').textContent.trim();

        const modalHTML = `
            <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 animate-fade-in">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-bold text-primary-dark">Edit payment status</h2>
                    <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                
                <form id="edit-status-form" onsubmit="submitEditForm(event, ${statusId})">
                    <div class="mb-6">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Status name <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="text" 
                            name="status_name" 
                            id="edit_status_name"
                            value="${statusName}"
                            required
                            class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-secondary-blue focus:ring-2 focus:ring-secondary-blue transition-all"
                        >
                        <div id="error-status_name" class="text-red-600 text-sm mt-2 hidden"></div>
                    </div>
                    
                    <div class="flex gap-3">
                        <button 
                            type="submit" 
                            class="flex-1 bg-secondary-blue text-white px-6 py-3 rounded-lg font-semibold hover:bg-secondary-blue-dark transition-all shadow-lg"
                        >
                            Update status
                        </button>
                        <button 
                            type="button" 
                            onclick="closeModal()"
                            class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg font-semibold hover:bg-gray-300 transition-all"
                        >
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        `;

        const modal = document.getElementById('status-modal');
        modal.innerHTML = modalHTML;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.getElementById('edit_status_name').focus();

    } catch (error) {
        showToast('Failed to load status details', 'error');
    }
}

/**
 * Submit edit form via AJAX
 */
window.submitEditForm = async function(event, statusId) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);
    const submitBtn = form.querySelector('button[type="submit"]');

    // Trim whitespace
    const statusName = formData.get('status_name').trim();

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
                status_name: statusName
            })
        });

        const data = await response.json();

        if (data.success) {
            showToast(data.message, 'success');
            closeModal();
            setTimeout(() => location.reload(), 1000);
        } else {
            if (data.errors) {
                Object.keys(data.errors).forEach(key => {
                    const errorDiv = document.getElementById(`error-${key}`);
                    if (errorDiv) {
                        errorDiv.textContent = data.errors[key][0];
                        errorDiv.classList.remove('hidden');
                    }
                });
            }
            showToast(data.message || 'Failed to update payment status', 'error');
        }
    } catch (error) {
        showToast('An error occurred. Please try again.', 'error');
    } finally {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Update status';
    }
}

/**
 * Toggle payment status active/inactive
 */
window.toggleStatus = async function(statusId) {
    if (!confirm('Are you sure you want to change this status?')) return;

    try {
        const response = await fetch(`/admin/payment-statuses/${statusId}/toggle-status`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            }
        });

        const data = await response.json();

        if (data.success) {
            showToast(data.message, 'success');
            setTimeout(() => location.reload(), 800);
        } else {
            showToast(data.message || 'Failed to toggle status', 'error');
        }
    } catch (error) {
        showToast('An error occurred. Please try again.', 'error');
    }
}

/**
 * Delete payment status (with usage check)
 */
window.deleteStatus = async function(statusId) {
    try {
        // Check usage first
        const usageResponse = await fetch(`/admin/payment-statuses/${statusId}/usage`, {
            headers: {
                'Accept': 'application/json',
            }
        });

        const usageData = await usageResponse.json();

        if (usageData.usage_count > 0) {
            showToast(`Cannot delete — used in ${usageData.usage_count} ${usageData.usage_count === 1 ? 'payment' : 'payments'}`, 'error');
            return;
        }

        if (!confirm('Are you sure you want to delete this payment status? This action cannot be undone.')) return;

        const response = await fetch(`/admin/payment-statuses/${statusId}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ _method: 'DELETE' })
        });

        const data = await response.json();

        if (data.success) {
            showToast(data.message, 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(data.message || 'Failed to delete payment status', 'error');
        }
    } catch (error) {
        showToast('An error occurred. Please try again.', 'error');
    }
}

/**
 * Close modal
 */
window.closeModal = function() {
    const modal = document.getElementById('status-modal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    modal.innerHTML = '';
}

/**
 * Show toast notification
 */
window.showToast = function(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `px-6 py-4 rounded-lg shadow-lg text-white font-semibold animate-fade-in ${type === 'success' ? 'bg-forest-green' : 'bg-warm-coral'}`;
    toast.textContent = message;

    const container = document.getElementById('toast-container');
    container.appendChild(toast);

    setTimeout(() => {
        toast.style.opacity = '0';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Close modal when clicking outside
document.addEventListener('click', (e) => {
    const modal = document.getElementById('status-modal');
    if (e.target === modal) {
        closeModal();
    }
});