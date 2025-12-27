<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    protected $table = 'report';
    protected $primaryKey = 'report_id';
    
    protected $fillable = [
        'report_type',
        'report_title',
        'report_date_from',
        'report_date_to',
        'report_data',
        'generated_by',
        'generated_at',
    ];
    
    protected $casts = [
        'report_date_from' => 'date',
        'report_date_to' => 'date',
        'report_data' => 'array',
        'generated_at' => 'datetime',
    ];
    
    // Relationships
    public function generator()
    {
        return $this->belongsTo(UserAccount::class, 'generated_by', 'user_id');
    }
}