/*
 * ============================================================================
 * PAYMENT METHODS PAGE JAVASCRIPT
 * resources/js/admin-pages/payment-method.js
 * ============================================================================
 * Handles all interactions for payment methods management page:
 * - Create modal (open/close/submit)
 * - Edit modal (open/close/submit/fetch data)
 * - Delete with confirmation and usage check
 * - Toggle active/inactive status
 * - Toast notifications
 * - Form validation and error handling
 * ============================================================================
 */

document.addEventListener('DOMContentLoaded', function () {

    // ============================================================================
    // FILTER BY STATUS FROM STATISTICS CARDS
    // ============================================================================

    /**
     * Filters the table by status when clicking statistics cards
     * @param {string} status - 'all', 'active', or 'inactive'
     */
    window.filterByStatus = function(status) {
        const statusSelect = document.getElementById('statusFilter');
        if (statusSelect) {
            statusSelect.value = status;
            document.getElementById('filterForm').submit();
        }
    };
    
    // ============================================================================
    // CREATE MODAL FUNCTIONS
    // ============================================================================
    
    /**
     * Opens the create payment method modal
     */
    window.openCreateModal = function () {
        document.getElementById('createModal').classList.remove('hidden');
        document.getElementById('createForm').reset();
        document.getElementById('create-error').classList.add('hidden');
    };

    /**
     * Closes the create payment method modal
     */
    window.closeCreateModal = function () {
        document.getElementById('createModal').classList.add('hidden');
        document.getElementById('createForm').reset();
        document.getElementById('create-error').classList.add('hidden');
    };

    /**
     * Handles the submission of the create form
     * @param {Event} event - The form submission event
     */
    window.submitCreate = function (event) {
        event.preventDefault();
        
        const form = event.target;
        const formData = new FormData(form);
        const errorElement = document.getElementById('create-error');

        fetch('/admin/payment-methods', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast(data.message, 'success');
                closeCreateModal();
                // Reload page to show new method
                setTimeout(() => location.reload(), 800);
            } else if (data.errors) {
                // Display validation errors
                const errorMsg = Object.values(data.errors).flat().join(', ');
                errorElement.textContent = errorMsg;
                errorElement.classList.remove('hidden');
            }
        })
        .catch(error => {
            showToast('An error occurred while creating the payment method', 'error');
            console.error('Create error:', error);
        });
    };

    // ============================================================================
    // EDIT MODAL FUNCTIONS
    // ============================================================================
    
    /**
     * Opens the edit modal and fetches the payment method data
     * @param {number} id - The payment method ID
     */
    window.openEditModal = function (id) {
        const modal = document.getElementById('editModal');
        const errorElement = document.getElementById('edit-error');
        
        modal.classList.remove('hidden');
        errorElement.classList.add('hidden');

        // Fetch payment method data
        fetch(`/admin/payment-methods/${id}/edit`, {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Failed to fetch payment method data');
            }
            return response.json();
        })
        .then(data => {
            if (data.method) {
                // Populate form with existing data
                document.getElementById('edit-method-id').value = data.method.method_id;
                document.getElementById('edit-method-name').value = data.method.method_name;
            }
        })
        .catch(error => {
            showToast('Failed to load payment method data', 'error');
            closeEditModal();
            console.error('Fetch error:', error);
        });
    };

    /**
     * Closes the edit payment method modal
     */
    window.closeEditModal = function () {
        document.getElementById('editModal').classList.add('hidden');
        document.getElementById('editForm').reset();
        document.getElementById('edit-error').classList.add('hidden');
    };

    /**
     * Handles the submission of the edit form
     * @param {Event} event - The form submission event
     */
    window.submitEdit = function (event) {
        event.preventDefault();
        
        const form = event.target;
        const methodId = document.getElementById('edit-method-id').value;
        const formData = new FormData(form);
        const errorElement = document.getElementById('edit-error');

        fetch(`/admin/payment-methods/${methodId}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'X-HTTP-Method-Override': 'PUT'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast(data.message, 'success');
                closeEditModal();
                // Reload page to show updated method
                setTimeout(() => location.reload(), 800);
            } else if (data.errors) {
                // Display validation errors
                const errorMsg = Object.values(data.errors).flat().join(', ');
                errorElement.textContent = errorMsg;
                errorElement.classList.remove('hidden');
            }
        })
        .catch(error => {
            showToast('An error occurred while updating the payment method', 'error');
            console.error('Update error:', error);
        });
    };

    // ============================================================================
    // DELETE FUNCTION
    // ============================================================================
    
    /**
     * Deletes a payment method after confirmation and usage check
     * @param {number} id - The payment method ID
     * @param {number} usageCount - Number of times this method is used
     */
    window.deleteMethod = function (id, usageCount) {
        // Prevent deletion if method is in use
        if (usageCount > 0) {
            showToast(`Cannot delete — this method is used in ${usageCount} payment(s)`, 'error');
            return;
        }

        if (!confirm('Are you sure you want to delete this payment method? This action cannot be undone.')) {
            return;
        }

        fetch(`/admin/payment-methods/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast(data.message, 'success');
                // Reload page to reflect changes
                setTimeout(() => location.reload(), 800);
            } else {
                showToast(data.message || 'Failed to delete payment method', 'error');
            }
        })
        .catch(error => {
            showToast('An error occurred while deleting the payment method', 'error');
            console.error('Delete error:', error);
        });
    };

    // ============================================================================
    // TOGGLE STATUS FUNCTION
    // ============================================================================
    
    /**
     * Toggles the active/inactive status of a payment method
     * @param {number} id - The payment method ID
     */
    window.togglePaymentMethodStatus = function (id) {
        if (!confirm('Are you sure you want to change the status of this payment method?')) {
            return;
        }

        fetch(`/admin/payment-methods/${id}/toggle-status`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Failed to toggle status');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                showToast('Status updated successfully', 'success');
                // Reload page to reflect changes
                setTimeout(() => location.reload(), 800);
            } else {
                showToast(data.message || 'Failed to update status', 'error');
            }
        })
        .catch(error => {
            showToast('An error occurred while updating the status', 'error');
            console.error('Toggle status error:', error);
        });
    };

    // ============================================================================
    // TOAST NOTIFICATION FUNCTION
    // ============================================================================
    
    /**
     * Displays a toast notification message
     * @param {string} message - The message to display
     * @param {string} type - The type of toast ('success' or 'error')
     */
    function showToast(message, type = 'success') {
        const container = document.getElementById('toast-container') || document.body;
        const toast = document.createElement('div');
        
        // Set background color based on type
        let bgColor = type === 'success' ? 'bg-forest-green' : 'bg-warm-coral';
        
        toast.className = `p-4 rounded-lg shadow-lg text-white ${bgColor} fixed bottom-6 right-6 z-50 animate-fade-in`;
        toast.textContent = message;
        container.appendChild(toast);

        // Auto-remove toast after 5 seconds
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transition = 'opacity 0.3s ease-out';
            setTimeout(() => toast.remove(), 300);
        }, 5000);
    }

    // ============================================================================
    // CLOSE MODALS ON ESCAPE KEY
    // ============================================================================
    
    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeCreateModal();
            closeEditModal();
        }
    });

    // ============================================================================
    // CLOSE MODALS ON OUTSIDE CLICK
    // ============================================================================
    
    document.getElementById('createModal')?.addEventListener('click', function (event) {
        if (event.target === this) {
            closeCreateModal();
        }
    });

    document.getElementById('editModal')?.addEventListener('click', function (event) {
        if (event.target === this) {
            closeEditModal();
        }
    });
});