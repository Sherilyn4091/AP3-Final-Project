<?php

// app/Http/Controllers/Admin/PaymentMethodController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

class PaymentMethodController extends Controller
{
    // ============================================================================
    // INDEX - List payment methods with usage counts, filters, sorting, and stats
    // ============================================================================
    public function index(Request $request)
    {
        $allowedSorts = ['method_name', 'created_at', 'usage'];
        $allowedOrders = ['asc', 'desc'];

        $sortBy = $request->input('sort_by', 'method_name');
        $sortOrder = strtolower($request->input('sort_order', 'asc'));

        if (!in_array($sortBy, $allowedSorts, true)) {
            $sortBy = 'method_name';
        }

        if (!in_array($sortOrder, $allowedOrders, true)) {
            $sortOrder = 'asc';
        }

        $query = DB::table('payment_methods as pm')
            ->select(
                'pm.*',
                DB::raw('(SELECT COUNT(*) FROM payment WHERE payment.payment_method_id = pm.method_id) as usage_count')
            );

        // Search by method name or description.
        if ($request->filled('search')) {
            $search = trim($request->input('search'));

            $query->where(function ($q) use ($search) {
                $q->where('pm.method_name', 'ILIKE', "%{$search}%");

                if (Schema::hasColumn('payment_methods', 'description')) {
                    $q->orWhere('pm.description', 'ILIKE', "%{$search}%");
                }
            });
        }

        // Filter by active/inactive status.
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('pm.is_active', $request->status === 'active');
        }

        // Safe sorting.
        if ($sortBy === 'usage') {
            $query->orderByRaw('usage_count ' . $sortOrder);
        } elseif ($sortBy === 'created_at') {
            $query->orderBy('pm.created_at', $sortOrder);
        } else {
            $query->orderBy('pm.method_name', $sortOrder);
        }

        $methods = $query->paginate(15)->withQueryString();

        $stats = [
            'total' => DB::table('payment_methods')->count(),
            'active' => DB::table('payment_methods')->where('is_active', true)->count(),
            'inactive' => DB::table('payment_methods')->where('is_active', false)->count(),
            'most_used' => DB::table('payment_methods')
                ->select(
                    'payment_methods.method_id',
                    'payment_methods.method_name',
                    DB::raw('COUNT(payment.payment_id) as count')
                )
                ->leftJoin('payment', 'payment_methods.method_id', '=', 'payment.payment_method_id')
                ->groupBy('payment_methods.method_id', 'payment_methods.method_name')
                ->orderByDesc('count')
                ->orderBy('payment_methods.method_name')
                ->first(),
        ];

        return view('admin.payment-methods.index', compact('methods', 'stats'));
    }

    // ============================================================================
    // STORE - Create payment method from modal form
    // ============================================================================
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'method_name' => 'required|string|max:50',
            'description' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $methodName = $this->cleanMethodName($request->input('method_name'));
        $description = $this->cleanDescription($request->input('description'));

        if ($this->methodNameExists($methodName)) {
            return response()->json([
                'success' => false,
                'errors' => [
                    'method_name' => ['This payment method already exists.'],
                ],
            ], 422);
        }

        $data = [
            'method_name' => $methodName,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        if (Schema::hasColumn('payment_methods', 'description')) {
            $data['description'] = $description;
        }

        DB::table('payment_methods')->insert($data);

        return response()->json([
            'success' => true,
            'message' => 'Payment method created successfully.',
        ]);
    }

    // ============================================================================
    // EDIT - Return payment method data for modal
    // ============================================================================
    public function edit($id)
    {
        $method = DB::table('payment_methods')->where('method_id', $id)->first();

        if (!$method) {
            return response()->json([
                'success' => false,
                'message' => 'Payment method not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'method' => $method,
        ]);
    }

    // ============================================================================
    // UPDATE - Update payment method from modal form
    // ============================================================================
    public function update(Request $request, $id)
    {
        $method = DB::table('payment_methods')->where('method_id', $id)->first();

        if (!$method) {
            return response()->json([
                'success' => false,
                'message' => 'Payment method not found.',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'method_name' => 'required|string|max:50',
            'description' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $methodName = $this->cleanMethodName($request->input('method_name'));
        $description = $this->cleanDescription($request->input('description'));

        if ($this->methodNameExists($methodName, $id)) {
            return response()->json([
                'success' => false,
                'errors' => [
                    'method_name' => ['This payment method already exists.'],
                ],
            ], 422);
        }

        $data = [
            'method_name' => $methodName,
            'updated_at' => now(),
        ];

        if (Schema::hasColumn('payment_methods', 'description')) {
            $data['description'] = $description;
        }

        DB::table('payment_methods')->where('method_id', $id)->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Payment method updated successfully.',
        ]);
    }

    // ============================================================================
    // DESTROY - Delete only if not used by payments
    // ============================================================================
    public function destroy($id)
    {
        $method = DB::table('payment_methods')->where('method_id', $id)->first();

        if (!$method) {
            return response()->json([
                'success' => false,
                'message' => 'Payment method not found.',
            ], 404);
        }

        $usage = DB::table('payment')->where('payment_method_id', $id)->count();

        if ($usage > 0) {
            return response()->json([
                'success' => false,
                'message' => "Cannot delete â€” this method is used in {$usage} payment(s).",
            ], 400);
        }

        DB::table('payment_methods')->where('method_id', $id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Payment method deleted successfully.',
        ]);
    }

    // ============================================================================
    // TOGGLE STATUS - Activate/deactivate payment method
    // ============================================================================
    public function toggleStatus($id)
    {
        $method = DB::table('payment_methods')->where('method_id', $id)->first();

        if (!$method) {
            return response()->json([
                'success' => false,
                'message' => 'Payment method not found.',
            ], 404);
        }

        DB::table('payment_methods')->where('method_id', $id)->update([
            'is_active' => !((bool) $method->is_active),
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Payment method status updated successfully.',
        ]);
    }

    // ============================================================================
    // USAGE - Return usage count for safety checks
    // ============================================================================
    public function getUsage($id)
    {
        $count = DB::table('payment')->where('payment_method_id', $id)->count();

        return response()->json([
            'success' => true,
            'usage_count' => $count,
        ]);
    }

    // ============================================================================
    // HELPERS
    // ============================================================================

    private function cleanMethodName(?string $value): string
    {
        return preg_replace('/\s+/', ' ', trim((string) $value));
    }

    private function cleanDescription(?string $value): ?string
    {
        $cleaned = trim((string) $value);

        return $cleaned === '' ? null : $cleaned;
    }

    private function methodNameExists(string $methodName, ?int $ignoreId = null): bool
    {
        $query = DB::table('payment_methods')
            ->whereRaw('LOWER(method_name) = ?', [strtolower($methodName)]);

        if ($ignoreId) {
            $query->where('method_id', '<>', $ignoreId);
        }

        return $query->exists();
    }
}
