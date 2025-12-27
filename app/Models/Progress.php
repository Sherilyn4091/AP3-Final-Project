<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Progress extends Model
{
    protected $table = 'progress';
    protected $primaryKey = 'progress_id';
    
    protected $fillable = [
        'student_id',
        'enrollment_id',
        'instructor_id',
        'schedule_id',
        'progress_date',
        'lesson_topic',
        'skills_covered',
        'techniques_learned',
        'songs_practiced',
        'performance_rating',
        'technical_skills_rating',
        'musicality_rating',
        'effort_rating',
        'strengths',
        'areas_for_improvement',
        'instructor_notes',
        'homework',
        'practice_recommendations',
        'next_lesson_focus',
        'student_comments',
        'student_satisfaction',
    ];
    
    protected $casts = [
        'progress_date' => 'date',
        'performance_rating' => 'integer',
        'technical_skills_rating' => 'integer',
        'musicality_rating' => 'integer',
        'effort_rating' => 'integer',
        'student_satisfaction' => 'integer',
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
    
    public function instructor()
    {
        return $this->belongsTo(Instructor::class, 'instructor_id', 'instructor_id');
    }
    
    public function schedule()
    {
        return $this->belongsTo(Schedule::class, 'schedule_id', 'schedule_id');
    }
}