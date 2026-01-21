/**
 * ============================================================================
 * INSTRUMENT MANAGEMENT JAVASCRIPT FUNCTIONS
 * ============================================================================
 */

// resources/js/admin-pages/instrument.js

const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
/**
 * Displays a toast notification with close button.
 * @param {string} message The message to display.
 * @param {string} type 'success', 'error', or 'custom'.
 * @param {string|null} customColor The hex color for 'custom' type.
 */
function showToast(message, type = 'success', customColor = null) {
    const container = document.getElementById('toast-container');
    if (!container) return; // Don't proceed if container doesn't exist
    
    const toast = document.createElement('div');
    let bgColor, icon;
    
    if (type === 'custom' && customColor) {
        toast.style.backgroundColor = customColor;
        icon = ''; // No icon for custom type
    } else if (type === 'success') {
        bgColor = 'bg-forest-green';
        icon = `<svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>`;
    } else { // error
        bgColor = 'bg-warm-coral';
        icon = `<svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>`;
    }
    
    toast.className = `flex items-center justify-between p-4 rounded-lg shadow-lg text-white ${bgColor} animate-fade-in-up`;
    toast.innerHTML = `
        <div class="flex items-center">
            ${icon} <span class="font-semibold">${message}</span>
        </div>
        <button onclick="this.parentElement.remove()" class="ml-4 text-black hover:text-gray-700 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    `;
    
    container.appendChild(toast);
    
    setTimeout(() => {
        toast.classList.add('animate-fade-out');
        toast.addEventListener('animationend', () => toast.remove());
    }, 5000); // Auto-dismiss after 5 seconds
}

// Make functions globally accessible

window.openAddInstrumentModal = openAddInstrumentModal;
window.editInstrument = editInstrument;
window.closeInstrumentModal = closeInstrumentModal;
window.submitInstrument = submitInstrument;
window.deactivateInstrument = deactivateInstrument;
window.activateInstrument = activateInstrument;
window.applyInstrumentFilters = applyInstrumentFilters;
window.clearInstrumentFilters = clearInstrumentFilters;

/**
 * Open modal to add new instrument
 */
function openAddInstrumentModal() {
    const categoriesElement = document.getElementById('instrument-data');
    const categories = categoriesElement ? JSON.parse(categoriesElement.dataset.categories) : [];
    
    const modal = document.getElementById('instrument-modal');
    modal.innerHTML = `
        <div class="bg-white rounded-lg max-w-2xl w-full p-6 shadow-2xl animate-fade-in">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold text-primary-dark">Add new instrument</h2>
                <button onclick="closeInstrumentModal()" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <form id="instrument-form" onsubmit="submitInstrument(event)">
                <input type="hidden" id="instrument-id" value="">
                
                <div class="space-y-4">
                    <div>
                        <label class="block font-semibold text-gray-700 mb-2">Instrument name *</label>
                        <input type="text" id="instrument-name" required maxlength="100"
                               placeholder="e.g., Guitar, Piano, Drums"
                               class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-secondary-blue">
                    </div>
                    
                    <div>
                        <label class="block font-semibold text-gray-700 mb-2">Category *</label>
                        <select id="instrument-category" required class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-secondary-blue">
                            <option value="">Select category...</option>
                            ${categories.map(cat => `<option value="${cat}">${cat}</option>`).join('')}
                        </select>
                    </div>
                    
                    <div>
                        <label class="block font-semibold text-gray-700 mb-2">Description</label>
                        <textarea id="instrument-description" rows="3" maxlength="500"
                                  placeholder="Brief description of the instrument..."
                                  class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-secondary-blue"></textarea>
                        <p class="text-xs text-gray-500 mt-1">Maximum 500 characters</p>
                    </div>
                </div>
                
                <div class="flex gap-3 mt-6">
                    <button type="submit" class="bg-forest-green text-white px-6 py-3 rounded-lg hover:bg-forest-green-dark flex-1 font-semibold">
                        Create instrument
                    </button>
                    <button type="button" onclick="closeInstrumentModal()" class="bg-gray-200 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-300 flex-1 font-semibold">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    `;
    
    modal.classList.remove('hidden');
}

/**
 * Open modal to edit existing instrument
 */
