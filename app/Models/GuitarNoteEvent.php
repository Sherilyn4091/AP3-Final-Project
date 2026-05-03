<?php
#app/Models/GuitarNoteEvent.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GuitarNoteEvent extends Model
{
    protected $table = 'guitar_note_events';
    protected $primaryKey = 'event_id';

    protected $fillable = [
        'session_id',
        'note_name',
        'frequency',
        'cents_deviation',
        'tuning_status',
        'detected_at',
    ];

    protected $casts = [
        'frequency'       => 'decimal:2',
        'cents_deviation' => 'decimal:2',
        'detected_at'     => 'datetime',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(GuitarSession::class, 'session_id', 'session_id');
    }
}