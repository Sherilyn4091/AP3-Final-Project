<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * ============================================================================
 * SCHEDULE CONTROLLER - Manage lesson schedules
 * ============================================================================
 * Handles CRUD operations for lesson schedules with conflict detection
 * Features:
 * - Create/update/delete schedules
 * - Triple conflict detection (room, instructor, student)
 * - Calendar event data formatting
 * - Room availability checking
 * ============================================================================
 */
class ScheduleController extends Controller
{
    /**
     * Display schedule management page with calendar
     */
    public function index(Request $request)
    {
        // Fetch all active rooms for the view (dropdown, display, etc.)
        $rooms = DB::table('room')
            ->whereRaw('is_active = true')
            ->orderBy('room_number', 'asc')
            ->get();

        $instructors = DB::table('instructor')
            ->whereRaw('is_active = true')
            ->orderBy('last_name')
            ->select('instructor_id', DB::raw("CONCAT(first_name, ' ', last_name) as full_name"))
            ->get();

        $students = DB::table('student')
            ->whereRaw('is_active = true')
            ->orderBy('last_name')
            ->select('student_id', DB::raw("CONCAT(first_name, ' ', last_name) as full_name"))
            ->get();

        $statuses = ['scheduled', 'completed', 'cancelled', 'no_class', 'rescheduled', 'substitute', 'no_show', 'in_progress'];

        return view('admin.schedules.index', compact('rooms', 'instructors', 'students', 'statuses'));
    }

    /**
     * Get calendar events (for FullCalendar)
     */
    public function getEvents(Request $request)
    {
        $query = DB::table('schedule as s')
            ->leftJoin('student as st', 's.student_id', '=', 'st.student_id')
            ->leftJoin('instructor as i', 's.instructor_id', '=', 'i.instructor_id')
            ->leftJoin('enrollment as e', 's.enrollment_id', '=', 'e.enrollment_id')
            ->leftJoin('instrument as ins', 'st.instrument_id', '=', 'ins.instrument_id')
            ->select(
                's.schedule_id',
                's.schedule_date',
                's.start_time',
                's.end_time',
                's.room_number',
                's.status',
                's.lesson_topic',
                's.lesson_content',
                's.notes',
                's.enrollment_id',
                DB::raw("CONCAT(st.first_name, ' ', st.last_name) as student_name"),
                DB::raw("CONCAT(i.first_name, ' ', i.last_name) as instructor_name"),
                'ins.instrument_name',
                'st.student_id',
                'i.instructor_id'
            );

        // Apply filters
        if ($request->has('room') && $request->room !== 'all') {
            $query->where('s.room_number', $request->room);
        }

        if ($request->has('instructor') && $request->instructor !== 'all') {
            $query->where('s.instructor_id', $request->instructor);
        }

        if ($request->has('student') && $request->student !== 'all') {
            $query->where('s.student_id', $request->student);
        }

        if ($request->has('status') && $request->status !== 'all') {
            $query->where('s.status', $request->status);
        }

        // Date range for FullCalendar
        if ($request->has('start')) {
            $query->where('s.schedule_date', '>=', $request->start);
        }

        if ($request->has('end')) {
            $query->where('s.schedule_date', '<=', $request->end);
        }

        $schedules = $query->get();

        // Format for FullCalendar
        $events = $schedules->map(function ($schedule) {
            // Determine color based on status
            $colors = [
                'scheduled' => '#2563EB', // secondary-blue
                'completed' => '#377357', // forest-green
                'cancelled' => '#E57373', // warm-coral
                'no_class' => '#9E9E9E', // gray
                'rescheduled' => '#FFA726', // golden-yellow
                'substitute' => '#AB47BC', // purple
                'no_show' => '#EF5350', // red
                'in_progress' => '#29B6F6', // light-blue
            ];

            return [
                'id' => $schedule->schedule_id,
                'title' => $schedule->student_name . ' - ' . ($schedule->instrument_name ?? 'Lesson'),
                'start' => $schedule->schedule_date . 'T' . $schedule->start_time,
                'end' => $schedule->schedule_date . 'T' . $schedule->end_time,
                'backgroundColor' => $colors[$schedule->status] ?? '#2563EB',
                'borderColor' => $colors[$schedule->status] ?? '#2563EB',
                'extendedProps' => [
                    'schedule_id' => $schedule->schedule_id,
                    'student_name' => $schedule->student_name,
                    'instructor_name' => $schedule->instructor_name,
                    'instrument' => $schedule->instrument_name,
                    'room_number' => $schedule->room_number,
                    'lesson_topic' => $schedule->lesson_topic,
                    'status' => $schedule->status,
                    'enrollment_id' => $schedule->enrollment_id,
                    'student_id' => $schedule->student_id,
                    'instructor_id' => $schedule->instructor_id,
                    'notes' => $schedule->notes,
                    'lesson_content' => $schedule->lesson_content,
                ]
            ];
        });

        return response()->json($events);
    }

