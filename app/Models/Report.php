<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Auth;

class Report extends Model
{
    use HasFactory;
    
    protected $table = 'report';
    protected $primaryKey = 'report_id';
    
    protected $fillable = [
        'report_type',
        'report_title',
        'report_date_from',
        'report_date_to',
        'report_data',
        'generated_by',
    ];
    
    protected $casts = [
        'report_date_from' => 'date',
        'report_date_to' => 'date',
        'report_data' => 'array',
        'generated_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    
    // Relationship
    public function generator()
    {
        return $this->belongsTo(UserAccount::class, 'generated_by', 'user_id');
    }
    
    // Helper method to get current user's ID (IDE-friendly)
    public static function getCurrentUserId(): ?int
    {
        if (Auth::check()) {
            return Auth::user()->user_id;
        }
        return null;
    }
    
    // Auto-set generated_by when creating
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($report) {
            if (!$report->generated_by) {
                $report->generated_by = self::getCurrentUserId();
            }
        });
    }
    
    // Accessors
    public function getDateRangeAttribute()
    {
        return $this->report_date_from->format('M d, Y') . ' - ' . $this->report_date_to->format('M d, Y');
    }
    
    public function getFormattedTypeAttribute()
    {
        return self::getReportTypes()[$this->report_type] ?? ucfirst($this->report_type);
    }
    
    // Scopes
    public function scopeOfType($query, $type)
    {
        return $query->where('report_type', $type);
    }
    
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('generated_at', '>=', now()->subDays($days));
    }
    
    // Helpers
    public function getSummary($key = null)
    {
        $summary = $this->report_data['summary'] ?? [];
        return $key ? ($summary[$key] ?? null) : $summary;
    }
    
    public function hasChartData($chartType)
    {
        return isset($this->report_data[$chartType]);
    }
    
    // Static method: Get available report types
    public static function getReportTypes()
    {
        return [
            'revenue' => 'Revenue Report',
            'enrollment' => 'Enrollment Report',
            'inventory' => 'Inventory Report',
            'attendance' => 'Attendance Report',
            'analytics' => 'Analytics Report',
            'instructor_performance' => 'Instructor Performance',
            'student_progress' => 'Student Progress',
            'sales' => 'Sales Report',
            'payment_summary' => 'Payment Summary',
        ];
    }

    
}
