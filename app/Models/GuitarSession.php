<?php
// app/Models/GuitarSession.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GuitarSession extends Model
{
    protected $table = 'guitar_sessions';
    protected $primaryKey = 'session_id';

    protected $fillable = [
        'user_id',
        'target_string',
        'started_at',
        'ended_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at'   => 'datetime',
    ];

    protected $appends = ['duration_seconds'];

    public function noteEvents(): HasMany
    {
        return $this->hasMany(GuitarNoteEvent::class, 'session_id', 'session_id');
    }

    public function getDurationSecondsAttribute(): ?int
    {
        if (!$this->ended_at) {
            return null;
        }

        return (int) $this->started_at->diffInSeconds($this->ended_at);
    }
}