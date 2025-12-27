<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    protected $table = 'schedule';
    protected $primaryKey = 'schedule_id';
    
    protected $fillable = [
        'enrollment_id',
        'student_id',
        'instructor_id',
        'room_number',
        'schedule_date',
        'start_time',
        'end_time',
        'duration_minutes',
        'status',
        'lesson_topic',
        'lesson_content',
        'notes',
        'cancellation_reason',
        'cancelled_at',
    ];
    
    protected $casts = [
        'schedule_date' => 'date',
        'duration_minutes' => 'integer',
        'cancelled_at' => 'datetime',
    ];
    
    // Relationships
    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class, 'enrollment_id', 'enrollment_id');
    }
    
    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }
    
    public function instructor()
    {
        return $this->belongsTo(Instructor::class, 'instructor_id', 'instructor_id');
    }
    
    public function attendance()
    {
        return $this->hasMany(Attendance::class, 'schedule_id', 'schedule_id');
    }
    
    public function progress()
    {
        return $this->hasOne(Progress::class, 'schedule_id', 'schedule_id');
    }
}