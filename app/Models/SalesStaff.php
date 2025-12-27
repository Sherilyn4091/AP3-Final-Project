<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesStaff extends Model
{
    protected $table = 'sales_staff';
    protected $primaryKey = 'sales_staff_id';
    
    protected $fillable = [
        'user_id',
        'first_name',
        'middle_name',
        'last_name',
        'suffix',
        'phone',
        'email',
        'address_line1',
        'address_line2',
        'city',
        'province',
        'postal_code',
        'country',
        'date_of_birth',
        'gender',
        'nationality',
        'emergency_contact_name',
        'emergency_contact_relationship',
        'emergency_contact_phone',
        'employee_id',
        'position',
        'hire_date',
        'employment_status',
        'contract_type',
        'base_salary',
        'commission_rate',
        'education_level',
        'previous_experience',
        'work_schedule',
        'is_active',
        'notes',
    ];
    
    protected $casts = [
        'date_of_birth' => 'date',
        'hire_date' => 'date',
        'base_salary' => 'decimal:2',
        'commission_rate' => 'decimal:2',
        'is_active' => 'boolean',
    ];
    
    // Relationships
    public function userAccount()
    {
        return $this->belongsTo(UserAccount::class, 'user_id', 'user_id');
    }
    
    public function attendance()
    {
        return $this->hasMany(Attendance::class, 'sales_staff_id', 'sales_staff_id');
    }
    
    // Accessor for full name
    public function getFullNameAttribute()
    {
        return trim("{$this->first_name} {$this->middle_name} {$this->last_name} {$this->suffix}");
    }
}