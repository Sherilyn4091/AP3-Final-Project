<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Instrument extends Model
{
    protected $table = 'instrument';
    protected $primaryKey = 'instrument_id';
    public $incrementing = true;
    protected $keyType = 'integer'; 
    
    protected $fillable = [
        'instrument_name',
        'category',
        'description',
        'is_system',
        'is_active',
    ];
    
    protected $casts = [
        'is_system' => 'boolean',
        'is_active' => 'boolean',
    ];
    
    // Relationships
    public function students()
    {
        return $this->hasMany(Student::class, 'instrument_id', 'instrument_id');
    }

    public function getKeyName()
    {
        return 'instrument_id';
    }
}