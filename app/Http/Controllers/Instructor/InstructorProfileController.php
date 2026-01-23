<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class InstructorProfileController extends Controller
{
    /**
     * Display the instructor's profile page
     * Fetches all instructor data from the database
     */
    public function index()
    {
        // Get the logged-in user's instructor record with all fields
        $instructor = DB::table('instructor')
            ->where('user_id', Auth::id())
            ->first();

        // Redirect if no instructor record found
        if (!$instructor) {
            abort(404, 'Instructor record not found');
        }

        return view('instructor.profile.index', compact('instructor'));
    }

    /**
     * Update the instructor's profile information
     * Validates and updates all editable fields
     */
    public function update(Request $request)
    {
        // Validate all profile fields
        $validated = $request->validate([
            // Personal Information
            'first_name' => ['required', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'suffix' => ['nullable', 'string', 'max:20'],
            'date_of_birth' => ['nullable', 'date'],
            'gender' => ['nullable', 'in:Male,Female,Other,Prefer not to say'],
            'nationality' => ['nullable', 'string', 'max:100'],
            
            // Contact Information
            'phone' => ['nullable', 'regex:/^\d{11}$/'],
            
            // Address Information
            'address_line1' => ['nullable', 'string', 'max:255'],
            'address_line2' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'province' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'country' => ['nullable', 'string', 'max:100'],
            
            // Emergency Contact
            'emergency_contact_name' => ['nullable', 'string', 'max:200'],
            'emergency_contact_relationship' => ['nullable', 'string', 'max:100'],
            'emergency_contact_phone' => ['nullable', 'regex:/^\d{11}$/'],
            
            // Professional Qualifications
            'education_level' => ['nullable', 'string', 'max:100'],
            'music_degree' => ['nullable', 'string', 'max:200'],
            'certifications' => ['nullable', 'string'],
            'years_of_experience' => ['nullable', 'integer', 'min:0', 'max:50'],
            
            // Teaching Details
            'teaching_style' => ['nullable', 'string'],
            'bio' => ['nullable', 'string'],
            'languages_spoken' => ['nullable', 'string'],
            
            // Availability
            'available_days' => ['nullable', 'string'],
            'preferred_time_slots' => ['nullable', 'string'],
            'max_students_per_day' => ['nullable', 'integer', 'min:1', 'max:20'],
        ]);

        // Trim whitespace from all string fields
        $validated = array_map(function($value) {
            return is_string($value) ? trim($value) : $value;
        }, $validated);

        // Update the instructor record
        DB::table('instructor')
            ->where('user_id', Auth::id())
            ->update(array_merge($validated, [
                'updated_at' => now(),
            ]));

        return redirect()
            ->route('instructor.profile.index')
            ->with('success', 'Profile updated successfully!');
    }

    /**
     * Change the instructor's password
     * Validates current password and updates to new password
     */
    public function changePassword(Request $request)
    {
        // Validate password fields
        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = Auth::user();
        
        // Verify current password
        if (!Hash::check($validated['current_password'], $user->user_password)) {
            return back()->withErrors([
                'current_password' => 'The current password is incorrect.'
            ]);
        }
        
        // Update password in user_account table
        DB::table('user_account')
            ->where('user_id', $user->user_id)
            ->update([
                'user_password' => Hash::make($validated['password']),
                'updated_at' => now(),
            ]);

        return redirect()
            ->route('instructor.profile.index')
            ->with('success', 'Password changed successfully!');
    }
}