    /**
     * Get active enrollments for schedule creation
     */
    public function getEnrollments(Request $request)
    {
        $query = DB::table('enrollment as e')
            ->join('student as s', 'e.student_id', '=', 's.student_id')
            ->join('instructor as i', 'e.instructor_id', '=', 'i.instructor_id')
            ->join('lesson_session as ls', 'e.session_id', '=', 'ls.session_id')
            ->leftJoin('instrument as ins', 's.instrument_id', '=', 'ins.instrument_id')
            ->where('e.status', 'active')
            ->where('e.remaining_sessions', '>', 0)
            ->select(
                'e.enrollment_id',
                'e.student_id',
                'e.instructor_id',
                'e.remaining_sessions',
                'ls.duration_minutes',
                DB::raw("CONCAT(s.first_name, ' ', s.last_name) as student_name"),
                DB::raw("CONCAT(i.first_name, ' ', i.last_name) as instructor_name"),
                'ins.instrument_name'
            );

        // Optional: filter by student if provided
        if ($request->has('student_id')) {
            $query->where('e.student_id', $request->student_id);
        }

        $enrollments = $query->get();

        return response()->json($enrollments);
    }

    /**
     * Check for scheduling conflicts (room, instructor, student)
     */
    private function checkConflicts($scheduleDate, $startTime, $endTime, $roomNumber, $instructorId, $studentId, $excludeScheduleId = null)
    {
        // Build the conflict check query
        $query = DB::table('schedule')
            ->where('schedule_date', $scheduleDate)
            ->whereNotIn('status', ['cancelled', 'no_class', 'rescheduled'])
            ->where(function ($q) use ($startTime, $endTime) {
                // Check for time overlap: new schedule overlaps with existing
                $q->where(function ($overlap) use ($startTime, $endTime) {
                    // Case 1: New start time falls within existing schedule
                    $overlap->where('start_time', '<=', $startTime)
                        ->where('end_time', '>', $startTime);
                })->orWhere(function ($overlap) use ($startTime, $endTime) {
                    // Case 2: New end time falls within existing schedule
                    $overlap->where('start_time', '<', $endTime)
                        ->where('end_time', '>=', $endTime);
                })->orWhere(function ($overlap) use ($startTime, $endTime) {
                    // Case 3: New schedule completely contains existing schedule
                    $overlap->where('start_time', '>=', $startTime)
                        ->where('end_time', '<=', $endTime);
                });
            });

        // Exclude current schedule if updating
        if ($excludeScheduleId) {
            $query->where('schedule_id', '!=', $excludeScheduleId);
        }

        // Check all three conflict types
        $conflicts = $query->where(function ($q) use ($roomNumber, $instructorId, $studentId) {
            $q->where('room_number', $roomNumber)
                ->orWhere('instructor_id', $instructorId)
                ->orWhere('student_id', $studentId);
        })->get();

        // Categorize conflicts
        $conflictTypes = [];

        foreach ($conflicts as $conflict) {
            if ($conflict->room_number === $roomNumber) {
                $conflictTypes[] = "Room {$roomNumber} is already booked at this time";
            }
            if ($conflict->instructor_id == $instructorId) {
                $conflictTypes[] = "Instructor is already scheduled at this time";
            }
            if ($conflict->student_id == $studentId) {
                $conflictTypes[] = "Student already has a lesson at this time";
            }
        }

        return [
            'has_conflict' => count($conflictTypes) > 0,
            'conflicts' => array_unique($conflictTypes)
        ];
    }

