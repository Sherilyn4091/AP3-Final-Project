<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

Route::get('/run-migrations-secret-key-2026', function () {
    try {
        // Check if already migrated
        if (Schema::hasTable('users')) {
            return response()->json([
                'status' => 'already_migrated',
                'message' => 'Database tables already exist. Migrations were previously run.',
                'timestamp' => now()
            ]);
        }

        // Run migrations
        Artisan::call('migrate', ['--force' => true]);
        $output = Artisan::output();

        return response()->json([
            'status' => 'success',
            'message' => 'Migrations completed successfully!',
            'output' => $output,
            'timestamp' => now()
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
            'timestamp' => now()
        ], 500);
    }
});
