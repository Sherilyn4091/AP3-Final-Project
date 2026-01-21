/**  
 * ============================================================================  
 * STUDENT MANAGEMENT PAGE - JavaScript Functions
 * resources/js/admin-pages/student.js
 * ============================================================================  
 * This script handles all interactive functionality for student management:
 * - View student details with tabbed modal
 * - Edit student information
 * - Bulk actions (status updates)
 * - Toggle select all
 * - Toast notifications with close button
 * - Responsive design optimizations
 * ============================================================================  
 */

// ============================================================================
// CSRF TOKEN - Required for all POST/PUT/DELETE requests
// ============================================================================
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

// ============================================================================
// GLOBAL SCOPE EXPORTS - Make functions accessible to inline onclick handlers
// ============================================================================
window.viewStudent = viewStudent;
window.editStudent = editStudent;
window.saveStudent = saveStudent;
window.cancelEditStudent = cancelEditStudent;
window.toggleSelectAll = toggleSelectAll;
window.updateBulkActions = updateBulkActions;
window.clearSelection = clearSelection;
window.bulkUpdateStatus = bulkUpdateStatus;
window.closeStudentModal = closeStudentModal;
window.closeBulkModal = closeBulkModal;
window.showTab = showTab;
window.loadAttendanceDetails = loadAttendanceDetails;
window.loadAllProgress = loadAllProgress;
window.executeBulkStatusUpdate = executeBulkStatusUpdate;

// ============================================================================
// VIEW STUDENT DETAILS MODAL
// ============================================================================
/**
 * Fetch and display student details in a modal with multiple tabs
 * @param {number} studentId - The ID of the student
 */
async function viewStudent(studentId) {
    try {
        const response = await fetch(`/admin/students/${studentId}`, {
            headers: { 'Accept': 'application/json' }
        });
        
        if (!response.ok) throw new Error('Failed to fetch student details');
        
        const data = await response.json();
        const student = data.student;
        
        // Build modal with tabs
        const modal = document.getElementById('student-detail-modal');
        modal.innerHTML = `
            <div class="bg-white rounded-lg max-w-5xl w-full max-h-[90vh] overflow-hidden shadow-2xl animate-fade-in">
            <!-- Header -->
            <div class="bg-gradient-to-r from-secondary-blue to-forest-green p-4 md:p-6 text-white">
                <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl md:text-2xl font-bold">${student.first_name} ${student.last_name}</h2>
                    <p class="text-xs md:text-sm opacity-90 mt-1">Student ID: ${student.student_id} | ${student.status_name}</p>
                </div>
                <button onclick="closeStudentModal()" class="text-gray-700 hover:bg-gray-200 rounded-full p-2 transition-all">
                    <svg class="w-5 h-5 md:w-6 md:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
                </div>
            </div>
            
            <!-- Tabs -->
            <div class="border-b border-gray-200 bg-gray-50 overflow-x-auto">
                <nav class="flex whitespace-nowrap">
                <button onclick="showTab('personal')" class="student-tab active px-3 md:px-6 py-2 md:py-3 text-xs md:text-sm font-semibold border-b-2 border-secondary-blue text-secondary-blue">Personal</button>
                <button onclick="showTab('enrollment')" class="student-tab px-3 md:px-6 py-2 md:py-3 text-xs md:text-sm font-semibold text-gray-600 hover:text-secondary-blue">Enrollments</button>
                <button onclick="showTab('schedule')" class="student-tab px-3 md:px-6 py-2 md:py-3 text-xs md:text-sm font-semibold text-gray-600 hover:text-secondary-blue">Schedule</button>
                <button onclick="showTab('attendance')" class="student-tab px-3 md:px-6 py-2 md:py-3 text-xs md:text-sm font-semibold text-gray-600 hover:text-secondary-blue">Attendance</button>
                <button onclick="showTab('progress')" class="student-tab px-3 md:px-6 py-2 md:py-3 text-xs md:text-sm font-semibold text-gray-600 hover:text-secondary-blue">Progress</button>
                <button onclick="showTab('payments')" class="student-tab px-3 md:px-6 py-2 md:py-3 text-xs md:text-sm font-semibold text-gray-600 hover:text-secondary-blue">Payments</button>
                </nav>
            </div>
            
            <!-- Tab Content -->
            <div class="p-4 md:p-6 overflow-y-auto" style="max-height: calc(90vh - 180px);">
                ${buildTabContent(student, data)}
            </div>
            </div>
        `;
        
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        
    } catch (error) {
        showToast(error.message, 'error');
    }
}

