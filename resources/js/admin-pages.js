// resources/js/admin-pages.js
/**
 * ============================================================================
 * JAVASCRIPT FOR ADMIN USER MANAGEMENT PAGE (REVISION 3)
 * ============================================================================
 * This script handles all interactive functionality for the user management page,
 * including:
 * - Inline editing of user data.
 * - Professional modals for actions like password resets and deletions.
 * - Custom-colored toast notifications for user feedback.
 * - Bulk actions (activation, deactivation, deletion).
 * - Robust error handling for users with incomplete data.
 * ============================================================================
 */

document.addEventListener('DOMContentLoaded', function() {
    // Make functions globally accessible so they can be called from Blade templates
    window.toggleSelectAll = toggleSelectAll;
    window.updateBulkActions = updateBulkActions;
    window.clearSelection = clearSelection;
    window.bulkDeactivate = bulkDeactivate;
    window.bulkDelete = bulkDelete;
    window.toggleEdit = toggleEdit;
    window.cancelEdit = cancelEdit;
    window.resetPassword = resetPassword;
    window.activateUser = activateUser;
    window.deactivateUser = deactivateUser;
    window.deleteUser = deleteUser;
    window.copyPassword = copyPassword;
    window.closeResetModal = closeResetModal;
});

const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
let originalRowData = {}; // Store original data for cancellation

// ============================================================================
// INLINE EDITING FUNCTIONS
// ============================================================================

/**
 * Toggles a table row between view and edit mode.
 * @param {number} userId - The ID of the user to edit.
 */
function toggleEdit(userId) {
    const row = document.getElementById(`user-row-${userId}`);
    const isEditing = row.classList.contains('is-editing');
    
    if (isEditing) {
        // --- SAVE CHANGES ---
        const nameInput = document.getElementById(`name-edit-${userId}`);
        const emailInput = document.getElementById(`email-edit-${userId}`);
        
        // Extract first_name and last_name from the full name input
        const nameParts = nameInput.value.trim().split(' ');
        const firstName = nameParts.shift() || '';
        const lastName = nameParts.join(' ') || '';

        const payload = {
            first_name: firstName,
            last_name: lastName,
            user_email: emailInput.value,
            _method: 'PUT' // Method spoofing for Laravel
        };
        
        // Handle Super Admin case where names are not required
        if (nameInput.disabled) {
            delete payload.first_name;
            delete payload.last_name;
        }

        updateUser(userId, payload);

    } else {
        // --- ENTER EDIT MODE ---
        // Store original values in case of cancellation
        originalRowData[userId] = {
            name: document.getElementById(`name-view-${userId}`).textContent,
            email: document.getElementById(`email-view-${userId}`).textContent,
        };

        // Toggle visibility of view/edit elements
        document.getElementById(`name-view-${userId}`).classList.add('hidden');
        document.getElementById(`email-view-${userId}`).classList.add('hidden');
        document.getElementById(`name-edit-${userId}`).classList.remove('hidden');
        document.getElementById(`email-edit-${userId}`).classList.remove('hidden');

        // Toggle action icons
        document.getElementById(`edit-icon-${userId}`).classList.add('hidden');
        document.getElementById(`save-icon-${userId}`).classList.remove('hidden');
        document.getElementById(`cancel-btn-${userId}`).classList.remove('hidden');

        // Disable name field for Super Admins
        const fullName = originalRowData[userId].name.trim();
        if (fullName === 'Super Admin') {
            document.getElementById(`name-edit-${userId}`).disabled = true;
        }

        row.classList.add('is-editing', 'bg-yellow-50');
    }
}

/**
 * Cancels the inline editing for a row, reverting changes.
 * @param {number} userId - The ID of the user row to cancel.
 */
function cancelEdit(userId) {
    const row = document.getElementById(`user-row-${userId}`);

    // Restore original values
    document.getElementById(`name-view-${userId}`).textContent = originalRowData[userId].name;
    document.getElementById(`email-view-${userId}`).textContent = originalRowData[userId].email;
    document.getElementById(`name-edit-${userId}`).value = originalRowData[userId].name;
    document.getElementById(`email-edit-${userId}`).value = originalRowData[userId].email;
    
    // Toggle visibility back to view state
    document.getElementById(`name-view-${userId}`).classList.remove('hidden');
    document.getElementById(`email-view-${userId}`).classList.remove('hidden');
    document.getElementById(`name-edit-${userId}`).classList.add('hidden');
    document.getElementById(`email-edit-${userId}`).classList.add('hidden');

    // Toggle action icons
    document.getElementById(`edit-icon-${userId}`).classList.remove('hidden');
    document.getElementById(`save-icon-${userId}`).classList.add('hidden');
    document.getElementById(`cancel-btn-${userId}`).classList.add('hidden');

    row.classList.remove('is-editing', 'bg-yellow-50');
    delete originalRowData[userId]; // Clean up stored data
}

