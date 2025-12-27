<?php

// app/Models/Student.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $table = 'student';
    protected $primaryKey = 'student_id';
    
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
        'parent_guardian_name',
        'parent_guardian_relationship',
        'parent_guardian_phone',
        'parent_guardian_email',
        'parent_guardian_address',
        'instrument_id',
        'secondary_instruments',
        'previous_music_experience',
        'skill_level',
        'music_goals',
        'school_name',
        'grade_level',
        'medical_conditions',
        'allergies',
        'special_needs',
        'enrollment_date',
        'expected_completion_date',
        'student_status_id',
        'preferred_genre_id',
        'preferred_lesson_days',
        'preferred_lesson_time',
        'is_active',
    ];
    
    protected $casts = [
        'date_of_birth' => 'date',
        'enrollment_date' => 'date',
        'expected_completion_date' => 'date',
        'is_active' => 'boolean',
    ];
    
    // Relationships
    public function userAccount()
    {
        return $this->belongsTo(UserAccount::class, 'user_id', 'user_id');
    }
    
    public function instrument()
    {
        return $this->belongsTo(Instrument::class, 'instrument_id', 'instrument_id');
    }
    
    public function status()
    {
        return $this->belongsTo(StudentStatus::class, 'student_status_id', 'status_id');
    }
    
    public function preferredGenre()
    {
        return $this->belongsTo(Genre::class, 'preferred_genre_id', 'genre_id');
    }
    
    public function enrollments()
    {
        return $this->hasMany(Enrollment::class, 'student_id', 'student_id');
    }
    
    public function schedules()
    {
        return $this->hasMany(Schedule::class, 'student_id', 'student_id');
    }
    
    public function attendance()
    {
        return $this->hasMany(Attendance::class, 'student_id', 'student_id');
    }
    
    public function progress()
    {
        return $this->hasMany(Progress::class, 'student_id', 'student_id');
    }
    
    public function payments()
    {
        return $this->hasMany(Payment::class, 'student_id', 'student_id');
    }
    
    // Accessor for full name
    public function getFullNameAttribute()
    {
        return trim("{$this->first_name} {$this->middle_name} {$this->last_name} {$this->suffix}");
    }
}