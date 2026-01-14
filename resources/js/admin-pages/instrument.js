/**
 * ============================================================================
 * INSTRUMENT MANAGEMENT JAVASCRIPT FUNCTIONS
 * ============================================================================
 */

// resources/js/admin-pages/instrument.js

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
            method: isEdit ? 'POST' : 'POST',
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
 * END OF INSTRUMENT MANAGEMENT FUNCTIONS
 */