// ============================================================================
// BUILD TAB CONTENT
// ============================================================================
/**
 * Build all tab content panels for the student modal
 * @param {object} student - Student data
 * @param {object} data - Additional data (enrollments, schedule, etc.)
 * @returns {string} HTML string for all tabs
 */
function buildTabContent(student, data) {
    return `
        <!-- Personal Info Tab -->
        <div id="tab-personal" class="tab-content">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
                <div>
                    <h3 class="text-base md:text-lg font-bold text-primary-dark mb-3 flex items-center justify-between">
                        Contact information
                        <button onclick="editStudent(${student.student_id})" class="text-xs md:text-sm bg-secondary-blue text-white px-2 md:px-3 py-1 rounded hover:bg-secondary-blue-dark">Edit</button>
                    </h3>
                    <div class="space-y-2 text-xs md:text-sm">
                        <p><span class="font-semibold">Email:</span> ${student.email || 'N/A'}</p>
                        <p><span class="font-semibold">Phone:</span> ${student.phone || 'N/A'}</p>
                        <p><span class="font-semibold">Address:</span> ${student.address_line1 || 'N/A'}</p>
                        <p><span class="font-semibold">City:</span> ${student.city || 'N/A'}, ${student.province || 'N/A'}</p>
                    </div>
                </div>
                
                <div>
                    <h3 class="text-base md:text-lg font-bold text-primary-dark mb-3">Personal details</h3>
                    <div class="space-y-2 text-xs md:text-sm">
                        <p><span class="font-semibold">Date of birth:</span> ${student.date_of_birth || 'N/A'}</p>
                        <p><span class="font-semibold">Gender:</span> ${student.gender || 'N/A'}</p>
                        <p><span class="font-semibold">Nationality:</span> ${student.nationality || 'N/A'}</p>
                        <p><span class="font-semibold">Enrollment date:</span> ${student.enrollment_date ? new Date(student.enrollment_date).toLocaleDateString() : 'N/A'}</p>
                    </div>
                </div>
                
                <div>
                    <h3 class="text-base md:text-lg font-bold text-primary-dark mb-3">Musical background</h3>
                    <div class="space-y-2 text-xs md:text-sm">
                        <p><span class="font-semibold">Primary instrument:</span> ${student.instrument_name || 'None'}</p>
                        <p><span class="font-semibold">Preferred genre:</span> ${student.genre_name || 'No preference'}</p>
                    </div>
                </div>
                
                ${student.parent_guardian_name ? `
                <div>
                    <h3 class="text-base md:text-lg font-bold text-primary-dark mb-3">Guardian information</h3>
                    <div class="space-y-2 text-xs md:text-sm">
                        <p><span class="font-semibold">Name:</span> ${student.parent_guardian_name}</p>
                        <p><span class="font-semibold">Relationship:</span> ${student.parent_guardian_relationship || 'N/A'}</p>
                        <p><span class="font-semibold">Phone:</span> ${student.parent_guardian_phone || 'N/A'}</p>
                        <p><span class="font-semibold">Email:</span> ${student.parent_guardian_email || 'N/A'}</p>
                    </div>
                </div>
                ` : ''}
            </div>
        </div>

        <!-- Enrollment Tab -->
        <div id="tab-enrollment" class="tab-content hidden">
            <h3 class="text-base md:text-lg font-bold text-primary-dark mb-4">Enrollment history</h3>
            ${data.enrollments && data.enrollments.length > 0 ? `
                <div class="space-y-3">
                    ${data.enrollments.map(e => `
                        <div class="border-2 border-gray-200 rounded-lg p-3 md:p-4 hover:border-secondary-blue transition-all">
                            <div class="flex items-start justify-between mb-2">
                                <div>
                                    <h4 class="font-semibold text-sm md:text-base text-primary-dark">${e.session_name}</h4>
                                    <p class="text-xs md:text-sm text-gray-600">Instructor: ${e.instructor_name}</p>
                                </div>
                                <span class="px-2 md:px-3 py-1 rounded-full text-xs font-semibold ${
                                    e.status === 'active' ? 'bg-forest-green text-white' :
                                    e.status === 'completed' ? 'bg-secondary-blue text-white' :
                                    'bg-gray-400 text-white'
                                }">${e.status}</span>
                            </div>
                            <div class="grid grid-cols-2 gap-2 text-xs md:text-sm mt-3">
                                <p><span class="font-semibold">Start date:</span> ${new Date(e.enrollment_date).toLocaleDateString()}</p>
                                <p><span class="font-semibold">Sessions:</span> ${e.total_sessions} total</p>
                                <p><span class="font-semibold">Completed:</span> ${e.completed_sessions}</p>
                                <p><span class="font-semibold">Remaining:</span> ${e.remaining_sessions}</p>
                                <p class="col-span-2"><span class="font-semibold">Payment:</span> ₱${Number(e.amount_paid).toLocaleString()} / ₱${Number(e.total_amount).toLocaleString()}</p>
                            </div>
                        </div>
                    `).join('')}
                </div>
            ` : '<p class="text-gray-500 text-center py-8 text-sm">No enrollment records</p>'}
        </div>

        <!-- Schedule Tab -->
        <div id="tab-schedule" class="tab-content hidden">
            <h3 class="text-base md:text-lg font-bold text-primary-dark mb-4">Recent lessons</h3>
            ${data.upcomingSchedule && data.upcomingSchedule.length > 0 ? `
                <div class="space-y-3">
                    ${data.upcomingSchedule.map(s => `
                        <div class="border-l-4 ${
                            s.status === 'scheduled' ? 'border-secondary-blue' :
                            s.status === 'completed' ? 'border-forest-green' :
                            'border-warm-coral'
                        } pl-3 md:pl-4 py-2 hover:bg-gray-50 transition-all">
                            <div class="flex items-start justify-between">
                                <div>
                                    <p class="font-semibold text-sm md:text-base">${new Date(s.schedule_date).toLocaleDateString()} at ${s.start_time}</p>
                                    <p class="text-xs md:text-sm text-gray-600">${s.lesson_topic || 'General lesson'}</p>
                                    <p class="text-xs md:text-sm text-gray-600">Instructor: ${s.instructor_name}</p>
                                    <p class="text-xs text-gray-500 mt-1">Room: ${s.room_number || 'TBA'}</p>
                                </div>
                                <span class="px-2 py-1 rounded-full text-xs font-semibold whitespace-nowrap ${
                                    s.status === 'scheduled' ? 'bg-secondary-blue text-white' :
                                    s.status === 'completed' ? 'bg-forest-green text-white' :
                                    'bg-warm-coral text-white'
                                }">${s.status}</span>
                            </div>
                        </div>
                    `).join('')}
                </div>
            ` : '<p class="text-gray-500 text-center py-8 text-sm">No lessons scheduled</p>'}
        </div>

        <!-- Attendance Tab -->
        <div id="tab-attendance" class="tab-content hidden">
            <div class="mb-6">
                <h3 class="text-base md:text-lg font-bold text-primary-dark mb-4">Attendance statistics</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-2 md:gap-4">
                    <div class="bg-gradient-to-br from-secondary-blue to-primary-dark text-white p-3 md:p-4 rounded-lg text-center">
                        <p class="text-xl md:text-3xl font-bold">${data.attendanceStats?.total_lessons || 0}</p>
                        <p class="text-xs md:text-sm opacity-90">Total lessons</p>
                    </div>
                    <div class="bg-gradient-to-br from-forest-green to-forest-green-dark text-white p-3 md:p-4 rounded-lg text-center">
                        <p class="text-xl md:text-3xl font-bold">${data.attendanceStats?.present_count || 0}</p>
                        <p class="text-xs md:text-sm opacity-90">Present</p>
                    </div>
                    <div class="bg-gradient-to-br from-warm-coral to-warm-coral-dark text-white p-3 md:p-4 rounded-lg text-center">
                        <p class="text-xl md:text-3xl font-bold">${data.attendanceStats?.absent_count || 0}</p>
                        <p class="text-xs md:text-sm opacity-90">Absent</p>
                    </div>
                    <div class="bg-gradient-to-br from-golden-yellow to-golden-yellow-dark text-white p-3 md:p-4 rounded-lg text-center">
                        <p class="text-xl md:text-3xl font-bold">${data.attendanceStats?.attendance_rate || 0}%</p>
                        <p class="text-xs md:text-sm opacity-90">Rate</p>
                    </div>
                </div>
            </div>
            <button onclick="loadAttendanceDetails(${student.student_id})" class="w-full bg-secondary-blue text-white py-2 rounded-lg hover:bg-secondary-blue-dark mb-4 text-sm md:text-base">Load detailed history</button>
            <div id="attendance-details"></div>
        </div>

        <!-- Progress Tab -->
        <div id="tab-progress" class="tab-content hidden">
            <h3 class="text-base md:text-lg font-bold text-primary-dark mb-4">Recent progress reports</h3>
            ${data.progress && data.progress.length > 0 ? `
                <div class="space-y-4">
                    ${data.progress.map(p => `
                        <div class="border-2 border-gray-200 rounded-lg p-3 md:p-4">
                            <div class="flex items-start justify-between mb-3">
                                <div>
                                    <p class="font-semibold text-sm md:text-base">${new Date(p.progress_date).toLocaleDateString()}</p>
                                    <p class="text-xs md:text-sm text-gray-600">${p.lesson_topic || 'General practice'}</p>
                                    <p class="text-xs text-gray-500">Instructor: ${p.instructor_name}</p>
                                </div>
                                <div class="text-right">
                                    <div class="flex gap-1 mb-1">
                                        ${[1,2,3,4,5].map(i => `
                                            <svg class="w-3 h-3 md:w-4 md:h-4 ${i <= (p.performance_rating || 0) ? 'text-golden-yellow' : 'text-gray-300'}" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                            </svg>
                                        `).join('')}
                                    </div>
                                    <p class="text-xs text-gray-600">Performance</p>
                                </div>
                            </div>
                            ${p.skills_covered ? `<p class="text-xs md:text-sm mb-2"><span class="font-semibold">Skills:</span> ${p.skills_covered}</p>` : ''}
                            ${p.instructor_notes ? `<p class="text-xs md:text-sm text-gray-700 bg-gray-50 p-3 rounded">${p.instructor_notes}</p>` : ''}
                        </div>
                    `).join('')}
                </div>
                <button onclick="loadAllProgress(${student.student_id})" class="w-full mt-4 bg-secondary-blue text-white py-2 rounded-lg hover:bg-secondary-blue-dark text-sm md:text-base">View all progress</button>
            ` : '<p class="text-gray-500 text-center py-8 text-sm">No progress reports yet</p>'}
        </div>

        <!-- Payments Tab -->
        <div id="tab-payments" class="tab-content hidden">
            <div class="mb-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="bg-gradient-to-br from-forest-green to-forest-green-dark text-white p-3 md:p-4 rounded-lg">
                    <p class="text-xl md:text-2xl font-bold">₱${Number(data.paymentSummary?.total_paid || 0).toLocaleString()}</p>
                    <p class="text-xs md:text-sm opacity-90">Total paid</p>
                </div>
                <div class="bg-gradient-to-br from-warm-coral to-warm-coral-dark text-white p-3 md:p-4 rounded-lg">
                    <p class="text-xl md:text-2xl font-bold">₱${Number(data.outstandingBalance || 0).toLocaleString()}</p>
                    <p class="text-xs md:text-sm opacity-90">Outstanding</p>
                </div>
            </div>
            
            <h3 class="text-base md:text-lg font-bold text-primary-dark mb-4">Payment history</h3>
            ${data.payments && data.payments.length > 0 ? `
                <div class="space-y-3">
                    ${data.payments.map(pay => `
                        <div class="border-2 border-gray-200 rounded-lg p-3 md:p-4 hover:border-secondary-blue transition-all">
                            <div class="flex items-start justify-between">
                                <div>
                                    <p class="font-semibold text-sm md:text-base">₱${Number(pay.amount).toLocaleString()}</p>
                                    <p class="text-xs md:text-sm text-gray-600">${new Date(pay.payment_date).toLocaleDateString()}</p>
                                    <p class="text-xs text-gray-500">Receipt: ${pay.receipt_number || 'N/A'}</p>
                                    <p class="text-xs text-gray-500">Method: ${pay.method_name}</p>
                                </div>
                                <span class="px-2 md:px-3 py-1 rounded-full text-xs font-semibold whitespace-nowrap ${
                                    pay.payment_status === 'Paid' ? 'bg-forest-green text-white' :
                                    pay.payment_status === 'Pending' ? 'bg-golden-yellow text-white' :
                                    'bg-warm-coral text-white'
                                }">${pay.payment_status}</span>
                            </div>
                        </div>
                    `).join('')}
                </div>
            ` : '<p class="text-gray-500 text-center py-8 text-sm">No payment records</p>'}
        </div>
    `;
}

