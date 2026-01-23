<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

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
use App\Http\Controllers\Api\ChartController;
use App\Http\Controllers\ReviewController;


use App\Http\Controllers\Admin\ReportsController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\SupplierController;

use App\Http\Controllers\Instructor\InstructorController as InstructorPortalController;
use App\Http\Controllers\Instructor\InstructorDashboardController;
use App\Http\Controllers\Instructor\StudentController as InstructorStudentController;
use App\Http\Controllers\Instructor\ScheduleController as InstructorScheduleController;
use App\Http\Controllers\Instructor\AttendanceController;
use App\Http\Controllers\Instructor\ProgressController;
use App\Http\Controllers\Instructor\InstructorProfileController;

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

    // Password reset route
    Route::post('/forgot-password', [LoginController::class, 'resetPassword']);

    // Role Selection Page
    Route::get('/register', [RegistrationController::class, 'showStudentRegistrationForm'])->name('register');

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

    // ============================================================================
    // PASSWORD SETUP AFTER REGISTRATION (Guest only)
    // ============================================================================

    Route::get('/create-account', [RegistrationController::class, 'showCreateAccountForm'])
        ->name('account.create');

    Route::post('/create-account', [RegistrationController::class, 'processCreateAccount'])
        ->name('account.create.process');

}); // End of guest middleware group

// ============================================================================
// REVIEW ROUTES (Public - No Auth Required)
// ============================================================================
Route::get('/reviews', [ReviewController::class, 'index'])->name('reviews.index');
Route::post('/reviews', [ReviewController::class, 'store'])->name('reviews.store');

// ============================================================================
// AUTHENTICATED ROUTES
// ============================================================================

