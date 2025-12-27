<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $table = 'attendance';
    protected $primaryKey = 'attendance_id';
    
    protected $fillable = [
        'attendance_type',
        'schedule_id',
        'user_id',
        'student_id',
        'instructor_id',
        'sales_staff_id',
        'all_around_staff_id',
        'attendance_date',
        'attendance_status',
        'check_in_time',
        'check_out_time',
    ];
    
    protected $casts = [
        'attendance_date' => 'date',
        'check_in_time' => 'datetime',
        'check_out_time' => 'datetime',
    ];
    
    // Relationships
    public function schedule()
    {
        return $this->belongsTo(Schedule::class, 'schedule_id', 'schedule_id');
    }
    
    public function userAccount()
    {
        return $this->belongsTo(UserAccount::class, 'user_id', 'user_id');
    }
    
    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }
    
    public function instructor()
    {
        return $this->belongsTo(Instructor::class, 'instructor_id', 'instructor_id');
    }
    
    public function salesStaff()
    {
        return $this->belongsTo(SalesStaff::class, 'sales_staff_id', 'sales_staff_id');
    }
    
    public function allAroundStaff()
    {
        return $this->belongsTo(AllAroundStaff::class, 'all_around_staff_id', 'all_around_staff_id');
    }
}