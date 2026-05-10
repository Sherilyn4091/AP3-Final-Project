/**
 * ============================================================================
 * GENRE MANAGEMENT JAVASCRIPT
 * resources/js/admin-pages/genre.js
 * ============================================================================
 * Handles all client-side operations for genre management:
 * - Add/Edit/Delete genres
 * - Toggle active/inactive status
 * - View students who prefer this genre
 * - Form validation and modals
 * ============================================================================
 */

document.addEventListener('DOMContentLoaded', function() {
    // Make functions globally accessible
    window.openAddModal = openAddModal;
    window.editGenre = editGenre;
    window.deleteGenre = deleteGenre;
    window.toggleStatus = toggleStatus;
    window.viewStudents = viewStudents;
    window.closeModal = closeModal;
    window.closeStudentModal = closeStudentModal;
    window.submitGenre = submitGenre;
});

const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

/**
 * Escape values before placing them inside modal HTML.
 * This prevents quotes or HTML-like text from breaking the edit modal.
 */
function escapeHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

/**
 * Open modal to add new genre
 */
function openAddModal() {
    const modal = document.getElementById('genre-modal');
    
    modal.innerHTML = `
        <div class="bg-white rounded-lg max-w-lg w-full max-h-[90vh] overflow-y-auto p-4 sm:p-5 shadow-xl animate-fade-in">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold text-primary-dark">Add new genre</h2>
                <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            
            <form onsubmit="submitGenre(event, 'create')">
                <div class="space-y-3">
                    <div>
                        <label class="block font-semibold text-gray-700 mb-2">Genre name *</label>
                        <input type="text" id="genre-name" required maxlength="100" 
                               placeholder="e.g., Rock, Pop, Jazz, Classical"
                               class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-secondary-blue focus:ring-2 focus:ring-secondary-blue">
                        <p class="text-xs text-gray-500 mt-1">Maximum 100 characters</p>
                    </div>
                    
                    <div>
                        <label class="block font-semibold text-gray-700 mb-2">Description (optional)</label>
                        <textarea id="genre-description" rows="2" maxlength="500"
                                  placeholder="Brief description of this genre..."
                                  class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-secondary-blue focus:ring-2 focus:ring-secondary-blue"></textarea>
                        <p class="text-xs text-gray-500 mt-1">Maximum 500 characters</p>
                    </div>
                </div>
                
                <div class="flex gap-3 mt-5">
                    <button type="submit" class="bg-forest-green text-white px-4 py-2.5 rounded-lg hover:bg-forest-green-dark flex-1 font-semibold">
                        Create genre
                    </button>
                    <button type="button" onclick="closeModal()" class="bg-gray-200 text-gray-700 px-4 py-2.5 rounded-lg hover:bg-gray-300 flex-1 font-semibold">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    `;
    
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    
    // Focus on name input
    setTimeout(() => document.getElementById('genre-name')?.focus(), 100);
}

/**
 * Open modal to edit existing genre
 */
