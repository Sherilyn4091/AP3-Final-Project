<?php

// app/Http/Controllers/Admin/PaymentMethodController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PaymentMethodController extends Controller
{
    // ============================================================================
    // INDEX - List all payment methods with stats & filters
    // ============================================================================
    public function index(Request $request)
    {
        $query = DB::table('payment_method as pm')
            ->select(
                'pm.*',
                DB::raw('(SELECT COUNT(*) FROM payment WHERE payment_method_id = pm.method_id) as usage_count')
            );

        // Search filter - Fixed parentheses issue
        if ($request->filled('search')) {
            $query->where('pm.method_name', 'ILIKE', "%{$request->search}%");
        }

        // Active/Inactive filter
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('pm.is_active', $request->status === 'active');
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'method_name');
        $sortOrder = $request->input('sort_order', 'asc');

        if ($sortBy === 'usage') {
            $query->orderByRaw('usage_count ' . $sortOrder);
        } else {
            $query->orderBy('pm.method_name', $sortOrder);
        }

        $methods = $query->paginate(15);

        // Statistics
        $stats = [
            'total' => DB::table('payment_method')->count(),
            'active' => DB::table('payment_method')->whereRaw('is_active = TRUE')->count(),
            'inactive' => DB::table('payment_method')->whereRaw('is_active = FALSE')->count(),
            'most_used' => DB::table('payment_method')
                ->select('method_name', DB::raw('COUNT(payment.payment_id) as count'))
                ->leftJoin('payment', 'payment_method.method_id', '=', 'payment.payment_method_id')
                ->groupBy('payment_method.method_name')
                ->orderByDesc('count')
                ->first(),
        ];

        return view('admin.payment-methods.index', compact('methods', 'stats'));
    }

    // ============================================================================
    // STORE NEW METHOD - Returns JSON for modal
    // ============================================================================
    public function store(Request $request)
    {
        // Validation - removed description field
        $validator = Validator::make($request->all(), [
            'method_name' => 'required|string|max:50|unique:payment_method,method_name',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Trim whitespace from method_name
        $methodName = trim($request->method_name);

        DB::table('payment_method')->insert([
            'method_name' => $methodName,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Payment method created successfully'
        ]);
    }

    // ============================================================================
    // EDIT - Get method data for modal (Returns JSON)
    // ============================================================================
    public function edit($id)
    {
        $method = DB::table('payment_method')->where('method_id', $id)->first();
        
        if (!$method) {
            return response()->json(['error' => 'Method not found'], 404);
        }

        return response()->json(['method' => $method]);
    }

    // ============================================================================
    // UPDATE METHOD - Returns JSON for modal
    // ============================================================================
    public function update(Request $request, $id)
    {
        // Validation - fixed to use correct column name and removed description
        $validator = Validator::make($request->all(), [
            'method_name' => 'required|string|max:50|unique:payment_method,method_name,' . $id . ',method_id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Trim whitespace from method_name
        $methodName = trim($request->method_name);

        DB::table('payment_method')->where('method_id', $id)->update([
            'method_name' => $methodName,
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Payment method updated successfully'
        ]);
    }

    // ============================================================================
    // DELETE METHOD - Returns JSON (with usage check)
    // ============================================================================
    public function destroy($id)
    {
        $usage = DB::table('payment')->where('payment_method_id', $id)->count();

        if ($usage > 0) {
            return response()->json([
                'success' => false,
                'message' => "Cannot delete — this method is used in {$usage} payment(s)."
            ], 400);
        }

        DB::table('payment_method')->where('method_id', $id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Payment method deleted successfully'
        ]);
    }

    // ============================================================================
    // TOGGLE ACTIVE STATUS - Fixed bug (was updating method_name instead of is_active)
    // ============================================================================
    public function toggleStatus($id)
    {
        $method = DB::table('payment_method')->where('method_id', $id)->first();
        
        if (!$method) {
            return response()->json(['error' => 'Method not found'], 404);
        }

        // Fixed: Toggle is_active instead of updating method_name
        DB::table('payment_method')->where('method_id', $id)->update([
            'is_active' => !$method->is_active,
            'updated_at' => now(),
        ]);

        return response()->json(['success' => true]);
    }

    // ============================================================================
    // GET USAGE COUNT (for delete prevention)
    // ============================================================================
    public function getUsage($id)
    {
        $count = DB::table('payment')->where('payment_method_id', $id)->count();
        return response()->json(['usage_count' => $count]);
    }
}