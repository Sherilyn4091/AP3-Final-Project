<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/*
|--------------------------------------------------------------------------
| PitchMonitorEvent Model
|--------------------------------------------------------------------------
|
| Represents a single detected pitch event during one session.
|
*/

class PitchMonitorEvent extends Model
{
    protected $table = 'pitch_monitor_events';
    protected $primaryKey = 'event_id';

    protected $fillable = [
        'session_id',
        'note_name',
        'frequency',
        'cents_deviation',
        'confidence',
        'rms',
        'tuning_status',
        'detected_at',
    ];

    protected $casts = [
        'detected_at' => 'datetime',
        'frequency' => 'float',
        'cents_deviation' => 'float',
        'confidence' => 'float',
        'rms' => 'float',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function session()
    {
        return $this->belongsTo(PitchMonitorSession::class, 'session_id', 'session_id');
    }
}