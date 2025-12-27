<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $table = 'booking';
    protected $primaryKey = 'booking_id';
    public $incrementing = false;
    protected $keyType = 'string';
    
    protected $fillable = [
        'booking_id',
        'user_id',
        'room_number',
        'booking_date',
        'start_time',
        'end_time',
        'duration_hours',
        'hourly_rate',
        'total_amount',
        'booking_status',
        'purpose',
        'number_of_people',
        'band_name',
        'equipment_needed',
        'special_requests',
        'contact_name',
        'contact_phone',
        'contact_email',
        'confirmed_by',
        'confirmed_at',
        'cancelled_by',
        'cancelled_at',
        'cancellation_reason',
    ];
    
    protected $casts = [
        'booking_date' => 'date',
        'duration_hours' => 'decimal:2',
        'hourly_rate' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'number_of_people' => 'integer',
        'confirmed_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];
    
    // Relationships
    public function user()
    {
        return $this->belongsTo(UserAccount::class, 'user_id', 'user_id');
    }
    
    public function confirmer()
    {
        return $this->belongsTo(UserAccount::class, 'confirmed_by', 'user_id');
    }
    
    public function canceller()
    {
        return $this->belongsTo(UserAccount::class, 'cancelled_by', 'user_id');
    }
    
    public function payments()
    {
        return $this->hasMany(Payment::class, 'booking_id', 'booking_id');
    }
}