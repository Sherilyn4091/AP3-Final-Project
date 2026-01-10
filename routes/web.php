<?php 
 
// routes/web.php 
 
use Illuminate\Support\Facades\Route; 
use Illuminate\Support\Facades\DB; 
use App\Http\Controllers\Auth\RegistrationController; 
use App\Http\Controllers\Auth\LoginController; 
use App\Http\Controllers\Admin\DashboardController; 
use App\Http\Controllers\Admin\UserController; 
use App\Http\Controllers\Admin\InstructorController; 
 
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
            // ============================================================================ 
            // **IMPORTANT**: Static routes must be defined BEFORE dynamic routes. 
            // Laravel's router matches routes from top to bottom. If the dynamic `/{id}` route 
            // were first, it would incorrectly capture requests for "create", "students", etc., 
            // treating them as user IDs and causing an error. 
            // ============================================================================ 
 
            // Route to display the main user list. 
            Route::get('/', [UserController::class, 'index'])->name('index'); 
 
            // Route to show the form for creating a new user. 
            Route::get('/create', [UserController::class, 'create'])->name('create'); 
             
            // Route to handle the submission of the new user form. 
            Route::post('/', [UserController::class, 'store'])->name('store'); 
             
            // Redirect old student route to new student management page
            Route::get('/students', function() {
                return redirect()->route('admin.students.index');
            })->name('students');

            // These views might not exist yet - create them or redirect as needed
            Route::get('/sales-staff', fn() => view('admin.users.sales-staff'))->name('sales-staff'); 
            Route::get('/all-around-staff', fn() => view('admin.users.all-around-staff'))->name('all-around-staff');

            // Routes for bulk actions that operate on multiple users. 
            Route::post('/bulk-deactivate', [UserController::class, 'bulkDeactivate'])->name('bulk-deactivate'); 
            Route::post('/bulk-delete', [UserController::class, 'bulkDestroy'])->name('bulk-delete'); 
 
            // --- Dynamic Routes --- 
            // Routes that handle actions for a specific user, identified by their {id}. 
            // This block must come AFTER all static routes listed above. 
            Route::get('/{id}', [UserController::class, 'show'])->name('show'); 
            Route::put('/{id}', [UserController::class, 'update'])->name('update'); 
            Route::delete('/{id}', [UserController::class, 'destroy'])->name('destroy'); 
             
            Route::post('/{id}/reset-password', [UserController::class, 'resetPassword'])->name('reset-password'); 
            Route::post('/{id}/deactivate', [UserController::class, 'deactivate'])->name('deactivate'); 
            Route::post('/{id}/activate', [UserController::class, 'activate'])->name('activate'); 
             
            Route::get('/{id}/deletion-impact', [UserController::class, 'getDeletionImpact'])->name('deletion-impact'); 
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
        
        // ============================================================================
        // INSTRUCTORS MANAGEMENT (MOVED INSIDE ADMIN GROUP)
        // ============================================================================
        Route::prefix('instructors')->name('instructors.')->group(function () { 
            Route::get('/', [InstructorController::class, 'index'])->name('index'); 
            Route::get('/{id}', [InstructorController::class, 'show'])->name('show'); 
            Route::put('/{id}', [InstructorController::class, 'update'])->name('update'); 
             
            // Specialization management 
            Route::post('/{id}/specializations', [InstructorController::class, 'assignSpecialization'])->name('assign-specialization'); 
            Route::delete('/{id}/specializations/{specializationId}', [InstructorController::class, 'removeSpecialization'])->name('remove-specialization'); 
            Route::put('/{id}/specializations/{specializationId}/primary', [InstructorController::class, 'setPrimarySpecialization'])->name('set-primary-specialization'); 
             
            // Availability management 
            Route::put('/{id}/availability', [InstructorController::class, 'updateAvailability'])->name('update-availability'); 
             
            // Performance report 
            Route::get('/{id}/performance', [InstructorController::class, 'performanceReport'])->name('performance-report'); 
        });

         // Student Management Routes (inside admin group)
        Route::prefix('students')->name('students.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\StudentController::class, 'index'])->name('index');
            Route::get('/{id}', [App\Http\Controllers\Admin\StudentController::class, 'show'])->name('show');
            Route::put('/{id}', [App\Http\Controllers\Admin\StudentController::class, 'update'])->name('update');
            Route::post('/bulk-status', [App\Http\Controllers\Admin\StudentController::class, 'bulkUpdateStatus'])->name('bulk-status');
            Route::get('/{id}/attendance', [App\Http\Controllers\Admin\StudentController::class, 'getAttendance'])->name('attendance');
            Route::get('/{id}/progress', [App\Http\Controllers\Admin\StudentController::class, 'getProgress'])->name('progress');
        });
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
 
