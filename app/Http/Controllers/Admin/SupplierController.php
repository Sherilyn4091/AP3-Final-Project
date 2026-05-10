<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * ============================================================================
 * SUPPLIER CONTROLLER
 * ============================================================================
 *
 * Handles supplier listing, searching, creation, updating, and safe deletion.
 * A supplier cannot be deleted when inventory items are still connected to it.
 * ============================================================================
 */
class SupplierController extends Controller
{
    /**
     * Display suppliers with search, inventory relationship count, and statistics.
     */
    public function index(Request $request)
    {
        $query = Supplier::query()
            ->withCount('inventoryItems');

        if ($search = trim((string) $request->input('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('supplier_name', 'ilike', "%{$search}%")
                    ->orWhere('supplier_code', 'ilike', "%{$search}%")
                    ->orWhere('contact_person', 'ilike', "%{$search}%")
                    ->orWhere('email', 'ilike', "%{$search}%")
                    ->orWhere('phone', 'ilike', "%{$search}%");
            });
        }

        $suppliers = $query->orderByDesc('is_active')
            ->orderBy('supplier_name')
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'total' => Supplier::count(),
            'active' => Supplier::where('is_active', true)->count(),
            'inactive' => Supplier::where('is_active', false)->count(),
        ];

        return view('admin.suppliers.index', compact('suppliers', 'stats'));
    }

    /**
     * Show create supplier form.
     */
    public function create()
    {
        return view('admin.suppliers.create');
    }

    /**
     * Store a new supplier.
     */
    public function store(Request $request)
    {
        $validated = $this->validatedSupplierData($request);

        $supplier = Supplier::create($validated);

        return redirect()
            ->route('admin.suppliers.index')
            ->with('success', 'New supplier added successfully.')
            ->with('highlight_id', $supplier->supplier_id);
    }

    /**
     * Show edit supplier form.
     */
    public function edit($id)
    {
        $supplier = Supplier::findOrFail($id);

        return view('admin.suppliers.edit', compact('supplier'));
    }

    /**
     * Update an existing supplier.
     */
    public function update(Request $request, $id)
    {
        $supplier = Supplier::findOrFail($id);

        $validated = $this->validatedSupplierData($request, $supplier->supplier_id);

        $supplier->update($validated);

        return redirect()
            ->route('admin.suppliers.index')
            ->with('success', 'Supplier updated successfully.')
            ->with('highlight_id', $supplier->supplier_id);
    }

    /**
     * Delete supplier only if no inventory items are connected.
     */
    public function destroy($id)
    {
        $supplier = Supplier::findOrFail($id);

        if ($supplier->inventoryItems()->exists()) {
            return redirect()
                ->route('admin.suppliers.index')
                ->with('error', 'Cannot delete this supplier because inventory items are still connected to it. Edit the supplier and set it inactive instead.');
        }

        $supplier->delete();

        return redirect()
            ->route('admin.suppliers.index')
            ->with('success', 'Supplier deleted successfully.');
    }

    /**
     * Shared validation for create and update.
     */
    private function validatedSupplierData(Request $request, ?int $supplierId = null): array
    {
        return $request->validate([
            'supplier_name' => ['required', 'string', 'max:200'],
            'supplier_code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('supplier', 'supplier_code')->ignore($supplierId, 'supplier_id'),
            ],
            'contact_person' => ['nullable', 'string', 'max:200'],
            'contact_position' => ['nullable', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:11'],
            'website' => ['nullable', 'string', 'max:255'],
            'address_line1' => ['required', 'string', 'max:255'],
            'address_line2' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'province' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['required', 'string', 'max:20'],
            'country' => ['required', 'string', 'max:100'],
            'payment_terms' => ['nullable', 'string', 'max:200'],
            'delivery_terms' => ['nullable', 'string', 'max:200'],
            'rating' => ['nullable', 'numeric', 'min:0', 'max:5'],
            'is_active' => ['required', 'boolean'],
            'notes' => ['nullable', 'string'],
        ]);
    }
}