// ============================================================================
// SWITCH BETWEEN TABS
// ============================================================================
/**
 * Switch between tabs in the student modal
 * @param {string} tabName - The name of the tab to show
 */
function showTab(tabName) {
    // Hide all tabs
    document.querySelectorAll('.tab-content').forEach(tab => tab.classList.add('hidden'));
    
    // Remove active class from all tab buttons
    document.querySelectorAll('.student-tab').forEach(btn => {
        btn.classList.remove('active', 'border-secondary-blue', 'text-secondary-blue');
        btn.classList.add('text-gray-600');
    });
    
    // Show selected tab
    document.getElementById(`tab-${tabName}`).classList.remove('hidden');
    
    // Mark button as active
    event.target.classList.add('active', 'border-secondary-blue', 'text-secondary-blue');
    event.target.classList.remove('text-gray-600');
}

// ============================================================================
// EDIT STUDENT INFORMATION
// ============================================================================
/**
 * Open edit form for student information
 * @param {number} studentId - The ID of the student
 */
async function editStudent(studentId) {
    try {
        const response = await fetch(`/admin/students/${studentId}`);
        const data = await response.json();
        const student = data.student;
        
        // Fetch filter options for dropdowns
        const statuses = Array.from(document.querySelectorAll('select[name="status"] option'))
            .filter(opt => opt.value !== 'all')
            .map(opt => ({ id: opt.value, name: opt.textContent.trim() }));
        
        const instruments = Array.from(document.querySelectorAll('select[name="instrument"] option'))
            .filter(opt => opt.value !== 'all')
            .map(opt => ({ id: opt.value, name: opt.textContent.trim() }));
        
        const genres = Array.from(document.querySelectorAll('select[name="genre"] option'))
            .filter(opt => opt.value !== 'all')
            .map(opt => ({ id: opt.value, name: opt.textContent.trim() }));
        
        const modal = document.getElementById('student-detail-modal');
        modal.innerHTML = `
            <div class="bg-white rounded-lg max-w-3xl w-full max-h-[90vh] overflow-hidden shadow-2xl animate-fade-in">
                <div class="bg-gradient-to-r from-secondary-blue to-forest-green p-4 md:p-6 text-white">
                    <div class="flex items-center justify-between">
                        <h2 class="text-xl md:text-2xl font-bold">Edit student information</h2>
                        <button onclick="closeStudentModal()" class="text-gray hover:bg-white hover:bg-opacity-20 rounded-full p-2 transition-all">
                            <svg class="w-5 h-5 md:w-6 md:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                </div>
                
                <div class="p-4 md:p-6 overflow-y-auto" style="max-height: calc(90vh - 150px);">
                    <form onsubmit="saveStudent(event, ${studentId})">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 md:gap-4">
                            <div>
                                <label class="block font-semibold text-gray-700 mb-2 text-sm md:text-base">First name *</label>
                                <input type="text" id="edit-first-name" value="${student.first_name}" required 
                                       class="w-full px-3 md:px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-secondary-blue text-sm md:text-base">
                            </div>
                            
                            <div>
                                <label class="block font-semibold text-gray-700 mb-2 text-sm md:text-base">Last name *</label>
                                <input type="text" id="edit-last-name" value="${student.last_name}" required 
                                       class="w-full px-3 md:px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-secondary-blue text-sm md:text-base">
                            </div>
                            
                            <div>
                                <label class="block font-semibold text-gray-700 mb-2 text-sm md:text-base">Email</label>
                                <input type="email" id="edit-email" value="${student.email || ''}" 
                                       class="w-full px-3 md:px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-secondary-blue text-sm md:text-base">
                            </div>
                            
                            <div>
                                <label class="block font-semibold text-gray-700 mb-2 text-sm md:text-base">Phone</label>
                                <input type="text" id="edit-phone" value="${student.phone || ''}" 
                                       pattern="[0-9]{11}" maxlength="11"
                                       class="w-full px-3 md:px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-secondary-blue text-sm md:text-base">
                            </div>
                            
                            <div>
                                <label class="block font-semibold text-gray-700 mb-2 text-sm md:text-base">Primary instrument</label>
                                <select id="edit-instrument" class="w-full px-3 md:px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-secondary-blue text-sm md:text-base">
                                    <option value="">None</option>
                                    ${instruments.map(i => `
                                        <option value="${i.id}" ${student.instrument_id == i.id ? 'selected' : ''}>${i.name}</option>
                                    `).join('')}
                                </select>
                            </div>
                            
                            <div>
                                <label class="block font-semibold text-gray-700 mb-2 text-sm md:text-base">Preferred genre</label>
                                <select id="edit-genre" class="w-full px-3 md:px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-secondary-blue text-sm md:text-base">
                                    <option value="">No preference</option>
                                    ${genres.map(g => `
                                        <option value="${g.id}" ${student.preferred_genre_id == g.id ? 'selected' : ''}>${g.name}</option>
                                    `).join('')}
                                </select>
                            </div>
                            
                            <div class="md:col-span-2">
                                <label class="block font-semibold text-gray-700 mb-2 text-sm md:text-base">Status *</label>
                                <select id="edit-status" required class="w-full px-3 md:px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-secondary-blue text-sm md:text-base">
                                    ${statuses.map(s => `
                                        <option value="${s.id}" ${student.student_status_id == s.id ? 'selected' : ''}>${s.name}</option>
                                    `).join('')}
                                </select>
                            </div>
                        </div>
                        
                        <div class="flex gap-3 mt-6">
                            <button type="submit" class="bg-forest-green text-white px-4 md:px-6 py-2 md:py-3 rounded-lg hover:bg-forest-green-dark flex-1 font-semibold text-sm md:text-base">
                                Save changes
                            </button>
                            <button type="button" onclick="cancelEditStudent(${studentId})" class="bg-gray-200 text-gray-700 px-4 md:px-6 py-2 md:py-3 rounded-lg hover:bg-gray-300 flex-1 font-semibold text-sm md:text-base">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        `;
        
    } catch (error) {
        showToast(error.message, 'error');
    }
}

