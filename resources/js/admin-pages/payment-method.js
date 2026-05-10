/*
 * ============================================================================
 * PAYMENT METHODS PAGE JAVASCRIPT
 * resources/js/admin-pages/payment-method.js
 * ============================================================================
 * Handles:
 * - Create modal
 * - Edit modal
 * - Delete with usage protection
 * - Toggle active/inactive status
 * - Toast notifications
 * - Safer fetch JSON parsing and validation display
 * ============================================================================
 */

document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    // ============================================================================
    // SMALL HELPERS
    // ============================================================================

    function csrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.content || '';
    }

    function setElementText(id, value) {
        const element = document.getElementById(id);
        if (element) {
            element.textContent = value;
        }
    }

    function showError(elementId, message) {
        const element = document.getElementById(elementId);
        if (!element) return;

        element.textContent = message;
        element.classList.remove('hidden');
    }

    function hideError(elementId) {
        const element = document.getElementById(elementId);
        if (!element) return;

        element.textContent = '';
        element.classList.add('hidden');
    }

    function validationMessage(errors, fallback) {
        if (!errors) return fallback;

        return Object.values(errors).flat().join(', ') || fallback;
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

    async function requestJson(url, options = {}) {
        const response = await fetch(url, {
            ...options,
            headers: {
                'X-CSRF-TOKEN': csrfToken(),
                'Accept': 'application/json',
                ...(options.headers || {}),
            },
        });

        const data = await parseJsonResponse(response);

        if (!response.ok) {
            data.success = false;
            data.status = response.status;
        }

        return data;
    }

    function setFormBusy(form, isBusy) {
        if (!form) return;

        form.querySelectorAll('button, input, textarea, select').forEach((element) => {
            element.disabled = isBusy;
        });
    }

    // ============================================================================
    // FILTER BY STATUS FROM STATISTICS CARDS
    // ============================================================================

    window.filterByStatus = function (status) {
        const statusSelect = document.getElementById('statusFilter');
        const filterForm = document.getElementById('filterForm');

        if (statusSelect && filterForm) {
            statusSelect.value = status;
            filterForm.submit();
        }
    };

    // ============================================================================
    // CREATE MODAL
    // ============================================================================

    window.openCreateModal = function () {
        const modal = document.getElementById('createModal');
        const form = document.getElementById('createForm');

        if (!modal || !form) return;

        modal.classList.remove('hidden');
        form.reset();
        hideError('create-error');
    };

    window.closeCreateModal = function () {
        const modal = document.getElementById('createModal');
        const form = document.getElementById('createForm');

        if (!modal || !form) return;

        modal.classList.add('hidden');
        form.reset();
        hideError('create-error');
    };

    window.submitCreate = async function (event) {
        event.preventDefault();

        const form = event.target;
        const formData = new FormData(form);

        hideError('create-error');
        setFormBusy(form, true);

        try {
            const data = await requestJson('/admin/payment-methods', {
                method: 'POST',
                body: formData,
            });

            if (data.success) {
                showToast(data.message || 'Payment method created successfully.', 'success');
                closeCreateModal();
                setTimeout(() => location.reload(), 700);
                return;
            }

            showError(
                'create-error',
                validationMessage(data.errors, data.message || 'Failed to create payment method.')
            );
        } catch (error) {
            console.error('Create payment method error:', error);
            showError('create-error', 'An error occurred while creating the payment method.');
            showToast('An error occurred while creating the payment method.', 'error');
        } finally {
            setFormBusy(form, false);
        }
    };

    // ============================================================================
    // EDIT MODAL
    // ============================================================================

    window.openEditModal = async function (id) {
        const modal = document.getElementById('editModal');
        const form = document.getElementById('editForm');

        if (!modal || !form) return;

        form.reset();
        hideError('edit-error');
        modal.classList.remove('hidden');

        try {
            const data = await requestJson(`/admin/payment-methods/${id}/edit`, {
                method: 'GET',
            });

            if (!data.success || !data.method) {
                throw new Error(data.message || 'Failed to fetch payment method data.');
            }

            document.getElementById('edit-method-id').value = data.method.method_id;
            document.getElementById('edit-method-name').value = data.method.method_name || '';

            const descriptionInput = document.getElementById('edit-description');
            if (descriptionInput) {
                descriptionInput.value = data.method.description || '';
            }
        } catch (error) {
            console.error('Fetch payment method error:', error);
            closeEditModal();
            showToast('Failed to load payment method data.', 'error');
        }
    };

    window.closeEditModal = function () {
        const modal = document.getElementById('editModal');
        const form = document.getElementById('editForm');

        if (!modal || !form) return;

        modal.classList.add('hidden');
        form.reset();
        hideError('edit-error');
    };

    window.submitEdit = async function (event) {
        event.preventDefault();

        const form = event.target;
        const methodId = document.getElementById('edit-method-id')?.value;
        const formData = new FormData(form);

        if (!methodId) {
            showError('edit-error', 'Missing payment method ID.');
            return;
        }

        hideError('edit-error');
        setFormBusy(form, true);

        try {
            const data = await requestJson(`/admin/payment-methods/${methodId}`, {
                method: 'POST',
                headers: {
                    'X-HTTP-Method-Override': 'PUT',
                },
                body: formData,
            });

            if (data.success) {
                showToast(data.message || 'Payment method updated successfully.', 'success');
                closeEditModal();
                setTimeout(() => location.reload(), 700);
                return;
            }

            showError(
                'edit-error',
                validationMessage(data.errors, data.message || 'Failed to update payment method.')
            );
        } catch (error) {
            console.error('Update payment method error:', error);
            showError('edit-error', 'An error occurred while updating the payment method.');
            showToast('An error occurred while updating the payment method.', 'error');
        } finally {
            setFormBusy(form, false);
        }
    };

    // ============================================================================
    // DELETE
    // ============================================================================

    window.deleteMethod = async function (id, usageCount) {
        if (Number(usageCount) > 0) {
            showToast(`Cannot delete â€” this method is used in ${usageCount} payment(s).`, 'error');
            return;
        }

        if (!confirm('Are you sure you want to delete this payment method? This action cannot be undone.')) {
            return;
        }

        try {
            const data = await requestJson(`/admin/payment-methods/${id}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                },
            });

            if (data.success) {
                showToast(data.message || 'Payment method deleted successfully.', 'success');
                setTimeout(() => location.reload(), 700);
                return;
            }

            showToast(data.message || 'Failed to delete payment method.', 'error');
        } catch (error) {
            console.error('Delete payment method error:', error);
            showToast('An error occurred while deleting the payment method.', 'error');
        }
    };

    // ============================================================================
    // TOGGLE STATUS
    // ============================================================================

    window.togglePaymentMethodStatus = async function (id) {
        if (!confirm('Are you sure you want to change the status of this payment method?')) {
            return;
        }

        try {
            const data = await requestJson(`/admin/payment-methods/${id}/toggle-status`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
            });

            if (data.success) {
                showToast(data.message || 'Status updated successfully.', 'success');
                setTimeout(() => location.reload(), 700);
                return;
            }

            showToast(data.message || 'Failed to update status.', 'error');
        } catch (error) {
            console.error('Toggle payment method status error:', error);
            showToast('An error occurred while updating the status.', 'error');
        }
    };

    // ============================================================================
    // TOAST
    // ============================================================================

    function showToast(message, type = 'success') {
        let container = document.getElementById('toast-container');

        if (!container) {
            container = document.createElement('div');
            container.id = 'toast-container';
            container.className = 'fixed bottom-5 right-5 z-[9999] space-y-2';
            container.setAttribute('aria-live', 'polite');
            document.body.appendChild(container);
        }

        const toast = document.createElement('div');
        const colorClass = type === 'success' ? 'bg-forest-green' : 'bg-warm-coral';

        toast.className = `rounded-lg px-4 py-3 text-sm font-semibold text-white shadow-lg transition ${colorClass}`;
        toast.textContent = message;

        container.appendChild(toast);

        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(6px)';

            setTimeout(() => {
                toast.remove();
            }, 250);
        }, 3500);
    }

    // ============================================================================
    // MODAL CLOSE EVENTS
    // ============================================================================

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeCreateModal();
            closeEditModal();
        }
    });

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

    // Keeps old references from other scripts safe if needed.
    window.__paymentMethodPageReady = true;
});
