/**  
 * ============================================================================  
 * JAVASCRIPT FOR ADMIN USER MANAGEMENT PAGE  
 * resources/js/admin-pages.js  
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
  
    // ============================================================================  
// CREATE USER FORM LOGIC - Enhanced with auto-formatting and validation  
// ============================================================================  
  
// Auto-capitalize names (First letter of each word)  
const firstNameInput = document.getElementById('first_name');  
const lastNameInput = document.getElementById('last_name');  
  
if (firstNameInput && lastNameInput) {  
    function capitalizeName(input) {  
        input.addEventListener('input', function(e) {  
            const words = this.value.split(' ');  
            const capitalizedWords = words.map(word => {  
                if (word.length === 0) return word;  
                return word.charAt(0).toUpperCase() + word.slice(1).toLowerCase();  
            });  
            this.value = capitalizedWords.join(' ');  
        });  
    }  
  
    capitalizeName(firstNameInput);  
    capitalizeName(lastNameInput);  
}  
  
// Auto-lowercase email with validation  
const emailInput = document.getElementById('email');  
if (emailInput) {  
    emailInput.addEventListener('input', function(e) {  
        this.value = this.value.toLowerCase();  
        validateEmail(this.value);  
    });  
  
    function validateEmail(email) {  
        const emailRegex = /^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$/;  
        const validationMsg = document.getElementById('email-validation-msg');  
          
        if (!validationMsg) return;  
          
        if (email.length === 0) {  
            validationMsg.classList.add('hidden');  
            return;  
        }  
          
        if (emailRegex.test(email)) {  
            validationMsg.textContent = '✓ Valid email format';  
            validationMsg.className = 'text-xs mt-1 text-forest-green font-medium';  
            validationMsg.classList.remove('hidden');  
        } else {  
            validationMsg.textContent = '✗ Invalid email format';  
            validationMsg.className = 'text-xs mt-1 text-warm-coral font-medium';  
            validationMsg.classList.remove('hidden');  
        }  
    }  
}  
  
// Password strength indicator  
const passwordInput = document.getElementById('password');  
  
// Moved this function to a higher scope to be accessible by the form submit event listener  
function calculatePasswordStrength(password) {  
    let strength = 0;  
    const checks = {  
        length: password.length >= 8,  
        uppercase: /[A-Z]/.test(password),  
        lowercase: /[a-z]/.test(password),  
        number: /[0-9]/.test(password),  
        symbol: /[^A-Za-z0-9]/.test(password)  
    };  
  
    if (checks.length) strength++;  
    if (checks.uppercase && checks.lowercase) strength++;  
    if (checks.number) strength++;  
    if (checks.symbol) strength++;  
  
    return { score: strength, checks: checks };  
}  
  
if (passwordInput) {  
    const strengthBars = [  
        document.getElementById('strength-bar-1'),  
        document.getElementById('strength-bar-2'),  
        document.getElementById('strength-bar-3'),  
        document.getElementById('strength-bar-4')  
    ];  
    const strengthText = document.getElementById('password-strength-text');  
  
    passwordInput.addEventListener('input', function(e) {  
        const password = this.value;  
        const strength = calculatePasswordStrength(password);  
        updateStrengthIndicator(strength);  
    });  
  
    function updateStrengthIndicator(strength) {  
        strengthBars.forEach(bar => {  
            bar.className = 'h-1 flex-1 bg-gray-200 rounded transition-all';  
        });  
  
        const colors = ['bg-warm-coral', 'bg-golden-yellow', 'bg-secondary-blue', 'bg-forest-green'];  
        const texts = [  
            'Weak - Add more characters and variety',  
            'Fair - Add symbols for better security',  
            'Good - Almost there!',  
            'Strong - Excellent password!'  
        ];  
  
        for (let i = 0; i < strength.score; i++) {  
            strengthBars[i].classList.remove('bg-gray-200');  
            strengthBars[i].classList.add(colors[Math.min(strength.score - 1, 3)]);  
        }  
  
        if (strength.score === 0) {  
            strengthText.textContent = 'Must include: 8+ characters, uppercase, lowercase, number, symbol';  
            strengthText.className = 'text-xs font-medium text-gray-500';  
        } else {  
            strengthText.textContent = texts[strength.score - 1];  
            const textColors = ['text-warm-coral', 'text-golden-yellow', 'text-secondary-blue', 'text-forest-green'];  
            strengthText.className = 'text-xs font-medium ' + textColors[Math.min(strength.score - 1, 3)];  
        }  
    }  
}  
  
// Toggle password visibility  
const togglePasswordBtn = document.getElementById('togglePassword');  
  
if (togglePasswordBtn && passwordInput) {  
    const eyeOpen = document.getElementById('eyeOpen');  
    const eyeClosed = document.getElementById('eyeClosed');  
  
    togglePasswordBtn.addEventListener('click', function() {  
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';  
        passwordInput.setAttribute('type', type);  
          
        if (eyeOpen && eyeClosed) {  
            eyeOpen.classList.toggle('hidden');  
            eyeClosed.classList.toggle('hidden');  
        }  
    });  
}  
  
// Role-based field visibility  
const roleSelect = document.getElementById('role');  
const instructorFields = document.getElementById('instructor-fields');  
const yearsExpInput = document.getElementById('years_of_experience');  
  
if (roleSelect && instructorFields) {  
    roleSelect.addEventListener('change', function() {  
        if (this.value === 'instructor') {  
            instructorFields.classList.remove('hidden');  
            if (yearsExpInput) yearsExpInput.setAttribute('required', 'required');  
        } else {  
            instructorFields.classList.add('hidden');  
            if (yearsExpInput) yearsExpInput.removeAttribute('required');  
        }  
    });  
}  
  
// Specialization modal functionality  
const modal = document.getElementById('specialization-modal');  
const assignSpecBtn = document.getElementById('assign-spec-btn');  
const closeModalBtns = document.querySelectorAll('.close-spec-modal, #close-spec-modal-btn');  
const saveSpecBtn = document.getElementById('save-spec-modal-btn');  
const specializationPills = document.getElementById('specialization-pills');  
const specializationCheckboxes = document.querySelectorAll('.specialization-checkbox');  
const primarySpecSelect = document.getElementById('primary_specialization');  
  
if (assignSpecBtn && modal) {  
    assignSpecBtn.addEventListener('click', function() {  
        modal.classList.remove('hidden');  
        modal.classList.add('flex');  
    });  
}  
  
if (closeModalBtns) {  
    closeModalBtns.forEach(btn => {  
        btn.addEventListener('click', function() {  
            if (modal) {  
                modal.classList.add('hidden');  
                modal.classList.remove('flex');  
            }  
        });  
    });  
}  
  
if (modal) {  
    modal.addEventListener('click', function(e) {  
        if (e.target === modal) {  
            modal.classList.add('hidden');  
            modal.classList.remove('flex');  
        }  
    });  
}  
  
if (saveSpecBtn) {  
    saveSpecBtn.addEventListener('click', function() {  
        updateSpecializationPills();  
        updatePrimarySpecializationOptions();  
        modal.classList.add('hidden');  
        modal.classList.remove('flex');  
    });  
}  
  
function updateSpecializationPills() {  
    if (!specializationCheckboxes || !specializationPills) return;  
      
    const selectedSpecs = Array.from(specializationCheckboxes)  
        .filter(cb => cb.checked)  
        .map(cb => ({  
            id: cb.value,  
            name: cb.parentElement.querySelector('span').textContent  
        }));  
  
    if (selectedSpecs.length === 0) {  
        specializationPills.innerHTML = '<p class="text-sm text-gray-500">No specializations assigned yet.</p>';  
        return;  
    }  
  
    specializationPills.innerHTML = selectedSpecs.map(spec => `  
        <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium bg-forest-green text-white">  
            ${spec.name}  
        </span>  
    `).join('');  
}  
  
function updatePrimarySpecializationOptions() {  
    if (!specializationCheckboxes || !primarySpecSelect) return;  
      
    const selectedSpecIds = Array.from(specializationCheckboxes)  
        .filter(cb => cb.checked)  
        .map(cb => cb.value);  
  
    Array.from(primarySpecSelect.options).forEach(option => {  
        if (option.value && !selectedSpecIds.includes(option.value)) {  
            option.disabled = true;  
            option.style.display = 'none';  
        } else {  
            option.disabled = false;  
            option.style.display = 'block';  
        }  
    });  
}  
  
// Phone number validation (11 digits only)  
const phoneInput = document.getElementById('phone');  
if (phoneInput) {  
    phoneInput.addEventListener('input', function(e) {  
        this.value = this.value.replace(/\D/g, '');  
        if (this.value.length > 11) {  
            this.value = this.value.slice(0, 11);  
        }  
    });  
}  
  
// Form validation before submit  
const form = document.getElementById('createUserForm');  
if (form) {  
    form.addEventListener('submit', function(e) {  
        const emailRegex = /^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$/;  
          
        if (emailInput && !emailRegex.test(emailInput.value)) {  
            e.preventDefault();  
            emailInput.focus();  
            alert('Please enter a valid email address.');  
            return false;  
        }  
  
        if (roleSelect && roleSelect.value === 'instructor' && yearsExpInput && !yearsExpInput.value) {  
            e.preventDefault();  
            yearsExpInput.focus();  
            alert('Years of experience is required for instructors.');  
            return false;  
        }  
  
        if (passwordInput) {  
            const passwordStrength = calculatePasswordStrength(passwordInput.value);  
            if (passwordStrength.score < 4) {  
                e.preventDefault();  
                passwordInput.focus();  
                alert('Password must include: 8+ characters, uppercase, lowercase, number, and symbol.');  
                return false;  
            }  
        }
         // If all validations pass, allow form to submit normally
         return true;  
    });  
}  
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
    if (!container) return; // Don't proceed if container doesn't exist  
      
    const toast = document.createElement('div');  
      
    let bgColor;  
    let icon;  
      
    if (type === 'custom' && customColor) {  
        toast.style.backgroundColor = customColor;  
        icon = `...`; // Some default icon or none  
    } else if (type === 'success') {  
        bgColor = 'bg-forest-green';  
        icon = `<svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>`;  
    } else { // error  
        bgColor = 'bg-warm-coral';  
        icon = `<svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>`;  
    }  
      
    toast.className = `flex items-center p-4 rounded-lg shadow-lg text-white ${bgColor} animate-fade-in-up`;  
    toast.innerHTML = `${icon} <span class="font-semibold">${message}</span>`;  
      
    container.appendChild(toast);  
      
    setTimeout(() => {  
        toast.classList.add('animate-fade-out');  
        toast.addEventListener('animationend', () => toast.remove());  
    }, 5000); // 5 seconds  
}  
  
/**  
 * ============================================================================  
 * INSTRUCTOR MANAGEMENT JAVASCRIPT FUNCTIONS  
 * ============================================================================  
 * These functions handle instructor-specific operations:  
 * - View instructor details in modal  
 * - Manage specializations (assign/remove/set primary)  
 * - View performance reports  
 * - Update availability  
 * ============================================================================  
 */  
  
