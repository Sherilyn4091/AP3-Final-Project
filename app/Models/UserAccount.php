<?php

// app/Models/UserAccount.php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Hash;

class UserAccount extends Authenticatable
{
    // === Database Configuration ===
    protected $table = 'user_account';
    protected $primaryKey = 'user_id';
    public $timestamps = true; // ✅ Enable Laravel's automatic timestamp management
    
    // === Authentication Configuration ===
    // Tell Laravel which column to use for authentication identifier
    public function getAuthIdentifierName()
    {
        return 'user_id';
    }
    
    // Tell Laravel which column contains the email for password reset
    public function getEmailForPasswordReset()
    {
        return $this->user_email;
    }
    
    // Tell Laravel which column contains the hashed password
    public function getAuthPassword()
    {
        return $this->user_password;
    }
    
    // === Mass Assignment Protection ===
    protected $fillable = [
        'user_email',
        'user_password',
        'is_super_admin',
        'last_login', // Added so we can mass-assign it
    ];
    
    // Hide sensitive fields from JSON output
    protected $hidden = [
        'user_password',
    ];
    
    // === Type Casting ===
    protected $casts = [
        'is_super_admin' => 'boolean', // Auto-cast to boolean
        'last_login' => 'datetime',    // Auto-cast to Carbon instance
    ];
    
    // === Mutators (Auto-processing) ===
    // Auto-hash password when setting (e.g., $user->user_password = 'plain')
    // IMPORTANT: Only hashes if value is NOT already hashed (prevents double-hashing)
    public function setUserPasswordAttribute($value)
    {
        // Check if password is already hashed (bcrypt hashes start with $2y$)
        if (!str_starts_with($value, '$2y$')) {
            $this->attributes['user_password'] = Hash::make($value);
        } else {
            // Already hashed, store as-is (prevents double-hashing in seeders)
            $this->attributes['user_password'] = $value;
        }
    }
}