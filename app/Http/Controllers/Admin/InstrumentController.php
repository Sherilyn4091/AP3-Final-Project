<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

/**
 * ============================================================================
 * ADMIN INSTRUMENT CONTROLLER
 * ============================================================================
 *
 * Handles the Admin Instruments module.
 *
 * Main responsibilities:
 * - Display instruments with filters, pagination, and useful statistics
 * - Create new custom instruments
 * - Update custom instruments
 * - Allow limited update for protected system instruments
 * - Activate / deactivate instruments safely
 * - Protect relationships with students and enrollments
 * - Return usage and student details for the frontend modals
 *
 * Database note:
 * - The actual table name is singular: instrument
 * - Important relationships:
 *   student.instrument_id    -> instrument.instrument_id
 *   enrollment.instrument_id -> instrument.instrument_id
 * ============================================================================
 */
class InstrumentController extends Controller
{
    /**
     * Allowed instrument categories.
     */
    private const CATEGORIES = [
        'String',
        'Keyboard',
        'Percussion',
        'Wind',
        'Brass',
        'Voice/Vocal',
        'Electronic',
        'Other',
    ];

    /**
     * Display instrument management page.
     */
    public function index(Request $request)
    {
        $filters = [
            'search' => trim((string) $request->query('search', '')),
            'category' => $request->query('category', 'all'),
            'status' => $request->query('status', 'all'),
            'type' => $request->query('type', 'all'),
            'sort' => $request->query('sort', 'name_asc'),
        ];

        $query = DB::table('instrument as ins')
            ->select('ins.*');

        /*
        |--------------------------------------------------------------------------
        | Relationship Counters
        |--------------------------------------------------------------------------
        |
        | These counters are added as subqueries to avoid incorrect row duplication
        | from multiple joins. This keeps the instrument list accurate.
        |
        */
        $query->selectSub(function ($subquery) {
            $subquery->from('student as s')
                ->selectRaw('COUNT(*)')
                ->whereColumn('s.instrument_id', 'ins.instrument_id');
        }, 'students_count');

        $query->selectSub(function ($subquery) {
            $subquery->from('student as s')
                ->selectRaw('COUNT(*)')
                ->whereColumn('s.instrument_id', 'ins.instrument_id')
                ->where('s.is_active', true);
        }, 'active_students_count');

        if (Schema::hasTable('enrollment') && Schema::hasColumn('enrollment', 'instrument_id')) {
            $query->selectSub(function ($subquery) {
                $subquery->from('enrollment as e')
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('e.instrument_id', 'ins.instrument_id');
            }, 'enrollments_count');

            $query->selectSub(function ($subquery) {
                $subquery->from('enrollment as e')
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('e.instrument_id', 'ins.instrument_id')
                    ->where('e.status', 'active');
            }, 'active_enrollments_count');
        } else {
            $query->selectRaw('0 as enrollments_count');
            $query->selectRaw('0 as active_enrollments_count');
        }

        /*
        |--------------------------------------------------------------------------
        | Search and Filters
        |--------------------------------------------------------------------------
        */
        if ($filters['search'] !== '') {
            $search = $filters['search'];

            $query->where(function ($innerQuery) use ($search) {
                $innerQuery->where('ins.instrument_name', 'ILIKE', "%{$search}%")
                    ->orWhere('ins.category', 'ILIKE', "%{$search}%")
                    ->orWhere('ins.description', 'ILIKE', "%{$search}%");
            });
        }

        if ($filters['category'] !== 'all') {
            $query->where('ins.category', $filters['category']);
        }

        if ($filters['status'] === 'active') {
            $query->where('ins.is_active', true);
        } elseif ($filters['status'] === 'inactive') {
            $query->where('ins.is_active', false);
        }

        if ($filters['type'] === 'system') {
            $query->where('ins.is_system', true);
        } elseif ($filters['type'] === 'custom') {
            $query->where('ins.is_system', false);
        }

        /*
        |--------------------------------------------------------------------------
        | Sorting
        |--------------------------------------------------------------------------
        */
        match ($filters['sort']) {
            'name_desc' => $query->orderBy('ins.instrument_name', 'desc'),
            'category_asc' => $query->orderBy('ins.category')->orderBy('ins.instrument_name'),
            'students_desc' => $query->orderByDesc('active_students_count')->orderBy('ins.instrument_name'),
            'enrollments_desc' => $query->orderByDesc('active_enrollments_count')->orderBy('ins.instrument_name'),
            'newest' => $query->orderByDesc('ins.created_at'),
            default => $query->orderBy('ins.instrument_name'),
        };

        $instruments = $query->paginate(12)->withQueryString();

        $categories = self::CATEGORIES;
        $stats = $this->buildStats();
        $categoryStats = $this->buildCategoryStats();

        return view('admin.instruments.index', compact(
            'instruments',
            'categories',
            'stats',
            'categoryStats',
            'filters'
        ));
    }

