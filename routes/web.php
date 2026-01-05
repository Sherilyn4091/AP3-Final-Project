<?php

// routes/web.php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Auth\RegistrationController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;

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

    // All-Around Staff Registration (UPDATED ROUTE)
    Route::get('/register/all-around-staff', [RegistrationController::class, 'showAllAroundStaffRegistrationForm'])
        ->name('register.staff.form');
    Route::post('/register/all-around-staff', [RegistrationController::class, 'registerAllAroundStaff'])
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

    // ============================================================================
    // ADMIN ROUTES
    // ============================================================================
    
    Route::prefix('admin')->name('admin.')->group(function () {
        
        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        
        // Users Management
        Route::prefix('users')->name('users.')->group(function () {
            // Must be first to avoid conflict with /create, /students, etc.
            Route::get('/{id}', [UserController::class, 'show'])->name('show');
            
            Route::get('/', [UserController::class, 'index'])->name('index');
            
            Route::post('/{id}/reset-password', [UserController::class, 'resetPassword'])->name('reset-password');
            Route::post('/{id}/deactivate', [UserController::class, 'deactivate'])->name('deactivate');
            Route::post('/{id}/activate', [UserController::class, 'activate'])->name('activate');
            Route::delete('/{id}', [UserController::class, 'destroy'])->name('destroy');
            
            Route::get('/{id}/deletion-impact', [UserController::class, 'getDeletionImpact'])->name('deletion-impact');
            
            Route::post('/bulk-deactivate', [UserController::class, 'bulkDeactivate'])->name('bulk-deactivate');
            Route::post('/bulk-delete', [UserController::class, 'bulkDestroy'])->name('bulk-destroy'); // Added

            // Static pages
            Route::get('/create', fn() => view('admin.users.create'))->name('create');
            Route::get('/students', fn() => view('admin.users.students'))->name('students');
            Route::get('/instructors', fn() => view('admin.users.instructors'))->name('instructors');
            Route::get('/sales-staff', fn() => view('admin.users.sales-staff'))->name('sales-staff');
            Route::get('/all-around-staff', fn() => view('admin.users.all-around-staff'))->name('all-around-staff');
        });
        
        // Lessons Management
        Route::get('/lessons', function () {
            return view('admin.lessons.index');
        })->name('lessons.index');
        
        // Instruments Management
        Route::get('/instruments', function () {
            return view('admin.instruments.index');
        })->name('instruments.index');
        
        // Payments Management
        Route::get('/payments', function () {
            return view('admin.payments.index');
        })->name('payments.index');
        
        // Inventory Management
        Route::get('/inventory', function () {
            return view('admin.inventory.index');
        })->name('inventory.index');
        
        // Reports
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/', function () {
                return view('admin.reports.index');
            })->name('index');
            
            Route::get('/financial', function () {
                return view('admin.reports.financial');
            })->name('financial');
        });
        
        // Schedules Management
        Route::get('/schedules', function () {
            return view('admin.schedules.index');
        })->name('schedules.index');
        
        // Settings
        Route::get('/settings', function () {
            return view('admin.settings.index');
        })->name('settings.index');
    });

    // ============================================================================
    // CHART API ENDPOINTS (Admin only)
    // ============================================================================
    
    Route::prefix('api/admin/charts')->middleware('auth')->group(function () {
        Route::get('/revenue-weekly', [DashboardController::class, 'getWeeklyRevenue']);
        Route::get('/enrollment-trend', [DashboardController::class, 'getEnrollmentTrend']);
        Route::get('/instrument-popularity', [DashboardController::class, 'getInstrumentPopularity']);
        Route::get('/instructor-performance', [DashboardController::class, 'getInstructorPerformance']);
    });
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