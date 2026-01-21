<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InstructorController extends Controller
{
    /**
     * Show the instructor dashboard redirect / overview
     * (This can serve as a fallback or quick redirect if needed)
     */
    public function index()
    {
        // Redirect to the main dashboard (most common pattern)
        return redirect()->route('instructor.profile');
        
        // Alternative: If you want a simple instructor home page instead:
        // return view('instructor.index');
    }

    /**
     * Show the instructor's own profile
     */
    public function profile()
    {
        $instructor = Auth::user()->instructor;

        // Load additional useful relationships if needed
        $instructor->load([
            'specializations' => function ($q) {
                $q->with('specialization');
            }
        ]);

        return view('instructor.profile', compact('instructor'));
    }

    /**
     * Update instructor profile (basic info, availability, etc.)
     */
    public function updateProfile(Request $request)
    {
        $instructor = Auth::user()->instructor;

        $validated = $request->validate([
            'bio'                  => 'nullable|string|max:2000',
            'teaching_style'       => 'nullable|string|max:1000',
            'available_days'       => 'nullable|string|max:500',
            'preferred_time_slots' => 'nullable|string|max:500',
            'max_students_per_day' => 'nullable|integer|min:1|max:20',
            'phone'                => 'nullable|regex:/^09\d{9}$/',
        ]);

        $instructor->update($validated);

        return redirect()
            ->route('instructor.profile')
            ->with('success', 'Profile updated successfully!');
    }

    /**
     * Show instructor's availability calendar (placeholder)
     */
    public function availability()
    {
        $instructor = Auth::user()->instructor;

        return view('instructor.availability', compact('instructor'));
    }

    /**
     * Quick redirect to instructor's own schedule
     */
    public function mySchedule()
    {
        return redirect()->route('instructor.schedule');
    }

    /**
     * Logout from instructor portal (optional - can be global)
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'You have been logged out.');
    }
}
