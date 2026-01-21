<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * ============================================================================
 * INSTRUMENT MANAGEMENT CONTROLLER
 * ============================================================================
 * Handles CRUD operations for musical instruments
 * Features:
 * - List all instruments with active student counts
 * - Add new instruments with category validation
 * - Edit existing instruments (except system instruments)
 * - Soft delete (deactivate) unused instruments
 * - Prevent deletion of instruments in use
 * - Protect system instruments from deletion
 * ============================================================================
 */
class InstrumentController extends Controller
{
    /**
     * Predefined instrument categories
     */
    private const CATEGORIES = [
        'String',
        'Keyboard',
        'Percussion',
        'Wind',
        'Brass',
        'Voice/Vocal',
        'Electronic',
        'Other'
    ];

    /**
     * Display list of all instruments with student counts
     */
    public function index()
    {
        // Fetch all instruments with count of ACTIVE students using each instrument
        $instruments = DB::table('instrument')
            ->leftJoin('student', function($join) {
                $join->on('student.instrument_id', '=', 'instrument.instrument_id')
                    ->where('student.is_active', '=', true);
            })
            ->select(
                'instrument.*',
                DB::raw('COUNT(student.student_id) as students_count')
            )
            ->groupBy(
                'instrument.instrument_id',
                'instrument.instrument_name',
                'instrument.category',
                'instrument.description',
                'instrument.is_active',
                'instrument.is_system',
                'instrument.created_at',
                'instrument.updated_at'
            )
            ->orderBy('instrument.instrument_name', 'ASC')
            ->get();

        // Calculate statistics
        $stats = [
            'total' => $instruments->count(),
            'active' => $instruments->where('is_active', true)->count(),
            'inactive' => $instruments->where('is_active', false)->count(),
            'most_used_name' => 'N/A',
            'most_used_count' => 0
        ];

        // Find most popular instrument
        $mostUsed = $instruments->where('students_count', '>', 0)->sortByDesc('students_count')->first();
        if ($mostUsed) {
            $stats['most_used_name'] = $mostUsed->instrument_name;
            $stats['most_used_count'] = $mostUsed->students_count;
        }

        $categories = self::CATEGORIES;

        return view('admin.instruments.index', compact('instruments', 'categories', 'stats'));
    }