async function editInstrument(instrumentId) {
    try {
        const response = await fetch(`/admin/instruments/${instrumentId}`, {
            headers: { 'Accept': 'application/json' }
        });
        
        const data = await response.json();
        
        if (!data.success) {
            throw new Error(data.message);
        }
        
        const instrument = data.instrument;
        const categoriesElement = document.getElementById('instrument-data');
        const categories = categoriesElement ? JSON.parse(categoriesElement.dataset.categories) : [];
        
        const modal = document.getElementById('instrument-modal');
        modal.innerHTML = `
            <div class="bg-white rounded-lg max-w-2xl w-full p-6 shadow-2xl animate-fade-in">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-bold text-primary-dark">Edit instrument</h2>
                    <button onclick="closeInstrumentModal()" class="text-gray-500 hover:text-gray-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                ${instrument.is_system ? `
                    <div class="bg-secondary-blue bg-opacity-10 border-l-4 border-secondary-blue p-4 rounded mb-4">
                        <p class="text-sm text-gray-700"><strong>Note:</strong> This is a system instrument. Name and category cannot be changed.</p>
                    </div>
                ` : ''}
                
                <form id="instrument-form" onsubmit="submitInstrument(event, ${instrumentId})">
                    <input type="hidden" id="instrument-id" value="${instrumentId}">
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block font-semibold text-gray-700 mb-2">Instrument name *</label>
                            <input type="text" id="instrument-name" required maxlength="100"
                                   value="${instrument.instrument_name}"
                                   ${instrument.is_system ? 'readonly' : ''}
                                   class="w-full px-4 py-2 border-2 ${instrument.is_system ? 'bg-gray-100' : 'border-gray-300'} rounded-lg focus:border-secondary-blue">
                        </div>
                        
                        <div>
                            <label class="block font-semibold text-gray-700 mb-2">Category *</label>
                            <select id="instrument-category" required ${instrument.is_system ? 'disabled' : ''}
                                    class="w-full px-4 py-2 border-2 ${instrument.is_system ? 'bg-gray-100' : 'border-gray-300'} rounded-lg focus:border-secondary-blue">
                                ${categories.map(cat => `<option value="${cat}" ${cat === instrument.category ? 'selected' : ''}>${cat}</option>`).join('')}
                            </select>
                        </div>
                        
                        <div>
                            <label class="block font-semibold text-gray-700 mb-2">Description</label>
                            <textarea id="instrument-description" rows="3" maxlength="500"
                                      class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-secondary-blue">${instrument.description || ''}</textarea>
                            <p class="text-xs text-gray-500 mt-1">Maximum 500 characters</p>
                        </div>
                    </div>
                    
                    <div class="flex gap-3 mt-6">
                        <button type="submit" class="bg-forest-green text-white px-6 py-3 rounded-lg hover:bg-forest-green-dark flex-1 font-semibold">
                            Save changes
                        </button>
                        <button type="button" onclick="closeInstrumentModal()" class="bg-gray-200 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-300 flex-1 font-semibold">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        `;
        
        modal.classList.remove('hidden');
        
    } catch (error) {
        showToast(error.message, 'error');
    }
}

/**
 * Submit instrument form (create or update)
 */
async function submitInstrument(event, instrumentId = null) {
    event.preventDefault();
    
    const name = document.getElementById('instrument-name').value.trim();
    const category = document.getElementById('instrument-category').value;
    const description = document.getElementById('instrument-description').value.trim();
    
    const payload = {
        instrument_name: name,
        category: category,
        description: description || null
    };
    
    const isEdit = instrumentId !== null;
    const url = isEdit ? `/admin/instruments/${instrumentId}` : '/admin/instruments';
    const method = isEdit ? 'PUT' : 'POST';
    
    if (isEdit) {
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
        closeInstrumentModal();
        setTimeout(() => window.location.reload(), 1500);
        
    } catch (error) {
        showToast(error.message, 'error');
    }
}

/**
 * Deactivate instrument
 */
async function deactivateInstrument(instrumentId) {
    try {
        // First check usage
        const usageResponse = await fetch(`/admin/instruments/${instrumentId}/usage`, {
            headers: { 'Accept': 'application/json' }
        });
        
        const usageData = await usageResponse.json();
        
        if (usageData.success && usageData.usage.in_use) {
            if (!confirm(`${usageData.usage.message}\n\nAre you sure you want to deactivate this instrument?`)) {
                return;
            }
        } else {
            if (!confirm('Deactivate this instrument? It will be hidden from active lists.')) {
                return;
            }
        }
        
        const response = await fetch(`/admin/instruments/${instrumentId}/toggle-status`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        });
        
        const data = await response.json();
        
        if (!response.ok) {
            throw new Error(data.message || 'Failed to deactivate');
        }
        
        showToast(data.message, 'success');
        setTimeout(() => window.location.reload(), 1500);
        
    } catch (error) {
        showToast(error.message, 'error');
    }
}

/**
 * Activate instrument
 */
async function activateInstrument(instrumentId) {
    if (!confirm('Activate this instrument? It will be available for new enrollments.')) {
        return;
    }
    
    try {
        const response = await fetch(`/admin/instruments/${instrumentId}/toggle-status`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        });
        
        const data = await response.json();
        
        if (!response.ok) {
            throw new Error(data.message || 'Failed to activate');
        }
        
        showToast(data.message, 'success');
        setTimeout(() => window.location.reload(), 1500);
        
    } catch (error) {
        showToast(error.message, 'error');
    }
}

/**
 * Close instrument modal
 */
function closeInstrumentModal() {
    const modal = document.getElementById('instrument-modal');
    modal.classList.add('hidden');
    modal.innerHTML = '';
}

/**
 * Apply filters to instrument table
 */
