<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Instructor extends Model
{
    protected $table = 'instructor';
    protected $primaryKey = 'instructor_id';
    
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
        'hire_date',
        'employment_status',
        'contract_type',
        'hourly_rate',
        'monthly_salary',
        'education_level',
        'music_degree',
        'certifications',
        'years_of_experience',
        'teaching_style',
        'bio',
        'languages_spoken',
        'is_available',
        'available_days',
        'preferred_time_slots',
        'max_students_per_day',
        'total_students_taught',
        'average_rating',
        'is_active',
        'notes',
    ];
    
    protected $casts = [
        'date_of_birth' => 'date',
        'hire_date' => 'date',
        'hourly_rate' => 'decimal:2',
        'monthly_salary' => 'decimal:2',
        'years_of_experience' => 'integer',
        'max_students_per_day' => 'integer',
        'total_students_taught' => 'integer',
        'average_rating' => 'decimal:2',
        'is_available' => 'boolean',
        'is_active' => 'boolean',
    ];
    
    // Relationships
    public function userAccount()
    {
        return $this->belongsTo(UserAccount::class, 'user_id', 'user_id');
    }
    
    public function specializations()
    {
        return $this->belongsToMany(
            Specialization::class,
            'instructor_specialization',
            'instructor_id',
            'specialization_id'
        )->withTimestamps();
    }
    
    public function enrollments()
    {
        return $this->hasMany(Enrollment::class, 'instructor_id', 'instructor_id');
    }
    
    public function schedules()
    {
        return $this->hasMany(Schedule::class, 'instructor_id', 'instructor_id');
    }
    
    public function progress()
    {
        return $this->hasMany(Progress::class, 'instructor_id', 'instructor_id');
    }
    
    public function attendance()
    {
        return $this->hasMany(Attendance::class, 'instructor_id', 'instructor_id');
    }
    
    // Accessor for full name
    public function getFullNameAttribute()
    {
        return trim("{$this->first_name} {$this->middle_name} {$this->last_name} {$this->suffix}");
    }
}