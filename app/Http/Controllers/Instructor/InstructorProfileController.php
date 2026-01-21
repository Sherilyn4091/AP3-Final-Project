<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InstructorProfileController extends Controller
{
    public function index()
    {
        // Get the logged-in user's instructor record
        $instructor = DB::table('instructor')
            ->where('user_id', Auth::id())
            ->first();

        // Your view is: resources/views/instructor/profile/index.blade.php
        return view('instructor.profile.index', compact('instructor'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'bio' => ['nullable', 'string'],
            'teaching_style' => ['nullable', 'string'],
            'available_days' => ['nullable', 'string', 'max:255'],
            'preferred_time_slots' => ['nullable', 'string', 'max:255'],
            'max_students_per_day' => ['nullable', 'integer', 'min:1', 'max:20'],
        ]);

        DB::table('instructor')
            ->where('user_id', Auth::id())
            ->update([
                'bio' => $validated['bio'] ?? null,
                'teaching_style' => $validated['teaching_style'] ?? null,
                'available_days' => $validated['available_days'] ?? null,
                'preferred_time_slots' => $validated['preferred_time_slots'] ?? null,
                'max_students_per_day' => $validated['max_students_per_day'] ?? 8,
                'updated_at' => now(),
            ]);

        return redirect()
            ->route('instructor.profile.index')
            ->with('success', 'Profile updated!');
    }
} 
