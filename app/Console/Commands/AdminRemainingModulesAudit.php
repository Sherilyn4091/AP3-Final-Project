<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Throwable;

/*
|--------------------------------------------------------------------------
| AdminRemainingModulesAudit
|--------------------------------------------------------------------------
|
| Purpose:
| - Checks the remaining Admin modules without changing data.
| - Avoids Laravel's db:show / db:table "intl" extension issue.
| - Helps debug routes, controllers, views, models, tables, columns,
|   row counts, foreign keys, and basic relationship health.
*/

class AdminRemainingModulesAudit extends Command
{
    /**
     * The command name.
     */
    protected $signature = 'admin:audit-remaining';

    /**
     * Command description.
     */
    protected $description = 'Safely audit the remaining Admin modules, routes, UI files, DB tables, and relationships.';

    /**
     * Target modules to check.
     */
    private array $modules = [
        'Users' => [
            'route_prefixes' => ['admin.users'],
            'controller' => 'app/Http/Controllers/Admin/UserController.php',
            'views' => [
                'resources/views/admin/users/index.blade.php',
                'resources/views/admin/users/create.blade.php',
            ],
            'js' => [
                'resources/js/admin-pages/user.js',
                'resources/js/admin-pages/user-create.js',
            ],
            'models' => [
                'app/Models/UserAccount.php',
                'app/Models/User.php',
            ],
            'tables' => ['user_account', 'users'],
        ],

        'Students' => [
            'route_prefixes' => ['admin.students'],
            'controller' => 'app/Http/Controllers/Admin/StudentController.php',
            'views' => [
                'resources/views/admin/students/index.blade.php',
            ],
            'js' => [
                'resources/js/admin-pages/student.js',
            ],
            'models' => [
                'app/Models/Student.php',
                'app/Models/StudentStatus.php',
            ],
            'tables' => ['student', 'student_status'],
        ],

        'Instructors' => [
            'route_prefixes' => ['admin.instructors'],
            'controller' => 'app/Http/Controllers/Admin/InstructorController.php',
            'views' => [
                'resources/views/admin/instructors/index.blade.php',
            ],
            'js' => [
                'resources/js/admin-pages/instructor.js',
            ],
            'models' => [
                'app/Models/Instructor.php',
                'app/Models/InstructorSpecialization.php',
            ],
            'tables' => ['instructor', 'instructor_specialization'],
        ],

        'Schedule' => [
            'route_prefixes' => ['admin.schedules'],
            'controller' => 'app/Http/Controllers/Admin/ScheduleController.php',
            'views' => [
                'resources/views/admin/schedules/index.blade.php',
            ],
            'js' => [
                'resources/js/admin-pages/schedule.js',
            ],
            'models' => [
                'app/Models/Schedule.php',
            ],
            'tables' => ['schedule', 'room', 'enrollment'],
        ],

        'Lesson Packages' => [
            'route_prefixes' => ['admin.lesson-sessions', 'admin.lessons'],
            'controller' => 'app/Http/Controllers/Admin/LessonSessionController.php',
            'views' => [
                'resources/views/admin/lesson-sessions/index.blade.php',
            ],
            'js' => [
                'resources/js/admin-pages/lesson-session.js',
            ],
            'models' => [
                'app/Models/LessonSession.php',
            ],
            'tables' => ['lesson_session'],
        ],

        'Instruments' => [
            'route_prefixes' => ['admin.instruments'],
            'controller' => 'app/Http/Controllers/Admin/InstrumentController.php',
            'views' => [
                'resources/views/admin/instruments/index.blade.php',
            ],
            'js' => [
                'resources/js/admin-pages/instrument.js',
            ],
            'models' => [
                'app/Models/Instrument.php',
            ],
            'tables' => ['instrument'],
        ],

        'Specializations' => [
            'route_prefixes' => ['admin.specializations'],
            'controller' => 'app/Http/Controllers/Admin/SpecializationController.php',
            'views' => [
                'resources/views/admin/specializations/index.blade.php',
            ],
            'js' => [
                'resources/js/admin-pages/specialization.js',
            ],
            'models' => [
                'app/Models/Specialization.php',
            ],
            'tables' => ['specialization'],
        ],

        'Genres' => [
            'route_prefixes' => ['admin.genres'],
            'controller' => 'app/Http/Controllers/Admin/GenreController.php',
            'views' => [
                'resources/views/admin/genres/index.blade.php',
            ],
            'js' => [
                'resources/js/admin-pages/genre.js',
            ],
            'models' => [
                'app/Models/Genre.php',
            ],
            'tables' => ['genre'],
        ],

        'Payment Methods' => [
            'route_prefixes' => ['admin.payment-methods'],
            'controller' => 'app/Http/Controllers/Admin/PaymentMethodController.php',
            'views' => [
                'resources/views/admin/payment-methods/index.blade.php',
            ],
            'js' => [
                'resources/js/admin-pages/payment-method.js',
            ],
            'models' => [
                'app/Models/PaymentMethod.php',
            ],
            'tables' => ['payment_methods'],
        ],

        'Payment Statuses' => [
            'route_prefixes' => ['admin.payment-statuses'],
            'controller' => 'app/Http/Controllers/Admin/PaymentStatusController.php',
            'views' => [
                'resources/views/admin/payment-statuses/index.blade.php',
            ],
            'js' => [
                'resources/js/admin-pages/payment-status.js',
            ],
            'models' => [
                'app/Models/PaymentStatus.php',
            ],
            'tables' => ['payment_status'],
        ],

        'Inventory' => [
            'route_prefixes' => ['admin.inventory'],
            'controller' => 'app/Http/Controllers/Admin/InventoryController.php',
            'views' => [
                'resources/views/admin/inventory/index.blade.php',
                'resources/views/admin/inventory/create.blade.php',
                'resources/views/admin/inventory/edit.blade.php',
            ],
            'js' => [],
            'models' => [
                'app/Models/Inventory.php',
            ],
            'tables' => ['inventory', 'supplier', 'instrument'],
        ],

        'Suppliers' => [
            'route_prefixes' => ['admin.suppliers'],
            'controller' => 'app/Http/Controllers/Admin/SupplierController.php',
            'views' => [
                'resources/views/admin/suppliers/index.blade.php',
                'resources/views/admin/suppliers/create.blade.php',
                'resources/views/admin/suppliers/edit.blade.php',
            ],
            'js' => [],
            'models' => [
                'app/Models/Supplier.php',
            ],
            'tables' => ['supplier'],
        ],
    ];

