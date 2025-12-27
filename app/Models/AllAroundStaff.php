<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AllAroundStaff extends Model
{
    protected $table = 'all_around_staff';
    protected $primaryKey = 'all_around_staff_id';
    
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
        'monthly_salary',
        'education_level',
        'skills',
        'certifications',
        'responsibilities',
        'primary_duties',
        'work_schedule',
        'is_active',
        'notes',
    ];
    
    protected $casts = [
        'date_of_birth' => 'date',
        'hire_date' => 'date',
        'monthly_salary' => 'decimal:2',
        'is_active' => 'boolean',
    ];
    
    // Relationships
    public function userAccount()
    {
        return $this->belongsTo(UserAccount::class, 'user_id', 'user_id');
    }
    
    public function attendance()
    {
        return $this->hasMany(Attendance::class, 'all_around_staff_id', 'all_around_staff_id');
    }
    
    // Accessor for full name
    public function getFullNameAttribute()
    {
        return trim("{$this->first_name} {$this->middle_name} {$this->last_name} {$this->suffix}");
    }
}