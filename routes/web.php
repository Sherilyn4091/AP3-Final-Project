<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\Auth\RegistrationController;
use App\Http\Controllers\Auth\LoginController;

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\InstructorController;
use App\Http\Controllers\Admin\LessonSessionController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\ScheduleController;
use App\Http\Controllers\Admin\InstrumentController;
use App\Http\Controllers\Admin\SpecializationController;
use App\Http\Controllers\Admin\GenreController;
use App\Http\Controllers\Admin\PaymentMethodController;
use App\Http\Controllers\Admin\PaymentStatusController;

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
            // ============================================================================
            // **IMPORTANT**: Static routes must be defined BEFORE dynamic routes.
            // Laravel's router matches routes from top to bottom.
            // ============================================================================

            // Main user list
            Route::get('/', [UserController::class, 'index'])->name('index');

            // Create new user
            Route::get('/create', [UserController::class, 'create'])->name('create');
            Route::post('/', [UserController::class, 'store'])->name('store');

            // Redirect old student route
            Route::get('/students', function () {
                return redirect()->route('admin.students.index');
            })->name('students');

            // Placeholder views (create them or redirect later)
            Route::get('/sales-staff', fn() => view('admin.users.sales-staff'))->name('sales-staff');
            Route::get('/all-around-staff', fn() => view('admin.users.all-around-staff'))->name('all-around-staff');

            // Bulk actions
            Route::post('/bulk-deactivate', [UserController::class, 'bulkDeactivate'])->name('bulk-deactivate');
            Route::post('/bulk-delete', [UserController::class, 'bulkDestroy'])->name('bulk-delete');

            // Dynamic routes (must come AFTER static ones)
            Route::get('/{id}', [UserController::class, 'show'])->name('show');
            Route::put('/{id}', [UserController::class, 'update'])->name('update');
            Route::delete('/{id}', [UserController::class, 'destroy'])->name('destroy');

            Route::post('/{id}/reset-password', [UserController::class, 'resetPassword'])->name('reset-password');
            Route::post('/{id}/deactivate', [UserController::class, 'deactivate'])->name('deactivate');
            Route::post('/{id}/activate', [UserController::class, 'activate'])->name('activate');

            Route::get('/{id}/deletion-impact', [UserController::class, 'getDeletionImpact'])->name('deletion-impact');
        });

        // Lessons Management (placeholder)
        Route::get('/lessons', function () {
            return view('admin.lessons.index');
        })->name('lessons.index');

        // Instruments Management
        Route::prefix('instruments')->name('instruments.')->group(function () {
            Route::get('/', [InstrumentController::class, 'index'])->name('index');
            Route::post('/', [InstrumentController::class, 'store'])->name('store');
            Route::get('/{id}', [InstrumentController::class, 'show'])->name('show');
            Route::put('/{id}', [InstrumentController::class, 'update'])->name('update');
            Route::delete('/{id}', [InstrumentController::class, 'destroy'])->name('destroy');

            Route::post('/{id}/toggle-status', [InstrumentController::class, 'toggleStatus'])->name('toggle-status');
            Route::get('/{id}/usage', [InstrumentController::class, 'getUsageDetails'])->name('usage');
        });

        // Instructors Management
        Route::prefix('instructors')->name('instructors.')->group(function () {
            Route::get('/', [InstructorController::class, 'index'])->name('index');
            Route::get('/{id}', [InstructorController::class, 'show'])->name('show');
            Route::put('/{id}', [InstructorController::class, 'update'])->name('update');

            Route::post('/{id}/specializations', [InstructorController::class, 'assignSpecialization'])->name('assign-specialization');
            Route::delete('/{id}/specializations/{specializationId}', [InstructorController::class, 'removeSpecialization'])->name('remove-specialization');
            Route::put('/{id}/specializations/{specializationId}/primary', [InstructorController::class, 'setPrimarySpecialization'])->name('set-primary-specialization');

            Route::put('/{id}/availability', [InstructorController::class, 'updateAvailability'])->name('update-availability');

            Route::get('/{id}/performance', [InstructorController::class, 'performanceReport'])->name('performance-report');
        });

        // Students Management
        Route::prefix('students')->name('students.')->group(function () {
            Route::get('/', [StudentController::class, 'index'])->name('index');
            Route::get('/{id}', [StudentController::class, 'show'])->name('show');
            Route::put('/{id}', [StudentController::class, 'update'])->name('update');
            Route::post('/bulk-status', [StudentController::class, 'bulkUpdateStatus'])->name('bulk-status');
            Route::get('/{id}/attendance', [StudentController::class, 'getAttendance'])->name('attendance');
            Route::get('/{id}/progress', [StudentController::class, 'getProgress'])->name('progress');
        });

        // Schedules Management
        Route::prefix('schedules')->name('schedules.')->group(function () {
            Route::get('/', [ScheduleController::class, 'index'])->name('index');

            Route::get('/events', [ScheduleController::class, 'getEvents'])->name('events');
            Route::get('/enrollments', [ScheduleController::class, 'getEnrollments'])->name('enrollments');
            Route::get('/rooms', function () {
                return response()->json(DB::table('room')->where('is_active', true)->get());
            })->name('rooms');

            Route::post('/', [ScheduleController::class, 'store'])->name('store');
            Route::get('/{id}', [ScheduleController::class, 'show'])->name('show');
            Route::put('/{id}', [ScheduleController::class, 'update'])->name('update');
            Route::delete('/{id}', [ScheduleController::class, 'destroy'])->name('destroy');

            Route::post('/{id}/quick-update', [ScheduleController::class, 'quickUpdate'])->name('quick-update');

            Route::get('/check-availability', [ScheduleController::class, 'checkAvailability'])->name('check-availability');
        });

        // Specializations Management
        Route::prefix('specializations')->name('specializations.')->group(function () {
            Route::get('/', [SpecializationController::class, 'index'])->name('index');
            Route::post('/', [SpecializationController::class, 'store'])->name('store');
            Route::get('/{id}', [SpecializationController::class, 'show'])->name('show');
            Route::put('/{id}', [SpecializationController::class, 'update'])->name('update');
            Route::delete('/{id}', [SpecializationController::class, 'destroy'])->name('destroy');

            Route::post('/{id}/toggle-status', [SpecializationController::class, 'toggleStatus'])->name('toggle-status');
            Route::get('/{id}/instructors', [SpecializationController::class, 'getInstructors'])->name('instructors');
        });

        // Genres Management
        Route::prefix('genres')->name('genres.')->group(function () {
            Route::get('/', [GenreController::class, 'index'])->name('index');
            Route::post('/', [GenreController::class, 'store'])->name('store');
            Route::get('/{id}', [GenreController::class, 'show'])->name('show');
            Route::put('/{id}', [GenreController::class, 'update'])->name('update');
            Route::delete('/{id}', [GenreController::class, 'destroy'])->name('destroy');

            Route::post('/{id}/toggle-status', [GenreController::class, 'toggleStatus'])->name('toggle-status');
            Route::get('/{id}/students', [GenreController::class, 'getStudents'])->name('students');
        });

        // Payment Methods Management
        Route::prefix('payment-methods')->name('payment-methods.')->group(function () {
            Route::get('/', [PaymentMethodController::class, 'index'])->name('index');
            Route::post('/', [PaymentMethodController::class, 'store'])->name('store');
            Route::put('/{id}', [PaymentMethodController::class, 'update'])->name('update');
            Route::get('/{id}/edit', [PaymentMethodController::class, 'edit'])->name('edit');
            Route::delete('/{id}', [PaymentMethodController::class, 'destroy'])->name('destroy');

            Route::post('/{id}/toggle-status', [PaymentMethodController::class, 'toggleStatus'])->name('toggle-status');
            Route::get('/{id}/usage', [PaymentMethodController::class, 'getUsage'])->name('usage');
        });

        // Payment Statuses Management
        Route::prefix('payment-statuses')->name('payment-statuses.')->group(function () {
            Route::get('/', [PaymentStatusController::class, 'index'])->name('index');
            Route::post('/', [PaymentStatusController::class, 'store'])->name('store');
            Route::put('/{id}', [PaymentStatusController::class, 'update'])->name('update');
            Route::delete('/{id}', [PaymentStatusController::class, 'destroy'])->name('destroy');

            Route::post('/{id}/toggle-status', [PaymentStatusController::class, 'toggleStatus'])->name('toggle-status');
            Route::get('/{id}/usage', [PaymentStatusController::class, 'getUsage'])->name('usage');
        });

        // Lesson Sessions Management
        Route::prefix('lesson-sessions')->name('lesson-sessions.')->group(function () {
            Route::get('/', [LessonSessionController::class, 'index'])->name('index');
            Route::get('/create', [LessonSessionController::class, 'create'])->name('create');
            Route::post('/', [LessonSessionController::class, 'store'])->name('store');
            Route::get('/{session_id}/edit', [LessonSessionController::class, 'edit'])->name('edit');
            Route::put('/{session_id}', [LessonSessionController::class, 'update'])->name('update');
            Route::delete('/{session_id}', [LessonSessionController::class, 'destroy'])->name('destroy');
            Route::post('/{session_id}/toggle-status', [LessonSessionController::class, 'toggleStatus'])->name('toggle-status');
        });

        // Reports
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/', function () {
                return view('admin.reports.index');
            })->name('index');

            Route::get('/financial', function () {
                return view('admin.reports.financial');
            })->name('financial');
        });

        // Placeholder routes (to be implemented)
        Route::get('/payments', function () {
            return view('admin.payments.index');
        })->name('payments.index');

        Route::get('/inventory', function () {
            return view('admin.inventory.index');
        })->name('inventory.index');

        Route::get('/settings', function () {
            return view('admin.settings.index');
        })->name('settings.index');
    }); // End of admin prefix group

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
        $userId = auth()->id();

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