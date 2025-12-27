<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model
{
    protected $table = 'enrollment';
    protected $primaryKey = 'enrollment_id';
    public $incrementing = false;
    protected $keyType = 'string';
    
    protected $fillable = [
        'enrollment_id',
        'student_id',
        'session_id',
        'instructor_id',
        'enrollment_date',
        'start_date',
        'end_date',
        'total_sessions',
        'completed_sessions',
        'remaining_sessions',
        'status',
        'payment_status',
        'total_amount',
        'amount_paid',
        'notes',
    ];
    
    protected $casts = [
        'enrollment_date' => 'date',
        'start_date' => 'date',
        'end_date' => 'date',
        'total_sessions' => 'integer',
        'completed_sessions' => 'integer',
        'remaining_sessions' => 'integer',
        'total_amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
    ];
    
    // Relationships
    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }
    
    public function lessonSession()
    {
        return $this->belongsTo(LessonSession::class, 'session_id', 'session_id');
    }
    
    public function instructor()
    {
        return $this->belongsTo(Instructor::class, 'instructor_id', 'instructor_id');
    }
    
    public function schedules()
    {
        return $this->hasMany(Schedule::class, 'enrollment_id', 'enrollment_id');
    }
    
    public function progress()
    {
        return $this->hasMany(Progress::class, 'enrollment_id', 'enrollment_id');
    }
    
    public function payments()
    {
        return $this->hasMany(Payment::class, 'enrollment_id', 'enrollment_id');
    }
}