<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InstructorSpecialization extends Model
{
    protected $table = 'instructor_specialization';
    protected $primaryKey = 'instructor_specialization_id';
    
    protected $fillable = [
        'instructor_id',
        'specialization_id',
        'is_primary',
    ];
    
    protected $casts = [
        'is_primary' => 'boolean',
    ];
    
    // Relationships
    public function instructor()
    {
        return $this->belongsTo(Instructor::class, 'instructor_id', 'instructor_id');
    }
    
    public function specialization()
    {
        return $this->belongsTo(Specialization::class, 'specialization_id', 'specialization_id');
    }
}