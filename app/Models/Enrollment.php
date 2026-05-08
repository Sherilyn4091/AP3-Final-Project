<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model
{
    /**
     * Database table used by this model.
     */
    protected $table = 'enrollment';

    /**
     * Primary key is a custom string ID like 2026-05-0000001.
     */
    protected $primaryKey = 'enrollment_id';

    public $incrementing = false;

    protected $keyType = 'string';

    /**
     * Fields allowed for mass assignment.
     */
    protected $fillable = [
        'enrollment_id',
        'student_id',
        'instrument_id',
        'session_id',
        'instructor_id',
        'payment_method_id',
        'enrollment_date',
        'start_date',
        'preferred_lesson_days',
        'preferred_lesson_time',
        'end_date',
        'total_sessions',
        'completed_sessions',
        'remaining_sessions',
        'status',
        'payment_status',
        'total_amount',
        'amount_paid',
        'notes',
        'cancellation_reason',
        'cancelled_at',
        'withdrawal_reason',
        'withdrawal_requested_at',
    ];

    /**
     * Cast database values into useful PHP types.
     */
    protected $casts = [
        'enrollment_date' => 'date',
        'start_date' => 'date',
        'end_date' => 'date',
        'total_sessions' => 'integer',
        'completed_sessions' => 'integer',
        'remaining_sessions' => 'integer',
        'total_amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'cancelled_at' => 'datetime',
        'withdrawal_requested_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }

    public function instrument()
    {
        return $this->belongsTo(Instrument::class, 'instrument_id', 'instrument_id');
    }

    public function lessonSession()
    {
        return $this->belongsTo(LessonSession::class, 'session_id', 'session_id');
    }

    public function instructor()
    {
        return $this->belongsTo(Instructor::class, 'instructor_id', 'instructor_id');
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class, 'enrollment_id', 'enrollment_id');
    }

    public function progress()
    {
        return $this->hasMany(Progress::class, 'enrollment_id', 'enrollment_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'enrollment_id', 'enrollment_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Small Helpers for Student UI
    |--------------------------------------------------------------------------
    |
    | These helpers keep view files readable and avoid duplicated date/status
    | logic across dashboard, enrollment cards, and action buttons.
    |
    */
    public function getProgressPercentAttribute(): float
    {
        if ((int) $this->total_sessions <= 0) {
            return 0;
        }

        return round(((int) $this->completed_sessions / (int) $this->total_sessions) * 100, 1);
    }

    public function getHasStartedAttribute(): bool
    {
        if (!$this->start_date) {
            return false;
        }

        return Carbon::parse($this->start_date)->startOfDay()->lte(now()->startOfDay());
    }

    public function getCanBeEditedAttribute(): bool
    {
        return $this->status === 'active'
            && !$this->has_started
            && (int) $this->completed_sessions === 0
            && $this->schedules()->count() === 0;
    }

    public function getCanBeCancelledAttribute(): bool
    {
        return $this->status === 'active'
            && !$this->has_started
            && (int) $this->completed_sessions === 0;
    }

    public function getCanRequestWithdrawalAttribute(): bool
    {
        return $this->status === 'active'
            && ($this->has_started || (int) $this->completed_sessions > 0 || $this->schedules()->count() > 0);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'active' => 'Active',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            'withdrawal_requested' => 'Withdrawal Requested',
            'withdrawn' => 'Withdrawn',
            default => ucwords(str_replace('_', ' ', (string) $this->status)),
        };
    }
}
