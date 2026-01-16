{{--
    ============================================================================
    SCHEDULE MANAGEMENT PAGE - resources/views/admin/schedules/index.blade.php
    ============================================================================
    Features:
    - FullCalendar integration with responsive views
    - Add/Edit/Delete lesson schedules
    - Triple conflict detection (room, instructor, student)
    - Room availability checker
    - Quick actions (Complete, Cancel, Reschedule, View Details)
    - Mobile-optimized (Week → Day view on small screens)
    ============================================================================
--}}

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Schedule Management - Admin Dashboard</title>
    @vite(['resources/css/style.css', 'resources/js/app.js', 'resources/js/admin-pages.js'])

    <!-- FullCalendar CSS -->
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css' rel='stylesheet' />
    
    <!-- Mobile-specific styles -->
    <style>
        /* Mobile calendar adjustments */
        @media (max-width: 768px) {
            .fc .fc-toolbar-title {
                font-size: 1rem !important;
            }
            .fc .fc-button {
                padding: 0.3rem 0.5rem !important;
                font-size: 0.75rem !important;
            }
            .fc-event {
                font-size: 0.7rem !important;
            }
        }
        
        /* Calendar container */
        #calendar {
            background: white;
            padding: 1rem;
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        
        /* Event styling */
        .fc-event {
            cursor: pointer;
            border-radius: 0.25rem;
            padding: 0.25rem;
        }
        
        .fc-event:hover {
            opacity: 0.85;
        }
    </style>
</head>

<body class="bg-light-gray">

@include('layouts.admin-header')

<main class="lg:ml-64 min-h-screen bg-light-gray">

    {{-- Page Header --}}
    <header class="bg-white shadow-sm p-6 border-b-4 border-secondary-blue">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <h1 class="text-3xl font-bold text-primary-dark">Schedule management</h1>
                <p class="text-secondary-blue mt-1">Manage lesson schedules and room bookings</p>
            </div>
            <div class="flex gap-3">
                <button onclick="openAddScheduleModal()" class="bg-forest-green text-white px-6 py-3 rounded-lg font-semibold hover:bg-forest-green-dark transition-all shadow-lg">
                    <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Add lesson schedule
                </button>
                <button onclick="openRoomAvailabilityChecker()" class="bg-secondary-blue text-white px-6 py-3 rounded-lg font-semibold hover:bg-secondary-blue-dark transition-all shadow-lg">
                    <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                    Check availability
                </button>
            </div>
        </div>
    </header>

    <div class="p-4 lg:p-6">

        {{-- Filters --}}
        <div class="card p-6 mb-6">
            <h3 class="text-lg font-bold text-primary-dark mb-4">Filters</h3>
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Room</label>
                    <select id="filter-room" class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-secondary-blue">
                        <option value="all">All rooms</option>
                        @foreach($rooms as $room)
                            <option value="{{ $room->room_number }}">{{ $room->room_name ?? 'Room ' . $room->room_number }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Instructor</label>
                    <select id="filter-instructor" class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-secondary-blue">
                        <option value="all">All instructors</option>
                        @foreach($instructors as $instructor)
                            <option value="{{ $instructor->instructor_id }}">{{ $instructor->full_name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Student</label>
                    <select id="filter-student" class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-secondary-blue">
                        <option value="all">All students</option>
                        @foreach($students as $student)
                            <option value="{{ $student->student_id }}">{{ $student->full_name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Status</label>
                    <select id="filter-status" class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-secondary-blue">
                        <option value="all">All statuses</option>
                        @foreach($statuses as $status)
                            <option value="{{ $status }}">{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="flex items-end">
                    <button onclick="applyFilters()" class="w-full bg-secondary-blue text-white px-6 py-2 rounded-lg font-semibold hover:bg-secondary-blue-dark transition-all">
                        Apply
                    </button>
                </div>
            </div>
        </div>

        {{-- Legend --}}
        <div class="card p-4 mb-6">
            <div class="flex flex-wrap gap-4 items-center text-sm">
                <span class="font-semibold text-gray-700">Legend:</span>
                <div class="flex items-center gap-2"><span class="w-4 h-4 rounded" style="background-color: #2563EB;"></span> Scheduled</div>
                <div class="flex items-center gap-2"><span class="w-4 h-4 rounded" style="background-color: #377357;"></span> Completed</div>
                <div class="flex items-center gap-2"><span class="w-4 h-4 rounded" style="background-color: #E57373;"></span> Cancelled</div>
                <div class="flex items-center gap-2"><span class="w-4 h-4 rounded" style="background-color: #FFA726;"></span> Rescheduled</div>
                <div class="flex items-center gap-2"><span class="w-4 h-4 rounded" style="background-color: #EF5350;"></span> No Show</div>
                <div class="flex items-center gap-2"><span class="w-4 h-4 rounded" style="background-color: #29B6F6;"></span> In Progress</div>
            </div>
        </div>

        {{-- Calendar --}}
        <div class="card p-6">
            <div id="calendar"></div>
        </div>
    </div>

</main>

{{-- Add Schedule Modal --}}
<div id="add-schedule-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-4 overflow-y-auto">
    <!-- Content will be populated by JS -->
</div>

{{-- Event Detail Modal --}}
<div id="event-detail-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-4 overflow-y-auto">
    <!-- Content will be populated by JS -->
</div>

{{-- Room Availability Modal --}}
<div id="room-availability-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-4 overflow-y-auto">
    <!-- Content will be populated by JS -->
</div>

{{-- Toast Container --}}
<div id="toast-container" class="fixed bottom-6 right-6 z-50 space-y-3"></div>

<!-- FullCalendar JS -->
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js'></script>

</body>
</html>