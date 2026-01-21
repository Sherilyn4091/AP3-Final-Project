/**
 * ============================================================================
 * SCHEDULE MANAGEMENT JAVASCRIPT
 * resources/js/admin-pages/schedule.js
 * ============================================================================
 * This script handles all interactive functionality for the schedule management page,
 * including:
 * - FullCalendar initialization with responsive views
 * - Add/Edit/Delete schedule modals
 * - Triple conflict detection (room, instructor, student)
 * - Room availability checker
 * - Quick actions (Complete, Cancel, Reschedule, View Details)
 * - Filter management
 * ============================================================================
 */

const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

// ============================================================================
// GLOBAL CALENDAR INSTANCE & FILTERS
// ============================================================================
let calendar = null;
let currentFilters = {
    room: 'all',
    instructor: 'all',
    student: 'all',
    status: 'all'
};

// ============================================================================
// EXPOSE FUNCTIONS GLOBALLY FOR BLADE ONCLICK HANDLERS
// ============================================================================
window.applyFilters = applyFilters;
window.clearFilters = clearFilters;
window.openAddScheduleModal = openAddScheduleModal;
window.openRoomAvailabilityChecker = openRoomAvailabilityChecker;
window.closeScheduleModal = closeScheduleModal;
window.closeEventModal = closeEventModal;
window.closeAvailabilityModal = closeAvailabilityModal;
window.submitSchedule = submitSchedule;
window.loadEnrollmentDetails = loadEnrollmentDetails;
window.checkRoomAvailability = checkRoomAvailability;
window.markAsCompleted = markAsCompleted;
window.cancelSchedule = cancelSchedule;
window.deleteSchedule = deleteSchedule;

/**
 * ============================================================================
 * INITIALIZE FULLCALENDAR ON PAGE LOAD
 * ============================================================================
 */
