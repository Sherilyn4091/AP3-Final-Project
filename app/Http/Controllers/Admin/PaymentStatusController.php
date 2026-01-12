<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * ============================================================================
 * PAYMENT STATUS CONTROLLER
 * Manages payment status labels (Pending, Paid, Cancelled, etc.)
 * Used for record-keeping when manually recording student payments
 * ============================================================================
 */
class PaymentStatusController extends Controller
{
    /**
     * Display payment statuses list with statistics
     */
    public function index(Request $request)
    {
        $query = DB::table('payment_status as ps')
            ->leftJoin(DB::raw('(SELECT payment_status_id, COUNT(*) as usage_count FROM payment GROUP BY payment_status_id) as p'), 
                      'ps.status_id', '=', 'p.payment_status_id')
            ->select('ps.*', DB::raw('COALESCE(p.usage_count, 0) as usage_count'))
            ->orderBy('ps.status_name', 'asc');

        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('ps.status_name', 'ILIKE', "%{$search}%");
        }

        // Apply status filter
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('ps.is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('ps.is_active', false);
            }
        }

        // Apply sorting
        if ($request->filled('sort_by')) {
            switch ($request->sort_by) {
                case 'usage':
                    $query->orderBy('usage_count', 'desc');
                    break;
                case 'newest':
                    $query->orderBy('ps.created_at', 'desc');
                    break;
                case 'oldest':
                    $query->orderBy('ps.created_at', 'asc');
                    break;
                default:
                    $query->orderBy('ps.status_name', 'asc');
            }
        }

        $statuses = $query->paginate(15)->withQueryString();

        // Calculate statistics
        $stats = [
            'total' => DB::table('payment_status')->count(),
            'active' => DB::table('payment_status')->where('is_active', true)->count(),
            'inactive' => DB::table('payment_status')->where('is_active', false)->count(),
        ];

        // Get most used status
        $mostUsed = DB::table('payment')
            ->select('payment_status_id', DB::raw('COUNT(*) as count'))
            ->groupBy('payment_status_id')
            ->orderBy('count', 'desc')
            ->first();

        if ($mostUsed) {
            $mostUsedStatus = DB::table('payment_status')
                ->where('status_id', $mostUsed->payment_status_id)
                ->first();
            $stats['most_used_name'] = $mostUsedStatus->status_name ?? 'N/A';
            $stats['most_used_count'] = $mostUsed->count;
        } else {
            $stats['most_used_name'] = 'N/A';
            $stats['most_used_count'] = 0;
        }

        return view('admin.payment-statuses.index', compact('statuses', 'stats'));
    }

    /**
     * Store a new payment status
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'status_name' => 'required|string|max:50|unique:payment_status,status_name',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $statusId = DB::table('payment_status')->insertGetId([
                'status_name' => trim($request->status_name),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ], 'status_id');  // Specify the primary key column name

            $status = DB::table('payment_status')->where('status_id', $statusId)->first();

            return response()->json([
                'success' => true,
                'message' => 'Payment status added successfully',
                'status' => $status
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add payment status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update an existing payment status
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status_name' => 'required|string|max:50|unique:payment_status,status_name,' . $id . ',status_id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::table('payment_status')
                ->where('status_id', $id)
                ->update([
                    'status_name' => trim($request->status_name),
                    'updated_at' => now(),
                ]);

            $status = DB::table('payment_status')->where('status_id', $id)->first();

            return response()->json([
                'success' => true,
                'message' => 'Payment status updated successfully',
                'status' => $status
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update payment status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a payment status (only if not used in any payment)
     */
    public function destroy($id)
    {
        try {
            // Check if status is used in any payment
            $usageCount = DB::table('payment')
                ->where('payment_status_id', $id)
                ->count();

            if ($usageCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => "Cannot delete — used in {$usageCount} " . ($usageCount === 1 ? 'payment' : 'payments')
                ], 422);
            }

            DB::table('payment_status')->where('status_id', $id)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Payment status deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete payment status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle active/inactive status
     */
    public function toggleStatus($id)
    {
        try {
            $status = DB::table('payment_status')->where('status_id', $id)->first();

            if (!$status) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment status not found'
                ], 404);
            }

            DB::table('payment_status')
                ->where('status_id', $id)
                ->update([
                    'is_active' => !$status->is_active,
                    'updated_at' => now(),
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Status updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to toggle status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get usage details for a payment status
     */
    public function getUsage($id)
    {
        try {
            $usageCount = DB::table('payment')
                ->where('payment_status_id', $id)
                ->count();

            return response()->json([
                'success' => true,
                'usage_count' => $usageCount
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch usage details'
            ], 500);
        }
    }
}