// ============================================================================
// SAVE STUDENT CHANGES
// ============================================================================
/**
 * Save student information changes
 * @param {Event} event - Form submit event
 * @param {number} studentId - The ID of the student
 */
async function saveStudent(event, studentId) {
    event.preventDefault();
    
    const payload = {
        first_name: document.getElementById('edit-first-name').value.trim(),
        last_name: document.getElementById('edit-last-name').value.trim(),
        email: document.getElementById('edit-email').value.trim() || null,
        phone: document.getElementById('edit-phone').value.trim() || null,
        instrument_id: document.getElementById('edit-instrument').value || null,
        preferred_genre_id: document.getElementById('edit-genre').value || null,
        student_status_id: document.getElementById('edit-status').value,
        _method: 'PUT'
    };
    
    try {
        const response = await fetch(`/admin/students/${studentId}`, {
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
                throw new Error(data.message || 'Update failed');
            }
            return;
        }
        
        showToast(data.message, 'success');
        closeStudentModal();
        setTimeout(() => window.location.reload(), 1500);
        
    } catch (error) {
        showToast(error.message, 'error');
    }
}

/**
 * Cancel student edit and return to view mode
 * @param {number} studentId - The ID of the student
 */
function cancelEditStudent(studentId) {
    viewStudent(studentId);
}