    /**
     * Store a new schedule
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'enrollment_id' => 'required|exists:enrollment,enrollment_id',
            'schedule_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'room_number' => 'required|exists:room,room_number',
            'lesson_topic' => 'nullable|string|max:200',
            'lesson_content' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Get enrollment details
            $enrollment = DB::table('enrollment')
                ->where('enrollment_id', $request->enrollment_id)
                ->first();

            if (!$enrollment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Enrollment not found'
                ], 404);
            }

            // Check conflicts
            $conflictCheck = $this->checkConflicts(
                $request->schedule_date,
                $request->start_time,
                $request->end_time,
                $request->room_number,
                $enrollment->instructor_id,
                $enrollment->student_id
            );

            if ($conflictCheck['has_conflict']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Scheduling conflict detected',
                    'conflicts' => $conflictCheck['conflicts']
                ], 409);
            }

            // Calculate duration
            $start = new \DateTime($request->start_time);
            $end = new \DateTime($request->end_time);
            $duration = $start->diff($end)->h * 60 + $start->diff($end)->i;

            // Insert schedule
            $scheduleId = DB::table('schedule')->insertGetId([
                'enrollment_id' => $request->enrollment_id,
                'student_id' => $enrollment->student_id,
                'instructor_id' => $enrollment->instructor_id,
                'room_number' => $request->room_number,
                'schedule_date' => $request->schedule_date,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'duration_minutes' => $duration,
                'lesson_topic' => $request->lesson_topic,
                'lesson_content' => $request->lesson_content,
                'notes' => $request->notes,
                'status' => 'scheduled',
                'created_at' => now(),
                'updated_at' => now()
            ], 'schedule_id');

            return response()->json([
                'success' => true,
                'message' => 'Schedule created successfully',
                'schedule_id' => $scheduleId
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create schedule: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get schedule details
     */
    public function show($id)
    {
        $schedule = DB::table('schedule as s')
            ->leftJoin('student as st', 's.student_id', '=', 'st.student_id')
            ->leftJoin('instructor as i', 's.instructor_id', '=', 'i.instructor_id')
            ->leftJoin('enrollment as e', 's.enrollment_id', '=', 'e.enrollment_id')
            ->leftJoin('instrument as ins', 'st.instrument_id', '=', 'ins.instrument_id')
            ->where('s.schedule_id', $id)
            ->select(
                's.*',
                DB::raw("CONCAT(st.first_name, ' ', st.last_name) as student_name"),
                DB::raw("CONCAT(i.first_name, ' ', i.last_name) as instructor_name"),
                'ins.instrument_name',
                'e.remaining_sessions'
            )
            ->first();

        if (!$schedule) {
            return response()->json([
                'success' => false,
                'message' => 'Schedule not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'schedule' => $schedule
        ]);
    }

    /**
     * Update schedule
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'schedule_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'room_number' => 'required|exists:room,room_number',
            'lesson_topic' => 'nullable|string|max:200',
            'lesson_content' => 'nullable|string',
            'notes' => 'nullable|string',
            'status' => 'required|in:scheduled,completed,cancelled,no_class,rescheduled,substitute,no_show,in_progress',
            'cancellation_reason' => 'required_if:status,cancelled|nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Get existing schedule
            $schedule = DB::table('schedule')->where('schedule_id', $id)->first();

            if (!$schedule) {
                return response()->json([
                    'success' => false,
                    'message' => 'Schedule not found'
                ], 404);
            }

            // Only check conflicts if not cancelling
            if ($request->status !== 'cancelled') {
                $conflictCheck = $this->checkConflicts(
                    $request->schedule_date,
                    $request->start_time,
                    $request->end_time,
                    $request->room_number,
                    $schedule->instructor_id,
                    $schedule->student_id,
                    $id // Exclude current schedule
                );

                if ($conflictCheck['has_conflict']) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Scheduling conflict detected',
                        'conflicts' => $conflictCheck['conflicts']
                    ], 409);
                }
            }

            // Calculate duration
            $start = new \DateTime($request->start_time);
            $end = new \DateTime($request->end_time);
            $duration = $start->diff($end)->h * 60 + $start->diff($end)->i;

            // Update schedule
            $updateData = [
                'schedule_date' => $request->schedule_date,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'duration_minutes' => $duration,
                'room_number' => $request->room_number,
                'lesson_topic' => $request->lesson_topic,
                'lesson_content' => $request->lesson_content,
                'notes' => $request->notes,
                'status' => $request->status,
                'updated_at' => now()
            ];

            // Add cancellation data if cancelling
            if ($request->status === 'cancelled') {
                $updateData['cancellation_reason'] = $request->cancellation_reason;
                $updateData['cancelled_at'] = now();
            }

            DB::table('schedule')
                ->where('schedule_id', $id)
                ->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Schedule updated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update schedule: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete schedule
     */
    public function destroy($id)
    {
        try {
            $deleted = DB::table('schedule')
                ->where('schedule_id', $id)
                ->delete();

            if (!$deleted) {
                return response()->json([
                    'success' => false,
                    'message' => 'Schedule not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Schedule deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete schedule: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check room availability
     */
    public function checkAvailability(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Get all rooms
        $rooms = DB::table('room')
            ->whereRaw('is_active = true')
            ->get();

        // Check which rooms are occupied
        $occupiedRooms = DB::table('schedule')
            ->where('schedule_date', $request->date)
            ->whereNotIn('status', ['cancelled', 'no_class', 'rescheduled'])
            ->where(function ($q) use ($request) {
                $q->where(function ($overlap) use ($request) {
                    $overlap->where('start_time', '<=', $request->start_time)
                        ->where('end_time', '>', $request->start_time);
                })->orWhere(function ($overlap) use ($request) {
                    $overlap->where('start_time', '<', $request->end_time)
                        ->where('end_time', '>=', $request->end_time);
                })->orWhere(function ($overlap) use ($request) {
                    $overlap->where('start_time', '>=', $request->start_time)
                        ->where('end_time', '<=', $request->end_time);
                });
            })
            ->pluck('room_number')
            ->toArray();

        $availability = $rooms->map(function ($room) use ($occupiedRooms) {
            return [
                'room_number' => $room->room_number,
                'room_name' => $room->room_name,
                'is_available' => !in_array($room->room_number, $occupiedRooms)
            ];
        });

        return response()->json([
            'success' => true,
            'availability' => $availability
        ]);
    }

    /**
     * Quick status update (for calendar actions)
     */
    public function quickUpdate(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:scheduled,completed,cancelled,no_class,rescheduled,substitute,no_show,in_progress',
            'cancellation_reason' => 'required_if:status,cancelled|nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $updateData = [
                'status' => $request->status,
                'updated_at' => now()
            ];

            if ($request->status === 'cancelled') {
                $updateData['cancellation_reason'] = $request->cancellation_reason;
                $updateData['cancelled_at'] = now();
            }

            DB::table('schedule')
                ->where('schedule_id', $id)
                ->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Status updated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update status: ' . $e->getMessage()
            ], 500);
        }
    }
}