/**
 * Sends the update request to the server.
 * @param {number} userId - The ID of the user to update.
 * @param {object} payload - The data to send.
 */
async function updateUser(userId, payload) {
    try {
        const response = await fetch(`/admin/users/${userId}`, {
            method: 'POST', // Use POST and rely on _method spoofing
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        });

        const result = await response.json();

        if (!response.ok) {
            if (result.errors) {
                const errorMsg = Object.values(result.errors).flat().join('\n');
                showToast(errorMsg, 'error');
            } else {
                throw new Error(result.message || 'Update failed.');
            }
            return; // Do not exit edit mode if there was an error
        }

        showToast(result.message, 'success');
        
        // Update the view with the new data
        document.getElementById(`name-view-${userId}`).textContent = payload.first_name ? `${payload.first_name} ${payload.last_name}`.trim() : 'Super Admin';
        document.getElementById(`email-view-${userId}`).textContent = payload.user_email;

        // Exit edit mode
        cancelEdit(userId); 

    } catch (error) {
        showToast(error.message, 'error');
    }
}


// ============================================================================
// OTHER USER ACTIONS (Activate, Deactivate, Delete, Reset Password)
// ============================================================================

function activateUser(userId) {
    performUserAction(`/admin/users/${userId}/activate`, 'Are you sure you want to activate this user?', 'User activated successfully.');
}

function deactivateUser(userId) {
    performUserAction(`/admin/users/${userId}/deactivate`, 'Are you sure you want to deactivate this user?', 'User deactivated successfully.');
}

async function deleteUser(userId) {
    try {
        const impactRes = await fetch(`/admin/users/${userId}/deletion-impact`);
        if (!impactRes.ok) throw new Error('Could not fetch deletion impact.');
        const impactData = await impactRes.json();

        let impactMsg = 'This action will permanently delete this user.';
        if (Object.keys(impactData.impact).length > 0) {
            const details = Object.entries(impactData.impact).map(([key, value]) => `- ${value} ${key}`).join('\n');
            impactMsg = `Permanently delete this user? This will also affect:\n${details}\nThis action cannot be undone.`;
        }

        if (confirm(impactMsg)) {
            await performUserAction(`/admin/users/${userId}`, null, 'User deleted successfully.', 'DELETE');
        }
    } catch (error) {
        showToast(error.message, 'error');
    }
}

async function resetPassword(userId) {
    if (confirm('Are you sure you want to generate a new password for this user?')) {
        try {
            const response = await fetch(`/admin/users/${userId}/reset-password`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            });
            const data = await response.json();
            if (!data.success) throw new Error(data.message || 'Failed to reset password.');
            
            // Show the modal with the new password
            const modal = document.getElementById('reset-password-modal');
            modal.innerHTML = `
                <div class="bg-white rounded-lg max-w-md w-full p-6 shadow-2xl animate-fade-in-up">
                    <h3 class="text-xl font-bold text-primary-dark mb-4">New Password Generated</h3>
                    <p class="text-sm text-gray-700 mb-2">Copy this password and share it with the user:</p>
                    <div class="flex items-center gap-2">
                        <input type="text" id="generated-password" value="${data.password}" readonly class="input-field flex-1">
                        <button onclick="copyPassword()" class="btn-secondary px-4 py-2">Copy</button>
                    </div>
                    <p class="text-xs text-gray-600 mt-2">*This password will not be shown again.</p>
                    <button onclick="closeResetModal()" class="btn-primary w-full mt-4">Done</button>
                </div>
            `;
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            
            showToast('Password has been reset.', 'custom', '#377357');
        } catch (error) {
            showToast(error.message, 'error');
        }
    }
}

// ============================================================================
// BULK ACTIONS
// ============================================================================

