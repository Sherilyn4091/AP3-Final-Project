<?php

// routes/web.php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Auth\RegistrationController;
use App\Http\Controllers\Auth\LoginController;

/*
|--------------------------------------------------------------------------
| Authentication & Public Routes
|--------------------------------------------------------------------------
*/

// ============================================================================
// PUBLIC ROUTES (Guest only)
// ============================================================================

Route::middleware('guest')->group(function () {

    // Login
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.process');

    // Role Selection Page
    Route::get('/register', function () {
        return view('auth.register.select-role');
    })->name('register');

    // Student Registration
    Route::get('/register/student', [RegistrationController::class, 'showStudentRegistrationForm'])
        ->name('register.student.form');
    Route::post('/register/student', [RegistrationController::class, 'registerStudent'])
        ->name('register.student.process');

    // Instructor Registration
    Route::get('/register/instructor', [RegistrationController::class, 'showInstructorRegistrationForm'])
        ->name('register.instructor.form');
    Route::post('/register/instructor', [RegistrationController::class, 'registerInstructor'])
        ->name('register.instructor.process');

    // Sales Staff Registration
    Route::get('/register/sales-staff', [RegistrationController::class, 'showSalesStaffRegistrationForm'])
        ->name('register.sales.form');
    Route::post('/register/sales-staff', [RegistrationController::class, 'registerSalesStaff'])
        ->name('register.sales.process');

    // All-Around Staff Registration
    Route::get('/register/staff', [RegistrationController::class, 'showStaffRegistrationForm'])
        ->name('register.staff.form');
    Route::post('/register/staff', [RegistrationController::class, 'registerStaff'])
        ->name('register.staff.process');

    // ============================================================================
    // PASSWORD SETUP AFTER REGISTRATION (Guest only)
    // ============================================================================

    Route::get('/create-account', [RegistrationController::class, 'showCreateAccountForm'])
        ->name('account.create');

    Route::post('/create-account', [RegistrationController::class, 'processCreateAccount'])
        ->name('account.create.process');

}); // End of guest middleware group

// ============================================================================
// AUTHENTICATED ROUTES
// ============================================================================

Route::middleware('auth')->group(function () {

    // Logout Route
    Route::post('/logout', [LoginController::class, 'logout'])
        ->name('logout');

    // Role-Specific Dashboard Routes
    // Views are located in resources/views/dashboards/*.blade.php
    Route::get('/student/dashboard', function () {
        return view('dashboards.student');
    })->name('student.dashboard');

    Route::get('/instructor/dashboard', function () {
        return view('dashboards.instructor');
    })->name('instructor.dashboard');

    Route::get('/sales/dashboard', function () {
        return view('dashboards.sales');
    })->name('sales.dashboard');

    Route::get('/staff/dashboard', function () {
        return view('dashboards.staff');
    })->name('staff.dashboard');
});

// ============================================================================
// HOME ROUTE - Smart redirect based on role
// ============================================================================

Route::get('/', function () {
    if (auth()->check()) {
        $userId = auth()->id(); // Safe - uses the actual authenticated user ID

        if (DB::table('student')->where('user_id', $userId)->exists()) {
            return redirect()->route('student.dashboard');
        } elseif (DB::table('instructor')->where('user_id', $userId)->exists()) {
            return redirect()->route('instructor.dashboard');
        } elseif (DB::table('sales_staff')->where('user_id', $userId)->exists()) {
            return redirect()->route('sales.dashboard');
        } elseif (DB::table('all_around_staff')->where('user_id', $userId)->exists()) {
            return redirect()->route('staff.dashboard');
        }
    }

    return view('welcome');
})->name('home');