Route::middleware('auth')->group(function () {

    // Logout Route
    Route::post('/logout', [LoginController::class, 'logout'])
        ->name('logout');

    // Role-Specific Dashboard Routes
    Route::get('/student/dashboard', [App\Http\Controllers\Student\DashboardController::class, 'index'])->name('student.dashboard');

    Route::get('/instructor/dashboard', function () {
        return view('dashboards.instructor');
    })->name('instructor.dashboard');

    // ============================================================================
    // ADMIN ROUTES
    // ============================================================================

    Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {

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

            Route::get('/admin/instruments/{id}/students', [InstrumentController::class, 'getStudents']);

            Route::get('/{id}/students', [InstrumentController::class, 'getStudents'])->name('students');
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
            
            // STATIC ROUTES
            Route::get('/events', [ScheduleController::class, 'getEvents'])->name('events');
            Route::get('/enrollments', [ScheduleController::class, 'getEnrollments'])->name('enrollments');
            Route::get('/check-availability', [ScheduleController::class, 'checkAvailability'])->name('check-availability');
            Route::get('/rooms', function () {
                return response()->json(DB::table('room')->where('is_active', true)->get());
            })->name('rooms');
            
            // DYNAMIC ROUTES
            Route::post('/', [ScheduleController::class, 'store'])->name('store');
            Route::get('/{id}', [ScheduleController::class, 'show'])->name('show');
            Route::put('/{id}', [ScheduleController::class, 'update'])->name('update');
            Route::delete('/{id}', [ScheduleController::class, 'destroy'])->name('destroy');
            Route::post('/{id}/quick-update', [ScheduleController::class, 'quickUpdate'])->name('quick-update');
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
            Route::get('/{session_id}/enrollments', [LessonSessionController::class, 'getEnrollments'])->name('enrollments');
        });

        // ============================================================================
        // REPORTS MANAGEMENT
        // ============================================================================
        Route::prefix('reports')->name('reports.')->group(function () {
            // Monthly Reports Dashboard
            Route::get('/', [ReportsController::class, 'index'])->name('index');
            
            // Export Monthly Report as PDF
            Route::get('/export-pdf', [ReportsController::class, 'exportPdf'])->name('export-pdf');
            
            // Financial Report (YOUR EXISTING ROUTE)
            Route::get('/financial', function () {
                return view('admin.reports.financial');
            })->name('financial');
        });

        // ============================================================================
        // INVENTORY MANAGEMENT
        // ============================================================================
        Route::prefix('inventory')->name('inventory.')->group(function () {
            Route::get('/', [InventoryController::class, 'index'])->name('index');
            Route::get('create', [InventoryController::class, 'create'])->name('create');
            Route::post('/', [InventoryController::class, 'store'])->name('store');
            Route::get('{inventory}/edit', [InventoryController::class, 'edit'])->name('edit');
            Route::put('{inventory}', [InventoryController::class, 'update'])->name('update');
            Route::delete('{inventory}', [InventoryController::class, 'destroy'])->name('destroy');
            Route::post('{inventory}/activate', [InventoryController::class, 'activate'])->name('activate');
            Route::get('low-stock', [InventoryController::class, 'lowStockView'])->name('low-stock');
            Route::get('export', [InventoryController::class, 'export'])->name('export');
        });
        
        // ============================================================================
        // SUPPLIER MANAGEMENT
        // ============================================================================
        Route::prefix('suppliers')->name('suppliers.')->group(function () {
            Route::get('/', [SupplierController::class, 'index'])->name('index');
            Route::get('/create', [SupplierController::class, 'create'])->name('create');
            Route::post('/', [SupplierController::class, 'store'])->name('store');
            Route::get('/{id}/edit', [SupplierController::class, 'edit'])->name('edit');
            Route::put('/{id}', [SupplierController::class, 'update'])->name('update');
            Route::delete('/{id}', [SupplierController::class, 'destroy'])->name('destroy');
        });

        // Placeholder routes (to be implemented)
        Route::get('/payments', function () {
            return view('admin.payments.index');
        })->name('payments.index');

        Route::get('/settings', function () {
            return view('admin.settings.index');
        })->name('settings.index');

        Route::post('/admin/users/{id}/activate', [UserController::class, 'activate'])->name('admin.users.activate');
        Route::post('/admin/users/{id}/deactivate', [UserController::class, 'deactivate'])->name('admin.users.deactivate');

        
        Route::post('/change-password', function (Request $request) {
            try {
                $validated = $request->validate([
                    'current_password' => 'required|string',
                    'password' => 'required|string|min:8'
                ]);

                $user = Auth::user();
                
                if (!Hash::check($validated['current_password'], $user->user_password)) {
                    return response()->json([
                        'success' => false, 
                        'message' => 'Current password is incorrect'
                    ], 400);
                }
                
                DB::table('user_account')
                    ->where('user_id', $user->user_id)
                    ->update(['user_password' => Hash::make($validated['password'])]);

                return response()->json([
                    'success' => true, 
                    'message' => 'Password changed successfully'
                ]);
                
            } catch (\Exception $e) {
                \Log::error('Password change error: ' . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'message' => 'Server error. Please try again.'
                ], 500);
            }
        })->name('admin.change-password');

    }); // End of admin prefix group

    // ============================================================================
    // CHART API ENDPOINTS (Admin only)
    // ============================================================================

    Route::prefix('api/admin/charts')->middleware('auth')->group(function () {
        Route::get('/enrollment-trend', [App\Http\Controllers\Api\ChartController::class, 'enrollmentTrend']);
        Route::get('/revenue-weekly', [App\Http\Controllers\Api\ChartController::class, 'revenueWeekly']);
        Route::get('/instrument-popularity', [App\Http\Controllers\Api\ChartController::class, 'instrumentPopularity']);
        Route::get('/instructor-performance', [App\Http\Controllers\Api\ChartController::class, 'instructorPerformance']);
    });

    // ============================================================================
    // STUDENT ROUTES
    // ============================================================================
    Route::prefix('student')->name('student.')->group(function () {
        
        Route::get('/dashboard', function () {
            $userId = Auth::id();
            
            // Get student record
            $student = DB::table('student')->where('user_id', $userId)->first();
            
            if (!$student) {
                abort(404, 'Student record not found');
            }
            
            // Get current enrollment with lesson session details
            $currentEnrollment = DB::table('enrollment as e')
                ->leftJoin('lesson_session as ls', 'e.session_id', '=', 'ls.session_id')
                ->where('e.student_id', $student->student_id)
                ->where('e.status', 'active')
                ->select('e.*', 'ls.session_count', 'ls.session_name', 'ls.price')
                ->first();
            
            // Calculate progress percentage
            $progressPercentage = 0;
            if ($currentEnrollment && $currentEnrollment->total_sessions > 0) {
                $progressPercentage = round(($currentEnrollment->completed_sessions / $currentEnrollment->total_sessions) * 100);
            }
            
            // Get next lesson with instructor details
            $nextLesson = DB::table('schedule as s')
                ->leftJoin('instructor as i', 's.instructor_id', '=', 'i.instructor_id')
                ->where('s.student_id', $student->student_id)
                ->where('s.schedule_date', '>=', now()->toDateString())
                ->where('s.status', 'scheduled')
                ->select('s.*', 'i.first_name as instructor_first_name', 'i.last_name as instructor_last_name')
                ->orderBy('s.schedule_date')
                ->orderBy('s.start_time')
                ->first();
            
            // Get recent progress
            $recentProgress = DB::table('progress')
                ->where('student_id', $student->student_id)
                ->orderBy('progress_date', 'desc')
                ->limit(6)
                ->get();
            
            // Convert dates to Carbon instances
            if ($currentEnrollment) {
                $currentEnrollment->enrollment_date = $currentEnrollment->enrollment_date 
                    ? \Carbon\Carbon::parse($currentEnrollment->enrollment_date) : null;
            }
            
            if ($nextLesson) {
                $nextLesson->schedule_date = \Carbon\Carbon::parse($nextLesson->schedule_date);
                $nextLesson->start_time = \Carbon\Carbon::parse($nextLesson->start_time);
                $nextLesson->end_time = \Carbon\Carbon::parse($nextLesson->end_time);
                $nextLesson->instructor = (object)[
                    'first_name' => $nextLesson->instructor_first_name,
                    'last_name' => $nextLesson->instructor_last_name,
                ];
            }
            
            $recentProgress = $recentProgress->map(function($p) {
                $p->progress_date = \Carbon\Carbon::parse($p->progress_date);
                return $p;
            });
            
            if ($currentEnrollment) {
                $currentEnrollment->lessonSession = (object)[
                    'session_count' => $currentEnrollment->session_count,
                    'session_name' => $currentEnrollment->session_name,
                    'price' => $currentEnrollment->price,
                ];
            }
            
            return view('dashboards.student', compact('student', 'currentEnrollment', 'progressPercentage', 'nextLesson', 'recentProgress'));
        })->name('dashboard');
        
        // Schedule, Progress, Profile routes
        Route::get('/schedule', [App\Http\Controllers\Student\ScheduleController::class, 'index'])->name('schedule');
        Route::get('/progress', [App\Http\Controllers\Student\ProgressController::class, 'index'])->name('progress');
        Route::get('/profile', [App\Http\Controllers\Student\ProfileController::class, 'index'])->name('profile');
        Route::patch('/profile', [App\Http\Controllers\Student\ProfileController::class, 'update'])->name('profile.update');

        // Enrollment routes
        Route::get('/packages', [App\Http\Controllers\Student\EnrollmentController::class, 'packages'])->name('packages');
        Route::get('/enroll/{sessionId}', [App\Http\Controllers\Student\EnrollmentController::class, 'enrollmentForm'])->name('enroll.form');
        Route::get('/enrollments', [App\Http\Controllers\Student\EnrollmentController::class, 'index'])->name('enrollments');

        // Process enrollment form
        Route::post('/enroll/process', [App\Http\Controllers\Student\EnrollmentController::class, 'processEnrollment'])->name('enroll.process');

        // API: Get instructors by instrument
        Route::get('/api/instructors-by-instrument/{instrumentId}', [App\Http\Controllers\Student\EnrollmentController::class, 'getInstructorsByInstrument']);

        Route::post('/password/change', [App\Http\Controllers\Student\ProfileController::class, 'changePassword'])
            ->name('password.change');
    });
    

    // ============================================================================
    // INSTRUCTOR ROUTES (REFINED)
    // ============================================================================
    Route::prefix('instructor')
    ->name('instructor.')
    ->middleware(['auth', 'role:instructor'])
    ->group(function () {

        Route::get('/dashboard', [InstructorDashboardController::class, 'index'])
            ->name('dashboard');

        Route::get('/some-route', [InstructorPortalController::class, 'someMethod']);

        // Students
        Route::get('/students', [InstructorStudentController::class, 'index'])
            ->name('students.index');

        Route::get('/students/{student}', [InstructorStudentController::class, 'show'])
            ->whereNumber('student')
            ->name('students.show');

        // Schedule
        Route::get('/schedule', [InstructorScheduleController::class, 'index'])
            ->name('schedule.index');

        Route::get('/schedule/{schedule}/edit', [InstructorScheduleController::class, 'edit'])
            ->name('schedule.edit');

        Route::put('/schedule/{schedule}', [InstructorScheduleController::class, 'update'])
            ->name('schedule.update');

        // Schedule create/store
        Route::get('/schedule/create', [InstructorScheduleController::class, 'create'])->name('schedule.create');
        Route::post('/schedule', [InstructorScheduleController::class, 'store'])->name('schedule.store');

        // Attendance
        Route::get('/attendance', [AttendanceController::class, 'index'])
            ->name('attendance.index');

        Route::get('/attendance/{student}/edit', [AttendanceController::class, 'edit'])
            ->name('attendance.edit');

        Route::put('/attendance/{student}', [AttendanceController::class, 'update'])
            ->name('attendance.update');

        // Progress
        Route::get('/progress', [ProgressController::class, 'index'])->name('progress.index');
        Route::get('/progress/create', [ProgressController::class, 'create'])->name('progress.create');
        Route::post('/progress', [ProgressController::class, 'store'])->name('progress.store');

        Route::get('/progress/{progress}', [ProgressController::class, 'show'])
            ->whereNumber('progress')
            ->name('progress.show');

        Route::get('/progress/{progress}/edit', [ProgressController::class, 'edit'])
            ->whereNumber('progress')
            ->name('progress.edit');

        Route::put('/progress/{progress}', [ProgressController::class, 'update'])
            ->whereNumber('progress')
            ->name('progress.update');

        Route::get('/profile', [InstructorProfileController::class, 'index'])
            ->name('profile.index');
        Route::patch('/profile', [InstructorProfileController::class, 'update'])
            ->name('profile.update');
        Route::post('/profile/password', [InstructorProfileController::class, 'changePassword'])
            ->name('password.change');
    });

});

// ============================================================================
// HOME ROUTE
// ============================================================================

Route::get('/', function () {
    if (auth()->check()) {
        $userId = auth()->id();

        if (DB::table('student')->where('user_id', $userId)->exists()) {
            return redirect()->route('student.dashboard');
        } elseif (DB::table('instructor')->where('user_id', $userId)->exists()) {
            return redirect()->route('instructor.dashboard');
        }
    }

    return view('welcome');
})->name('home');

Route::get('/health', function () {
    return response()->json(['status' => 'ok'], 200);
});