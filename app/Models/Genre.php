<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Genre extends Model
{
    protected $table = 'genre';
    protected $primaryKey = 'genre_id';
    
    protected $fillable = [
        'genre_name',
        'description',
        'is_active',
    ];
    
    protected $casts = [
        'is_active' => 'boolean',
    ];
    
    // Relationships
    public function students()
    {
        return $this->hasMany(Student::class, 'preferred_genre_id', 'genre_id');
    }
}