<?php

// app/Models/PaymentMethod.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    protected $table = 'payment_methods';
    protected $primaryKey = 'method_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'method_name',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Relationship: one payment method can be used by many payment records.
    public function payments()
    {
        return $this->hasMany(Payment::class, 'payment_method_id', 'method_id');
    }
}
