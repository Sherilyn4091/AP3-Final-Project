/**
 * ============================================================================
 * USER CREATION FORM JAVASCRIPT
 * resources/js/admin-pages/user-create.js
 * ============================================================================
 * This script handles all interactive functionality for the user creation page,
 * including:
 * - Role-based form visibility (Student/Instructor)
 * - Password generation with visibility toggle
 * - Auto-formatting (names capitalize, emails lowercase)
 * - Real-time email validation
 * - Phone number validation (11 digits)
 * - Specialization modal for instructors
 * - Form validation before submission
 * ============================================================================
 */

document.addEventListener('DOMContentLoaded', function() {

     window.generateNewPassword = function(fieldId) {
        const field = document.getElementById(fieldId);
        if (field) {
            field.value = generateStrongPassword();
        }
    };
    
    // ============================================================================
    // INITIAL PASSWORD GENERATION
    // ============================================================================
    // Generate passwords for both forms on page load
    generateNewPassword('student_password');
    generateNewPassword('instructor_password');

    // ============================================================================
    // PASSWORD GENERATION FUNCTIONS
    // ============================================================================

    /**
     * Generate a strong random password meeting all requirements:
     * - Minimum 8 characters
     * - At least 1 uppercase letter
     * - At least 1 lowercase letter
     * - At least 1 number
     * - At least 1 special character
     */
    function generateStrongPassword() {
        const uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        const lowercase = 'abcdefghijklmnopqrstuvwxyz';
        const numbers = '0123456789';
        const symbols = '!@#$%^&*()_+-=[]{}|;:,.<>?';
        
        let password = '';
        
        // Ensure at least one of each type (requirement for Laravel validation)
        password += uppercase[Math.floor(Math.random() * uppercase.length)];
        password += lowercase[Math.floor(Math.random() * lowercase.length)];
        password += numbers[Math.floor(Math.random() * numbers.length)];
        password += symbols[Math.floor(Math.random() * symbols.length)];
        
        // Fill the rest randomly to reach 12 characters total
        const allChars = uppercase + lowercase + numbers + symbols;
        for (let i = password.length; i < 12; i++) {
            password += allChars[Math.floor(Math.random() * allChars.length)];
        }
        
        // Shuffle the password to randomize character positions
        return password.split('').sort(() => Math.random() - 0.5).join('');
    }


    /**
     * Toggle password visibility (show/hide password text)
     * @param {string} fieldId - The ID of the password input field
     * @param {string} eyeOpenId - The ID of the "eye open" SVG icon
     * @param {string} eyeClosedId - The ID of the "eye closed" SVG icon
     */
    window.togglePasswordVisibility = function(fieldId, eyeOpenId, eyeClosedId) {
        const field = document.getElementById(fieldId);
        const eyeOpen = document.getElementById(eyeOpenId);
        const eyeClosed = document.getElementById(eyeClosedId);
        
        if (field && eyeOpen && eyeClosed) {
            if (field.type === 'text') {
                // Currently showing password, switch to hidden
                field.type = 'password';
                eyeOpen.classList.remove('hidden');
                eyeClosed.classList.add('hidden');
            } else {
                // Currently hiding password, switch to visible
                field.type = 'text';
                eyeOpen.classList.add('hidden');
                eyeClosed.classList.remove('hidden');
            }
        }
    };

    // ============================================================================
    // AUTO-FORMATTING: CAPITALIZE NAMES
    // ============================================================================
    const firstNameInputs = document.querySelectorAll('#student_first_name, #instructor_first_name');
    const lastNameInputs = document.querySelectorAll('#student_last_name, #instructor_last_name');

    /**
     * Capitalize the first letter of each word in a name input
     * Example: "john doe" → "John Doe"
     */
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

    // Apply capitalization to all name fields
    firstNameInputs.forEach(input => capitalizeName(input));
    lastNameInputs.forEach(input => capitalizeName(input));

    // ============================================================================
    // AUTO-FORMATTING: LOWERCASE EMAIL + REAL-TIME VALIDATION
    // ============================================================================
    const emailInputs = document.querySelectorAll('#student_email, #instructor_email');

    emailInputs.forEach(emailInput => {
        // Get the corresponding validation message element ID
        const validationMsgId = emailInput.id.replace('email', 'email-validation-msg');
        
        emailInput.addEventListener('input', function(e) {
            // Convert to lowercase automatically
            this.value = this.value.toLowerCase();
            // Validate email format in real-time
            validateEmail(this.value, validationMsgId);
        });
    });

    /**
     * Validate email format and display feedback message
     * @param {string} email - The email address to validate
     * @param {string} msgId - The ID of the validation message element
     */
    function validateEmail(email, msgId) {
        const emailRegex = /^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$/;
        const validationMsg = document.getElementById(msgId);
        
        if (!validationMsg) return;
        
        // Don't show validation for empty field
        if (email.length === 0) {
            validationMsg.classList.add('hidden');
            return;
        }
        
        // Show success or error message based on validation
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

    // ============================================================================
    // ROLE-BASED FIELD VISIBILITY (Student vs Instructor)
    // ============================================================================
    const roleSelect = document.getElementById('role');
    const studentForm = document.getElementById('student-form');
    const instructorForm = document.getElementById('instructor-form');

    if (roleSelect && studentForm && instructorForm) {
        roleSelect.addEventListener('change', function() {
            const selectedRole = this.value;
            
            if (selectedRole === 'student') {
                // Show student form, hide instructor form
                studentForm.classList.remove('hidden');
                instructorForm.classList.add('hidden');
                
                // Enable student fields, disable instructor fields
                document.querySelectorAll('.student-field').forEach(field => {
                    field.disabled = false;
                    if (field.hasAttribute('required')) field.setAttribute('required', 'required');
                });
                document.querySelectorAll('.instructor-field').forEach(field => {
                    field.disabled = true;
                    field.removeAttribute('required');
                });
                
            } else if (selectedRole === 'instructor') {
                // Show instructor form, hide student form
                instructorForm.classList.remove('hidden');
                studentForm.classList.add('hidden');
                
                // Enable instructor fields, disable student fields
                document.querySelectorAll('.instructor-field').forEach(field => {
                    field.disabled = false;
                    if (field.hasAttribute('required')) field.setAttribute('required', 'required');
                });
                document.querySelectorAll('.student-field').forEach(field => {
                    field.disabled = true;
                    field.removeAttribute('required');
                });
            } else {
                // No role selected - hide both forms
                studentForm.classList.add('hidden');
                instructorForm.classList.add('hidden');
            }
        });
    }

    // ============================================================================
    // SPECIALIZATION MODAL FUNCTIONALITY (Instructor only)
    // ============================================================================
    const modal = document.getElementById('specialization-modal');
    const assignSpecBtn = document.getElementById('assign-spec-btn');
    const closeModalBtns = document.querySelectorAll('.close-spec-modal, #close-spec-modal-btn');
    const saveSpecBtn = document.getElementById('save-spec-modal-btn');
    const specializationPills = document.getElementById('specialization-pills');
    const specializationCheckboxes = document.querySelectorAll('.specialization-checkbox');
    const primarySpecSelect = document.getElementById('primary_specialization');

    // Open modal when "Assign specializations" button is clicked
    if (assignSpecBtn && modal) {
        assignSpecBtn.addEventListener('click', function() {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        });
    }

    // Close modal when close buttons are clicked
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

    // Close modal when clicking outside the modal content
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }
        });
    }

    // Save specializations and close modal
    if (saveSpecBtn) {
        saveSpecBtn.addEventListener('click', function() {
            updateSpecializationPills();
            updatePrimarySpecializationOptions();
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        });
    }

    /**
     * Update the visual "pills" showing selected specializations
     */
    function updateSpecializationPills() {
        if (!specializationCheckboxes || !specializationPills) return;
        
        // Get all checked specializations
        const selectedSpecs = Array.from(specializationCheckboxes)
            .filter(cb => cb.checked)
            .map(cb => ({
                id: cb.value,
                name: cb.parentElement.querySelector('span').textContent
            }));

        // If no specializations selected, show placeholder
        if (selectedSpecs.length === 0) {
            specializationPills.innerHTML = '<p class="text-sm text-gray-500">No specializations assigned yet.</p>';
            return;
        }

        // Display selected specializations as pills
        specializationPills.innerHTML = selectedSpecs.map(spec => `
            <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium bg-forest-green text-white">
                ${spec.name}
            </span>
        `).join('');
    }

    /**
     * Update primary specialization dropdown to only show selected specializations
     */
    function updatePrimarySpecializationOptions() {
        if (!specializationCheckboxes || !primarySpecSelect) return;
        
        // Get IDs of all selected specializations
        const selectedSpecIds = Array.from(specializationCheckboxes)
            .filter(cb => cb.checked)
            .map(cb => cb.value);

        // Show/hide options in primary specialization dropdown
        Array.from(primarySpecSelect.options).forEach(option => {
            if (option.value && !selectedSpecIds.includes(option.value)) {
                // Hide options that are not selected
                option.disabled = true;
                option.style.display = 'none';
            } else {
                // Show selected options
                option.disabled = false;
                option.style.display = 'block';
            }
        });
    }

    // ============================================================================
    // PHONE NUMBER VALIDATION (11 digits only, numeric)
    // ============================================================================
    const phoneInputs = document.querySelectorAll('#student_phone, #instructor_phone');
    phoneInputs.forEach(phoneInput => {
        phoneInput.addEventListener('input', function(e) {
            // Remove all non-numeric characters
            this.value = this.value.replace(/\D/g, '');
            // Limit to 11 digits (Philippine mobile format)
            if (this.value.length > 11) {
                this.value = this.value.slice(0, 11);
            }
        });
    });

    // ============================================================================
    // FORM VALIDATION BEFORE SUBMISSION
    // ============================================================================
    const form = document.getElementById('createUserForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            const role = roleSelect.value;
            const emailRegex = /^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$/;
            
            // Get the active email input based on selected role
            let activeEmailInput;
            if (role === 'student') {
                activeEmailInput = document.getElementById('student_email');
            } else if (role === 'instructor') {
                activeEmailInput = document.getElementById('instructor_email');
            }
            
            // Validate email format before submission
            if (activeEmailInput && !emailRegex.test(activeEmailInput.value)) {
                e.preventDefault(); // Stop form submission
                activeEmailInput.focus();
                // Use showToast from admin-pages.js
                if (typeof showToast === 'function') {
                    showToast('Please enter a valid email address.', 'error');
                }
                return false;
            }

            // Validate student status if student role is selected
            if (role === 'student') {
                const statusSelect = document.getElementById('student_status_id');
                if (!statusSelect.value) {
                    e.preventDefault(); // Stop form submission
                    statusSelect.focus();
                    // Use showToast from admin-pages.js
                    if (typeof showToast === 'function') {
                        showToast('Please select a student status.', 'error');
                    }
                    return false;
                }
            }

            // All validations passed - allow form submission
            return true;
        });
    }
});