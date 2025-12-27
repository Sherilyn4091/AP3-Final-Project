<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LessonSession extends Model
{
    protected $table = 'lesson_session';
    protected $primaryKey = 'session_id';
    
    protected $fillable = [
        'session_count',
        'duration_minutes',
        'price',
        'session_name',
        'description',
        'is_active',
    ];
    
    protected $casts = [
        'session_count' => 'integer',
        'duration_minutes' => 'integer',
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];
    
    // Relationships
    public function enrollments()
    {
        return $this->hasMany(Enrollment::class, 'session_id', 'session_id');
    }
}