document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('calendar');
    
    if (!calendarEl) return; // Not on schedule page
    
    // Initialize FullCalendar
    calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: getInitialView(),
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        buttonText: {
            today: 'Today',
            month: 'Month',
            week: 'Week',
            day: 'Day'
        },
        slotMinTime: '10:00:00', // Studio opens at 10 AM
        slotMaxTime: '19:00:00', // Studio closes at 7 PM
        allDaySlot: false,
        height: 'auto',
        expandRows: true,
        slotDuration: '00:30:00', // 30-minute intervals
        slotLabelInterval: '01:00', // Show hour labels
        snapDuration: '00:15:00', // Snap to 15-minute intervals
        editable: true, // Enable drag & drop
        droppable: false,
        eventResizableFromStart: true,
        
        // Mobile-specific settings - auto switch to day view on small screens
        windowResize: function() {
            if (window.innerWidth < 480) {
                calendar.changeView('timeGridDay');
            } else if (window.innerWidth < 768) {
                calendar.changeView('timeGridWeek');
            }
        },
        
        // Fetch events from backend
        events: function(info, successCallback, failureCallback) {
            const params = new URLSearchParams({
                start: info.startStr,
                end: info.endStr,
                ...currentFilters
            });
            
            fetch(`/admin/schedules/events?${params}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            })
            .then(response => response.json())
            .then(data => successCallback(data))
            .catch(error => {
                console.error('Error fetching events:', error);
                failureCallback(error);
            });
        },
        
        // Event click handler - open detail modal
        eventClick: function(info) {
            info.jsEvent.preventDefault();
            openEventDetailModal(info.event);
        },
        
        // Event drag & drop (reschedule)
        eventDrop: function(info) {
            const event = info.event;
            const newStart = event.start;
            const newEnd = event.end;
            
            // Ask for confirmation
            if (!confirm(`Reschedule to ${formatDateTime(newStart)}?`)) {
                info.revert();
                return;
            }
            
            // Update via API
            updateScheduleDateTime(event.extendedProps.schedule_id, newStart, newEnd)
                .then(() => {
                    showToast('Schedule updated successfully', 'success');
                })
                .catch(error => {
                    showToast(error.message, 'error');
                    info.revert();
                });
        },
        
        // Event resize (change duration)
        eventResize: function(info) {
            const event = info.event;
            const newEnd = event.end;
            
            if (!confirm('Update lesson duration?')) {
                info.revert();
                return;
            }
            
            updateScheduleDateTime(event.extendedProps.schedule_id, event.start, newEnd)
                .then(() => {
                    showToast('Duration updated successfully', 'success');
                })
                .catch(error => {
                    showToast(error.message, 'error');
                    info.revert();
                });
        }
    });
    
    calendar.render();

});

/**
 * Get initial calendar view based on screen size
 */
function getInitialView() {
    if (window.innerWidth < 480) {
        return 'timeGridDay';
    } else if (window.innerWidth < 768) {
        return 'timeGridWeek';
    }
    return 'dayGridMonth';
}

/**
 * ============================================================================
 * FILTER MANAGEMENT
 * ============================================================================
 */

/**
 * Apply filters and refresh calendar
 */
function applyFilters() {
    currentFilters = {
        room: document.getElementById('filter-room')?.value || 'all',
        instructor: document.getElementById('filter-instructor')?.value || 'all',
        student: document.getElementById('filter-student')?.value || 'all',
        status: document.getElementById('filter-status')?.value || 'all'
    };
    
    if (calendar) {
        calendar.refetchEvents();
    }
}

/**
 * Clear all filters and refresh calendar
 */
function clearFilters() {
    // Reset all filter dropdowns to "all"
    document.getElementById('filter-room').value = 'all';
    document.getElementById('filter-instructor').value = 'all';
    document.getElementById('filter-student').value = 'all';
    document.getElementById('filter-status').value = 'all';
    
    // Reset current filters object
    currentFilters = {
        room: 'all',
        instructor: 'all',
        student: 'all',
        status: 'all'
    };
    
    // Refresh calendar
    if (calendar) {
        calendar.refetchEvents();
    }
    
    showToast('Filters cleared', 'success');
}

/**
 * ============================================================================
 * ADD SCHEDULE MODAL
 * ============================================================================
 */

/**
 * Open Add Schedule Modal
 */
async function openAddScheduleModal() {
    try {
        // Fetch active enrollments
        const response = await fetch('/admin/schedules/enrollments', {
            headers: { 'Accept': 'application/json' }
        });
        const enrollments = await response.json();
        
        // Fetch rooms
        const roomsResponse = await fetch('/admin/schedules/rooms', {
            headers: { 'Accept': 'application/json' }
        });
        const rooms = await roomsResponse.json();
        
        const modal = document.getElementById('add-schedule-modal');
        modal.innerHTML = `
            <div class="bg-white rounded-lg max-w-2xl w-full p-6 shadow-2xl animate-fade-in">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-bold text-primary-dark">Add lesson schedule</h2>
                    <button onclick="closeScheduleModal()" class="text-gray-500 hover:text-gray-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                
                <form onsubmit="submitSchedule(event)">
                    <div class="space-y-4">
                        <!-- Enrollment Selection -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Select enrollment *</label>
                            <select id="enrollment-select" onchange="loadEnrollmentDetails()" required class="w-full px-3 py-2 text-sm border-2 border-gray-300 rounded-lg focus:border-secondary-blue">
                                <option value="">Choose a student enrollment...</option>
                                ${enrollments.map(e => `
                                    <option value="${e.enrollment_id}" 
                                            data-student="${e.student_name}" 
                                            data-instructor="${e.instructor_name}" 
                                            data-instrument="${e.instrument_name || 'N/A'}"
                                            data-remaining="${e.remaining_sessions}"
                                            data-duration="${e.duration_minutes}">
                                        ${e.student_name} - ${e.instructor_name} (${e.remaining_sessions} sessions left)
                                    </option>
                                `).join('')}
                            </select>
                        </div>
                        
                        <!-- Enrollment Details Display -->
                        <div id="enrollment-details" class="hidden bg-secondary-blue bg-opacity-10 p-3 rounded-lg">
                            <div class="grid grid-cols-2 gap-2 text-xs">
                                <p><span class="font-semibold">Student:</span> <span id="detail-student"></span></p>
                                <p><span class="font-semibold">Instructor:</span> <span id="detail-instructor"></span></p>
                                <p><span class="font-semibold">Instrument:</span> <span id="detail-instrument"></span></p>
                                <p><span class="font-semibold">Remaining:</span> <span id="detail-remaining"></span> sessions</p>
                            </div>
                        </div>
                        
                        <!-- Date & Time -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Schedule date *</label>
                                <input type="date" id="schedule-date" required min="${new Date().toISOString().split('T')[0]}" class="w-full px-3 py-2 text-sm border-2 border-gray-300 rounded-lg focus:border-secondary-blue">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Start time *</label>
                                <input type="time" id="start-time" required value="10:00" class="w-full px-3 py-2 text-sm border-2 border-gray-300 rounded-lg focus:border-secondary-blue">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">End time *</label>
                                <input type="time" id="end-time" required value="11:00" class="w-full px-3 py-2 text-sm border-2 border-gray-300 rounded-lg focus:border-secondary-blue">
                            </div>
                        </div>
                        
                        <!-- Room Selection -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Room *</label>
                            <select id="room-select" required class="w-full px-3 py-2 text-sm border-2 border-gray-300 rounded-lg focus:border-secondary-blue">
                                <option value="">Select room...</option>
                                ${rooms.map(r => `
                                    <option value="${r.room_number}">${r.room_name || 'Room ' + r.room_number}</option>
                                `).join('')}
                            </select>
                        </div>
                        
                        <!-- Lesson Details -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Lesson topic</label>
                            <input type="text" id="lesson-topic" placeholder="e.g., Scales and arpeggios" maxlength="200" class="w-full px-3 py-2 text-sm border-2 border-gray-300 rounded-lg focus:border-secondary-blue">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Lesson content</label>
                            <textarea id="lesson-content" rows="2" placeholder="Detailed lesson plan or objectives..." class="w-full px-3 py-2 text-sm border-2 border-gray-300 rounded-lg focus:border-secondary-blue"></textarea>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Notes</label>
                            <textarea id="notes" rows="2" placeholder="Additional notes..." class="w-full px-3 py-2 text-sm border-2 border-gray-300 rounded-lg focus:border-secondary-blue"></textarea>
                        </div>
                        
                        <!-- Conflict Warning Area -->
                        <div id="conflict-warning" class="hidden bg-warm-coral bg-opacity-20 border-l-4 border-warm-coral p-3 rounded">
                            <p class="font-semibold text-sm text-warm-coral mb-2">Scheduling Conflicts Detected:</p>
                            <ul id="conflict-list" class="list-disc list-inside text-xs text-gray-700"></ul>
                        </div>
                    </div>
                    
                    <div class="flex gap-3 mt-6">
                        <button type="submit" class="bg-forest-green text-white px-4 py-2 text-sm rounded-lg hover:bg-forest-green-dark flex-1 font-semibold">
                            Create schedule
                        </button>
                        <button type="button" onclick="closeScheduleModal()" class="bg-gray-200 text-gray-700 px-4 py-2 text-sm rounded-lg hover:bg-gray-300 flex-1 font-semibold">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        `;
        
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        
    } catch (error) {
        showToast('Failed to load enrollment data: ' + error.message, 'error');
    }
}

/**
 * Load enrollment details when selected
 */
function loadEnrollmentDetails() {
    const select = document.getElementById('enrollment-select');
    const option = select.options[select.selectedIndex];
    
    if (!option.value) {
        document.getElementById('enrollment-details').classList.add('hidden');
        return;
    }
    
    document.getElementById('detail-student').textContent = option.dataset.student;
    document.getElementById('detail-instructor').textContent = option.dataset.instructor;
    document.getElementById('detail-instrument').textContent = option.dataset.instrument;
    document.getElementById('detail-remaining').textContent = option.dataset.remaining;
    
    // Auto-set end time based on duration
    const duration = parseInt(option.dataset.duration);
    const startTime = document.getElementById('start-time').value;
    if (startTime && duration) {
        const [hours, minutes] = startTime.split(':');
        const endDate = new Date();
        endDate.setHours(parseInt(hours));
        endDate.setMinutes(parseInt(minutes) + duration);
        document.getElementById('end-time').value = endDate.toTimeString().slice(0, 5);
    }
    
    document.getElementById('enrollment-details').classList.remove('hidden');
}

/**
 * Submit new schedule
 */
async function submitSchedule(event) {
    event.preventDefault();
    
    const payload = {
        enrollment_id: document.getElementById('enrollment-select').value,
        schedule_date: document.getElementById('schedule-date').value,
        start_time: document.getElementById('start-time').value,
        end_time: document.getElementById('end-time').value,
        room_number: document.getElementById('room-select').value,
        lesson_topic: document.getElementById('lesson-topic').value || null,
        lesson_content: document.getElementById('lesson-content').value || null,
        notes: document.getElementById('notes').value || null
    };
    
    try {
        const response = await fetch('/admin/schedules', {
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
            // Handle conflict errors
            if (response.status === 409 && data.conflicts) {
                const conflictWarning = document.getElementById('conflict-warning');
                const conflictList = document.getElementById('conflict-list');
                conflictList.innerHTML = data.conflicts.map(c => `<li>${c}</li>`).join('');
                conflictWarning.classList.remove('hidden');
                return;
            }
            
            throw new Error(data.message || 'Failed to create schedule');
        }
        
        showToast(data.message, 'success');
        closeScheduleModal();
        calendar.refetchEvents();
        
    } catch (error) {
        showToast(error.message, 'error');
    }
}

/**
 * ============================================================================
 * EVENT DETAIL MODAL
 * ============================================================================
 */

/**
 * Open event detail modal with quick actions
 */
function openEventDetailModal(event) {
    const props = event.extendedProps;
    const modal = document.getElementById('event-detail-modal');
    
    modal.innerHTML = `
        <div class="bg-white rounded-lg max-w-2xl w-full p-6 shadow-2xl animate-fade-in">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold text-primary-dark">Lesson details</h2>
                <button onclick="closeEventModal()" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            
            <!-- Event Details -->
            <div class="space-y-3 mb-6">
                <div class="grid grid-cols-2 gap-3 text-sm">
                    <div>
                        <p class="font-semibold text-gray-700">Student</p>
                        <p class="text-gray-900">${props.student_name}</p>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-700">Instructor</p>
                        <p class="text-gray-900">${props.instructor_name}</p>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-700">Instrument</p>
                        <p class="text-gray-900">${props.instrument || 'N/A'}</p>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-700">Room</p>
                        <p class="text-gray-900">${props.room_number}</p>
                    </div>
                    <div class="col-span-2">
                        <p class="font-semibold text-gray-700">Date & Time</p>
                        <p class="text-gray-900">${formatDateTime(event.start)} - ${event.end.toLocaleTimeString('en-US', {hour: '2-digit', minute: '2-digit'})}</p>
                    </div>
                    ${props.lesson_topic ? `
                    <div class="col-span-2">
                        <p class="font-semibold text-gray-700">Topic</p>
                        <p class="text-gray-900">${props.lesson_topic}</p>
                    </div>
                    ` : ''}
                    ${props.lesson_content ? `
                    <div class="col-span-2">
                        <p class="font-semibold text-gray-700">Content</p>
                        <p class="text-gray-900">${props.lesson_content}</p>
                    </div>
                    ` : ''}
                    ${props.notes ? `
                    <div class="col-span-2">
                        <p class="font-semibold text-gray-700">Notes</p>
                        <p class="text-gray-900">${props.notes}</p>
                    </div>
                    ` : ''}
                    <div class="col-span-2">
                        <p class="font-semibold text-gray-700">Status</p>
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold" style="background-color: ${event.backgroundColor}; color: white;">
                            ${props.status.replace('_', ' ').toUpperCase()}
                        </span>
                    </div>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="border-t pt-4">
                <p class="font-semibold text-gray-700 mb-3 text-sm">Quick actions</p>
                <div class="grid grid-cols-2 gap-2">
                    ${props.status === 'scheduled' ? `
                        <button onclick="markAsCompleted(${props.schedule_id})" class="bg-forest-green text-white px-3 py-2 text-sm rounded-lg hover:bg-forest-green-dark font-semibold">
                            Mark as completed
                        </button>
                    ` : ''}
                    ${props.status !== 'cancelled' ? `
                        <button onclick="cancelSchedule(${props.schedule_id})" class="bg-warm-coral text-white px-3 py-2 text-sm rounded-lg hover:bg-warm-coral-dark font-semibold">
                            Cancel lesson
                        </button>
                    ` : ''}
                    <button onclick="deleteSchedule(${props.schedule_id})" class="bg-red-600 text-white px-3 py-2 text-sm rounded-lg hover:bg-red-700 font-semibold">
                        Delete
                    </button>
                    <button onclick="closeEventModal()" class="bg-gray-200 text-gray-700 px-3 py-2 text-sm rounded-lg hover:bg-gray-300 font-semibold">
                        Close
                    </button>
                </div>
            </div>
        </div>
    `;
    
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

/**
 * ============================================================================
 * QUICK ACTIONS
 * ============================================================================
 */

/**
 * Mark schedule as completed
 */
async function markAsCompleted(scheduleId) {
    if (!confirm('Mark this lesson as completed?')) return;
    
    try {
        const response = await fetch(`/admin/schedules/${scheduleId}/quick-update`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ status: 'completed' })
        });
        
        const data = await response.json();
        
        if (!response.ok) throw new Error(data.message || 'Failed to update');
        
        showToast(data.message, 'success');
        closeEventModal();
        calendar.refetchEvents();
        
    } catch (error) {
        showToast(error.message, 'error');
    }
}

/**
 * Cancel schedule
 */
async function cancelSchedule(scheduleId) {
    const reason = prompt('Enter cancellation reason:');
    if (!reason) return;
    
    try {
        const response = await fetch(`/admin/schedules/${scheduleId}/quick-update`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ 
                status: 'cancelled',
                cancellation_reason: reason
            })
        });
        
        const data = await response.json();
        
        if (!response.ok) throw new Error(data.message || 'Failed to cancel');
        
        showToast(data.message, 'success');
        closeEventModal();
        calendar.refetchEvents();
        
    } catch (error) {
        showToast(error.message, 'error');
    }
}

/**
 * Delete schedule
 */
async function deleteSchedule(scheduleId) {
    if (!confirm('Permanently delete this schedule? This cannot be undone.')) return;
    
    try {
        const response = await fetch(`/admin/schedules/${scheduleId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        });
        
        const data = await response.json();
        
        if (!response.ok) throw new Error(data.message || 'Failed to delete');
        
        showToast(data.message, 'success');
        closeEventModal();
        calendar.refetchEvents();
        
    } catch (error) {
        showToast(error.message, 'error');
    }
}

