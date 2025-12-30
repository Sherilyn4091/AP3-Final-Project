<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\UserAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

/**
 * app/Http/Controllers/Auth/RegistrationController.php
 * 
 * Handles user registration for all roles in Music Lab:
 * - Student
 * - Instructor
 * - Sales Staff
 * - All-Around Staff
 * 
 * Each role has its own form and validation rules.
 * All registrations follow the same secure pattern:
 * 1. Validate input (except password)
 * 2. Create user_account record without password
 * 3. Create role-specific record
 * 4. Use database transactions for data integrity
 * 5. Redirect to create-account page to set password
 */
class RegistrationController extends Controller
{
    // ============================================================================
    // STUDENT REGISTRATION
    // ============================================================================

    /**
     * Display the student registration form
     * 
     * Loads all active instruments for the dropdown selection
     * 
     * @return \Illuminate\View\View
     */
    public function showStudentRegistrationForm()
    {
        // Fetch only active instruments, ordered alphabetically
        $instruments = DB::table('instrument')
            ->where('is_active', true)
            ->orderBy('instrument_name')
            ->get();

        // Pass instruments to the view
        return view('auth.register.student', compact('instruments'));
    }

    /**
     * Process student registration submission
     * 
     * Validates data, creates user account and student profile without password
     * Uses transaction to ensure both records are created successfully
     * Redirects to create-account with email and role
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function registerStudent(Request $request)
    {
        // Validate incoming form data (no password required here)
        $validated = $request->validate([
            // Account Information
            'user_email' => 'required|email|unique:user_account,user_email',
            
            // Personal Information (Required)
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'phone' => 'required|regex:/^[0-9]{11}$/',
            
            // Personal Information (Optional)
            'middle_name' => 'nullable|string|max:100',
            'suffix' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:Male,Female,Other,Prefer not to say',
            
            // Musical Information (Optional)
            'instrument_id' => 'nullable|exists:instrument,instrument_id',
            'skill_level' => 'nullable|in:beginner,intermediate,advanced,expert',
            'music_goals' => 'nullable|string',
        ], [
            'user_email.unique' => 'This email is already registered.',
            'phone.regex' => 'Phone number must be exactly 11 digits.',
        ]);

        DB::beginTransaction();

        try {
            // Step 1: Create the main user account without password
            $user = UserAccount::create([
                'user_email' => $validated['user_email'],
                'user_password' => null, // Will be set later
                'is_super_admin' => false,
            ]);

            // Step 2: Get the "Active" status ID for new students
            $activeStatusId = DB::table('student_status')
                ->where('status_name', 'Active')
                ->value('status_id');

            if (!$activeStatusId) {
                throw new \Exception('Active student status not found in database.');
            }

            // Step 3: Create the student profile linked to the user
            DB::table('student')->insert([
                'user_id' => $user->user_id,
                'first_name' => $validated['first_name'],
                'middle_name' => $validated['middle_name'] ?? null,
                'last_name' => $validated['last_name'],
                'suffix' => $validated['suffix'] ?? null,
                'phone' => $validated['phone'],
                'email' => $validated['user_email'],
                'date_of_birth' => $validated['date_of_birth'] ?? null,
                'gender' => $validated['gender'] ?? null,
                'instrument_id' => $validated['instrument_id'] ?? null,
                'skill_level' => $validated['skill_level'] ?? null,
                'music_goals' => $validated['music_goals'] ?? null,
                'student_status_id' => $activeStatusId,
                'enrollment_date' => now()->format('Y-m-d'),
                'country' => 'Philippines',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();

            // Redirect to password setup page
            return redirect()->route('account.create')
                ->with('email', $validated['user_email'])
                ->with('role', 'student');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Student registration failed: ' . $e->getMessage());

            return back()->withInput()
                ->withErrors(['error' => 'Registration failed. Please try again.']);
        }
    }

    // ============================================================================
    // INSTRUCTOR REGISTRATION
    // ============================================================================

    /**
     * Display the instructor registration form
     * 
     * Loads all active specializations for selection
     */
    public function showInstructorRegistrationForm()
    {
        $specializations = DB::table('specialization')
            ->where('is_active', true)
            ->orderBy('specialization_name')
            ->get();

        return view('auth.register.instructor', compact('specializations'));
    }

