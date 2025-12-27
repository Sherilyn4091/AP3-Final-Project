<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    protected $table = 'inventory';
    protected $primaryKey = 'item_id';
    
    protected $fillable = [
        'item_code',
        'item_name',
        'item_type',
        'brand',
        'model',
        'quantity',
        'unit_of_measure',
        'unit_price',
        'retail_price',
        'low_stock_threshold',
        'reorder_quantity',
        'supplier_id',
        'supplier_product_code',
        'location',
        'warranty_period',
        'last_restocked_date',
        'last_ordered_date',
        'is_active',
    ];
    
    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'retail_price' => 'decimal:2',
        'low_stock_threshold' => 'integer',
        'reorder_quantity' => 'integer',
        'last_restocked_date' => 'date',
        'last_ordered_date' => 'date',
        'is_active' => 'boolean',
    ];
    
    // Relationships
    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id', 'supplier_id');
    }
    
    // Accessor to check if item is low stock
    public function getIsLowStockAttribute()
    {
        return $this->quantity <= $this->low_stock_threshold;
    }
}