// ============================================================================
// BULK ACTIONS
// ============================================================================
/**
 * Toggle all student checkboxes
 * @param {HTMLElement} checkbox - The "select all" checkbox
 */
function toggleSelectAll(checkbox) {
    const studentCheckboxes = document.querySelectorAll('.student-checkbox');
    studentCheckboxes.forEach(cb => cb.checked = checkbox.checked);
    updateBulkActions();
}

/**
 * Update bulk actions bar based on selected students
 */
function updateBulkActions() {
    const selected = document.querySelectorAll('.student-checkbox:checked');
    const bulkBar = document.getElementById('bulk-actions-bar');
    
    if (selected.length > 0) {
        bulkBar.innerHTML = `
            <div class="flex items-center justify-between flex-wrap gap-3 md:gap-4 bg-secondary-blue bg-opacity-10 border-l-4 border-secondary-blue p-3 md:p-4 rounded-lg">
                <span class="font-semibold text-primary-dark text-sm md:text-base">${selected.length} student(s) selected</span>
                <div class="flex gap-2">
                    <button onclick="bulkUpdateStatus()" class="bg-secondary-blue text-white px-3 md:px-4 py-2 rounded-lg hover:bg-secondary-blue-dark font-semibold text-xs md:text-sm">Update status</button>
                    <button onclick="clearSelection()" class="bg-gray-200 text-gray-700 px-3 md:px-4 py-2 rounded-lg hover:bg-gray-300 font-semibold text-xs md:text-sm">Clear</button>
                </div>
            </div>
        `;
        bulkBar.classList.remove('hidden');
    } else {
        bulkBar.classList.add('hidden');
        bulkBar.innerHTML = '';
    }
}

