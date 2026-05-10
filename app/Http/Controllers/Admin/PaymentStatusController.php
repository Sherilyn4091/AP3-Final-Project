<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

/**
 * ============================================================================
 * PAYMENT STATUS CONTROLLER
 * ============================================================================
 *
 * Handles admin management of payment status labels such as Pending, Paid,
 * Cancelled, and other record-keeping states.
 *
 * Audit-safe revisions:
 * - Keeps CRUD and AJAX JSON responses.
 * - Checks payment usage before deletion.
 * - Avoids errors if the payment table is unavailable.
 * - Keeps sorting, search, active/inactive filtering, and usage counts clean.
 * ============================================================================
 */
class PaymentStatusController extends Controller
{
    /**
     * Display payment statuses with filters, sorting, usage counts, and stats.
     */
    public function index(Request $request)
    {
        $query = DB::table('payment_status as ps');

        if (Schema::hasTable('payment')) {
            $usageSubquery = DB::table('payment')
                ->select('payment_status_id', DB::raw('COUNT(*) as usage_count'))
                ->whereNotNull('payment_status_id')
                ->groupBy('payment_status_id');

            $query->leftJoinSub($usageSubquery, 'payment_usage', function ($join) {
                $join->on('ps.status_id', '=', 'payment_usage.payment_status_id');
            })
            ->select('ps.*', DB::raw('COALESCE(payment_usage.usage_count, 0) as usage_count'));
        } else {
            $query->select('ps.*', DB::raw('0 as usage_count'));
        }

        if ($request->filled('search')) {
            $search = trim($request->input('search'));

            $query->where('ps.status_name', 'ILIKE', "%{$search}%");
        }

        if ($request->input('status') === 'active') {
            $query->where('ps.is_active', true);
        }

        if ($request->input('status') === 'inactive') {
            $query->where('ps.is_active', false);
        }

        match ($request->input('sort_by')) {
            'usage' => $query->orderByDesc('usage_count')->orderBy('ps.status_name'),
            'newest' => $query->orderByDesc('ps.created_at'),
            'oldest' => $query->orderBy('ps.created_at'),
            default => $query->orderBy('ps.status_name'),
        };

        $statuses = $query->paginate(15)->withQueryString();

        $mostUsedStatus = $this->getMostUsedStatus();

        $stats = [
            'total' => DB::table('payment_status')->count(),
            'active' => DB::table('payment_status')->where('is_active', true)->count(),
            'inactive' => DB::table('payment_status')->where('is_active', false)->count(),
            'most_used_name' => $mostUsedStatus?->status_name ?? 'N/A',
            'most_used_count' => (int) ($mostUsedStatus?->usage_count ?? 0),
        ];

        return view('admin.payment-statuses.index', compact('statuses', 'stats'));
    }

    /**
     * Store a new payment status.
     */
    public function store(Request $request)
    {
        $this->normalizeStatusName($request);

        $validator = $this->statusValidator($request);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        try {
            $statusId = DB::table('payment_status')->insertGetId([
                'status_name' => $request->input('status_name'),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ], 'status_id');

            $status = DB::table('payment_status')
                ->where('status_id', $statusId)
                ->first();

            return response()->json([
                'success' => true,
                'message' => 'Payment status added successfully.',
                'status' => $status,
            ]);
        } catch (\Throwable $e) {
            return $this->serverError('Failed to add payment status.', $e);
        }
    }

    /**
     * Update an existing payment status.
     */
    public function update(Request $request, $id)
    {
        $this->normalizeStatusName($request);

        $validator = $this->statusValidator($request, (int) $id);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        try {
            $updated = DB::table('payment_status')
                ->where('status_id', $id)
                ->update([
                    'status_name' => $request->input('status_name'),
                    'updated_at' => now(),
                ]);

            if ($updated === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment status not found.',
                ], 404);
            }

            $status = DB::table('payment_status')
                ->where('status_id', $id)
                ->first();

            return response()->json([
                'success' => true,
                'message' => 'Payment status updated successfully.',
                'status' => $status,
            ]);
        } catch (\Throwable $e) {
            return $this->serverError('Failed to update payment status.', $e);
        }
    }

    /**
     * Delete a payment status only when it is not used by payments.
     */
    public function destroy($id)
    {
        try {
            $usageCount = $this->getUsageCount((int) $id);

            if ($usageCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => "Cannot delete. This status is used in {$usageCount} " . ($usageCount === 1 ? 'payment.' : 'payments.'),
                ], 422);
            }

            $deleted = DB::table('payment_status')
                ->where('status_id', $id)
                ->delete();

            if ($deleted === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment status not found.',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Payment status deleted successfully.',
            ]);
        } catch (\Throwable $e) {
            return $this->serverError('Failed to delete payment status.', $e);
        }
    }

    /**
     * Toggle active/inactive status.
     */
    public function toggleStatus($id)
    {
        try {
            $status = DB::table('payment_status')
                ->where('status_id', $id)
                ->first();

            if (!$status) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment status not found.',
                ], 404);
            }

            DB::table('payment_status')
                ->where('status_id', $id)
                ->update([
                    'is_active' => ! (bool) $status->is_active,
                    'updated_at' => now(),
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment status availability updated successfully.',
            ]);
        } catch (\Throwable $e) {
            return $this->serverError('Failed to update payment status availability.', $e);
        }
    }

    /**
     * Return payment usage count for AJAX delete checking.
     */
    public function getUsage($id)
    {
        return response()->json([
            'success' => true,
            'usage_count' => $this->getUsageCount((int) $id),
        ]);
    }

    /**
     * Normalize user input before validation.
     */
    private function normalizeStatusName(Request $request): void
    {
        $request->merge([
            'status_name' => trim((string) $request->input('status_name')),
        ]);
    }

    /**
     * Build reusable validation rules.
     */
    private function statusValidator(Request $request, ?int $ignoreId = null)
    {
        return Validator::make($request->all(), [
            'status_name' => [
                'required',
                'string',
                'max:50',
                Rule::unique('payment_status', 'status_name')->ignore($ignoreId, 'status_id'),
            ],
        ]);
    }

    /**
     * Count how many payment records are using a status.
     */
    private function getUsageCount(int $statusId): int
    {
        if (!Schema::hasTable('payment')) {
            return 0;
        }

        return DB::table('payment')
            ->where('payment_status_id', $statusId)
            ->count();
    }

    /**
     * Get the most used payment status.
     */
    private function getMostUsedStatus(): ?object
    {
        if (!Schema::hasTable('payment')) {
            return null;
        }

        return DB::table('payment as p')
            ->join('payment_status as ps', 'ps.status_id', '=', 'p.payment_status_id')
            ->select('ps.status_name', DB::raw('COUNT(*) as usage_count'))
            ->groupBy('ps.status_id', 'ps.status_name')
            ->orderByDesc('usage_count')
            ->first();
    }

    /**
     * JSON validation error response.
     */
    private function validationError($validator)
    {
        return response()->json([
            'success' => false,
            'errors' => $validator->errors(),
            'message' => 'Please check the form fields.',
        ], 422);
    }

    /**
     * JSON server error response.
     */
    private function serverError(string $message, \Throwable $e)
    {
        return response()->json([
            'success' => false,
            'message' => $message . ' ' . $e->getMessage(),
        ], 500);
    }
}