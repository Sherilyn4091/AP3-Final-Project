/**
 * ============================================================================
 * INSTRUCTOR MANAGEMENT JAVASCRIPT
 * resources/js/admin-pages/instructor.js
 * ============================================================================
 * Handles all instructor-specific operations:
 * - View instructor details
 * - Manage specializations
 * - Update availability
 * - View performance reports
 * ============================================================================
 */

document.addEventListener('DOMContentLoaded', function() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

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
    window.editAvailability = editAvailability;
    window.updateAvailability = updateAvailability;

    /**
     * View instructor details in modal
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
                    <div class="bg-gradient-to-r from-warm-coral to-golden-yellow p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="text-2xl font-bold text-gray-900">${instructor.first_name} ${instructor.last_name}</h2>
                                <p class="text-sm text-gray-700 mt-1">Employee ID: ${instructor.employee_id || 'N/A'}</p>
                            </div>
                            <button onclick="closeInstructorModal()" class="text-gray-900 hover:bg-gray-900 hover:bg-opacity-20 rounded-full p-2 transition-all">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="p-6 space-y-6">

                        <!-- Personal Information -->
                        <div class="border-l-4 border-secondary-blue pl-4">
                            <h3 class="text-lg font-bold text-primary-dark mb-3">Personal information</h3>
                            <div class="grid grid-cols-2 gap-4 text-sm">
                                <div><span class="font-semibold text-gray-700">Email:</span> ${instructor.email || 'N/A'}</div>
                                <div><span class="font-semibold text-gray-700">Phone:</span> ${instructor.phone || 'N/A'}</div>
                                <div><span class="font-semibold text-gray-700">Gender:</span> ${instructor.gender || 'N/A'}</div>
                                <div><span class="font-semibold text-gray-700">Nationality:</span> ${instructor.nationality || 'N/A'}</div>
                            </div>
                        </div>

                        <!-- Professional Qualifications -->
                        <div class="border-l-4 border-forest-green pl-4">
                            <h3 class="text-lg font-bold text-primary-dark mb-3">Professional qualifications</h3>
                            <div class="grid grid-cols-2 gap-4 text-sm">
                                <div><span class="font-semibold text-gray-700">Education:</span> ${instructor.education_level || 'N/A'}</div>
                                <div><span class="font-semibold text-gray-700">Music degree:</span> ${instructor.music_degree || 'N/A'}</div>
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
                                <div><span class="font-semibold text-gray-700">Max students/day:</span> ${instructor.max_students_per_day || 'N/A'}</div>
                                <div class="col-span-2"><span class="font-semibold text-gray-700">Available days:</span> ${instructor.available_days || 'N/A'}</div>
                                <div class="col-span-2"><span class="font-semibold text-gray-700">Preferred time:</span> ${instructor.preferred_time_slots || 'N/A'}</div>
                            </div>
                        </div>

                        <!-- Current Assignments -->
                        ${assignments.length > 0 ? `
                        <div class="border-l-4 border-secondary-blue pl-4">
                            <h3 class="text-lg font-bold text-primary-dark mb-3">Current assignments</h3>
                            <div class="space-y-2">
                                ${assignments.map(a => `
                                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                        <div>
                                            <p class="font-semibold text-sm">${a.first_name} ${a.last_name}</p>
                                            <p class="text-xs text-gray-600">${a.instrument_name || 'N/A'}</p>
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
     * Manage specializations modal
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
            const allSpecsRes = await fetch('/admin/specializations', {
                headers: { 'Accept': 'application/json' }
            });

            const allSpecsData = await allSpecsRes.json();
            const allSpecs = allSpecsData.specializations || [];

            // Filter out already assigned specializations
            const availableSpecs = allSpecs.filter(spec =>
                !currentSpecs.some(curr => curr.specialization_id === spec.specialization_id)
            );

            // Build modal
            const modal = document.getElementById('specialization-modal');
            modal.innerHTML = `
                <div class="bg-white rounded-lg max-w-2xl w-full p-6 shadow-2xl animate-fade-in">
                    <h3 class="text-xl font-bold text-primary-dark mb-4">Manage specializations</h3>

                    <!-- Current Specializations -->
                    <div class="mb-6">
                        <h4 class="font-semibold text-gray-700 mb-3">Current specializations</h4>
                        <div class="space-y-2">
                            ${currentSpecs.length > 0 ? currentSpecs.map(s => `
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                    <div class="flex items-center gap-2">
                                        <span class="font-semibold">${s.specialization_name}</span>
                                        ${s.is_primary ? '<span class="text-xs bg-forest-green text-white px-2 py-1 rounded-full">Primary</span>' : ''}
                                    </div>
                                    <div class="flex gap-2">
                                        ${!s.is_primary ? `<button onclick="setPrimarySpec(${instructorId}, ${s.specialization_id})" class="text-xs bg-secondary-blue text-white px-3 py-1 rounded hover:bg-secondary-blue-dark">Set primary</button>` : ''}
                                        <button onclick="removeSpecialization(${instructorId}, ${s.specialization_id})" class="text-xs bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600">Remove</button>
                                    </div>
                                </div>
                            `).join('') : '<p class="text-gray-500 text-sm">No specializations assigned</p>'}
                        </div>
                    </div>

                    <!-- Add Specialization Form -->
                    ${availableSpecs.length > 0 ? `
                    <div>
                        <h4 class="font-semibold text-gray-700 mb-3">Add new specialization</h4>
                        <form onsubmit="assignSpecialization(event, ${instructorId})">
                            <select id="new-spec-select" class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg mb-3">
                                <option value="">Select specialization...</option>
                                ${availableSpecs.map(spec => `
                                    <option value="${spec.specialization_id}">${spec.specialization_name}</option>
                                `).join('')}
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
                    ` : '<p class="text-sm text-gray-500">All available specializations are assigned.</p><button type="button" onclick="closeSpecModal()" class="bg-gray-200 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-300 mt-4">Close</button>'}
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
     * Edit instructor availability
     */
    async function editAvailability(instructorId) {
        try {
            const response = await fetch(`/admin/instructors/${instructorId}`, {
                headers: { 'Accept': 'application/json' }
            });

            const data = await response.json();
            const instructor = data.instructor;

            const modal = document.getElementById('instructor-detail-modal');
            modal.innerHTML = `
                <div class="bg-white rounded-lg max-w-2xl w-full p-6 shadow-2xl animate-fade-in">
                    <h3 class="text-xl font-bold text-primary-dark mb-4">Edit availability</h3>

                    <form onsubmit="updateAvailability(event, ${instructorId})">
                        <!-- Availability Toggle -->
                        <div class="mb-4">
                            <label class="flex items-center gap-3">
                                <input type="checkbox" id="is-available" class="checkbox-custom" ${instructor.is_available ? 'checked' : ''}>
                                <span class="font-semibold text-gray-700">Currently available for teaching</span>
                            </label>
                        </div>

                        <!-- Available Days -->
                        <div class="mb-4">
                            <label class="block font-semibold text-gray-700 mb-2">Available days</label>
                            <input type="text" id="available-days" value="${instructor.available_days || ''}"
                                   placeholder="e.g., Monday, Wednesday, Friday"
                                   class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-secondary-blue">
                        </div>

                        <!-- Preferred Time Slots -->
                        <div class="mb-4">
                            <label class="block font-semibold text-gray-700 mb-2">Preferred time slots</label>
                            <input type="text" id="preferred-time" value="${instructor.preferred_time_slots || ''}"
                                   placeholder="e.g., 9:00 AM - 12:00 PM, 2:00 PM - 5:00 PM"
                                   class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-secondary-blue">
                        </div>

                        <!-- Max Students Per Day -->
                        <div class="mb-6">
                            <label class="block font-semibold text-gray-700 mb-2">Max students per day</label>
                            <input type="number" id="max-students" value="${instructor.max_students_per_day || ''}"
                                   min="1" max="20"
                                   class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-secondary-blue">
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex gap-3">
                            <button type="submit" class="bg-forest-green text-white px-6 py-2 rounded-lg hover:bg-forest-green-dark flex-1">
                                Save changes
                            </button>
                            <button type="button" onclick="closeInstructorModal()" class="bg-gray-200 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-300 flex-1">
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
     * Update instructor availability
     */
    async function updateAvailability(event, instructorId) {
        event.preventDefault();

        const isAvailable = document.getElementById('is-available').checked;
        const availableDays = document.getElementById('available-days').value;
        const preferredTime = document.getElementById('preferred-time').value;
        const maxStudents = document.getElementById('max-students').value;

        try {
            const response = await fetch(`/admin/instructors/${instructorId}/availability`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    is_available: Boolean(isAvailable), // ← FIXED: Force boolean type for PostgreSQL
                    available_days: availableDays,
                    preferred_time_slots: preferredTime,
                    max_students_per_day: maxStudents ? parseInt(maxStudents) : null
                })
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'Failed to update availability');
            }

            showToast(data.message, 'success');
            closeInstructorModal();
            setTimeout(() => window.location.reload(), 1500);

        } catch (error) {
            showToast(error.message, 'error');
        }
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
                        <h2 class="text-2xl font-bold text-primary-dark">Performance report</h2>
                        <button onclick="closePerfModal()" class="text-gray-500 hover:text-gray-700">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <!-- Metrics Grid -->
                    <div class="grid grid-cols-3 gap-4 mb-6">
                        <div class="p-4 bg-gradient-to-br from-warm-coral to-warm-coral-dark text-white rounded-lg">
                            <p class="text-3xl font-bold">${report.total_lessons}</p>
                            <p class="text-sm opacity-90">Total lessons</p>
                        </div>
                        <div class="p-4 bg-gradient-to-br from-forest-green to-forest-green-dark text-white rounded-lg">
                            <p class="text-3xl font-bold">${report.attendance_rate}%</p>
                            <p class="text-sm opacity-90">Attendance rate</p>
                        </div>
                        <div class="p-4 bg-gradient-to-br from-golden-yellow to-golden-yellow-dark text-white rounded-lg">
                            <p class="text-3xl font-bold">${report.avg_student_rating ? Number(report.avg_student_rating).toFixed(1) : 'N/A'}</p>
                            <p class="text-sm opacity-90">Avg rating</p>
                        </div>
                        <div class="p-4 bg-gradient-to-br from-secondary-blue to-secondary-blue-dark text-white rounded-lg">
                            <p class="text-3xl font-bold">${report.student_retention_rate}%</p>
                            <p class="text-sm opacity-90">Retention rate</p>
                        </div>
                        <div class="p-4 bg-gradient-to-br from-primary-dark to-primary-darker text-white rounded-lg col-span-2">
                            <p class="text-3xl font-bold">₱${Number(report.revenue_generated).toLocaleString()}</p>
                            <p class="text-sm opacity-90">Revenue generated</p>
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
     * Close modals
     */
    function closeInstructorModal() {
        const modal = document.getElementById('instructor-detail-modal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function closeSpecModal() {
        const modal = document.getElementById('specialization-modal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function closePerfModal() {
        const modal = document.getElementById('performance-modal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    /**
     * Toast notification helper
     */
    function showToast(message, type = 'success') {
        const container = document.getElementById('toast-container');
        if (!container) return;

        const toast = document.createElement('div');

        let bgColor, icon;
        if (type === 'success') {
            bgColor = 'bg-forest-green';
            icon = `<svg class="w-6 h-6 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>`;
        } else {
            bgColor = 'bg-warm-coral';
            icon = `<svg class="w-6 h-6 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>`;
        }

        toast.className = `flex items-center justify-between p-4 rounded-lg shadow-lg text-white ${bgColor} animate-fade-in-up min-w-[300px]`;
        toast.innerHTML = `
            <div class="flex items-center">
                ${icon}
                <span class="font-semibold">${message}</span>
            </div>
            <button onclick="this.parentElement.remove()" class="ml-4 text-gray-900 hover:text-gray-700 flex-shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        `;

        container.appendChild(toast);

        setTimeout(() => {
            toast.classList.add('animate-fade-out');
            toast.addEventListener('animationend', () => toast.remove());
        }, 5000);
    }
});