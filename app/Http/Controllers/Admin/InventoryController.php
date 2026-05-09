<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/*
|--------------------------------------------------------------------------
| InventoryController
|--------------------------------------------------------------------------
|
| Handles the admin inventory module:
| - list inventory items
| - create inventory items
| - edit/update inventory items
| - archive/activate/delete items
| - export inventory to CSV
| - show low-stock inventory view
| - provide low-stock JSON data
|
| Notes:
| - This controller keeps actions separated into small methods.
| - This avoids long method, duplicated code, and route/controller mismatch.
|
*/

class InventoryController extends Controller
{
    /**
     * Display inventory items with filters and dashboard statistics.
     */
    public function index(Request $request)
    {
        $query = Inventory::query()
            ->with(['supplier:supplier_id,supplier_name'])
            ->select([
                'item_id',
                'item_code',
                'item_name',
                'item_type',
                'brand',
                'model',
                'quantity',
                'unit_price',
                'retail_price',
                'low_stock_threshold',
                'supplier_id',
                'is_active',
                'created_at',
                'updated_at',
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

        // Item type filter
        if ($type = $request->input('type')) {
            $query->where('item_type', $type);
        }

        // Stock status filter
        if ($stock = $request->input('stock_status')) {
            if ($stock === 'available') {
                $query->where('quantity', '>', 0);
            }

            if ($stock === 'low') {
                $query->whereColumn('quantity', '<=', 'low_stock_threshold')
                    ->where('quantity', '>', 0);
            }

            if ($stock === 'out') {
                $query->where('quantity', 0);
            }
        }

        // Show active items first, then recently updated items.
        $inventoryItems = $query
            ->orderByDesc('is_active')
            ->orderByDesc('updated_at')
            ->paginate(15)
            ->withQueryString();

        // Dashboard statistics
        $stats = [
            'total_items' => Inventory::count(),

            'active_items' => Inventory::where('is_active', true)->count(),

            'inactive_items' => Inventory::where('is_active', false)->count(),

            'low_stock' => Inventory::where('is_active', true)
                ->whereColumn('quantity', '<=', 'low_stock_threshold')
                ->where('quantity', '>', 0)
                ->count(),

            'out_of_stock' => Inventory::where('is_active', true)
                ->where('quantity', 0)
                ->count(),

            'total_inventory_value' => Inventory::where('is_active', true)
                ->selectRaw('COALESCE(SUM(quantity * COALESCE(unit_price, 0)), 0) AS total_value')
                ->value('total_value'),
        ];

        $itemTypes = Inventory::select('item_type')
            ->distinct()
            ->orderBy('item_type')
            ->pluck('item_type');

        return view('admin.inventory.index', compact('inventoryItems', 'stats', 'itemTypes'));
    }

    /**
     * Show the create inventory item form.
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

    /**
     * Store a newly created inventory item.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'item_code' => 'required|string|max:50|unique:inventory,item_code',
            'item_name' => 'required|string|max:200',
            'item_type' => 'required|string|max:100',
            'brand' => 'nullable|string|max:100',
            'model' => 'nullable|string|max:100',
            'quantity' => 'required|integer|min:0',
            'unit_of_measure' => 'nullable|string|max:50',
            'unit_price' => 'nullable|numeric|min:0',
            'retail_price' => 'nullable|numeric|min:0',
            'low_stock_threshold' => 'required|integer|min:0',
            'reorder_quantity' => 'nullable|integer|min:1',
            'supplier_id' => 'nullable|exists:supplier,supplier_id',
            'supplier_product_code' => 'nullable|string|max:100',
            'location' => 'nullable|string|max:100',
            'warranty_period' => 'nullable|string|max:100',
            'is_active' => 'boolean',
        ]);

        // New items should be active by default.
        $validated['is_active'] = true;

        // Mark the creation date as the latest restock date.
        $validated['last_restocked_date'] = now()->toDateString();

        Inventory::create($validated);

        $message = 'Item created successfully!';

        if ($request->input('action') === 'save_and_new') {
            return redirect()
                ->route('admin.inventory.create')
                ->with('success', $message);
        }

        return redirect()
            ->route('admin.inventory.index')
            ->with('success', $message);
    }

    /**
     * Show the edit inventory item form.
     */
    public function edit($id)
    {
        $item = Inventory::findOrFail($id);

        $suppliers = Supplier::where('is_active', true)
            ->orderBy('supplier_name')
            ->pluck('supplier_name', 'supplier_id');

        $itemTypes = Inventory::where('is_active', true)
            ->distinct()
            ->orderBy('item_type')
            ->pluck('item_type');

        return view('admin.inventory.edit', compact('item', 'suppliers', 'itemTypes'));
    }

    /**
     * Update an inventory item.
     */
    public function update(Request $request, $id)
    {
        $item = Inventory::findOrFail($id);

        $validated = $request->validate([
            'item_code' => "required|string|max:50|unique:inventory,item_code,{$id},item_id",
            'item_name' => 'required|string|max:200',
            'item_type' => 'required|string|max:100',
            'brand' => 'nullable|string|max:100',
            'model' => 'nullable|string|max:100',
            'quantity' => 'required|integer|min:0',
            'unit_of_measure' => 'nullable|string|max:50',
            'unit_price' => 'nullable|numeric|min:0',
            'retail_price' => 'nullable|numeric|min:0',
            'low_stock_threshold' => 'required|integer|min:0',
            'reorder_quantity' => 'nullable|integer|min:1',
            'supplier_id' => 'nullable|exists:supplier,supplier_id',
            'supplier_product_code' => 'nullable|string|max:100',
            'location' => 'nullable|string|max:100',
            'warranty_period' => 'nullable|string|max:100',
            'is_active' => 'boolean',
        ]);

        $item->fill($validated);
        $item->touch();
        $item->save();

        return redirect()
            ->route('admin.inventory.index')
            ->with('success', 'Item updated successfully!');
    }

    /**
     * Show low-stock items by redirecting to the inventory page
     * with the low-stock filter applied.
     */
    public function lowStockView()
    {
        return redirect()->route('admin.inventory.index', [
            'stock_status' => 'low',
        ]);
    }

    /**
     * Quick JSON endpoint for low-stock items.
     */
    public function lowStock()
    {
        $lowStockItems = Inventory::whereColumn('quantity', '<=', 'low_stock_threshold')
            ->where('is_active', true)
            ->where('quantity', '>', 0)
            ->orderBy('quantity')
            ->with('supplier:supplier_id,supplier_name')
            ->get([
                'item_id',
                'item_name',
                'quantity',
                'low_stock_threshold',
                'supplier_id',
            ]);

        return response()->json($lowStockItems);
    }

    /**
     * Export inventory records to a CSV file.
     */
    public function export(Request $request)
    {
        $fileName = 'inventory-export-' . now()->format('Y-m-d_His') . '.csv';

        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');

            // CSV header row
            fputcsv($handle, [
                'Item Code',
                'Item Name',
                'Type',
                'Brand',
                'Model',
                'Quantity',
                'Unit Price',
                'Retail Price',
                'Low Stock Threshold',
                'Supplier',
                'Status',
                'Created At',
                'Updated At',
            ]);

            Inventory::with('supplier:supplier_id,supplier_name')
                ->orderBy('item_name')
                ->chunk(200, function ($items) use ($handle) {
                    foreach ($items as $item) {
                        fputcsv($handle, [
                            $item->item_code,
                            $item->item_name,
                            $item->item_type,
                            $item->brand,
                            $item->model,
                            $item->quantity,
                            $item->unit_price,
                            $item->retail_price,
                            $item->low_stock_threshold,
                            optional($item->supplier)->supplier_name,
                            $item->is_active ? 'Active' : 'Inactive',
                            $item->created_at,
                            $item->updated_at,
                        ]);
                    }
                });

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv',
        ]);
    }

    /**
     * Fetch one inventory item as JSON.
     */
    public function getItemData($id)
    {
        $item = Inventory::with('supplier')->findOrFail($id);

        return response()->json($item);
    }

    /**
     * Archive an inventory item.
     */
    public function archive(Inventory $inventory)
    {
        $inventory->update([
            'is_active' => false,
        ]);

        return back()->with('success', "Item '{$inventory->item_name}' has been archived.");
    }

    /**
     * Activate an archived inventory item.
     */
    public function activate(Inventory $inventory)
    {
        $inventory->update([
            'is_active' => true,
        ]);

        return back()->with('success', "Item '{$inventory->item_name}' has been activated.");
    }

    /**
     * Permanently delete an inventory item.
     */
    public function destroy(Inventory $inventory)
    {
        $inventory->delete();

        return redirect()
            ->route('admin.inventory.index')
            ->with('success', 'Item deleted permanently.');
    }
}