<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $query = Supplier::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('supplier_name', 'ilike', "%{$search}%")
                    ->orWhere('supplier_code', 'ilike', "%{$search}%")
                    ->orWhere('contact_person', 'ilike', "%{$search}%");
            });
        }

        $suppliers = $query->orderByDesc('updated_at')
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'total'    => Supplier::count(),
            'active'   => Supplier::where('is_active', true)->count(),
            'inactive' => Supplier::where('is_active', false)->count(),
        ];

        return view('admin.suppliers.index', compact('suppliers', 'stats'));
    }

    public function create()
    {
        return view('admin.suppliers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_name'    => 'required|string|max:200',
            'supplier_code'    => 'required|string|max:50|unique:supplier,supplier_code',
            'contact_person'   => 'nullable|string|max:200',
            'contact_position' => 'nullable|string|max:100',
            'email'            => 'nullable|email:rfc,dns|max:255',
            'phone'            => 'nullable|string|max:11',
            'website'          => 'nullable|string|max:255',
            'address_line1'    => 'required|string|max:255',
            'address_line2'    => 'nullable|string|max:255',
            'city'             => 'required|string|max:100',
            'province'         => 'nullable|string|max:100',
            'postal_code'      => 'required|string|max:20',
            'country'          => 'required|string|max:100',
            'payment_terms'    => 'nullable|string|max:200',
            'delivery_terms'   => 'nullable|string|max:200',
            'rating'           => 'nullable|numeric|min:0|max:5',
            'is_active'        => 'required|boolean',
            'notes'            => 'nullable|string',
        ]);

        $supplier = Supplier::create($validated);

        return redirect()->route('admin.suppliers.index')
            ->with('success', 'New supplier added successfully!')
            ->with('highlight_id', $supplier->supplier_id);
    }

    public function edit($id)
    {
        $supplier = Supplier::findOrFail($id);
        return view('admin.suppliers.edit', compact('supplier'));
    }

   public function update(Request $request, $id)
{
    $supplier = Supplier::findOrFail($id);

    $validated = $request->validate([
        'supplier_name'  => 'required|string|max:200',
        'supplier_code'  => 'required|string|max:50',
        'contact_person' => 'nullable|string|max:200',
        'contact_position' => 'nullable|string|max:100',
        'email' => 'nullable|email:rfc,dns|max:255',
        'phone'          => 'nullable|string|max:11',
        'website'        => 'nullable|string|max:255',
        'address_line1'  => 'nullable|string|max:255',
        'address_line2'  => 'nullable|string|max:255',
        'city'           => 'required|string|max:100',
        'province'       => 'nullable|string|max:100',
        'postal_code'    => 'required|string|max:20',
        'country'        => 'required|string|max:100',
        'payment_terms'  => 'nullable|string|max:200',
        'delivery_terms'=> 'nullable|string|max:200',
        'rating'         => 'nullable|numeric|min:0|max:5',
        'is_active'      => 'required|boolean',
        'notes'          => 'nullable|string',
    ]);

    $supplier->update($validated);
    $supplier->touch(); // guarantees updated_at changes

    return redirect()
        ->route('admin.suppliers.index')
        ->with('success', 'Supplier updated successfully!')
        ->with('highlight_id', $supplier->supplier_id);
}

    public function destroy($id)
    {
        $supplier = Supplier::findOrFail($id);
        $supplier->delete();

        return redirect()->route('admin.suppliers.index')
            ->with('success', 'Supplier deleted successfully!');
    }
}