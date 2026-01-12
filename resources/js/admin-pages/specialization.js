/**
 * ============================================================================
 * SPECIALIZATION MANAGEMENT JAVASCRIPT
 * resources/js/admin-pages/specialization.js
 * ============================================================================
 * Handles all client-side operations for specialization management:
 * - Add/Edit/Delete specializations
 * - Toggle active/inactive status
 * - View assigned instructors
 * - Form validation and modals
 * ============================================================================
 */

document.addEventListener('DOMContentLoaded', function() {
    // Make functions globally accessible
    window.openAddModal = openAddModal;
    window.editSpecialization = editSpecialization;
    window.deleteSpecialization = deleteSpecialization;
    window.toggleStatus = toggleStatus;
    window.viewInstructors = viewInstructors;
    window.closeModal = closeModal;
    window.closeInstructorModal = closeInstructorModal;
    window.submitSpecialization = submitSpecialization;
});

const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

/**
 * Open modal to add new specialization
 */
function openAddModal() {
    const modal = document.getElementById('specialization-modal');
    
    modal.innerHTML = `
        <div class="bg-white rounded-lg max-w-xl w-full p-6 shadow-2xl animate-fade-in">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold text-primary-dark">Add new specialization</h2>
                <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            
            <form onsubmit="submitSpecialization(event, 'create')">
                <div class="space-y-4">
                    <div>
                        <label class="block font-semibold text-gray-700 mb-2">Specialization name *</label>
                        <input type="text" id="spec-name" required maxlength="100" 
                               placeholder="e.g., Guitar, Piano, Voice/Vocals"
                               class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-secondary-blue focus:ring-2 focus:ring-secondary-blue">
                        <p class="text-xs text-gray-500 mt-1">Maximum 100 characters</p>
                    </div>
                    
                    <div>
                        <label class="block font-semibold text-gray-700 mb-2">Description (optional)</label>
                        <textarea id="spec-description" rows="3" maxlength="500"
                                  placeholder="Brief description of this specialization..."
                                  class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-secondary-blue focus:ring-2 focus:ring-secondary-blue"></textarea>
                        <p class="text-xs text-gray-500 mt-1">Maximum 500 characters</p>
                    </div>
                </div>
                
                <div class="flex gap-3 mt-6">
                    <button type="submit" class="bg-forest-green text-white px-6 py-3 rounded-lg hover:bg-forest-green-dark flex-1 font-semibold">
                        Create specialization
                    </button>
                    <button type="button" onclick="closeModal()" class="bg-gray-200 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-300 flex-1 font-semibold">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    `;
    
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    
    // Focus on name input
    setTimeout(() => document.getElementById('spec-name')?.focus(), 100);
}

/**
 * Open modal to edit existing specialization
 */
async function editSpecialization(specializationId) {
    try {
        const response = await fetch(`/admin/specializations/${specializationId}`, {
            headers: { 'Accept': 'application/json' }
        });
        
        const data = await response.json();
        
        if (!response.ok) {
            throw new Error(data.message || 'Failed to fetch specialization');
        }
        
        const spec = data.specialization;
        
        const modal = document.getElementById('specialization-modal');
        modal.innerHTML = `
            <div class="bg-white rounded-lg max-w-xl w-full p-6 shadow-2xl animate-fade-in">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-bold text-primary-dark">Edit specialization</h2>
                    <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                
                <form onsubmit="submitSpecialization(event, 'update', ${specializationId})">
                    <div class="space-y-4">
                        <div>
                            <label class="block font-semibold text-gray-700 mb-2">Specialization name *</label>
                            <input type="text" id="spec-name" required maxlength="100" 
                                   value="${spec.specialization_name}"
                                   class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-secondary-blue focus:ring-2 focus:ring-secondary-blue">
                            <p class="text-xs text-gray-500 mt-1">Maximum 100 characters</p>
                        </div>
                        
                        <div>
                            <label class="block font-semibold text-gray-700 mb-2">Description (optional)</label>
                            <textarea id="spec-description" rows="3" maxlength="500"
                                      class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-secondary-blue focus:ring-2 focus:ring-secondary-blue">${spec.description || ''}</textarea>
                            <p class="text-xs text-gray-500 mt-1">Maximum 500 characters</p>
                        </div>
                        
                        <div class="bg-gray-100 p-3 rounded-lg text-sm">
                            <p class="font-semibold text-gray-700">Current usage</p>
                            <p class="text-gray-600 mt-1">${spec.instructor_count} instructor(s) assigned to this specialization</p>
                        </div>
                    </div>
                    
                    <div class="flex gap-3 mt-6">
                        <button type="submit" class="bg-secondary-blue text-white px-6 py-3 rounded-lg hover:bg-secondary-blue-dark flex-1 font-semibold">
                            Save changes
                        </button>
                        <button type="button" onclick="closeModal()" class="bg-gray-200 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-300 flex-1 font-semibold">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        `;
        
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        
    } catch (error) {
        showToast(error.message, 'error');
    }
}

