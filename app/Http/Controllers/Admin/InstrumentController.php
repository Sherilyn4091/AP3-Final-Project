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

        $categories = self::CATEGORIES;

        return view('admin.instruments.index', compact('instruments', 'categories'));
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
            ]);

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

            // Prevent editing system instruments' core fields
            if ($instrument->is_system) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot modify system instruments. Only description can be updated.'
                ], 403);
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
}