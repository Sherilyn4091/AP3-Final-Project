<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentMethodSeeder extends Seeder
{
    public function run(): void
    {
        $paymentMethods = [
            ['method_name' => 'Cash', 'is_active' => true],
            ['method_name' => 'GCash', 'is_active' => true],
            ['method_name' => 'Bank Transfer', 'is_active' => true],
            ['method_name' => 'Credit Card', 'is_active' => true],
            ['method_name' => 'Debit Card', 'is_active' => true],
            ['method_name' => 'Check', 'is_active' => true],
            ['method_name' => 'PayMaya', 'is_active' => true],
            ['method_name' => 'Online Banking', 'is_active' => true],
        ];

        foreach ($paymentMethods as $paymentMethod) {
            if (!DB::table('payment_method')->where('method_name', $paymentMethod['method_name'])->exists()) {
                $paymentMethod['created_at'] = now();
                $paymentMethod['updated_at'] = now();
                DB::table('payment_method')->insert($paymentMethod);
            }
        }
    }
}