    /**
     * Store a new custom instrument.
     */
    public function store(Request $request)
    {
        $validator = $this->validateInstrument($request);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        try {
            $instrumentId = DB::table('instrument')->insertGetId([
                'instrument_name' => trim($request->instrument_name),
                'category' => $request->category,
                'description' => $this->nullableTrim($request->description),
                'is_system' => false,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ], 'instrument_id');

            return response()->json([
                'success' => true,
                'message' => 'Instrument created successfully.',
                'instrument_id' => $instrumentId,
            ]);
        } catch (\Throwable $exception) {
            return $this->serverError('Failed to create instrument.', $exception);
        }
    }

    /**
     * Return one instrument with usage summary.
     */
    public function show($id)
    {
        try {
            $instrument = DB::table('instrument')
                ->where('instrument_id', $id)
                ->first();

            if (!$instrument) {
                return $this->notFound();
            }

            return response()->json([
                'success' => true,
                'instrument' => $instrument,
                'usage' => $this->checkInstrumentUsage((int) $id),
            ]);
        } catch (\Throwable $exception) {
            return $this->serverError('Failed to fetch instrument.', $exception);
        }
    }

    /**
     * Update an existing instrument.
     *
     * Protected system instruments:
     * - Name and category are locked.
     * - Description can still be updated for admin clarity.
     */
    public function update(Request $request, $id)
    {
        try {
            $instrument = DB::table('instrument')
                ->where('instrument_id', $id)
                ->first();

            if (!$instrument) {
                return $this->notFound();
            }

            if ((bool) $instrument->is_system) {
                $validator = Validator::make($request->all(), [
                    'description' => 'nullable|string|max:500',
                ]);

                if ($validator->fails()) {
                    return $this->validationError($validator);
                }

                DB::table('instrument')
                    ->where('instrument_id', $id)
                    ->update([
                        'description' => $this->nullableTrim($request->description),
                        'updated_at' => now(),
                    ]);

                return response()->json([
                    'success' => true,
                    'message' => 'System instrument description updated successfully.',
                ]);
            }

            $validator = $this->validateInstrument($request, (int) $id);

            if ($validator->fails()) {
                return $this->validationError($validator);
            }

            DB::table('instrument')
                ->where('instrument_id', $id)
                ->update([
                    'instrument_name' => trim($request->instrument_name),
                    'category' => $request->category,
                    'description' => $this->nullableTrim($request->description),
                    'updated_at' => now(),
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Instrument updated successfully.',
            ]);
        } catch (\Throwable $exception) {
            return $this->serverError('Failed to update instrument.', $exception);
        }
    }

    /**
     * Deactivate an instrument.
     *
     * This is intentionally a soft delete to protect records connected to
     * students, enrollments, reports, and history.
     */
    public function destroy($id)
    {
        try {
            $instrument = DB::table('instrument')
                ->where('instrument_id', $id)
                ->first();

            if (!$instrument) {
                return $this->notFound();
            }

            if ((bool) $instrument->is_system) {
                return response()->json([
                    'success' => false,
                    'message' => 'System instruments are protected and cannot be deactivated.',
                ], 403);
            }

            if (!(bool) $instrument->is_active) {
                return response()->json([
                    'success' => true,
                    'message' => 'Instrument is already inactive.',
                ]);
            }

            $usage = $this->checkInstrumentUsage((int) $id);

            if ($usage['in_use']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot deactivate this instrument because it is still connected to active records.',
                    'details' => $usage['message'],
                    'usage' => $usage,
                ], 409);
            }

