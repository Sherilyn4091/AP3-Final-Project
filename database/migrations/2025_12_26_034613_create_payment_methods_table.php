<?php

// database/migrations/2025_12_26_034613_create_payment_methods_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create table only if it doesn't exist
        if (!Schema::hasTable('payment_methods')) {
            Schema::create('payment_methods', function (Blueprint $table) {
                $table->id('method_id');
                $table->string('method_name', 50)->unique(); // e.g., "Cash", "GCash"
                $table->text('description')->nullable();     // Optional description
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });

            // Seed default payment methods (safe even if run multiple times)
            $defaults = [
                'Cash',
                'GCash',
                'Bank Transfer',
                'Credit Card',
                'Debit Card',
                'Check',
                'PayMaya',
                'Online Banking',
            ];

            foreach ($defaults as $name) {
                DB::table('payment_methods')->updateOrInsert(
                    ['method_name' => $name],
                    ['method_name' => $name, 'created_at' => now(), 'updated_at' => now()]
                );
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};