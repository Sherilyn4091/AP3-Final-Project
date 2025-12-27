<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StudentStatusSeeder extends Seeder
{
    public function run(): void
    {
        $studentStatuses = [
            ['status_name' => 'Active', 'description' => 'Currently enrolled and attending', 'is_active' => true],
            ['status_name' => 'Inactive', 'description' => 'Not currently attending', 'is_active' => true],
            ['status_name' => 'Completed', 'description' => 'Successfully completed the session', 'is_active' => true],
            ['status_name' => 'Withdrawn', 'description' => 'Withdrew from session', 'is_active' => true],
            ['status_name' => 'On Hold', 'description' => 'Temporarily paused enrollment', 'is_active' => true],
        ];

        foreach ($studentStatuses as $studentStatus) {
            // Check if the student status already exists
            if (!DB::table('student_status')->where('status_name', $studentStatus['status_name'])->exists()) {
                $studentStatus['created_at'] = now();
                $studentStatus['updated_at'] = now();
                DB::table('student_status')->insert($studentStatus);
            }
        }
    }
}