async function editGenre(genreId) {
    try {
        const response = await fetch(`/admin/genres/${genreId}`, {
            headers: { 'Accept': 'application/json' }
        });
        
        const data = await response.json();
        
        if (!response.ok) {
            throw new Error(data.message || 'Failed to fetch genre');
        }
        
        const genre = data.genre;
        const safeGenreName = escapeHtml(genre.genre_name || '');
        const safeDescription = escapeHtml(genre.description || '');
        
        const modal = document.getElementById('genre-modal');
        modal.innerHTML = `
            <div class="bg-white rounded-lg max-w-lg w-full max-h-[90vh] overflow-y-auto p-4 sm:p-5 shadow-xl animate-fade-in">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-bold text-primary-dark">Edit genre</h2>
                    <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                
                <form onsubmit="submitGenre(event, 'update', ${genreId})">
                    <div class="space-y-3">
                        <div>
                            <label class="block font-semibold text-gray-700 mb-2">Genre name *</label>
                            <input type="text" id="genre-name" required maxlength="100" 
                                   value="${safeGenreName}"
                                   class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-secondary-blue focus:ring-2 focus:ring-secondary-blue">
                            <p class="text-xs text-gray-500 mt-1">Maximum 100 characters</p>
                        </div>
                        
                        <div>
                            <label class="block font-semibold text-gray-700 mb-2">Description (optional)</label>
                            <textarea id="genre-description" rows="2" maxlength="500"
                                      class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-secondary-blue focus:ring-2 focus:ring-secondary-blue">${safeDescription}</textarea>
                            <p class="text-xs text-gray-500 mt-1">Maximum 500 characters</p>
                        </div>
                        
                        <div class="bg-gray-100 p-3 rounded-lg text-sm">
                            <p class="font-semibold text-gray-700">Current usage</p>
                            <p class="text-gray-600 mt-1">${genre.student_count} student(s) prefer this genre</p>
                        </div>
                    </div>
                    
                    <div class="flex gap-3 mt-5">
                        <button type="submit" class="bg-secondary-blue text-white px-4 py-2.5 rounded-lg hover:bg-secondary-blue-dark flex-1 font-semibold">
                            Save changes
                        </button>
                        <button type="button" onclick="closeModal()" class="bg-gray-200 text-gray-700 px-4 py-2.5 rounded-lg hover:bg-gray-300 flex-1 font-semibold">
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
 * Submit genre form (create or update)
 */
async function submitGenre(event, action, genreId = null) {
    event.preventDefault();
    
    const name = document.getElementById('genre-name').value.trim();
    const description = document.getElementById('genre-description').value.trim();
    
    // Client-side validation
    if (!name) {
        showToast('Genre name is required', 'error');
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
        genre_name: name,
        description: description || null
    };
    
    let url, method;
    
    if (action === 'create') {
        url = '/admin/genres';
        method = 'POST';
    } else {
        url = `/admin/genres/${genreId}`;
        method = 'PUT';
    }
    
    try {
        const response = await fetch(url, {
            method: method,
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
 * Delete genre with usage check (HARD BLOCK)
 */
async function deleteGenre(genreId) {
    if (!confirm('Are you sure you want to delete this genre?\n')) {
        return;
    }
    
    try {
        const response = await fetch(`/admin/genres/${genreId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        });
        
        const data = await response.json();
        
        if (!response.ok) {
            // Handle usage conflict (409 status) - HARD BLOCK
            if (response.status === 409) {
                alert(data.message + '\n\nPlease update student preferences first, or set this genre to inactive instead.');
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
 * Toggle genre active/inactive status
 */
async function toggleStatus(genreId) {
    try {
        const response = await fetch(`/admin/genres/${genreId}/toggle-status`, {
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
 * View students who prefer this genre
 */
async function viewStudents(genreId) {
    try {
        const response = await fetch(`/admin/genres/${genreId}/students`, {
            headers: { 'Accept': 'application/json' }
        });
        
        const data = await response.json();
        
        if (!response.ok) {
            throw new Error(data.message || 'Failed to fetch students');
        }
        
        const modal = document.getElementById('student-list-modal');
        modal.innerHTML = `
            <div class="bg-white rounded-lg max-w-2xl w-full max-h-[85vh] overflow-hidden shadow-xl animate-fade-in">
                <div class="bg-gradient-to-r from-secondary-blue to-forest-green p-4 text-white">
                    <div class="flex items-center justify-between">
                        <h2 class="text-xl font-bold">Students who prefer this genre</h2>
                        <button onclick="closeStudentModal()" class="text-white hover:bg-white hover:bg-opacity-20 rounded-full p-2 transition-all">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                    <p class="text-sm opacity-90 mt-1">${data.students.length} student(s) prefer this genre</p>
                </div>
                
                <div class="p-4 overflow-y-auto" style="max-height: calc(85vh - 130px);">
                    ${data.students.length > 0 ? `
                        <div class="space-y-3">
                            ${data.students.map(student => `
                                <div class="border border-gray-200 rounded-lg p-3 hover:border-secondary-blue transition-all">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-2 mb-2">
                                                <h3 class="font-semibold text-gray-900">${student.full_name}</h3>
                                            </div>
                                            <div class="text-sm text-gray-600 space-y-1">
                                                ${student.email ? `<p>Email: ${student.email}</p>` : ''}
                                                ${student.phone ? `<p>Phone: ${student.phone}</p>` : ''}
                                                ${student.enrollment_date ? `<p>Enrolled: ${new Date(student.enrollment_date).toLocaleDateString('en-US', {year: 'numeric', month: 'short', day: 'numeric'})}</p>` : ''}
                                            </div>
                                        </div>
                                        <span class="ml-4 px-3 py-1 rounded-full text-xs font-semibold ${student.is_active ? 'bg-forest-green text-white' : 'bg-gray-400 text-white'}">
                                            ${student.is_active ? 'Active' : 'Inactive'}
                                        </span>
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    ` : `
                        <div class="text-center py-8 text-gray-500">
                            <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                            <p class="text-lg font-semibold">No students prefer this genre</p>
                        </div>
                    `}
                </div>
                
                <div class="border-t p-4 bg-gray-50">
                    <button onclick="closeStudentModal()" class="w-full bg-secondary-blue text-white py-3 rounded-lg hover:bg-secondary-blue-dark font-semibold">
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
    const modal = document.getElementById('genre-modal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function closeStudentModal() {
    const modal = document.getElementById('student-list-modal');
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