<?php

// database/seeders/UserAccountSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\UserAccount;

class UserAccountSeeder extends Seeder
{
    /**
     * Seed the user_account table with super admin and test users.
     * 
     * Idempotent: Can be run multiple times without errors
     * Safe: Resets PostgreSQL sequence to prevent duplicate key errors
     */
    public function run(): void
    {
        $faker = fake('en_US'); // Use English locale for consistent safe emails

        // === 1. Create Super Admin (always user_id = 1) ===
        // Use firstOrCreate to prevent duplicates (checks by email)
        // If admin exists, this does nothing. If not, creates with user_id = 1
        $superAdmin = UserAccount::firstOrCreate(
            ['user_email' => 'admin@musiclab.com'], // Search by this
            [
                // If not found, create with these values:
                'user_password'  => Hash::make('password'), // Manually hash here to avoid mutator issues
                'is_super_admin' => true,
                'last_login'     => null,
            ]
        );

        // === 2. CRITICAL: Reset PostgreSQL sequence after manual ID insertion ===
        // This prevents the "duplicate key" error by telling PostgreSQL:
        // "The highest user_id is X, so start auto-increment from X+1"
        // This MUST run after any manual user_id insertion (like super admin)
        DB::statement("SELECT setval('user_account_user_id_seq', (SELECT COALESCE(MAX(user_id), 1) FROM user_account))");

        // === 3. Create 5 regular user accounts ===
        // These use BIGSERIAL auto-increment (will be 2, 3, 4, 5, 6...)
        // firstOrCreate makes this idempotent (won't create duplicates on re-run)
        foreach (range(1, 5) as $i) {
            $email = $faker->unique()->safeEmail;

            // Create user only if email doesn't exist
            // Let BIGSERIAL handle user_id automatically (don't specify it)
            UserAccount::firstOrCreate(
                ['user_email' => $email], // Search by this
                [
                    // If not found, create with these values:
                    'user_password'  => Hash::make('password'),
                    'is_super_admin' => false,
                    'last_login'     => null,
                ]
            );
        }

        // === 4. Output success message ===
        $this->command->info('User accounts seeded successfully!');
        $this->command->info('   Super Admin: admin@musiclab.com (password: password)');
    }
}