function toggleSelectAll(checkbox) {
    document.querySelectorAll('.user-checkbox').forEach(cb => cb.checked = checkbox.checked);
    updateBulkActions();
}

function updateBulkActions() {
    const selected = document.querySelectorAll('.user-checkbox:checked');
    const bulkBar = document.getElementById('bulk-actions-bar');
    if (selected.length > 0) {
        bulkBar.innerHTML = `
            <div class="flex items-center justify-between flex-wrap gap-4 bg-warm-coral bg-opacity-10 border-l-4 border-warm-coral p-4 rounded">
                <span class="font-medium">${selected.length} user(s) selected</span>
                <div class="flex gap-2">
                    <button onclick="bulkDeactivate()" class="btn-secondary btn-sm">Deactivate</button>
                    <button onclick="bulkDelete()" class="btn-secondary btn-sm hover:bg-red-600 hover:text-white">Delete</button>
                    <button onclick="clearSelection()" class="btn-secondary btn-sm">Clear</button>
                </div>
            </div>`;
        bulkBar.classList.remove('hidden');
    } else {
        bulkBar.classList.add('hidden');
        bulkBar.innerHTML = '';
    }
}

function clearSelection() {
    document.querySelectorAll('.user-checkbox, #select-all').forEach(cb => cb.checked = false);
    updateBulkActions();
}

function bulkDeactivate() {
    performBulkAction('/admin/users/bulk-deactivate', count => `Are you sure you want to deactivate ${count} users?`);
}

function bulkDelete() {
    performBulkAction('/admin/users/bulk-delete', count => `Are you sure you want to permanently delete ${count} users? This action cannot be undone.`);
}

// ============================================================================
// HELPER FUNCTIONS (Modals, Toasts, API calls)
// ============================================================================

function closeResetModal() {
    const modal = document.getElementById('reset-password-modal');
    modal.classList.add('hidden');
    modal.innerHTML = '';
}

function copyPassword() {
    const passwordInput = document.getElementById('generated-password');
    passwordInput.select();
    passwordInput.setSelectionRange(0, 99999); // For mobile devices
    document.execCommand('copy');
    showToast('Password copied to clipboard!', 'success');
}

/**
 * A generic function to perform a user action with confirmation.
 */
async function performUserAction(url, confirmMsg, successMsg, method = 'POST') {
    if (confirmMsg && !confirm(confirmMsg)) return;

    try {
        const response = await fetch(url, {
            method: method,
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        });
        const data = await response.json();
        if (!data.success) throw new Error(data.message || 'Action failed.');
        showToast(successMsg, 'success');
        setTimeout(() => window.location.reload(), 1500);
    } catch (error) {
        showToast(error.message, 'error');
    }
}

/**
 * A generic function to perform a bulk action.
 */
async function performBulkAction(url, confirmMsgCallback) {
    const userIds = Array.from(document.querySelectorAll('.user-checkbox:checked')).map(cb => cb.value);
    if (userIds.length === 0) {
        showToast('No users selected.', 'error');
        return;
    }
    if (confirm(confirmMsgCallback(userIds.length))) {
        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: JSON.stringify({ user_ids: userIds }),
            });
            const data = await response.json();
            if (!data.success) throw new Error(data.message || 'Bulk action failed.');
            showToast(data.message, 'success');
            setTimeout(() => window.location.reload(), 1500);
        } catch (error) {
            showToast(error.message, 'error');
        }
    }
}


/**
 * Displays a toast notification.
 * @param {string} message The message to display.
 * @param {string} type 'success', 'error', or 'custom'.
 * @param {string|null} customColor The hex color for 'custom' type.
 */
function showToast(message, type = 'success', customColor = null) {
    const container = document.getElementById('toast-container');
    const toast = document.createElement('div');
    
    let bgColor;
    if (type === 'custom' && customColor) {
        toast.style.backgroundColor = customColor;
    } else {
        bgColor = type === 'success' ? 'bg-forest-green' : 'bg-red-600';
    }
    
    toast.className = `p-4 rounded-lg shadow-lg text-white ${bgColor} animate-fade-in-up`;
    toast.textContent = message;
    container.appendChild(toast);
    
    setTimeout(() => {
        toast.classList.add('animate-fade-out');
        toast.addEventListener('animationend', () => toast.remove());
    }, 5000);
}