    /**
     * Store a newly created instrument
     */
    public function store(Request $request)
    {
        // Validation rules
        $validator = Validator::make($request->all(), [
            'instrument_name' => [
                'required',
                'string',
                'max:100',
                // Case-insensitive unique check
                function ($attribute, $value, $fail) {
                    $exists = DB::table('instrument')
                        ->whereRaw('LOWER(instrument_name) = ?', [strtolower($value)])
                        ->exists();
                    
                    if ($exists) {
                        $fail('An instrument with this name already exists.');
                    }
                },
            ],
            'category' => 'required|in:' . implode(',', self::CATEGORIES),
            'description' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Insert new instrument
            $instrumentId = DB::table('instrument')->insertGetId([
                'instrument_name' => trim($request->instrument_name),
                'category' => $request->category,
                'description' => $request->description ? trim($request->description) : null,
                'is_system' => false, // New instruments are never system instruments
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ], 'instrument_id');

            return response()->json([
                'success' => true,
                'message' => 'Instrument created successfully.',
                'instrument_id' => $instrumentId
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create instrument: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified instrument
     */
    public function show($id)
    {
        try {
            $instrument = DB::table('instrument')
                ->where('instrument_id', $id)
                ->first();

            if (!$instrument) {
                return response()->json([
                    'success' => false,
                    'message' => 'Instrument not found.'
                ], 404);
            }

            // Get student count (active students only)
            $studentsCount = DB::table('student')
                ->where('instrument_id', $id)
                ->whereRaw('is_active = TRUE')
                ->count();

            return response()->json([
                'success' => true,
                'instrument' => $instrument,
                'students_count' => $studentsCount
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch instrument: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified instrument
     */
    public function update(Request $request, $id)
    {
        try {
            // Check if instrument exists
            $instrument = DB::table('instrument')
                ->where('instrument_id', $id)
                ->first();

            if (!$instrument) {
                return response()->json([
                    'success' => false,
                    'message' => 'Instrument not found.'
                ], 404);
            }

            // For system instruments, only allow description updates
            if ($instrument->is_system) {
                // Only validate and update description
                $validator = Validator::make($request->all(), [
                    'description' => 'nullable|string|max:500',
                ]);

                if ($validator->fails()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Validation failed.',
                        'errors' => $validator->errors()
                    ], 422);
                }

                DB::table('instrument')
                    ->where('instrument_id', $id)
                    ->update([
                        'description' => $request->description ? trim($request->description) : null,
                        'updated_at' => now()
                    ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Instrument description updated successfully.'
                ]);
            }

            // Validation rules
            $validator = Validator::make($request->all(), [
                'instrument_name' => [
                    'required',
                    'string',
                    'max:100',
                    // Case-insensitive unique check (excluding current instrument)
                    function ($attribute, $value, $fail) use ($id) {
                        $exists = DB::table('instrument')
                            ->whereRaw('LOWER(instrument_name) = ?', [strtolower($value)])
                            ->where('instrument_id', '!=', $id)
                            ->exists();
                        
                        if ($exists) {
                            $fail('An instrument with this name already exists.');
                        }
                    },
                ],
                'category' => 'required|in:' . implode(',', self::CATEGORIES),
                'description' => 'nullable|string|max:500',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed.',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Update instrument
            DB::table('instrument')
                ->where('instrument_id', $id)
                ->update([
                    'instrument_name' => trim($request->instrument_name),
                    'category' => $request->category,
                    'description' => $request->description ? trim($request->description) : null,
                    'updated_at' => now()
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Instrument updated successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update instrument: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Soft delete (deactivate) the specified instrument
     */
    public function destroy($id)
    {
        try {
            // Check if instrument exists
            $instrument = DB::table('instrument')
                ->where('instrument_id', $id)
                ->first();

            if (!$instrument) {
                return response()->json([
                    'success' => false,
                    'message' => 'Instrument not found.'
                ], 404);
            }

            // Prevent deletion of system instruments
            if ($instrument->is_system) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete system instruments. These are protected default instruments.'
                ], 403);
            }

            // Check if instrument is in use
            $usageCheck = $this->checkInstrumentUsage($id);

            if ($usageCheck['in_use']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot deactivate this instrument.',
                    'details' => $usageCheck['message']
                ], 409);
            }

            // Soft delete: set is_active = false
            DB::table('instrument')
                ->where('instrument_id', $id)
                ->update([
                    'is_active' => false,
                    'updated_at' => now()
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Instrument deactivated successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to deactivate instrument: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle instrument active status (activate/deactivate)
     */
    public function toggleStatus($id)
    {
        try {
            // Check if instrument exists
            $instrument = DB::table('instrument')
                ->where('instrument_id', $id)
                ->first();

            if (!$instrument) {
                return response()->json([
                    'success' => false,
                    'message' => 'Instrument not found.'
                ], 404);
            }

            // Prevent toggling system instruments to inactive
            if ($instrument->is_system && $instrument->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot deactivate system instruments.'
                ], 403);
            }

            // If trying to deactivate, check usage
            if ($instrument->is_active) {
                $usageCheck = $this->checkInstrumentUsage($id);

                if ($usageCheck['in_use']) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cannot deactivate this instrument.',
                        'details' => $usageCheck['message']
                    ], 409);
                }
            }

            // Toggle status
            $newStatus = !$instrument->is_active;
            
            DB::table('instrument')
                ->where('instrument_id', $id)
                ->update([
                    'is_active' => $newStatus,
                    'updated_at' => now()
                ]);

            $statusText = $newStatus ? 'activated' : 'deactivated';

            return response()->json([
                'success' => true,
                'message' => "Instrument {$statusText} successfully.",
                'new_status' => $newStatus
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to toggle status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check if instrument is currently in use
     * 
     * @param int $instrumentId
     * @return array ['in_use' => bool, 'message' => string, 'counts' => array]
     */
    private function checkInstrumentUsage($instrumentId)
    {
        // Count active students using this instrument
        $activeStudentsCount = DB::table('student')
            ->where('instrument_id', $instrumentId)
            ->whereRaw('is_active = TRUE')
            ->count();

        $usageDetails = [];
        $inUse = false;

        if ($activeStudentsCount > 0) {
            $inUse = true;
            $usageDetails[] = "{$activeStudentsCount} active student(s)";
        }

        $message = $inUse 
            ? 'This instrument is currently used by: ' . implode(', ', $usageDetails) . '.'
            : 'Instrument is not in use.';

        return [
            'in_use' => $inUse,
            'message' => $message,
            'counts' => [
                'students' => $activeStudentsCount
            ]
        ];
    }

    /**
     * Get instrument usage details (for frontend display)
     */
    public function getUsageDetails($id)
    {
        try {
            $usageCheck = $this->checkInstrumentUsage($id);

            return response()->json([
                'success' => true,
                'usage' => $usageCheck
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to check usage: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get list of students enrolled in a specific instrument
     * 
     * @param int $id - instrument_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStudents($id)
    {
        try {
            // Check if instrument exists
            $instrument = DB::table('instrument')
                ->where('instrument_id', $id)
                ->first();

            if (!$instrument) {
                return response()->json([
                    'success' => false,
                    'message' => 'Instrument not found.'
                ], 404);
            }

            // Get all ACTIVE students using this instrument
            $students = DB::table('student')
                ->join('user_account', 'student.user_id', '=', 'user_account.user_id')
                ->where('student.instrument_id', $id)
                ->where('student.is_active', true)
                ->select(
                    'student.student_id',
                    'student.first_name',
                    'student.last_name',
                    'student.enrollment_date',
                    'user_account.user_email'
                )
                ->orderBy('student.last_name', 'ASC')
                ->orderBy('student.first_name', 'ASC')
                ->get()
                ->map(function($student) {
                    // Format the student name
                    $student->student_name = trim($student->first_name . ' ' . $student->last_name);
                    return $student;
                });

            return response()->json([
                'success' => true,
                'students' => $students,
                'instrument_name' => $instrument->instrument_name
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch students: ' . $e->getMessage()
            ], 500);
        }
    }
}