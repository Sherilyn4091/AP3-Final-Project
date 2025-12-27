<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $table = 'supplier';
    protected $primaryKey = 'supplier_id';
    
    protected $fillable = [
        'supplier_name',
        'supplier_code',
        'contact_person',
        'contact_position',
        'email',
        'phone',
        'website',
        'address_line1',
        'address_line2',
        'city',
        'province',
        'postal_code',
        'country',
        'products_supplied',
        'product_categories',
        'payment_terms',
        'delivery_terms',
        'minimum_order_amount',
        'rating',
        'total_orders',
        'last_order_date',
        'is_active',
        'notes',
    ];
    
    protected $casts = [
        'minimum_order_amount' => 'decimal:2',
        'rating' => 'decimal:2',
        'total_orders' => 'integer',
        'last_order_date' => 'date',
        'is_active' => 'boolean',
    ];
    
    // Relationships
    public function inventoryItems()
    {
        return $this->hasMany(Inventory::class, 'supplier_id', 'supplier_id');
    }
}