/**
 * Submit specialization form (create or update)
 */
async function submitSpecialization(event, action, specializationId = null) {
    event.preventDefault();
    
    const name = document.getElementById('spec-name').value.trim();
    const description = document.getElementById('spec-description').value.trim();
    
    // Client-side validation
    if (!name) {
        showToast('Specialization name is required', 'error');
        return;
    }
    
    if (name.length > 100) {
        showToast('Name cannot exceed 100 characters', 'error');
        return;
    }
    
    if (description && description.length > 500) {
        showToast('Description cannot exceed 500 characters', 'error');
        return;
    }
    
    const payload = {
        specialization_name: name,
        description: description || null
    };
    
    let url, method;
    
    if (action === 'create') {
        url = '/admin/specializations';
        method = 'POST';
    } else {
        url = `/admin/specializations/${specializationId}`;
        method = 'PUT';
        payload._method = 'PUT';
    }
    
    try {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        });
        
        const data = await response.json();
        
        if (!response.ok) {
            if (data.errors) {
                const errorMsg = Object.values(data.errors).flat().join('\n');
                showToast(errorMsg, 'error');
            } else {
                throw new Error(data.message || 'Operation failed');
            }
            return;
        }
        
        showToast(data.message, 'success');
        closeModal();
        setTimeout(() => window.location.reload(), 1500);
        
    } catch (error) {
        showToast(error.message, 'error');
    }
}

/**
 * Delete specialization with usage check
 */