function applyInstrumentFilters() {
    const searchTerm = document.getElementById('search-name').value.toLowerCase().trim();
    const categoryFilter = document.getElementById('filter-category').value;
    const statusFilter = document.getElementById('filter-status').value;
    
    const rows = document.querySelectorAll('.instrument-row');
    let visibleCount = 0;
    
    rows.forEach(row => {
        const name = row.dataset.name;
        const category = row.dataset.category;
        const status = row.dataset.status;
        
        let showRow = true;
        
        // Search filter
        if (searchTerm && !name.includes(searchTerm)) {
            showRow = false;
        }
        
        // Category filter
        if (categoryFilter !== 'all' && category !== categoryFilter) {
            showRow = false;
        }
        
        // Status filter
        if (statusFilter !== 'all' && status !== statusFilter) {
            showRow = false;
        }
        
        if (showRow) {
            row.classList.remove('hidden');
            visibleCount++;
        } else {
            row.classList.add('hidden');
        }
    });
    
    // Show "no results" message if needed
    if (visibleCount === 0) {
        // You can add a "no results" row here if desired
    }
}

/**
 * Clear all filters
 */
function clearInstrumentFilters() {
    document.getElementById('search-name').value = '';
    document.getElementById('filter-category').value = 'all';
    document.getElementById('filter-status').value = 'all';
    
    applyInstrumentFilters();
}

/**
 * Refresh a single instrument row without page reload
 */
async function refreshInstrumentRow(instrumentId) {
    try {
        const response = await fetch(`/admin/instruments/${instrumentId}`, {
            headers: { 'Accept': 'application/json' }
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Update the row's data attributes and button
            const row = document.querySelector(`[data-instrument-id="${instrumentId}"]`);
            if (row) {
                row.dataset.status = data.instrument.is_active ? 'active' : 'inactive';
                
                // Update status badge
                const statusBadge = row.querySelector('.status-badge');
                if (statusBadge) {
                    statusBadge.className = `status-badge inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold ${
                        data.instrument.is_active 
                            ? 'bg-forest-green text-white' 
                            : 'bg-gray-400 text-white'
                    }`;
                    statusBadge.textContent = data.instrument.is_active ? 'Active' : 'Inactive';
                }
                
                // Update action button
                const actionBtn = row.querySelector('.action-toggle-btn');
                if (actionBtn) {
                    if (data.instrument.is_active) {
                        actionBtn.onclick = () => deactivateInstrument(instrumentId);
                        actionBtn.className = 'action-toggle-btn p-2 rounded-lg text-warm-coral hover:bg-warm-coral hover:text-white transition-all duration-200 shadow-sm hover:shadow';
                        actionBtn.title = 'Deactivate';
                        actionBtn.innerHTML = `<svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 5.636l-12.728 12.728m0-12.728l12.728 12.728" /></svg>`;
                    } else {
                        actionBtn.onclick = () => activateInstrument(instrumentId);
                        actionBtn.className = 'action-toggle-btn p-2 rounded-lg text-forest-green hover:bg-forest-green hover:text-white transition-all duration-200 shadow-sm hover:shadow';
                        actionBtn.title = 'Activate';
                        actionBtn.innerHTML = `<svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>`;
                    }
                }
            }
        }
    } catch (error) {
        console.error('Failed to refresh row:', error);
    }
}

/**
 * Open modal showing students enrolled in an instrument
 */
async function viewInstrumentStudents(instrumentId, instrumentName) {
    try {
        const response = await fetch(`/admin/instruments/${instrumentId}/students`, {
            headers: { 'Accept': 'application/json' }
        });
        
        const data = await response.json();
        
        if (!data.success) {
            throw new Error(data.message);
        }
        
        const modal = document.getElementById('instrument-modal');
        modal.innerHTML = `
            <div class="bg-white rounded-lg max-w-2xl w-full p-6 shadow-2xl animate-fade-in">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-bold text-primary-dark">Students enrolled in ${instrumentName}</h2>
                    <button onclick="closeInstrumentModal()" class="text-gray-500 hover:text-gray-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                ${data.students.length === 0 ? `
                    <div class="text-center py-8">
                        <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                        <p class="text-gray-600">No active students enrolled</p>
                    </div>
                ` : `
                    <div class="overflow-y-auto max-h-96">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">#</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Student name</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Email</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Enrolled since</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                ${data.students.map((student, index) => `
                                    <tr class="hover:bg-blue-50/40 transition-colors">
                                        <td class="px-4 py-3 text-sm text-gray-600">${index + 1}</td>
                                        <td class="px-4 py-3 text-sm font-medium text-gray-900">${student.student_name}</td>
                                        <td class="px-4 py-3 text-sm text-gray-600">${student.user_email}</td>
                                        <td class="px-4 py-3 text-sm text-gray-600">${new Date(student.enrollment_date).toLocaleDateString()}</td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </div>
                `}
                
                <button onclick="closeInstrumentModal()" class="w-full mt-6 bg-gray-200 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-300 font-semibold">
                    Close
                </button>
            </div>
        `;
        
        modal.classList.remove('hidden');
        
    } catch (error) {
        showToast(error.message, 'error');
    }
}

// Make function globally accessible
window.viewInstrumentStudents = viewInstrumentStudents;

/**
 * END OF INSTRUMENT MANAGEMENT FUNCTIONS
 */