    /**
     * Run the audit.
     */
    public function handle(): int
    {
        $this->newLine();
        $this->line('============================================================');
        $this->line('MUSIC LAB ADMIN REMAINING MODULES - SAFE AUDIT');
        $this->line('============================================================');

        $this->showEnvironmentSummary();
        $this->showModuleChecklist();
        $this->showDatabaseRelationshipChecks();
        $this->showUiConsistencyChecklist();
        $this->showSuggestedDebugCommands();

        $this->newLine();
        $this->info('DONE. Review the generated output above or redirect it to a .txt file.');
        $this->newLine();

        return self::SUCCESS;
    }

    /**
     * Show environment summary.
     */
    private function showEnvironmentSummary(): void
    {
        $this->newLine();
        $this->line('============================================================');
        $this->line('1. ENVIRONMENT SUMMARY');
        $this->line('============================================================');

        $this->line('Laravel Version : ' . app()->version());
        $this->line('PHP Version     : ' . PHP_VERSION);
        $this->line('Environment     : ' . app()->environment());
        $this->line('Debug Mode      : ' . (config('app.debug') ? 'ENABLED' : 'DISABLED'));
        $this->line('Timezone        : ' . config('app.timezone'));
        $this->line('Database Driver : ' . config('database.default'));

        try {
            $this->line('Database Name   : ' . DB::connection()->getDatabaseName());
            $this->line('Database Status : CONNECTED');
        } catch (Throwable $error) {
            $this->error('Database Status : FAILED - ' . $error->getMessage());
        }

        $this->line('Storage Link    : ' . (is_link(public_path('storage')) ? 'LINKED' : 'NOT LINKED'));
        $this->line('PHP intl        : ' . (extension_loaded('intl') ? 'ENABLED' : 'DISABLED'));
    }