/**
 * Clear all selections
 */
function clearSelection() {
    document.querySelectorAll('.student-checkbox, #select-all').forEach(cb => cb.checked = false);
    updateBulkActions();
}

/**
 * Open bulk status update modal
 */
async function bulkUpdateStatus() {
    const studentIds = Array.from(document.querySelectorAll('.student-checkbox:checked')).map(cb => cb.value);
    
    if (studentIds.length === 0) {
        showToast('No students selected', 'error');
        return;
    }
    
    // Show status selection modal
    const modal = document.getElementById('bulk-status-modal');
    modal.innerHTML = `
        <div class="bg-white rounded-lg max-w-md w-full p-4 md:p-6 shadow-2xl animate-fade-in">
            <h3 class="text-lg md:text-xl font-bold text-primary-dark mb-4">Update student status</h3>
            <p class="text-xs md:text-sm text-gray-600 mb-4">${studentIds.length} student(s) selected</p>
            
            <form onsubmit="executeBulkStatusUpdate(event, ${JSON.stringify(studentIds).replace(/"/g, '&quot;')})">
                <label class="block font-semibold text-gray-700 mb-2 text-sm md:text-base">New status</label>
                <select id="bulk-status-select" required class="w-full px-3 md:px-4 py-2 border-2 border-gray-300 rounded-lg mb-4 focus:border-secondary-blue text-sm md:text-base">
                    <option value="">Select status...</option>
                    ${Array.from(document.querySelectorAll('select[name="status"] option'))
                        .filter(opt => opt.value !== 'all')
                        .map(opt => `<option value="${opt.value}">${opt.textContent.trim()}</option>`).join('')}
                </select>
                
                <div class="flex gap-3">
                    <button type="submit" class="bg-forest-green text-white px-4 md:px-6 py-2 rounded-lg hover:bg-forest-green-dark flex-1 font-semibold text-sm md:text-base">Update</button>
                    <button type="button" onclick="closeBulkModal()" class="bg-gray-200 text-gray-700 px-4 md:px-6 py-2 rounded-lg hover:bg-gray-300 flex-1 font-semibold text-sm md:text-base">Cancel</button>
                </div>
            </form>
        </div>
    `;
    
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

/**
 * Execute bulk status update
 * @param {Event} event - Form submit event
 * @param {array} studentIds - Array of student IDs
 */
async function executeBulkStatusUpdate(event, studentIds) {
    event.preventDefault();
    
    const statusId = document.getElementById('bulk-status-select').value;
    
    if (!statusId) {
        showToast('Please select a status', 'error');
        return;
    }
    
    try {
        const response = await fetch('/admin/students/bulk-status', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                student_ids: studentIds,
                status_id: statusId
            })
        });
        
        const data = await response.json();
        
        if (!response.ok) {
            throw new Error(data.message || 'Failed to update status');
        }
        
        showToast(data.message, 'success');
        closeBulkModal();
        setTimeout(() => window.location.reload(), 1500);
        
    } catch (error) {
        showToast(error.message, 'error');
    }
}

