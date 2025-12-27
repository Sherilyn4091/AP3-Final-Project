<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentStatus extends Model
{
    protected $table = 'student_status';
    protected $primaryKey = 'status_id';
    
    protected $fillable = [
        'status_name',
        'description',
        'is_active',
    ];
    
    protected $casts = [
        'is_active' => 'boolean',
    ];
    
    // Relationships
    public function students()
    {
        return $this->hasMany(Student::class, 'student_status_id', 'status_id');
    }
}