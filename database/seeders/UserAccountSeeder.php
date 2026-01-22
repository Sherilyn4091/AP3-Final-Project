<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserAccountSeeder extends Seeder
{
    /**
     * ============================================================================
     * INTELLIGENT USER ACCOUNT SEEDER
     * ============================================================================
     * 
     * This seeder ONLY creates the super admin account.
     * Other user accounts are created by their respective role seeders:
     * - StudentSeeder (creates user_account + student)
     * - InstructorSeeder (creates user_account + instructor)
     * - SalesStaffSeeder (creates user_account + sales_staff)
     * - AllAroundStaffSeeder (creates user_account + all_around_staff)
     * 
     * INTELLIGENT FEATURES:
     * ✓ Idempotent (safe to run multiple times)
     * ✓ Auto-detects highest user_id and continues from there
     * ✓ Resets PostgreSQL sequence to prevent duplicate key errors
     * ✓ Won't create duplicate super admin if already exists
     * ============================================================================
     */
    public function run(): void
    {
        $this->command->info('🔧 Starting User Account Seeder...');

        // ========================================================================
        // STEP 1: Get the current highest user_id in the database
        // ========================================================================
        $maxUserId = DB::table('user_account')->max('user_id') ?? 0;
        $this->command->info("   Current highest user_id: {$maxUserId}");

        // ========================================================================
        // STEP 2: Create Super Admin (only if doesn't exist)
        // ========================================================================
        $superAdminEmail = 'admin@musiclab.com';
        
        $existingSuperAdmin = DB::table('user_account')
            ->where('user_email', $superAdminEmail)
            ->first();

        if ($existingSuperAdmin) {
            $this->command->info("   ✓ Super admin already exists (user_id: {$existingSuperAdmin->user_id})");
        } else {
            DB::table('user_account')->insert([
                'user_email'     => $superAdminEmail,
                'user_password'  => Hash::make('password'),
                'is_super_admin' => true,
                'last_login'     => null,
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
            
            $newMaxId = DB::table('user_account')->max('user_id');
            $this->command->info("   ✓ Super admin created (user_id: {$newMaxId})");
        }

        // ========================================================================
        // STEP 3: CRITICAL - Reset PostgreSQL sequence
        // ========================================================================
        // This tells PostgreSQL: "The highest user_id is X, so start from X+1"
        // Prevents "duplicate key value violates unique constraint" errors
        // ========================================================================
        $currentMax = DB::table('user_account')->max('user_id') ?? 1;
        
        DB::statement("SELECT setval('user_account_user_id_seq', {$currentMax}, true)");
        
        // Verify the sequence was set correctly
        $nextVal = DB::selectOne("SELECT nextval('user_account_user_id_seq') as next_id")->next_id;
        $this->command->info("   ✓ Sequence reset: next user_id will be {$nextVal}");

        // ========================================================================
        // SUCCESS MESSAGE
        // ========================================================================
        $this->command->info('');
        $this->command->info('   User Account Seeder completed successfully!');
        $this->command->info('   Super Admin: admin@musiclab.com (password: password)');
        $this->command->info('   Other users will be created by role-specific seeders');
        $this->command->info('');
    }
}