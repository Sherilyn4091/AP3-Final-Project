<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $table = 'payment';
    protected $primaryKey = 'payment_id';
    
    protected $fillable = [
        'student_id',
        'enrollment_id',
        'booking_id',
        'amount',
        'payment_method_id',
        'payment_status_id',
        'payment_date',
        'transaction_reference',
        'receipt_number',
        'bank_name',
        'account_number',
        'check_number',
        'subtotal',
        'discount',
        'processed_by',
        'approved_by',
        'approved_at',
    ];
    
    protected $casts = [
        'amount' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'payment_date' => 'date',
        'approved_at' => 'datetime',
    ];
    
    // Relationships
    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }
    
    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class, 'enrollment_id', 'enrollment_id');
    }
    
    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id', 'booking_id');
    }
    
    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id', 'method_id');
    }
    
    public function paymentStatus()
    {
        return $this->belongsTo(PaymentStatus::class, 'payment_status_id', 'status_id');
    }
    
    public function processor()
    {
        return $this->belongsTo(UserAccount::class, 'processed_by', 'user_id');
    }
    
    public function approver()
    {
        return $this->belongsTo(UserAccount::class, 'approved_by', 'user_id');
    }
}