/**
 * Update schedule date/time (for drag & drop)
 */
async function updateScheduleDateTime(scheduleId, newStart, newEnd) {
    const response = await fetch(`/admin/schedules/${scheduleId}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            schedule_date: newStart.toISOString().split('T')[0],
            start_time: newStart.toTimeString().slice(0, 5),
            end_time: newEnd.toTimeString().slice(0, 5),
            status: 'scheduled'
        })
    });
    
    const data = await response.json();
    
    if (!response.ok) {
        if (data.conflicts) {
            throw new Error('Conflict: ' + data.conflicts.join(', '));
        }
        throw new Error(data.message || 'Update failed');
    }
    
    return data;
}

/**
 * ============================================================================
 * ROOM AVAILABILITY CHECKER
 * ============================================================================
 */

/**
 * Open room availability checker modal
 */
function openRoomAvailabilityChecker() {
    const modal = document.getElementById('room-availability-modal');
    
    modal.innerHTML = `
        <div class="bg-white rounded-lg max-w-xl w-full p-6 shadow-2xl animate-fade-in">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold text-primary-dark">Check room availability</h2>
                <button onclick="closeAvailabilityModal()" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            
            <form onsubmit="checkRoomAvailability(event)">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Date *</label>
                        <input type="date" id="avail-date" required min="${new Date().toISOString().split('T')[0]}" class="w-full px-3 py-2 text-sm border-2 border-gray-300 rounded-lg focus:border-secondary-blue">
                    </div>
                    
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Start time *</label>
                            <input type="time" id="avail-start" required value="10:00" class="w-full px-3 py-2 text-sm border-2 border-gray-300 rounded-lg focus:border-secondary-blue">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">End time *</label>
                            <input type="time" id="avail-end" required value="11:00" class="w-full px-3 py-2 text-sm border-2 border-gray-300 rounded-lg focus:border-secondary-blue">
                        </div>
                    </div>
                    
                    <button type="submit" class="w-full bg-secondary-blue text-white px-4 py-2 text-sm rounded-lg hover:bg-secondary-blue-dark font-semibold">
                        Check availability
                    </button>
                </div>
            </form>
            
            <div id="availability-results" class="mt-6 hidden">
                <!-- Results will be populated here -->
            </div>
        </div>
    `;
    
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}


/**
 * Check room availability
 */
async function checkRoomAvailability(event) {
    event.preventDefault();
    
    const date = document.getElementById('avail-date').value;
    const startTime = document.getElementById('avail-start').value;
    const endTime = document.getElementById('avail-end').value;
    
    try {
        const response = await fetch(`/admin/schedules/check-availability?date=${date}&start_time=${startTime}&end_time=${endTime}`, {
            headers: { 'Accept': 'application/json' }
        });
        
        const data = await response.json();
        
        if (!response.ok) throw new Error(data.message || 'Check failed');
        
        const resultsDiv = document.getElementById('availability-results');
        resultsDiv.innerHTML = `
            <div class="border-t pt-4">
                <h3 class="font-semibold text-gray-700 mb-3">Availability results</h3>
                <div class="space-y-2">
                    ${data.availability.map(room => `
                        <div class="flex items-center justify-between p-3 rounded-lg ${room.is_available ? 'bg-forest-green bg-opacity-10' : 'bg-warm-coral bg-opacity-10'}">
                            <div>
                                <p class="font-semibold">${room.room_name || 'Room ' + room.room_number}</p>
                                <p class="text-xs text-gray-600">Room ${room.room_number}</p>
                            </div>
                            <span class="px-3 py-1 rounded-full text-sm font-semibold ${room.is_available ? 'bg-forest-green text-white' : 'bg-warm-coral text-white'}">
                                ${room.is_available ? 'Available' : 'Occupied'}
                            </span>
                        </div>
                    `).join('')}
                </div>
            </div>
        `;
        resultsDiv.classList.remove('hidden');
        
    } catch (error) {
        showToast(error.message, 'error');
    }
}

/**
 * Close modals
 */
function closeScheduleModal() {
    const modal = document.getElementById('add-schedule-modal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function closeEventModal() {
    const modal = document.getElementById('event-detail-modal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function closeAvailabilityModal() {
    const modal = document.getElementById('room-availability-modal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

/**
 * Utility: Format date and time
 */
function formatDateTime(date) {
    return date.toLocaleString('en-US', {
        weekday: 'short',
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

/**
 * END OF SCHEDULE MANAGEMENT FUNCTIONS
 * ============================================================================
 */