// Make functions globally accessible  
window.viewInstructor = viewInstructor;  
window.manageSpecializations = manageSpecializations;  
window.viewPerformance = viewPerformance;  
window.closeInstructorModal = closeInstructorModal;  
window.closeSpecModal = closeSpecModal;  
window.closePerfModal = closePerfModal;  
window.assignSpecialization = assignSpecialization;  
window.removeSpecialization = removeSpecialization;  
window.setPrimarySpec = setPrimarySpec;  
window.updateAvailability = updateAvailability;  
  
/**  
 * View instructor details in modal  
 * @param {number} instructorId - The ID of the instructor  
 */  
async function viewInstructor(instructorId) {  
    try {  
        const response = await fetch(`/admin/instructors/${instructorId}`, {  
            headers: { 'Accept': 'application/json' }  
        });  
          
        if (!response.ok) throw new Error('Failed to fetch instructor details');  
          
        const data = await response.json();  
        const instructor = data.instructor;  
        const specializations = data.specializations;  
        const metrics = data.metrics;  
        const assignments = data.currentAssignments;  
        const schedule = data.weekSchedule;  
          
        // Build specialization badges  
        const specBadges = specializations.length > 0   
            ? specializations.map(s => `  
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold ${s.is_primary ? 'bg-forest-green text-white' : 'bg-gray-200 text-gray-800'}">  
                    ${s.specialization_name} ${s.is_primary ? '(Primary)' : ''}  
                </span>  
            `).join('')  
            : '<span class="text-gray-500 text-sm">No specializations assigned</span>';  
          
        // Build modal content  
        const modal = document.getElementById('instructor-detail-modal');  
        modal.innerHTML = `  
            <div class="bg-white rounded-lg max-w-4xl w-full max-h-[90vh] overflow-y-auto shadow-2xl animate-fade-in">  
                <!-- Header -->  
                <div class="bg-gradient-to-r from-warm-coral to-golden-yellow p-6 text-white">  
                    <div class="flex items-center justify-between">  
                        <div>  
                            <h2 class="text-2xl font-bold">${instructor.first_name} ${instructor.last_name}</h2>  
                            <p class="text-sm opacity-90 mt-1">Employee ID: ${instructor.employee_id || 'N/A'}</p>  
                        </div>  
                        <button onclick="closeInstructorModal()" class="text-white hover:bg-white hover:bg-opacity-20 rounded-full p-2 transition-all">  
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>  
                        </button>  
                    </div>  
                </div>  
                  
                <!-- Content -->  
                <div class="p-6 space-y-6">  
                      
                    <!-- Personal Information -->  
                    <div class="border-l-4 border-secondary-blue pl-4">  
                        <h3 class="text-lg font-bold text-primary-dark mb-3">Personal Information</h3>  
                        <div class="grid grid-cols-2 gap-4 text-sm">  
                            <div><span class="font-semibold text-gray-700">Email:</span> ${instructor.email}</div>  
                            <div><span class="font-semibold text-gray-700">Phone:</span> ${instructor.phone || 'N/A'}</div>  
                            <div><span class="font-semibold text-gray-700">Gender:</span> ${instructor.gender || 'N/A'}</div>  
                            <div><span class="font-semibold text-gray-700">Nationality:</span> ${instructor.nationality || 'N/A'}</div>  
                        </div>  
                    </div>  
                      
                    <!-- Professional Qualifications -->  
                    <div class="border-l-4 border-forest-green pl-4">  
                        <h3 class="text-lg font-bold text-primary-dark mb-3">Professional Qualifications</h3>  
                        <div class="grid grid-cols-2 gap-4 text-sm">  
                            <div><span class="font-semibold text-gray-700">Education:</span> ${instructor.education_level || 'N/A'}</div>  
                            <div><span class="font-semibold text-gray-700">Music Degree:</span> ${instructor.music_degree || 'N/A'}</div>  
                            <div><span class="font-semibold text-gray-700">Experience:</span> ${instructor.years_of_experience || 0} years</div>  
                            <div><span class="font-semibold text-gray-700">Languages:</span> ${instructor.languages_spoken || 'N/A'}</div>  
                        </div>  
                        ${instructor.certifications ? `<div class="mt-3"><span class="font-semibold text-gray-700">Certifications:</span><p class="text-sm mt-1">${instructor.certifications}</p></div>` : ''}  
                        ${instructor.bio ? `<div class="mt-3"><span class="font-semibold text-gray-700">Bio:</span><p class="text-sm mt-1">${instructor.bio}</p></div>` : ''}  
                    </div>  
                      
                    <!-- Specializations -->  
                    <div class="border-l-4 border-warm-coral pl-4">  
                        <div class="flex items-center justify-between mb-3">  
                            <h3 class="text-lg font-bold text-primary-dark">Specializations</h3>  
                            <button onclick="manageSpecializations(${instructorId})" class="text-sm bg-warm-coral text-white px-4 py-2 rounded-lg hover:bg-warm-coral-dark transition-all">  
                                Manage  
                            </button>  
                        </div>  
                        <div class="flex flex-wrap gap-2">  
                            ${specBadges}  
                        </div>  
                    </div>  
                      
                    <!-- Availability -->  
                    <div class="border-l-4 border-golden-yellow pl-4">  
                        <div class="flex items-center justify-between mb-3">  
                            <h3 class="text-lg font-bold text-primary-dark">Availability</h3>  
                            <button onclick="editAvailability(${instructorId})" class="text-sm bg-golden-yellow text-white px-4 py-2 rounded-lg hover:bg-golden-yellow-dark transition-all">  
                                Edit  
                            </button>  
                        </div>  
                        <div class="grid grid-cols-2 gap-4 text-sm">  
                            <div><span class="font-semibold text-gray-700">Status:</span>   
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold ${instructor.is_available ? 'bg-forest-green text-white' : 'bg-gray-400 text-white'} ml-2">  
                                    ${instructor.is_available ? 'Available' : 'Unavailable'}  
                                </span>  
                            </div>  
                            <div><span class="font-semibold text-gray-700">Max Students/Day:</span> ${instructor.max_students_per_day || 'N/A'}</div>  
                            <div class="col-span-2"><span class="font-semibold text-gray-700">Available Days:</span> ${instructor.available_days || 'N/A'}</div>  
                            <div class="col-span-2"><span class="font-semibold text-gray-700">Preferred Time:</span> ${instructor.preferred_time_slots || 'N/A'}</div>  
                        </div>  
                    </div>  
                      
                    <!-- Performance Metrics -->  
                    <div class="bg-gradient-to-r from-secondary-blue to-primary-dark text-white p-4 rounded-lg">  
                        <h3 class="text-lg font-bold mb-4">Performance Metrics</h3>  
                        <div class="grid grid-cols-3 gap-4 text-center">  
                            <div>  
                                <p class="text-3xl font-bold">${metrics.active_students}</p>  
                                <p class="text-sm opacity-90">Active Students</p>  
                            </div>  
                            <div>  
                                <p class="text-3xl font-bold">${metrics.total_lessons}</p>  
                                <p class="text-sm opacity-90">Total Lessons</p>  
                            </div>  
                            <div>  
                                <p class="text-3xl font-bold">${metrics.attendance_rate}%</p>  
                                <p class="text-sm opacity-90">Attendance Rate</p>  
                            </div>  
                            <div>  
                                <p class="text-3xl font-bold">${metrics.avg_student_rating ? Number(metrics.avg_student_rating).toFixed(1) : 'N/A'}</p>  
                                <p class="text-sm opacity-90">Avg Student Rating</p>  
                            </div>  
                            <div>  
                                <p class="text-3xl font-bold">${metrics.completion_rate}%</p>  
                                <p class="text-sm opacity-90">Completion Rate</p>  
                            </div>  
                            <div>  
                                <p class="text-3xl font-bold">${instructor.total_students_taught}</p>  
                                <p class="text-sm opacity-90">Total Taught</p>  
                            </div>  
                        </div>  
                    </div>  
                      
                    <!-- Current Assignments -->  
                    ${assignments.length > 0 ? `  
                    <div class="border-l-4 border-secondary-blue pl-4">  
                        <h3 class="text-lg font-bold text-primary-dark mb-3">Current Assignments</h3>  
                        <div class="space-y-2">  
                            ${assignments.map(a => `  
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">  
                                    <div>  
                                        <p class="font-semibold text-sm">${a.first_name} ${a.last_name}</p>  
                                        <p class="text-xs text-gray-600">${a.instrument_name}</p>  
                                    </div>  
                                    <span class="text-sm font-semibold text-secondary-blue">${a.remaining_sessions} sessions left</span>  
                                </div>  
                            `).join('')}  
                        </div>  
                    </div>  
                    ` : ''}  
                      
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
 * Close instructor detail modal  
 */  
function closeInstructorModal() {  
    const modal = document.getElementById('instructor-detail-modal');  
    modal.classList.add('hidden');  
    modal.classList.remove('flex');  
}  
  
/**  
 * Manage specializations modal  
 * @param {number} instructorId - The ID of the instructor  
 */  
async function manageSpecializations(instructorId) {  
    try {  
        // Fetch instructor specializations  
        const response = await fetch(`/admin/instructors/${instructorId}`, {  
            headers: { 'Accept': 'application/json' }  
        });  
          
        if (!response.ok) throw new Error('Failed to fetch specializations');  
          
        const data = await response.json();  
        const currentSpecs = data.specializations;  
          
        // Fetch all available specializations  
        const allSpecsRes = await fetch('/admin/instructors', {  
            headers: { 'Accept': 'application/json' }  
        });  
          
        // Build modal (simplified - you'll need all specializations from backend)  
        const modal = document.getElementById('specialization-modal');  
        modal.innerHTML = `  
            <div class="bg-white rounded-lg max-w-2xl w-full p-6 shadow-2xl animate-fade-in">  
                <h3 class="text-xl font-bold text-primary-dark mb-4">Manage Specializations</h3>  
                  
                <!-- Current Specializations -->  
                <div class="mb-6">  
                    <h4 class="font-semibold text-gray-700 mb-3">Current Specializations</h4>  
                    <div class="space-y-2">  
                        ${currentSpecs.length > 0 ? currentSpecs.map(s => `  
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">  
                                <div class="flex items-center gap-2">  
                                    <span class="font-semibold">${s.specialization_name}</span>  
                                    ${s.is_primary ? '<span class="text-xs bg-forest-green text-white px-2 py-1 rounded-full">Primary</span>' : ''}  
                                </div>  
                                <div class="flex gap-2">  
                                    ${!s.is_primary ? `<button onclick="setPrimarySpec(${instructorId}, ${s.specialization_id})" class="text-xs bg-secondary-blue text-white px-3 py-1 rounded hover:bg-secondary-blue-dark">Set Primary</button>` : ''}  
                                    <button onclick="removeSpecialization(${instructorId}, ${s.specialization_id})" class="text-xs bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600">Remove</button>  
                                </div>  
                            </div>  
                        `).join('') : '<p class="text-gray-500 text-sm">No specializations assigned</p>'}  
                    </div>  
                </div>  
                  
                <!-- Add Specialization Form -->  
                <div>  
                    <h4 class="font-semibold text-gray-700 mb-3">Add New Specialization</h4>  
                    <form onsubmit="assignSpecialization(event, ${instructorId})">  
                        <select id="new-spec-select" class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg mb-3">  
                            <option value="">Select specialization...</option>  
                            <!-- These should be populated from backend -->  
                        </select>  
                        <label class="flex items-center gap-2 mb-4">  
                            <input type="checkbox" id="is-primary-checkbox" class="checkbox-custom">  
                            <span class="text-sm">Set as primary specialization</span>  
                        </label>  
                        <div class="flex gap-3">  
                            <button type="submit" class="bg-forest-green text-white px-6 py-2 rounded-lg hover:bg-forest-green-dark">Add</button>  
                            <button type="button" onclick="closeSpecModal()" class="bg-gray-200 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-300">Close</button>  
                        </div>  
                    </form>  
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
 * Assign specialization to instructor  
 */  
async function assignSpecialization(event, instructorId) {  
    event.preventDefault();  
      
    const specializationId = document.getElementById('new-spec-select').value;  
    const isPrimary = document.getElementById('is-primary-checkbox').checked;  
      
    if (!specializationId) {  
        showToast('Please select a specialization', 'error');  
        return;  
    }  
      
    try {  
        const response = await fetch(`/admin/instructors/${instructorId}/specializations`, {  
            method: 'POST',  
            headers: {  
                'Content-Type': 'application/json',  
                'X-CSRF-TOKEN': csrfToken,  
                'Accept': 'application/json'  
            },  
            body: JSON.stringify({  
                specialization_id: specializationId,  
                is_primary: isPrimary  
            })  
        });  
          
        const data = await response.json();  
          
        if (!response.ok) {  
            throw new Error(data.message || 'Failed to assign specialization');  
        }  
          
        showToast(data.message, 'success');  
        closeSpecModal();  
        setTimeout(() => window.location.reload(), 1500);  
          
    } catch (error) {  
        showToast(error.message, 'error');  
    }  
}  
  
/**  
 * Remove specialization from instructor  
 */  
async function removeSpecialization(instructorId, specializationId) {  
    if (!confirm('Remove this specialization?')) return;  
      
    try {  
        const response = await fetch(`/admin/instructors/${instructorId}/specializations/${specializationId}`, {  
            method: 'DELETE',  
            headers: {  
                'X-CSRF-TOKEN': csrfToken,  
                'Accept': 'application/json'  
            }  
        });  
          
        const data = await response.json();  
          
        if (!response.ok) {  
            throw new Error(data.message || 'Failed to remove specialization');  
        }  
          
        showToast(data.message, 'success');  
        setTimeout(() => window.location.reload(), 1500);  
          
    } catch (error) {  
        showToast(error.message, 'error');  
    }  
}  
  
/**  
 * Set specialization as primary  
 */  
async function setPrimarySpec(instructorId, specializationId) {  
    try {  
        const response = await fetch(`/admin/instructors/${instructorId}/specializations/${specializationId}/primary`, {  
            method: 'PUT',  
            headers: {  
                'X-CSRF-TOKEN': csrfToken,  
                'Accept': 'application/json'  
            }  
        });  
          
        const data = await response.json();  
          
        if (!response.ok) {  
            throw new Error(data.message || 'Failed to set primary');  
        }  
          
        showToast(data.message, 'success');  
        setTimeout(() => window.location.reload(), 1500);  
          
    } catch (error) {  
        showToast(error.message, 'error');  
    }  
}  
  
/**  
 * Close specialization modal  
 */  
function closeSpecModal() {  
    const modal = document.getElementById('specialization-modal');  
    modal.classList.add('hidden');  
    modal.classList.remove('flex');  
}  
  
/**  
 * View performance report  
 */  
async function viewPerformance(instructorId) {  
    try {  
        const response = await fetch(`/admin/instructors/${instructorId}/performance`, {  
            headers: { 'Accept': 'application/json' }  
        });  
          
        if (!response.ok) throw new Error('Failed to fetch performance data');  
          
        const report = await response.json();  
          
        const modal = document.getElementById('performance-modal');  
        modal.innerHTML = `  
            <div class="bg-white rounded-lg max-w-4xl w-full max-h-[90vh] overflow-y-auto p-6 shadow-2xl animate-fade-in">  
                <div class="flex items-center justify-between mb-6">  
                    <h2 class="text-2xl font-bold text-primary-dark">Performance Report</h2>  
                    <button onclick="closePerfModal()" class="text-gray-500 hover:text-gray-700">  
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>  
                    </button>  
                </div>  
                  
                <!-- Metrics Grid -->  
                <div class="grid grid-cols-3 gap-4 mb-6">  
                    <div class="p-4 bg-gradient-to-br from-warm-coral to-warm-coral-dark text-white rounded-lg">  
                        <p class="text-3xl font-bold">${report.total_lessons}</p>  
                        <p class="text-sm opacity-90">Total Lessons</p>  
                    </div>  
                    <div class="p-4 bg-gradient-to-br from-forest-green to-forest-green-dark text-white rounded-lg">  
                        <p class="text-3xl font-bold">${report.attendance_rate}%</p>  
                        <p class="text-sm opacity-90">Attendance Rate</p>  
                    </div>  
                    <div class="p-4 bg-gradient-to-br from-golden-yellow to-golden-yellow-dark text-white rounded-lg">  
                        <p class="text-3xl font-bold">${report.avg_student_ratings ? Number(report.avg_student_ratings).toFixed(1) : 'N/A'}</p>  
                        <p class="text-sm opacity-90">Avg Rating</p>  
                    </div>  
                    <div class="p-4 bg-gradient-to-br from-secondary-blue to-secondary-blue-dark text-white rounded-lg">  
                        <p class="text-3xl font-bold">${report.student_retention_rate}%</p>  
                        <p class="text-sm opacity-90">Retention Rate</p>  
                    </div>  
                    <div class="p-4 bg-gradient-to-br from-primary-dark to-primary-darker text-white rounded-lg col-span-2">  
                        <p class="text-3xl font-bold">₱${Number(report.revenue_generated).toLocaleString()}</p>  
                        <p class="text-sm opacity-90">Revenue Generated</p>  
                    </div>  
                </div>  
                  
                <button onclick="closePerfModal()" class="w-full bg-secondary-blue text-white py-3 rounded-lg hover:bg-secondary-blue-dark">Close</button>  
            </div>  
        `;  
          
        modal.classList.remove('hidden');  
        modal.classList.add('flex');  
          
    } catch (error) {  
        showToast(error.message, 'error');  
    }  
}  
  
/**  
 * Close performance modal  
 */  
function closePerfModal() {  
    const modal = document.getElementById('performance-modal');  
    modal.classList.add('hidden');  
    modal.classList.remove('flex');  
}  
  