    /**
     * Check routes, controllers, views, JS, models, and tables per module.
     */
    private function showModuleChecklist(): void
    {
        $this->newLine();
        $this->line('============================================================');
        $this->line('2. MODULE CHECKLIST');
        $this->line('============================================================');

        foreach ($this->modules as $moduleName => $config) {
            $this->newLine();
            $this->line('------------------------------------------------------------');
            $this->line($moduleName);
            $this->line('------------------------------------------------------------');

            $this->checkRoutes($config['route_prefixes']);
            $this->checkFile('Controller', $config['controller']);

            foreach ($config['views'] as $view) {
                $this->checkFile('View', $view);
            }

            foreach ($config['js'] as $jsFile) {
                $this->checkFile('JS', $jsFile);
            }

            foreach ($config['models'] as $model) {
                $this->checkFile('Model', $model);
            }

            foreach ($config['tables'] as $table) {
                $this->checkTable($table);
            }
        }
    }

    /**
     * Check if routes exist based on route name prefixes.
     */
    private function checkRoutes(array $prefixes): void
    {
        $routes = collect(Route::getRoutes())->map(function ($route) {
            return [
                'method' => implode('|', $route->methods()),
                'uri' => $route->uri(),
                'name' => $route->getName(),
                'action' => $route->getActionName(),
            ];
        });

        foreach ($prefixes as $prefix) {
            $matchedRoutes = $routes->filter(function ($route) use ($prefix) {
                return is_string($route['name']) && str_starts_with($route['name'], $prefix);
            });

            if ($matchedRoutes->isEmpty()) {
                $this->warn("[ROUTES] {$prefix}: MISSING");
                continue;
            }

            $this->info("[ROUTES] {$prefix}: FOUND {$matchedRoutes->count()} route(s)");

            foreach ($matchedRoutes as $route) {
                $this->line("  - {$route['method']} {$route['uri']} => {$route['name']}");
            }
        }
    }

    /**
     * Check if a project file exists.
     */
    private function checkFile(string $type, string $relativePath): void
    {
        $fullPath = base_path($relativePath);

        if (file_exists($fullPath)) {
            $this->info("[{$type}] FOUND: {$relativePath}");
            return;
        }

        $this->warn("[{$type}] MISSING: {$relativePath}");
    }

    /**
     * Check if table exists, show row count and columns.
     */
    private function checkTable(string $table): void
    {
        try {
            if (!Schema::hasTable($table)) {
                $this->warn("[TABLE] MISSING: {$table}");
                return;
            }

            $count = DB::table($table)->count();
            $columns = Schema::getColumnListing($table);

            $this->info("[TABLE] FOUND: {$table} ({$count} rows)");
            $this->line('  Columns: ' . implode(', ', $columns));
        } catch (Throwable $error) {
            $this->error("[TABLE] ERROR: {$table} - {$error->getMessage()}");
        }
    }

    /**
     * Check foreign keys and common relationship problems.
     */
    private function showDatabaseRelationshipChecks(): void
    {
        $this->newLine();
        $this->line('============================================================');
        $this->line('3. DATABASE RELATIONSHIP CHECKS');
        $this->line('============================================================');

        $this->showForeignKeys();
        $this->checkOrphans();
        $this->checkDuplicateInstructorSpecializations();
        $this->checkScheduleConflictRisk();
    }

