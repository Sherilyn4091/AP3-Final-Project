<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Instrument extends Model
{
    /**
     * Database table used by this model.
     */
    protected $table = 'instrument';

    /**
     * Primary key of the instrument table.
     */
    protected $primaryKey = 'instrument_id';

    public $incrementing = true;

    protected $keyType = 'integer';

    /**
     * Fields allowed for mass assignment.
     */
    protected $fillable = [
        'instrument_name',
        'category',
        'description',
        'is_system',
        'is_active',
    ];

    /**
     * Cast tinyint/boolean database values into real booleans.
     */
    protected $casts = [
        'is_system' => 'boolean',
        'is_active' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function students()
    {
        return $this->hasMany(Student::class, 'instrument_id', 'instrument_id');
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class, 'instrument_id', 'instrument_id');
    }
}