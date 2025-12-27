<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentStatusSeeder extends Seeder
{
    public function run(): void
    {
        $paymentStatuses = [
            ['status_name' => 'Pending', 'is_active' => true],
            ['status_name' => 'Paid', 'is_active' => true],
            ['status_name' => 'Cancelled', 'is_active' => true],
            ['status_name' => 'Refunded', 'is_active' => true],
            ['status_name' => 'Partial', 'is_active' => true],
            ['status_name' => 'Failed', 'is_active' => true],
        ];

        foreach ($paymentStatuses as $paymentStatus) {
            if (!DB::table('payment_status')->where('status_name', $paymentStatus['status_name'])->exists()) {
                $paymentStatus['created_at'] = now();
                $paymentStatus['updated_at'] = now();
                DB::table('payment_status')->insert($paymentStatus);
            }
        }
    }
}