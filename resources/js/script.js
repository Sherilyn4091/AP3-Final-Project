/**
 * ============================================================================
 * MAIN JAVASCRIPT FILE
 * Client-side functionality for authentication pages
 * ============================================================================
 */

/**
 * Toggle Password Visibility
 * Switches between showing and hiding password text
 * Used in: Login, All Registration Forms
 */
function togglePassword() {
    // Get the password input element
    const passwordInput = document.getElementById('user_password');
    // Get the eye icon SVG element
    const eyeIcon = document.getElementById('eye-icon');
    
    if (!passwordInput) return; // Safety check
    
    // Toggle between 'password' and 'text' input types
    if (passwordInput.type === 'password') {
        // Show password
        passwordInput.type = 'text';
        
        // Change icon to "eye-off" (crossed eye)
        if (eyeIcon) {
            eyeIcon.innerHTML = `
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                      d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
            `;
        }
    } else {
        // Hide password
        passwordInput.type = 'password';
        
        // Change icon back to regular "eye"
        if (eyeIcon) {
            eyeIcon.innerHTML = `
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                      d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                      d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
            `;
        }
    }
}

/**
 * Form Validation Enhancement
 * Adds real-time validation feedback
 */
document.addEventListener('DOMContentLoaded', function() {
    
    // Get all input fields
    const inputs = document.querySelectorAll('input[required], select[required], textarea[required]');
    
    // Add validation on blur (when user leaves field)
    inputs.forEach(input => {
        input.addEventListener('blur', function() {
            validateField(this);
        });
        
        // Remove error styling on input
        input.addEventListener('input', function() {
            if (this.classList.contains('border-red-500')) {
                this.classList.remove('border-red-500');
                this.classList.add('border-gray-300');
            }
        });
    });
    
    /**
     * Validate individual field
     * @param {HTMLElement} field - The input field to validate
     */
    function validateField(field) {
        // Check if field is empty and required
        if (field.hasAttribute('required') && !field.value.trim()) {
            field.classList.add('border-red-500');
            field.classList.remove('border-gray-300');
            return false;
        }
        
        // Email validation
        if (field.type === 'email' && field.value) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(field.value)) {
                field.classList.add('border-red-500');
                field.classList.remove('border-gray-300');
                return false;
            }
        }
        
        // Phone validation (11 digits for Philippines)
        if (field.name === 'phone' && field.value) {
            const phoneRegex = /^[0-9]{11}$/;
            if (!phoneRegex.test(field.value)) {
                field.classList.add('border-red-500');
                field.classList.remove('border-gray-300');
                return false;
            }
        }
        
        // Field is valid
        field.classList.remove('border-red-500');
        field.classList.add('border-gray-300');
        return true;
    }
    
    /**
     * Password Confirmation Match Check
     * For registration forms
     */
    const passwordConfirm = document.getElementById('user_password_confirmation');
    const password = document.getElementById('user_password');
    
    if (passwordConfirm && password) {
        passwordConfirm.addEventListener('input', function() {
            if (this.value !== password.value) {
                this.classList.add('border-red-500');
                this.classList.remove('border-gray-300');
            } else {
                this.classList.remove('border-red-500');
                this.classList.add('border-gray-300');
            }
        });
    }
    
    /**
     * Phone Number Formatting
     * Only allow numbers, limit to 11 digits
     */
    const phoneInputs = document.querySelectorAll('input[name="phone"], input[name="emergency_contact_phone"]');
    phoneInputs.forEach(input => {
        input.addEventListener('input', function(e) {
            // Remove any non-numeric characters
            this.value = this.value.replace(/\D/g, '');
            
            // Limit to 11 digits
            if (this.value.length > 11) {
                this.value = this.value.slice(0, 11);
            }
        });
    });
    
    /**
     * Auto-dismiss Success/Error Messages
     * After 5 seconds
     */
    const alerts = document.querySelectorAll('[class*="bg-green-50"], [class*="bg-red-50"]');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s ease-out';
            alert.style.opacity = '0';
            setTimeout(() => {
                alert.remove();
            }, 500);
        }, 5000); // 5 seconds
    });
    
    /**
     * Specialization Checkbox Handler (Instructor Registration)
     * Ensure at least one specialization is selected
     */
    const specializationCheckboxes = document.querySelectorAll('input[name="specializations[]"]');
    const primarySpecializationSelect = document.querySelector('select[name="primary_specialization"]');
    
    if (specializationCheckboxes.length > 0 && primarySpecializationSelect) {
        // Update primary specialization dropdown based on checked boxes
        specializationCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                updatePrimarySpecializationOptions();
            });
        });
        
        function updatePrimarySpecializationOptions() {
            // Get all checked specialization values
            const checkedValues = Array.from(specializationCheckboxes)
                .filter(cb => cb.checked)
                .map(cb => cb.value);
            
            // Enable/disable options in primary specialization dropdown
            const options = primarySpecializationSelect.querySelectorAll('option');
            options.forEach(option => {
                if (option.value && !checkedValues.includes(option.value)) {
                    option.disabled = true;
                } else if (option.value) {
                    option.disabled = false;
                }
            });
            
            // If current selection is now disabled, reset to default
            if (primarySpecializationSelect.value && 
                !checkedValues.includes(primarySpecializationSelect.value)) {
                primarySpecializationSelect.value = '';
            }
        }
    }

    // General loading state for other forms (login, etc.)
    document.querySelectorAll('form:not(#instructorForm)').forEach(form => {
        form.addEventListener('submit', function () {
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = `
                    <svg class="animate-spin h-5 w-5 mx-auto" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                `;
            }
        });
    });

    // === INSTRUCTOR MULTI-STEP FORM ===
    const step1 = document.getElementById('step1');
    const step2 = document.getElementById('step2');
    const step3 = document.getElementById('step3');
    const nextBtn1 = document.getElementById('nextStepBtn1');
    const nextBtn2 = document.getElementById('nextStepBtn2');
    const prevBtn2 = document.getElementById('prevStepBtn2');
    const prevBtn3 = document.getElementById('prevStepBtn3');
    const blurOverlay = document.getElementById('blurOverlay');
    const motivationText = document.getElementById('motivationText');

    const motivations = [
        "Almost there! Let's complete your emergency contact...",
        "Great progress! Just one more step to showcase your expertise...",
    ];

    function validateStep(step) {
        // Remove any existing error messages
        const existingError = step.querySelector('.error-message');
        if (existingError) existingError.remove();

        const inputs = step.querySelectorAll('input[required], select[required]');
        let valid = true;
        let emptyFields = [];

        inputs.forEach(input => {
            if (!input.value.trim()) {
                input.classList.add('border-red-500', 'animate-shake');
                setTimeout(() => input.classList.remove('animate-shake'), 500);
                
                // Get field label
                const label = input.closest('div').querySelector('label');
                if (label) {
                    emptyFields.push(label.textContent.replace(' *', ''));
                }
                valid = false;
            } else {
                input.classList.remove('border-red-500');
            }
        });

        if (!valid) {
            const errorDiv = document.createElement('div');
            errorDiv.className = 'error-message';
            errorDiv.innerHTML = `
                <strong>Please fill in all required fields:</strong>
                <ul class="list-disc list-inside mt-2">
                    ${emptyFields.map(field => `<li>${field}</li>`).join('')}
                </ul>
            `;
            step.querySelector('.grid').insertAdjacentElement('afterend', errorDiv);
            
            // Scroll to error
            errorDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        return valid;
    }

    function showMotivation(index) {
        motivationText.textContent = motivations[index];
        blurOverlay.classList.add('active');
        setTimeout(() => blurOverlay.classList.remove('active'), 2000);
    }

    if (nextBtn1 && nextBtn2 && prevBtn2 && prevBtn3) {
        nextBtn1.addEventListener('click', () => {
            if (!validateStep(step1)) return;
            showMotivation(0);
            setTimeout(() => {
                step1.classList.remove('active');
                step1.classList.add('swipe-left');
                step2.classList.remove('hidden');
                step2.classList.add('active', 'swipe-right');
                document.querySelectorAll('.step-item')[1].classList.add('active');
            }, 500);
        });

        nextBtn2.addEventListener('click', () => {
            if (!validateStep(step2)) return;
            showMotivation(1);
            setTimeout(() => {
                step2.classList.remove('active', 'swipe-right');
                step2.classList.add('swipe-left');
                step3.classList.remove('hidden');
                step3.classList.add('active', 'swipe-right');
                document.querySelectorAll('.step-item')[2].classList.add('active');
            }, 500);
        });

        prevBtn2.addEventListener('click', () => {
            step2.classList.remove('active', 'swipe-right');
            step2.classList.add('swipe-right-back');
            step1.classList.remove('swipe-left');
            step1.classList.add('active');
            setTimeout(() => {
                step2.classList.add('hidden');
                step2.classList.remove('swipe-right-back');
            }, 600);
            document.querySelectorAll('.step-item')[1].classList.remove('active');
        });

        prevBtn3.addEventListener('click', () => {
            step3.classList.remove('active', 'swipe-right');
            step3.classList.add('swipe-right-back');
            step2.classList.remove('swipe-left');
            step2.classList.add('active');
            setTimeout(() => {
                step3.classList.add('hidden');
                step3.classList.remove('swipe-right-back');
            }, 600);
            document.querySelectorAll('.step-item')[2].classList.remove('active');
        });
    }

    // Age calculation
    const dobInput = document.getElementById('date_of_birth');
    const ageDisplay = document.getElementById('age-display');
    if (dobInput && ageDisplay) {
        dobInput.addEventListener('change', function () {
            const birth = new Date(this.value);
            const today = new Date();
            let age = today.getFullYear() - birth.getFullYear();
            const m = today.getMonth() - birth.getMonth();
            if (m < 0 || (m === 0 && today.getDate() < birth.getDate())) age--;
            ageDisplay.textContent = age >= 0 ? age + ' years old' : '—';
        });
    }

    // Capitalize names/address/languages
    document.querySelectorAll('.capitalize-words').forEach(el => {
        el.addEventListener('input', function () {
            this.value = this.value.toLowerCase().replace(/\b\w/g, c => c.toUpperCase());
        });
    });

    // Email lowercase
    const emailInput = document.querySelector('.lowercase-email');
    if (emailInput) {
        emailInput.addEventListener('input', function () {
            this.value = this.value.toLowerCase();
        });
    }

    // Dynamic certifications
    const certContainer = document.getElementById('certifications-container');
    const addCertBtn = document.getElementById('add-cert-btn');

    if (addCertBtn && certContainer) {
        addCertBtn.addEventListener('click', () => {
            const div = document.createElement('div');
            div.className = 'flex gap-4 mb-4';
            div.innerHTML = `
                <input type="text" name="certifications[]" class="input-field flex-1" placeholder="Certificate name">
                <button type="button" class="text-red-600 hover:text-red-800 font-medium">Remove</button>
            `;
            certContainer.appendChild(div);
            div.querySelector('button').addEventListener('click', () => div.remove());
        });
    }

    // === FINAL FORM SUBMISSION (Instructor Registration) ===
    const instructorForm = document.getElementById('instructorForm');

    if (instructorForm) {
        instructorForm.addEventListener('submit', function (e) {
            // Clean empty certification fields before submit
            document.querySelectorAll('#certifications-container > div').forEach(div => {
                const input = div.querySelector('input');
                if (input && !input.value.trim()) {
                    div.remove();
                }
            });

            // Optional: Client-side check for specializations (prevents empty submit)
            const checkedSpecs = document.querySelectorAll('input[name="specializations[]"]:checked');
            if (checkedSpecs.length === 0) {
                alert('Please select at least one specialization.');
                e.preventDefault();
                return;
            }

            const primarySpec = document.querySelector('select[name="primary_specialization"]');
            if (primarySpec && !primarySpec.value) {
                alert('Please select your primary specialization.');
                e.preventDefault();
                return;
            }

            // Show loading spinner on submit button
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = `
                    <svg class="animate-spin h-5 w-5 mx-auto" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                `;
                // Re-enable after timeout (fallback)
                setTimeout(() => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }, 10000);
            }

            // Form submits normally — Laravel handles full validation
        });
    }
        
}); // End of DOMContentLoaded

/**
 * Smooth Scroll Behavior
 * For anchor links
 */
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});

/**
 * Keyboard Navigation Enhancement
 * Allow Enter key to submit forms
 */
document.addEventListener('keypress', function(e) {
    if (e.key === 'Enter' && !e.shiftKey) {
        const form = e.target.closest('form');
        if (form && e.target.tagName !== 'TEXTAREA' && e.target.tagName !== 'BUTTON') {
            e.preventDefault();
            // Don't submit on Enter in regular inputs - let the form submit naturally
        }
    }
});

/**
 * Console Welcome Message
 * For developers
 */
console.log('%cMusic Lab Management System', 'font-size: 20px; font-weight: bold; color: #272829;');
console.log('%cDeveloped with Laravel & Tailwind CSS', 'font-size: 12px; color: #61677A;');

/**
 * Export functions for use in inline scripts if needed
 */
if (typeof window !== 'undefined') {
    window.musicLab = {
        togglePassword,
    };
}