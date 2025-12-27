<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    protected $table = 'payment_method';
    protected $primaryKey = 'method_id';
    
    protected $fillable = [
        'method_name',
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