// ============================================================================
// LOAD ATTENDANCE DETAILS
// ============================================================================
/**
 * Load detailed attendance history
 * @param {number} studentId - The ID of the student
 */
async function loadAttendanceDetails(studentId) {
    try {
        const response = await fetch(`/admin/students/${studentId}`);
        const data = await response.json();
        const attendance = data.attendance || [];
        
        const container = document.getElementById('attendance-details');
        container.innerHTML = attendance.length > 0 ? `
            <div class="space-y-2">
                ${attendance.map(a => `
                    <div class="border-l-4 ${
                        a.attendance_status === 'present' ? 'border-forest-green' :
                        a.attendance_status === 'absent' ? 'border-warm-coral' :
                        'border-golden-yellow'
                    } pl-3 md:pl-4 py-2 md:py-3 hover:bg-gray-50">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-semibold text-xs md:text-sm">${new Date(a.attendance_date).toLocaleDateString()} - ${a.start_time || 'N/A'}</p>
                                <p class="text-xs text-gray-500">Instructor: ${a.instructor_name || 'N/A'}</p>
                            </div>
                            <span class="px-2 md:px-3 py-1 rounded-full text-xs font-semibold whitespace-nowrap ${
                                a.attendance_status === 'present' ? 'bg-forest-green text-white' :
                                a.attendance_status === 'absent' ? 'bg-warm-coral text-white' :
                                a.attendance_status === 'late' ? 'bg-golden-yellow text-white' :
                                'bg-secondary-blue text-white'
                            }">${a.attendance_status}</span>
                        </div>
                        ${a.check_in_time ? `<p class="text-xs text-gray-500 mt-1">Check-in: ${a.check_in_time}</p>` : ''}
                    </div>
                `).join('')}
            </div>
        ` : '<p class="text-gray-500 text-center py-4 text-sm">No attendance records found</p>';
        
    } catch (error) {
        showToast(error.message, 'error');
    }
}

// ============================================================================
// LOAD ALL PROGRESS
// ============================================================================
/**
 * Load all progress reports
 * @param {number} studentId - The ID of the student
 */