    /**
     * Process instructor registration submission
     * 
     * Validates specializations and primary selection carefully
     */
    public function registerInstructor(Request $request)
    {
        $validated = $request->validate([
            // Account
            'user_email' => 'required|email|unique:user_account,user_email',
            
            // Personal Info
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'suffix' => 'nullable|string|max:20',
            'phone' => 'required|regex:/^[0-9]{11}$/',
            'date_of_birth' => 'required|date',
            'gender' => 'required|in:Male,Female,Other,Prefer not to say',
            'nationality' => 'nullable|string|max:100',
            
            // Address (ALL REQUIRED)
            'address_line1' => 'required|string|max:255',
            'address_line2' => 'nullable|string|max:255',
            'city' => 'required|string|max:100',
            'province' => 'required|string|max:100',
            'postal_code' => 'required|string|max:20',
            'country' => 'required|string|max:100',
            
            // Emergency Contact (ALL REQUIRED)
            'emergency_contact_name' => 'required|string|max:100',
            'emergency_contact_relationship' => 'required|string|max:100',
            'emergency_contact_phone' => 'required|regex:/^[0-9]{11}$/',
            
            // Professional
            'education_level' => 'required|string|max:100',
            'music_degree' => 'nullable|string|max:200',
            'years_of_experience' => 'required|integer|min:0',
            'languages_spoken' => 'nullable|string|max:255',
            'teaching_style' => 'nullable|string',
            'bio' => 'nullable|string',
            'certifications' => 'nullable|array',
            'certifications.*' => 'nullable|string|max:255',
            
            // Specializations
            'specializations' => 'required|array|min:1',
            'specializations.*' => 'exists:specialization,specialization_id',
            'primary_specialization' => ['required', Rule::in($request->input('specializations', []))],
        ], [
            'specializations.required' => 'Please select at least one specialization.',
            'primary_specialization.in' => 'Primary specialization must be one of your selected specializations.',
        ]);

        DB::beginTransaction();

        try {
            // Create user account
            $user = UserAccount::create([
                'user_email' => $validated['user_email'],
                'user_password' => null,
                'is_super_admin' => false,
            ]);

            // Insert instructor and get the instructor_id (not "id")
            DB::table('instructor')->insert([
                'user_id' => $user->user_id,
                'first_name' => $validated['first_name'],
                'middle_name' => $validated['middle_name'] ?? null,
                'last_name' => $validated['last_name'],
                'suffix' => $validated['suffix'] ?? null,
                'phone' => $validated['phone'],
                'email' => $validated['user_email'],
                'date_of_birth' => $validated['date_of_birth'],
                'gender' => $validated['gender'],
                'nationality' => $validated['nationality'] ?? null,
                
                // Address fields
                'address_line1' => $validated['address_line1'],
                'address_line2' => $validated['address_line2'] ?? null,
                'city' => $validated['city'],
                'province' => $validated['province'],
                'postal_code' => $validated['postal_code'],
                'country' => $validated['country'],
                
                // Emergency contact
                'emergency_contact_name' => $validated['emergency_contact_name'],
                'emergency_contact_relationship' => $validated['emergency_contact_relationship'],
                'emergency_contact_phone' => $validated['emergency_contact_phone'],
                
                // Professional
                'years_of_experience' => $validated['years_of_experience'],
                'education_level' => $validated['education_level'],
                'music_degree' => $validated['music_degree'] ?? null,
                'languages_spoken' => $validated['languages_spoken'] ?? null,
                'teaching_style' => $validated['teaching_style'] ?? null,
                'bio' => $validated['bio'] ?? null,
                
                'hire_date' => now(),
                'employment_status' => 'active',
                'is_available' => true,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Get the instructor_id we just created
            $instructorId = DB::table('instructor')
                ->where('user_id', $user->user_id)
                ->value('instructor_id');

            // Insert specializations
            foreach ($validated['specializations'] as $specializationId) {
                DB::table('instructor_specialization')->insert([
                    'instructor_id' => $instructorId,
                    'specialization_id' => $specializationId,
                    'is_primary' => ($specializationId == $validated['primary_specialization']),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::commit();

            // Redirect to password creation page
            return redirect()->route('account.create')
                ->with('email', $validated['user_email'])
                ->with('role', 'instructor')
                ->with('success', 'Registration successful! Please create your password.');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Instructor registration failed: ' . $e->getMessage());

            return back()->withInput()
                ->withErrors(['error' => 'Registration failed: ' . $e->getMessage()]);
        }
    }

    // ============================================================================
    // SALES STAFF REGISTRATION
    // ============================================================================

    public function showSalesStaffRegistrationForm()
    {
        return view('auth.register.sales-staff');
    }

    public function registerSalesStaff(Request $request)
    {
        $validated = $request->validate([
            'user_email' => 'required|email|unique:user_account,user_email',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'phone' => 'required|regex:/^[0-9]{11}$/',
            'middle_name' => 'nullable|string|max:100',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:Male,Female,Other,Prefer not to say',
        ]);

        DB::beginTransaction();

        try {
            $user = UserAccount::create([
                'user_email' => $validated['user_email'],
                'user_password' => null,
                'is_super_admin' => false,
            ]);

            DB::table('sales_staff')->insert([
                'user_id' => $user->user_id,
                'first_name' => $validated['first_name'],
                'middle_name' => $validated['middle_name'] ?? null,
                'last_name' => $validated['last_name'],
                'phone' => $validated['phone'],
                'email' => $validated['user_email'],
                'date_of_birth' => $validated['date_of_birth'] ?? null,
                'gender' => $validated['gender'] ?? null,
                'hire_date' => now(),
                'employment_status' => 'active',
                'country' => 'Philippines',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();

            return redirect()->route('account.create')
                ->with('email', $validated['user_email'])
                ->with('role', 'sales_staff');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Sales staff registration failed: ' . $e->getMessage());

            return back()->withInput()
                ->withErrors(['error' => 'Registration failed. Please try again.']);
        }
    }

    // ============================================================================
    // ALL-AROUND STAFF REGISTRATION
    // ============================================================================

    public function showStaffRegistrationForm()
    {
        return view('auth.register.staff');
    }

    public function registerStaff(Request $request)
    {
        $validated = $request->validate([
            'user_email' => 'required|email|unique:user_account,user_email',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'phone' => 'required|regex:/^[0-9]{11}$/',
            'middle_name' => 'nullable|string|max:100',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:Male,Female,Other,Prefer not to say',
        ]);

        DB::beginTransaction();

        try {
            $user = UserAccount::create([
                'user_email' => $validated['user_email'],
                'user_password' => null,
                'is_super_admin' => false,
            ]);

            DB::table('all_around_staff')->insert([
                'user_id' => $user->user_id,
                'first_name' => $validated['first_name'],
                'middle_name' => $validated['middle_name'] ?? null,
                'last_name' => $validated['last_name'],
                'phone' => $validated['phone'],
                'email' => $validated['user_email'],
                'date_of_birth' => $validated['date_of_birth'] ?? null,
                'gender' => $validated['gender'] ?? null,
                'hire_date' => now(),
                'employment_status' => 'active',
                'country' => 'Philippines',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();

            return redirect()->route('account.create')
                ->with('email', $validated['user_email'])
                ->with('role', 'all_around_staff');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('All-around staff registration failed: ' . $e->getMessage());

            return back()->withInput()
                ->withErrors(['error' => 'Registration failed. Please try again.']);
        }
    }

    // ============================================================================
    // PASSWORD SETUP (CREATE ACCOUNT)
    // ============================================================================

    /**
     * Show the create account (password setup) form
     * 
     * @return \Illuminate\View\View
     */
    public function showCreateAccountForm()
    {
        $email = session('email');
        $role = session('role', 'student'); // Get role from session
        
        if (!$email) {
            return redirect()->route('register')
                ->withErrors(['error' => 'Invalid session. Please start registration again.']);
        }

        // Verify the user exists and hasn't set password yet
        $user = UserAccount::where('user_email', $email)->first();
        
        if (!$user) {
            return redirect()->route('register')
                ->withErrors(['error' => 'Account not found. Please register again.']);
        }
        
        if ($user->user_password !== null) {
            return redirect()->route('login')
                ->with('info', 'Account already activated. Please login.');
        }

        return view('auth.create-account', compact('email', 'role'));
    }

    /**
     * Process password setup
     * 
     * Validates password, updates user, logs in, redirects to dashboard
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function processCreateAccount(Request $request)
    {
        $validated = $request->validate([
            'user_email' => 'required|email|exists:user_account,user_email',
            'user_password' => ['required', 'confirmed', Password::min(8)],
            'role' => 'required|in:student,instructor,sales_staff,all_around_staff',
        ]);

        $user = UserAccount::where('user_email', $validated['user_email'])->firstOrFail();

        // Prevent setting password if already set
        if ($user->user_password !== null) {
            return redirect()->route('login')
                ->withErrors(['error' => 'Account already activated. Please login.']);
        }

        // Set password (will be hashed by mutator in UserAccount model)
        $user->user_password = $validated['user_password'];
        $user->save();

        // Log the user in immediately
        Auth::login($user);

        // Redirect based on role
        $dashboardRoute = match ($validated['role']) {
            'student' => 'student.dashboard',
            'instructor' => 'instructor.dashboard',
            'sales_staff' => 'sales.dashboard',
            'all_around_staff' => 'staff.dashboard',
            default => 'home',
        };

        return redirect()->route($dashboardRoute)
            ->with('success', 'Welcome! Your account has been activated successfully.');
    }
}