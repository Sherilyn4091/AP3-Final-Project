<?php

// app/Models/Specialization.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Specialization extends Model
{
    use HasFactory;

    protected $table = 'specialization';
    protected $primaryKey = 'specialization_id';
    
    protected $fillable = [
        'specialization_name',
        'description',
        'is_active',
    ];
    
    protected $casts = [
        'is_active' => 'boolean',
    ];
    
    // Relationships
    public function instructors()
    {
        return $this->belongsToMany(
            Instructor::class,
            'instructor_specialization',
            'specialization_id',
            'instructor_id'
        )->withTimestamps();
    }
}