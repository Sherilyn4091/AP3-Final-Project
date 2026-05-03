<?php

namespace App\Models;

use App\Models\PitchMonitorEvent;
use Illuminate\Database\Eloquent\Model;

/*
|--------------------------------------------------------------------------
| PitchMonitorSession Model
|--------------------------------------------------------------------------
|
| Represents one real-time pitch monitoring session.
|
*/

class PitchMonitorSession extends Model
{
    protected $table = 'pitch_monitor_sessions';
    protected $primaryKey = 'session_id';

    protected $fillable = [
        'user_id',
        'source_type',
        'started_at',
        'ended_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at'   => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function events()
    {
        return $this->hasMany(PitchMonitorEvent::class, 'session_id', 'session_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    public function getDurationSecondsAttribute(): ?int
    {
        if (!$this->started_at || !$this->ended_at) {
            return null;
        }

        return $this->started_at->diffInSeconds($this->ended_at);
    }
}