async function deleteSpecialization(specializationId) {
    if (!confirm('Are you sure you want to delete this specialization?\n\nThis action cannot be undone.')) {
        return;
    }
    
    try {
        const response = await fetch(`/admin/specializations/${specializationId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        });
        
        const data = await response.json();
        
        if (!response.ok) {
            // Handle usage conflict (409 status)
            if (response.status === 409) {
                alert(data.message + '\n\nPlease remove this specialization from all instructors first, or set it to inactive instead.');
                return;
            }
            throw new Error(data.message || 'Failed to delete');
        }
        
        showToast(data.message, 'success');
        setTimeout(() => window.location.reload(), 1500);
        
    } catch (error) {
        showToast(error.message, 'error');
    }
}

/**
 * Toggle specialization active/inactive status
 */
async function toggleStatus(specializationId) {
    try {
        const response = await fetch(`/admin/specializations/${specializationId}/toggle-status`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        });
        
        const data = await response.json();
        
        if (!response.ok) {
            throw new Error(data.message || 'Failed to toggle status');
        }
        
        showToast(data.message, 'success');
        setTimeout(() => window.location.reload(), 1000);
        
    } catch (error) {
        showToast(error.message, 'error');
    }
}

/**
 * View instructors assigned to this specialization
 */
async function viewInstructors(specializationId) {
    try {
        const response = await fetch(`/admin/specializations/${specializationId}/instructors`, {
            headers: { 'Accept': 'application/json' }
        });
        
        const data = await response.json();
        
        if (!response.ok) {
            throw new Error(data.message || 'Failed to fetch instructors');
        }
        
        const modal = document.getElementById('instructor-list-modal');
        modal.innerHTML = `
            <div class="bg-white rounded-lg max-w-2xl w-full max-h-[80vh] overflow-hidden shadow-2xl animate-fade-in">
                <div class="bg-gradient-to-r from-secondary-blue to-forest-green p-6 text-white">
                    <div class="flex items-center justify-between">
                        <h2 class="text-2xl font-bold">Assigned instructors</h2>
                        <button onclick="closeInstructorModal()" class="text-white hover:bg-white hover:bg-opacity-20 rounded-full p-2 transition-all">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                    <p class="text-sm opacity-90 mt-1">${data.instructors.length} instructor(s) using this specialization</p>
                </div>
                
                <div class="p-6 overflow-y-auto" style="max-height: calc(80vh - 150px);">
                    ${data.instructors.length > 0 ? `
                        <div class="space-y-3">
                            ${data.instructors.map(instructor => `
                                <div class="border-2 border-gray-200 rounded-lg p-4 hover:border-secondary-blue transition-all">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-2 mb-2">
                                                <h3 class="font-semibold text-gray-900">${instructor.full_name}</h3>
                                                ${instructor.is_primary ? '<span class="px-2 py-1 bg-forest-green text-white text-xs font-semibold rounded-full">Primary</span>' : ''}
                                            </div>
                                            <div class="text-sm text-gray-600 space-y-1">
                                                ${instructor.employee_id ? `<p>Employee ID: ${instructor.employee_id}</p>` : ''}
                                                ${instructor.email ? `<p>Email: ${instructor.email}</p>` : ''}
                                                ${instructor.phone ? `<p>Phone: ${instructor.phone}</p>` : ''}
                                            </div>
                                        </div>
                                        <span class="ml-4 px-3 py-1 rounded-full text-xs font-semibold ${instructor.is_active ? 'bg-forest-green text-white' : 'bg-gray-400 text-white'}">
                                            ${instructor.is_active ? 'Active' : 'Inactive'}
                                        </span>
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    ` : `
                        <div class="text-center py-8 text-gray-500">
                            <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                            <p class="text-lg font-semibold">No instructors assigned</p>
                        </div>
                    `}
                </div>
                
                <div class="border-t p-4 bg-gray-50">
                    <button onclick="closeInstructorModal()" class="w-full bg-secondary-blue text-white py-3 rounded-lg hover:bg-secondary-blue-dark font-semibold">
                        Close
                    </button>
                </div>
            </div>
        `;
        
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        
    } catch (error) {
        showToast(error.message, 'error');
    }
}

/**
 * Close modals
 */
function closeModal() {
    const modal = document.getElementById('specialization-modal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function closeInstructorModal() {
    const modal = document.getElementById('instructor-list-modal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

/**
 * Display toast notification
 */
function showToast(message, type = 'success') {
    const container = document.getElementById('toast-container');
    if (!container) return;
    
    const toast = document.createElement('div');
    
    let bgColor, icon;
    
    if (type === 'success') {
        bgColor = 'bg-forest-green';
        icon = `<svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>`;
    } else {
        bgColor = 'bg-warm-coral';
        icon = `<svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>`;
    }
    
    toast.className = `flex items-center p-4 rounded-lg shadow-lg text-white ${bgColor} animate-fade-in`;
    toast.innerHTML = `${icon} <span class="font-semibold">${message}</span>`;
    
    container.appendChild(toast);
    
    setTimeout(() => {
        toast.classList.add('opacity-0', 'transform', 'translate-x-full');
        setTimeout(() => toast.remove(), 300);
    }, 5000);
}