async function loadAllProgress(studentId) {
    try {
        const response = await fetch(`/admin/students/${studentId}`);
        const data = await response.json();
        const progress = data.progress || [];
        
        const modal = document.getElementById('student-detail-modal');
        modal.innerHTML = `
            <div class="bg-white rounded-lg max-w-5xl w-full max-h-[90vh] overflow-hidden shadow-2xl animate-fade-in">
                <div class="bg-gradient-to-r from-secondary-blue to-forest-green p-4 md:p-6 text-white">
                    <div class="flex items-center justify-between">
                        <h2 class="text-xl md:text-2xl font-bold">Complete progress history</h2>
                        <button onclick="closeStudentModal()" class="text-white hover:bg-white hover:bg-opacity-20 rounded-full p-2 transition-all">
                            <svg class="w-5 h-5 md:w-6 md:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                </div>
                
                <div class="p-4 md:p-6 overflow-y-auto" style="max-height: calc(90vh - 150px);">
                    ${progress.length > 0 ? `
                        <div class="space-y-4">
                            ${progress.map(p => `
                                <div class="border-2 border-gray-200 rounded-lg p-3 md:p-4">
                                    <div class="flex items-start justify-between mb-3">
                                        <div>
                                            <p class="font-semibold text-base md:text-lg">${new Date(p.progress_date).toLocaleDateString()}</p>
                                            <p class="text-xs md:text-sm text-gray-600">${p.lesson_topic || 'General practice'}</p>
                                            <p class="text-xs text-gray-500">Instructor: ${p.instructor_name}</p>
                                        </div>
                                        <div class="grid grid-cols-2 gap-2 text-xs">
                                            ${['Performance', 'Technical', 'Musicality', 'Effort'].map((label, idx) => {
                                                const ratings = [p.performance_rating, p.technical_skills_rating, p.musicality_rating, p.effort_rating];
                                                const rating = ratings[idx] || 0;
                                                return `
                                                <div>
                                                    <p class="font-semibold">${label}</p>
                                                    <p class="text-golden-yellow">${'★'.repeat(rating)}${'☆'.repeat(5 - rating)}</p>
                                                </div>
                                                `;
                                            }).join('')}
                                        </div>
                                    </div>
                                    ${p.skills_covered ? `<p class="text-xs md:text-sm mb-2"><span class="font-semibold">Skills:</span> ${p.skills_covered}</p>` : ''}
                                    ${p.instructor_notes ? `<p class="text-xs md:text-sm text-gray-700 bg-gray-50 p-3 rounded mb-2"><span class="font-semibold">Feedback:</span> ${p.instructor_notes}</p>` : ''}
                                    ${p.homework ? `<p class="text-xs md:text-sm"><span class="font-semibold">Homework:</span> ${p.homework}</p>` : ''}
                                </div>
                            `).join('')}
                        </div>
                    ` : '<p class="text-gray-500 text-center py-8 text-sm">No progress reports found</p>'}
                </div>
            </div>
        `;
        
    } catch (error) {
        showToast(error.message, 'error');
    }
}

// ============================================================================
// CLOSE MODALS
// ============================================================================
/**
 * Close student detail modal
 */
function closeStudentModal() {
    const modal = document.getElementById('student-detail-modal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    modal.innerHTML = '';
}

/**
 * Close bulk status modal
 */
function closeBulkModal() {
    const modal = document.getElementById('bulk-status-modal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    modal.innerHTML = '';
}

// ============================================================================
// TOAST NOTIFICATIONS WITH CLOSE BUTTON
// ============================================================================
/**
 * Display a toast notification with close button
 * @param {string} message - The message to display
 * @param {string} type - 'success', 'error', or 'custom'
 * @param {string|null} customColor - Hex color for custom type
 */
function showToast(message, type = 'success', customColor = null) {
    const container = document.getElementById('toast-container');
    if (!container) return;
    
    const toast = document.createElement('div');
    
    let bgColor;
    let icon;
    
    if (type === 'custom' && customColor) {
        toast.style.backgroundColor = customColor;
        icon = `<svg class="w-5 h-5 md:w-6 md:h-6 mr-2 md:mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>`;
    } else if (type === 'success') {
        bgColor = 'bg-forest-green';
        icon = `<svg class="w-5 h-5 md:w-6 md:h-6 mr-2 md:mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>`;
    } else {
        bgColor = 'bg-warm-coral';
        icon = `<svg class="w-5 h-5 md:w-6 md:h-6 mr-2 md:mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>`;
    }
    
    toast.className = `flex items-center p-3 md:p-4 rounded-lg shadow-lg text-white ${bgColor} animate-fade-in-up max-w-sm`;
    toast.innerHTML = `
        ${icon} 
        <span class="font-semibold flex-1 text-xs md:text-sm">${message}</span>
        <button onclick="this.parentElement.remove()" class="ml-2 md:ml-3 hover:bg-white hover:bg-opacity-20 rounded-full p-1 transition-all flex-shrink-0">
            <svg class="w-4 h-4 md:w-5 md:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    `;
    
    container.appendChild(toast);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        toast.classList.add('animate-fade-out');
        toast.addEventListener('animationend', () => toast.remove());
    }, 5000);
}

// ============================================================================
// END OF STUDENT MANAGEMENT FUNCTIONS
// ============================================================================
console.log('%c✓ Student Management JS Loaded', 'color: #377357; font-weight: bold;');