<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    /**
     * Display a listing of the inventory items with filters
     */
public function index(Request $request)
{
    $query = Inventory::query()
        ->with(['supplier:supplier_id,supplier_name'])
        ->select([
            'item_id','item_code','item_name','item_type','brand','model',
            'quantity','unit_price','retail_price','low_stock_threshold',
            'supplier_id','is_active','created_at','updated_at'
        ]);

    // Search filter
    if ($search = $request->input('search')) {
        $query->where(function ($q) use ($search) {
            $q->where('item_name', 'ilike', "%{$search}%")
              ->orWhere('item_code', 'ilike', "%{$search}%")
              ->orWhere('brand', 'ilike', "%{$search}%")
              ->orWhere('model', 'ilike', "%{$search}%");
        });
    }

    // Type filter
    if ($type = $request->input('type')) {
        $query->where('item_type', $type);
    }

    // Stock filter (still works even if inactive)
    if ($stock = $request->input('stock_status')) {
        if ($stock === 'available') {
            $query->where('quantity', '>', 0);
        } elseif ($stock === 'low') {
            $query->whereColumn('quantity', '<=', 'low_stock_threshold')
                  ->where('quantity', '>', 0);
        } elseif ($stock === 'out') {
            $query->where('quantity', 0);
        }
    }

    // IMPORTANT: show active FIRST, then recently updated
    $inventoryItems = $query
        ->orderByDesc('is_active')
        ->orderByDesc('updated_at')
        ->paginate(15)
        ->withQueryString();

    // Stats (you can keep these as you want)
    $stats = [
        'total_items' => Inventory::count(),
        'active_items' => Inventory::where('is_active', true)->count(),
        'inactive_items' => Inventory::where('is_active', false)->count(),
        'low_stock' => Inventory::where('is_active', true)
            ->whereColumn('quantity', '<=', 'low_stock_threshold')
            ->where('quantity', '>', 0)
            ->count(),
        'out_of_stock' => Inventory::where('is_active', true)->where('quantity', 0)->count(),
        'total_inventory_value' => Inventory::where('is_active', true)
            ->selectRaw('COALESCE(SUM(quantity * COALESCE(unit_price, 0)), 0) AS total_value')
            ->value('total_value'),
    ];

    $itemTypes = Inventory::select('item_type')->distinct()->orderBy('item_type')->pluck('item_type');

    return view('admin.inventory.index', compact('inventoryItems', 'stats', 'itemTypes'));
}

    /**
     * Show form for creating a new inventory item
     */
   public function create()
{
    $suppliers = Supplier::where('is_active', true)
        ->orderBy('supplier_name')
        ->pluck('supplier_name', 'supplier_id');

    $itemTypes = Inventory::where('is_active', true)
        ->distinct()
        ->orderBy('item_type')
        ->pluck('item_type');

    return view('admin.inventory.create', compact('suppliers', 'itemTypes'));
}

public function store(Request $request)
{
    $validated = $request->validate([
        'item_code'           => 'required|string|max:50|unique:inventory,item_code',
        'item_name'           => 'required|string|max:200',
        'item_type'           => 'required|string|max:100',
        'brand'               => 'nullable|string|max:100',
        'model'               => 'nullable|string|max:100',
        'quantity'            => 'required|integer|min:0',
        'unit_of_measure'     => 'nullable|string|max:50',
        'unit_price'          => 'nullable|numeric|min:0',
        'retail_price'        => 'nullable|numeric|min:0',
        'low_stock_threshold' => 'required|integer|min:0',
        'reorder_quantity'    => 'nullable|integer|min:1',
        'supplier_id'         => 'nullable|exists:supplier,supplier_id',
        'supplier_product_code'=> 'nullable|string|max:100',
        'location'            => 'nullable|string|max:100',
        'warranty_period'     => 'nullable|string|max:100',
        'is_active'           => 'boolean',
    ]);
// IMPORTANT: Force active = true for new items
    $validated['is_active'] = true;  // or $request->boolean('is_active', true);

    $validated['last_restocked_date'] = now()->toDateString();

    $item = Inventory::create($validated);

    $message = 'Item created successfully!';

    if ($request->input('action') === 'save_and_new') {
        return redirect()
            ->route('admin.inventory.create')
            ->with('success', $message);
    }

return redirect()
    ->route('admin.inventory.index')
    ->with('success', 'Item updated successfully!');
}

    // You can add show(), edit(), update(), destroy() methods similarly
    /**
     * Quick API endpoint for low stock items (useful for notifications)
     */
public function edit($id)
{
    $item = Inventory::findOrFail($id);
    $suppliers = Supplier::where('is_active', true)
        ->orderBy('supplier_name')
        ->pluck('supplier_name', 'supplier_id');
    $itemTypes = Inventory::where('is_active', true)
        ->distinct()->orderBy('item_type')->pluck('item_type');

    return view('admin.inventory.edit', compact('item', 'suppliers', 'itemTypes'));
}

public function update(Request $request, $id)
{
    $item = Inventory::findOrFail($id);

    $validated = $request->validate([
        'item_code'           => "required|string|max:50|unique:inventory,item_code,{$id},item_id",
        'item_name'           => 'required|string|max:200',
        'item_type'           => 'required|string|max:100',
        'brand'               => 'nullable|string|max:100',
        'model'               => 'nullable|string|max:100',
        'quantity'            => 'required|integer|min:0',
        'unit_of_measure'     => 'nullable|string|max:50',
        'unit_price'          => 'nullable|numeric|min:0',
        'retail_price'        => 'nullable|numeric|min:0',
        'low_stock_threshold' => 'required|integer|min:0',
        'reorder_quantity'    => 'nullable|integer|min:1',
        'supplier_id'         => 'nullable|exists:supplier,supplier_id',
        'supplier_product_code'=> 'nullable|string|max:100',
        'location'            => 'nullable|string|max:100',
        'warranty_period'     => 'nullable|string|max:100',
        'is_active'           => 'boolean',
    ]);
$item->fill($validated);
    $item->touch(); // <--- This forces updated_at to NOW
    $item->save();


    return redirect()
        ->route('admin.inventory.index', $item)
        ->with('success', 'Item updated successfully!');
}
    public function lowStock()
    {
        $lowStockItems = Inventory::where('quantity', '<=', DB::raw('low_stock_threshold'))
            ->where('is_active', true)
            ->where('quantity', '>', 0) // exclude completely out
            ->orderBy('quantity')
            ->with('supplier:id,supplier_name')
            ->get(['item_id', 'item_name', 'quantity', 'low_stock_threshold', 'supplier_id']);

        return response()->json($lowStockItems);
    }

public function getItemData($id)
{
    // Fetch the item with its supplier relationship
    $item = Inventory::with('supplier')->findOrFail($id);

    // Return as JSON
    return response()->json($item);
}

public function archive(Inventory $inventory)
{
    // Set is_active to false
    $inventory->update(['is_active' => false]);

    return back()->with('success', "Item '{$inventory->item_name}' has been archived.");
}

public function activate(Inventory $inventory)
{
    // Set is_active to true
    $inventory->update(['is_active' => true]);

    return back()->with('success', "Item '{$inventory->item_name}' has been activated.");
}

public function destroy(Inventory $inventory)
{
    // Permanent deletion
    $inventory->delete();

    return redirect()->route('admin.inventory.index')
                     ->with('success', "Item deleted permanently.");
}
}
