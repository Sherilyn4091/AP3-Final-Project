<?php

//app/Models/Supplier.php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $table = 'supplier';
    protected $primaryKey = 'supplier_id';
    public $timestamps = true;

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
        'payment_terms',
        'delivery_terms',
        'rating',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'rating' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function inventoryItems()
    {
        return $this->hasMany(Inventory::class, 'supplier_id', 'supplier_id');
    }
}