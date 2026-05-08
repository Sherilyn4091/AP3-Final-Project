<?php

// app/Http/Controllers/Student/ProfileController.php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    private const VALID_DAYS = [
        'Monday',
        'Tuesday',
        'Wednesday',
        'Thursday',
        'Friday',
        'Saturday',
        'Sunday',
    ];

    /**
     * Display student profile page.
     * Shows personal information and preferences.
     */
    public function index()
    {
        // Get authenticated user ID
        $userId = Auth::id();

        // Fetch student record from database
        $student = DB::table('student')->where('user_id', $userId)->first();

        // Validate student exists
        if (!$student) {
            abort(404, 'Student record not found');
        }

        // Get instruments for dropdown
        $instruments = DB::table('instrument')
            ->where('is_active', true)
            ->orderBy('instrument_name')
            ->get();

        // Get genres for dropdown
        $genres = DB::table('genre')
            ->where('is_active', true)
            ->orderBy('genre_name')
            ->get();

        $validDays = self::VALID_DAYS;
        $timeSlots = $this->getPreferredTimeSlots();
        $selectedLessonDays = $this->parsePreferredDays($student->preferred_lesson_days);

        return view('student.profile', compact(
            'student',
            'instruments',
            'genres',
            'validDays',
            'timeSlots',
            'selectedLessonDays'
        ));
    }

    /**
     * Update student profile information.
     * Allows updating all profile fields.
     */
    public function update(Request $request)
    {
        // Get authenticated user ID
        $userId = Auth::id();

        // Get student record
        $student = DB::table('student')->where('user_id', $userId)->first();

        // Validate student exists
        if (!$student) {
            abort(404, 'Student record not found');
        }

        // Validate input
        $validated = $request->validate([
            // Name fields
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'suffix' => 'nullable|string|max:50',
            'gender' => 'nullable|in:Male,Female,Other,Prefer not to say',

            // Contact information
            'phone' => 'nullable|string|regex:/^\d{11}$/',

            // Personal information
            'date_of_birth' => 'nullable|date|before:today',
            'nationality' => 'nullable|string|max:100',

            // Address fields
            'address_line1' => 'nullable|string|max:255',
            'address_line2' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'province' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',

            // Emergency contact
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_relationship' => 'nullable|string|max:100',
            'emergency_contact_phone' => 'nullable|string|regex:/^\d{11}$/',

            // Parent/Guardian
            'parent_guardian_name' => 'nullable|string|max:255',
            'parent_guardian_relationship' => 'nullable|string|max:100',
            'parent_guardian_phone' => 'nullable|string|regex:/^\d{11}$/',
            'parent_guardian_email' => 'nullable|email|max:255',
            'parent_guardian_address' => 'nullable|string|max:500',

            // Musical background
            'instrument_id' => 'nullable|exists:instrument,instrument_id',
            'skill_level' => 'nullable|in:beginner,intermediate,advanced,expert',
            'secondary_instruments' => 'nullable|string|max:255',
            'previous_music_experience' => 'nullable|string|max:1000',
            'music_goals' => 'nullable|string|max:1000',
            'preferred_genre_id' => 'nullable|exists:genre,genre_id',

            // Educational background
            'school_name' => 'nullable|string|max:255',
            'grade_level' => 'nullable|string|max:100',

            // Medical information
            'medical_conditions' => 'nullable|string|max:1000',
            'allergies' => 'nullable|string|max:1000',
            'special_needs' => 'nullable|string|max:1000',

            // Lesson preferences as checkbox + selected time slot
            'preferred_lesson_days' => ['nullable', 'array'],
            'preferred_lesson_days.*' => ['string', Rule::in(self::VALID_DAYS)],
            'preferred_lesson_time' => ['nullable', 'string', 'max:100'],
        ]);

        // Update student record
        DB::table('student')
            ->where('student_id', $student->student_id)
            ->update([
                // Name fields
                'first_name' => $validated['first_name'],
                'middle_name' => $validated['middle_name'] ?? null,
                'last_name' => $validated['last_name'],
                'suffix' => $validated['suffix'] ?? null,
                'gender' => $validated['gender'] ?? null,

                // Contact
                'phone' => $validated['phone'] ?? null,

                // Personal
                'date_of_birth' => $validated['date_of_birth'] ?? null,
                'nationality' => $validated['nationality'] ?? null,

                // Address
                'address_line1' => $validated['address_line1'] ?? null,
                'address_line2' => $validated['address_line2'] ?? null,
                'city' => $validated['city'] ?? null,
                'province' => $validated['province'] ?? null,
                'postal_code' => $validated['postal_code'] ?? null,
                'country' => $validated['country'] ?? null,

                // Emergency contact
                'emergency_contact_name' => $validated['emergency_contact_name'] ?? null,
                'emergency_contact_relationship' => $validated['emergency_contact_relationship'] ?? null,
                'emergency_contact_phone' => $validated['emergency_contact_phone'] ?? null,

                // Parent/Guardian
                'parent_guardian_name' => $validated['parent_guardian_name'] ?? null,
                'parent_guardian_relationship' => $validated['parent_guardian_relationship'] ?? null,
                'parent_guardian_phone' => $validated['parent_guardian_phone'] ?? null,
                'parent_guardian_email' => $validated['parent_guardian_email'] ?? null,
                'parent_guardian_address' => $validated['parent_guardian_address'] ?? null,

                // Musical background
                'instrument_id' => $validated['instrument_id'] ?? null,
                'skill_level' => $validated['skill_level'] ?? null,
                'secondary_instruments' => $validated['secondary_instruments'] ?? null,
                'previous_music_experience' => $validated['previous_music_experience'] ?? null,
                'music_goals' => $validated['music_goals'] ?? null,
                'preferred_genre_id' => $validated['preferred_genre_id'] ?? null,

                // Educational background
                'school_name' => $validated['school_name'] ?? null,
                'grade_level' => $validated['grade_level'] ?? null,

                // Medical information
                'medical_conditions' => $validated['medical_conditions'] ?? null,
                'allergies' => $validated['allergies'] ?? null,
                'special_needs' => $validated['special_needs'] ?? null,

                // Lesson preferences
                'preferred_lesson_days' => $this->formatPreferredDays($validated['preferred_lesson_days'] ?? []),
                'preferred_lesson_time' => $validated['preferred_lesson_time'] ?? null,

                'updated_at' => now(),
            ]);

        return redirect()->route('student.profile')
            ->with('success', 'Profile updated successfully!');
    }

    /**
     * Change student password.
     */
    public function changePassword(Request $request)
    {
        // Validate input
        $validated = $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Get authenticated user
        $user = Auth::user();

        // Verify current password
        if (!Hash::check($validated['current_password'], $user->user_password)) {
            return redirect()->route('student.profile')
                ->with('error', 'Current password is incorrect.');
        }

        // Update password
        DB::table('user_account')
            ->where('user_id', $user->user_id)
            ->update([
                'user_password' => Hash::make($validated['password']),
                'updated_at' => now(),
            ]);

        return redirect()->route('student.profile')
            ->with('success', 'Password changed successfully!');
    }

    /**
     * Time slots offered in profile and enrollment forms.
     */
    private function getPreferredTimeSlots(): array
    {
        return [
            'Morning (9:00 AM - 12:00 PM)',
            'Afternoon (12:00 PM - 3:00 PM)',
            'Late Afternoon (3:00 PM - 6:00 PM)',
            'Evening (6:00 PM - 8:00 PM)',
            'Sunday Window (10:00 AM - 6:00 PM)',
        ];
    }

    /**
     * Convert stored comma-separated days into an array for checkbox selection.
     */
    private function parsePreferredDays(?string $days): array
    {
        if (!$days) {
            return [];
        }

        return collect(explode(',', $days))
            ->map(fn ($day) => trim($day))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * Convert selected days array into a clean string for storage.
     */
    private function formatPreferredDays(array $days): ?string
    {
        if (empty($days)) {
            return null;
        }

        return collect(self::VALID_DAYS)
            ->filter(fn ($day) => in_array($day, $days, true))
            ->values()
            ->implode(', ');
    }
}