    /**
     * Show PostgreSQL foreign keys for the target tables.
     */
    private function showForeignKeys(): void
    {
        $this->newLine();
        $this->line('FOREIGN KEYS');

        try {
            $foreignKeys = DB::select("
                SELECT
                    tc.table_name,
                    kcu.column_name,
                    ccu.table_name AS foreign_table_name,
                    ccu.column_name AS foreign_column_name,
                    tc.constraint_name
                FROM information_schema.table_constraints AS tc
                JOIN information_schema.key_column_usage AS kcu
                    ON tc.constraint_name = kcu.constraint_name
                    AND tc.table_schema = kcu.table_schema
                JOIN information_schema.constraint_column_usage AS ccu
                    ON ccu.constraint_name = tc.constraint_name
                    AND ccu.table_schema = tc.table_schema
                WHERE tc.constraint_type = 'FOREIGN KEY'
                AND tc.table_schema = 'public'
                ORDER BY tc.table_name, kcu.column_name
            ");

            if (empty($foreignKeys)) {
                $this->warn('No foreign keys found.');
                return;
            }

            foreach ($foreignKeys as $fk) {
                $this->line("  - {$fk->table_name}.{$fk->column_name} -> {$fk->foreign_table_name}.{$fk->foreign_column_name} ({$fk->constraint_name})");
            }
        } catch (Throwable $error) {
            $this->error('Unable to read foreign keys: ' . $error->getMessage());
        }
    }

    /**
     * Check common orphan records.
     */
    private function checkOrphans(): void
    {
        $this->newLine();
        $this->line('ORPHAN RECORD CHECKS');

        $checks = [
            [
                'label' => 'Students without user_account',
                'sql' => "
                    SELECT COUNT(*) AS total
                    FROM student s
                    LEFT JOIN user_account u ON u.user_id = s.user_id
                    WHERE s.user_id IS NOT NULL
                    AND u.user_id IS NULL
                ",
            ],
            [
                'label' => 'Instructors without user_account',
                'sql' => "
                    SELECT COUNT(*) AS total
                    FROM instructor i
                    LEFT JOIN user_account u ON u.user_id = i.user_id
                    WHERE i.user_id IS NOT NULL
                    AND u.user_id IS NULL
                ",
            ],
            [
                'label' => 'Enrollments without student',
                'sql' => "
                    SELECT COUNT(*) AS total
                    FROM enrollment e
                    LEFT JOIN student s ON s.student_id = e.student_id
                    WHERE e.student_id IS NOT NULL
                    AND s.student_id IS NULL
                ",
            ],
            [
                'label' => 'Enrollments without instructor',
                'sql' => "
                    SELECT COUNT(*) AS total
                    FROM enrollment e
                    LEFT JOIN instructor i ON i.instructor_id = e.instructor_id
                    WHERE e.instructor_id IS NOT NULL
                    AND i.instructor_id IS NULL
                ",
            ],
            [
                'label' => 'Schedules without enrollment',
                'sql' => "
                    SELECT COUNT(*) AS total
                    FROM schedule sc
                    LEFT JOIN enrollment e ON e.enrollment_id = sc.enrollment_id
                    WHERE sc.enrollment_id IS NOT NULL
                    AND e.enrollment_id IS NULL
                ",
            ],
            [
                'label' => 'Inventory without supplier',
                'sql' => "
                    SELECT COUNT(*) AS total
                    FROM inventory inv
                    LEFT JOIN supplier sup ON sup.supplier_id = inv.supplier_id
                    WHERE inv.supplier_id IS NOT NULL
                    AND sup.supplier_id IS NULL
                ",
            ],
        ];

        foreach ($checks as $check) {
            $this->runCountCheck($check['label'], $check['sql']);
        }
    }

    /**
     * Check duplicate instructor-specialization rows.
     */
    private function checkDuplicateInstructorSpecializations(): void
    {
        $this->newLine();
        $this->line('DUPLICATE INSTRUCTOR SPECIALIZATION CHECK');

        try {
            if (!Schema::hasTable('instructor_specialization')) {
                $this->warn('Table instructor_specialization missing.');
                return;
            }

            $duplicates = DB::select("
                SELECT instructor_id, specialization_id, COUNT(*) AS total
                FROM instructor_specialization
                GROUP BY instructor_id, specialization_id
                HAVING COUNT(*) > 1
                ORDER BY total DESC
            ");

            if (empty($duplicates)) {
                $this->info('No duplicate instructor-specialization pairs found.');
                return;
            }

            $this->warn('Duplicate instructor-specialization pairs found:');

            foreach ($duplicates as $row) {
                $this->line("  - instructor_id={$row->instructor_id}, specialization_id={$row->specialization_id}, total={$row->total}");
            }
        } catch (Throwable $error) {
            $this->error('Unable to check duplicates: ' . $error->getMessage());
        }
    }

    /**
     * Check real schedule conflict risk using overlapping time ranges.
     *
     * This is more accurate than checking same instructor + same date only.
     * Multiple lessons on the same day are valid if their times do not overlap.
     */
    private function checkScheduleConflictRisk(): void
    {
        $this->newLine();
        $this->line('SCHEDULE CONFLICT RISK CHECK');

        try {
            if (!Schema::hasTable('schedule')) {
                $this->warn('Table schedule missing.');
                return;
            }

            $columns = Schema::getColumnListing('schedule');

            $requiredColumns = [
                'schedule_id',
                'instructor_id',
                'room_number',
                'schedule_date',
                'start_time',
                'end_time',
                'status',
            ];

            foreach ($requiredColumns as $column) {
                if (!in_array($column, $columns, true)) {
                    $this->warn("Skipping conflict check because schedule.{$column} is missing.");
                    return;
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Instructor Time Conflict Check
            |--------------------------------------------------------------------------
            |
            | Detects cases where the same instructor has two active schedules
            | on the same date with overlapping time ranges.
            |
            */
            $instructorConflicts = DB::select("
                SELECT
                    a.schedule_id AS schedule_a,
                    b.schedule_id AS schedule_b,
                    a.instructor_id,
                    a.schedule_date,
                    a.start_time AS a_start,
                    a.end_time AS a_end,
                    b.start_time AS b_start,
                    b.end_time AS b_end
                FROM schedule a
                JOIN schedule b
                    ON a.instructor_id = b.instructor_id
                    AND a.schedule_date = b.schedule_date
                    AND a.schedule_id < b.schedule_id
                    AND a.start_time < b.end_time
                    AND b.start_time < a.end_time
                WHERE a.instructor_id IS NOT NULL
                AND COALESCE(a.status, '') NOT IN ('cancelled', 'canceled')
                AND COALESCE(b.status, '') NOT IN ('cancelled', 'canceled')
                ORDER BY a.schedule_date, a.instructor_id, a.start_time
                LIMIT 25
            ");

            if (empty($instructorConflicts)) {
                $this->info('No real instructor time-overlap conflicts found.');
            } else {
                $this->warn('Real instructor time-overlap conflicts found:');

                foreach ($instructorConflicts as $row) {
                    $this->line(
                        "  - instructor_id={$row->instructor_id}, date={$row->schedule_date}, " .
                        "schedule {$row->schedule_a} ({$row->a_start}-{$row->a_end}) overlaps " .
                        "schedule {$row->schedule_b} ({$row->b_start}-{$row->b_end})"
                    );
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Room Time Conflict Check
            |--------------------------------------------------------------------------
            |
            | Detects cases where the same room is assigned to overlapping schedules.
            |
            */
            $roomConflicts = DB::select("
                SELECT
                    a.schedule_id AS schedule_a,
                    b.schedule_id AS schedule_b,
                    a.room_number,
                    a.schedule_date,
                    a.start_time AS a_start,
                    a.end_time AS a_end,
                    b.start_time AS b_start,
                    b.end_time AS b_end
                FROM schedule a
                JOIN schedule b
                    ON a.room_number = b.room_number
                    AND a.schedule_date = b.schedule_date
                    AND a.schedule_id < b.schedule_id
                    AND a.start_time < b.end_time
                    AND b.start_time < a.end_time
                WHERE a.room_number IS NOT NULL
                AND COALESCE(a.status, '') NOT IN ('cancelled', 'canceled')
                AND COALESCE(b.status, '') NOT IN ('cancelled', 'canceled')
                ORDER BY a.schedule_date, a.room_number, a.start_time
                LIMIT 25
            ");

            if (empty($roomConflicts)) {
                $this->info('No real room time-overlap conflicts found.');
            } else {
                $this->warn('Real room time-overlap conflicts found:');

                foreach ($roomConflicts as $row) {
                    $this->line(
                        "  - room={$row->room_number}, date={$row->schedule_date}, " .
                        "schedule {$row->schedule_a} ({$row->a_start}-{$row->a_end}) overlaps " .
                        "schedule {$row->schedule_b} ({$row->b_start}-{$row->b_end})"
                    );
                }
            }
        } catch (Throwable $error) {
            $this->error('Unable to check schedule conflict risk: ' . $error->getMessage());
        }
    }

    /**
     * Run a count query safely.
     */
    private function runCountCheck(string $label, string $sql): void
    {
        try {
            $result = DB::selectOne($sql);
            $total = (int) ($result->total ?? 0);

            if ($total === 0) {
                $this->info("[OK] {$label}: 0");
                return;
            }

            $this->warn("[CHECK] {$label}: {$total}");
        } catch (Throwable $error) {
            $this->error("[ERROR] {$label}: {$error->getMessage()}");
        }
    }

    /**
     * UI consistency checklist.
     */
    private function showUiConsistencyChecklist(): void
    {
        $this->newLine();
        $this->line('============================================================');
        $this->line('4. UI CONSISTENCY CHECKLIST');
        $this->line('============================================================');

        $this->line('[ ] Admin pages should use the same layout wrapper.');
        $this->line('[ ] Cards should use the same rounded corners, spacing, border, and shadow style.');
        $this->line('[ ] Tables should use consistent empty states, loading states, and action buttons.');
        $this->line('[ ] Font choices should stay consistent: Sora for headings, Inter for UI text, JetBrains Mono for IDs/technical values.');
        $this->line('[ ] Palette should stay consistent: #223030, #29353C, #44576D, #768A96, #959D90, #D8DDD8, #F8F7F4.');
        $this->line('[ ] Index pages should have search/filter/sort/pagination where useful.');
        $this->line('[ ] Modals/forms should show validation errors clearly.');
        $this->line('[ ] Tables should be wrapped in overflow-x-auto for mobile responsiveness.');
        $this->line('[ ] Buttons should not move layout when dropdowns open.');
        $this->line('[ ] Destructive actions should have confirmation and relationship-impact checking.');
    }

    /**
     * Suggested faster debugging commands.
     */
    private function showSuggestedDebugCommands(): void
    {
        $this->newLine();
        $this->line('============================================================');
        $this->line('5. FASTER DEBUGGING COMMANDS');
        $this->line('============================================================');

        $this->line('php artisan admin:audit-remaining');
        $this->line('php artisan admin:audit-remaining > ADMIN_REMAINING_MODULES_FAST_AUDIT.txt 2>&1');
        $this->line('php artisan route:list --path=admin');
        $this->line('php artisan optimize:clear');
        $this->line('php artisan storage:link');
        $this->line('npm run dev');
        $this->line('git status --short');
    }
}