<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentStatus extends Model
{
    protected $table = 'payment_status';
    protected $primaryKey = 'status_id';
    
    protected $fillable = [
        'status_name',
        'is_active',
    ];
    
    protected $casts = [
        'is_active' => 'boolean',
    ];
    
    // Relationships
    public function payments()
    {
        return $this->hasMany(Payment::class, 'payment_status_id', 'status_id');
    }
}