<?php
cat > routes/migrate.php << 'EOF'
<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

Route::get('/run-migrations-secret-key-2026', function () {
    try {
        // Check if already migrated
        if (Schema::hasTable('users')) {
            return response()->json([
                'status' => 'already_migrated',
                'message' => 'Database tables already exist.',
                'tables' => Schema::getAllTables()
            ]);
        }

        // Run migrations
        Artisan::call('migrate', ['--force' => true]);
        $output = Artisan::output();

        return response()->json([
            'status' => 'success',
            'message' => 'Migrations completed!',
            'output' => $output
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
});