            DB::table('instrument')
                ->where('instrument_id', $id)
                ->update([
                    'is_active' => false,
                    'updated_at' => now(),
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Instrument deactivated successfully.',
            ]);
        } catch (\Throwable $exception) {
            return $this->serverError('Failed to deactivate instrument.', $exception);
        }
    }

    /**
     * Activate or deactivate an instrument.
     */
    public function toggleStatus($id)
    {
        try {
            $instrument = DB::table('instrument')
                ->where('instrument_id', $id)
                ->first();

            if (!$instrument) {
                return $this->notFound();
            }

            $currentlyActive = (bool) $instrument->is_active;
            $newStatus = !$currentlyActive;

            if ($currentlyActive && (bool) $instrument->is_system) {
                return response()->json([
                    'success' => false,
                    'message' => 'System instruments are protected and cannot be deactivated.',
                ], 403);
            }

            if ($currentlyActive) {
                $usage = $this->checkInstrumentUsage((int) $id);

                if ($usage['in_use']) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cannot deactivate this instrument because it is still connected to active records.',
                        'details' => $usage['message'],
                        'usage' => $usage,
                    ], 409);
                }
            }

            DB::table('instrument')
                ->where('instrument_id', $id)
                ->update([
                    'is_active' => $newStatus,
                    'updated_at' => now(),
                ]);

            return response()->json([
                'success' => true,
                'message' => $newStatus
                    ? 'Instrument activated successfully.'
                    : 'Instrument deactivated successfully.',
                'new_status' => $newStatus,
            ]);
        } catch (\Throwable $exception) {
            return $this->serverError('Failed to update instrument status.', $exception);
        }
    }

    /**
     * Return usage details for frontend relationship-impact display.
     */
    public function getUsageDetails($id)
    {
        try {
            $exists = DB::table('instrument')
                ->where('instrument_id', $id)
                ->exists();

            if (!$exists) {
                return $this->notFound();
            }

            return response()->json([
                'success' => true,
                'usage' => $this->checkInstrumentUsage((int) $id),
            ]);
        } catch (\Throwable $exception) {
            return $this->serverError('Failed to check instrument usage.', $exception);
        }
    }

    /**
     * Return students connected to a specific instrument.
     *
     * Uses both:
     * - student.instrument_id for the student's primary/default instrument
     * - enrollment.instrument_id for actual lesson enrollment relationships
     */
    public function getStudents($id)
    {
        try {
            $instrument = DB::table('instrument')
                ->where('instrument_id', $id)
                ->first();

            if (!$instrument) {
                return $this->notFound();
            }

            $students = DB::table('student as s')
                ->leftJoin('user_account as ua', 's.user_id', '=', 'ua.user_id')
                ->where(function ($query) use ($id) {
                    $query->where('s.instrument_id', $id);

                    if (Schema::hasTable('enrollment') && Schema::hasColumn('enrollment', 'instrument_id')) {
                        $query->orWhereExists(function ($subquery) use ($id) {
                            $subquery->select(DB::raw(1))
                                ->from('enrollment as e')
                                ->whereColumn('e.student_id', 's.student_id')
                                ->where('e.instrument_id', $id);
                        });
                    }
                })
                ->select(
                    's.student_id',
                    's.first_name',
                    's.last_name',
                    's.email',
                    's.phone',
                    's.enrollment_date',
                    's.is_active',
                    'ua.user_email'
                )
                ->selectSub(function ($subquery) use ($id) {
                    $subquery->from('enrollment as e')
                        ->selectRaw('COUNT(*)')
                        ->whereColumn('e.student_id', 's.student_id')
                        ->where('e.instrument_id', $id);
                }, 'enrollments_count')
                ->selectSub(function ($subquery) use ($id) {
                    $subquery->from('enrollment as e')
                        ->selectRaw('COUNT(*)')
                        ->whereColumn('e.student_id', 's.student_id')
                        ->where('e.instrument_id', $id)
                        ->where('e.status', 'active');
                }, 'active_enrollments_count')
                ->orderBy('s.last_name')
                ->orderBy('s.first_name')
                ->get()
                ->map(function ($student) {
                    $student->student_name = trim($student->first_name . ' ' . $student->last_name);
                    $student->contact_email = $student->email ?: $student->user_email;
                    return $student;
                });

            return response()->json([
                'success' => true,
                'instrument_name' => $instrument->instrument_name,
                'students' => $students,
            ]);
        } catch (\Throwable $exception) {
            return $this->serverError('Failed to fetch connected students.', $exception);
        }
    }

    /**
     * Build dashboard statistics for the Instruments page.
     */
    private function buildStats(): array
    {
        $total = DB::table('instrument')->count();
        $active = DB::table('instrument')->where('is_active', true)->count();
        $inactive = DB::table('instrument')->where('is_active', false)->count();
        $system = DB::table('instrument')->where('is_system', true)->count();
        $custom = DB::table('instrument')->where('is_system', false)->count();

        $linkedStudents = DB::table('student')
            ->whereNotNull('instrument_id')
            ->count();

        $activeLinkedStudents = DB::table('student')
            ->whereNotNull('instrument_id')
            ->where('is_active', true)
            ->count();

        $activeLinkedEnrollments = 0;

        if (Schema::hasTable('enrollment') && Schema::hasColumn('enrollment', 'instrument_id')) {
            $activeLinkedEnrollments = DB::table('enrollment')
                ->whereNotNull('instrument_id')
                ->where('status', 'active')
                ->count();
        }

        $categoryCount = DB::table('instrument')
            ->whereNotNull('category')
            ->distinct('category')
            ->count('category');

        $mostUsed = DB::table('instrument as ins')
            ->select('ins.instrument_name')
            ->selectSub(function ($subquery) {
                $subquery->from('student as s')
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('s.instrument_id', 'ins.instrument_id')
                    ->where('s.is_active', true);
            }, 'active_students_count')
            ->orderByDesc('active_students_count')
            ->orderBy('ins.instrument_name')
            ->first();

        return [
            'total' => (int) $total,
            'active' => (int) $active,
            'inactive' => (int) $inactive,
            'system' => (int) $system,
            'custom' => (int) $custom,
            'linked_students' => (int) $linkedStudents,
            'active_linked_students' => (int) $activeLinkedStudents,
            'active_linked_enrollments' => (int) $activeLinkedEnrollments,
            'category_count' => (int) $categoryCount,
            'most_used_name' => $mostUsed && (int) $mostUsed->active_students_count > 0
                ? $mostUsed->instrument_name
                : 'None yet',
            'most_used_count' => $mostUsed ? (int) $mostUsed->active_students_count : 0,
        ];
    }

    /**
     * Build category summary for small visual indicators.
     */
    private function buildCategoryStats()
    {
        return DB::table('instrument')
            ->select(
                DB::raw("COALESCE(category, 'Uncategorized') as category_name"),
                DB::raw('COUNT(*) as total')
            )
            ->groupBy('category_name')
            ->orderByDesc('total')
            ->orderBy('category_name')
            ->get();
    }

    /**
     * Validate instrument form data.
     */
    private function validateInstrument(Request $request, ?int $ignoreInstrumentId = null)
    {
        return Validator::make($request->all(), [
            'instrument_name' => [
                'required',
                'string',
                'max:100',
                function ($attribute, $value, $fail) use ($ignoreInstrumentId) {
                    $query = DB::table('instrument')
                        ->whereRaw('LOWER(instrument_name) = ?', [strtolower(trim($value))]);

                    if ($ignoreInstrumentId) {
                        $query->where('instrument_id', '!=', $ignoreInstrumentId);
                    }

                    if ($query->exists()) {
                        $fail('An instrument with this name already exists.');
                    }
                },
            ],
            'category' => 'required|in:' . implode(',', self::CATEGORIES),
            'description' => 'nullable|string|max:500',
        ]);
    }

    /**
     * Check whether an instrument is connected to active records.
     */
    private function checkInstrumentUsage(int $instrumentId): array
    {
        $activeStudents = DB::table('student')
            ->where('instrument_id', $instrumentId)
            ->where('is_active', true)
            ->count();

        $totalStudents = DB::table('student')
            ->where('instrument_id', $instrumentId)
            ->count();

        $activeEnrollments = 0;
        $totalEnrollments = 0;

        if (Schema::hasTable('enrollment') && Schema::hasColumn('enrollment', 'instrument_id')) {
            $totalEnrollments = DB::table('enrollment')
                ->where('instrument_id', $instrumentId)
                ->count();

            $activeEnrollments = DB::table('enrollment')
                ->where('instrument_id', $instrumentId)
                ->where('status', 'active')
                ->count();
        }

        $inventoryItems = 0;

        if (Schema::hasTable('inventory') && Schema::hasColumn('inventory', 'instrument_id')) {
            $inventoryItems = DB::table('inventory')
                ->where('instrument_id', $instrumentId)
                ->count();
        }

        $blockingParts = [];

        if ($activeStudents > 0) {
            $blockingParts[] = "{$activeStudents} active student(s)";
        }

        if ($activeEnrollments > 0) {
            $blockingParts[] = "{$activeEnrollments} active enrollment(s)";
        }

        $inUse = $activeStudents > 0 || $activeEnrollments > 0;

        return [
            'in_use' => $inUse,
            'message' => $inUse
                ? 'Connected to ' . implode(' and ', $blockingParts) . '.'
                : 'No active student or enrollment is blocking this action.',
            'counts' => [
                'active_students' => (int) $activeStudents,
                'total_students' => (int) $totalStudents,
                'active_enrollments' => (int) $activeEnrollments,
                'total_enrollments' => (int) $totalEnrollments,
                'inventory_items' => (int) $inventoryItems,
            ],
        ];
    }

    /**
     * Return clean validation JSON response.
     */
    private function validationError($validator)
    {
        return response()->json([
            'success' => false,
            'message' => 'Validation failed.',
            'errors' => $validator->errors(),
        ], 422);
    }

    /**
     * Return clean not found JSON response.
     */
    private function notFound()
    {
        return response()->json([
            'success' => false,
            'message' => 'Instrument not found.',
        ], 404);
    }

    /**
     * Return clean server error JSON response.
     */
    private function serverError(string $message, \Throwable $exception)
    {
        return response()->json([
            'success' => false,
            'message' => $message . ' ' . $exception->getMessage(),
        ], 500);
    }

    /**
     * Trim nullable text.
     */
    private function nullableTrim(?string $value): ?string
    {
        $trimmed = trim((string) $value);

        return $trimmed === '' ? null : $trimmed;
    }
}