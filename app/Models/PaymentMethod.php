<?php

// app/Models/PaymentMethod.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    /**
     * IMPORTANT:
     * the migration creates payment_methods, not payment_method.
     */
    protected $table = 'payment_methods';
    protected $primaryKey = 'method_id';

    protected $fillable = [
        'method_name',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Relationships
    public function payments()
    {
        return $this->hasMany(Payment::class, 'payment_method_id', 'method_id');
    }
}
