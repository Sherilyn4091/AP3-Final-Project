<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PaymentSeeder extends Seeder
{
    public function run(): void
    {
        $enrollments = DB::table('enrollment')
            ->select('enrollment_id', 'student_id', 'total_amount', 'amount_paid', 'enrollment_date')
            ->get();

        if ($enrollments->isEmpty()) {
            $this->command->warn('No enrollments found. Run EnrollmentSeeder first. Skipping PaymentSeeder.');
            return;
        }

        $created = 0;

        foreach ($enrollments as $e) {

            // Skip if already has a payment row
            $exists = DB::table('payment')->where('enrollment_id', $e->enrollment_id)->exists();
            if ($exists) continue;

            // Subtotal & discount (keep simple, but valid)
            $subtotal = (float) $e->total_amount;
            $discount = 0.00;

            // Decide payment status: mostly Paid, some Pending/Partial
            $roll = rand(1, 100);

            if ($roll <= 70) {
                // 70% Paid
                $paymentStatusId = 2; // Paid
                $amount = $subtotal - $discount;
            } elseif ($roll <= 90) {
                // 20% Partial (use Partial status if you want)
                $paymentStatusId = 5; // Partial
                $amount = round(($subtotal - $discount) * (rand(20, 80) / 100), 2);
            } else {
                // 10% Pending - still must be > 0 because of amount_check
                $paymentStatusId = 1; // Pending
                $amount = round(($subtotal - $discount) * (rand(10, 30) / 100), 2);
            }

            // ✅ Guarantee amount > 0 (fixes amount_check)
            if ($amount <= 0) {
                $amount = 1.00;
            }

            // Payment method 1..8
            $methodId = rand(1, 8);

            $paymentDate = $e->enrollment_date
                ? Carbon::parse($e->enrollment_date)->addDays(rand(0, 10))->toDateString()
                : Carbon::now()->subDays(rand(0, 30))->toDateString();

            DB::table('payment')->insert([
                'student_id' => $e->student_id,
                'enrollment_id' => $e->enrollment_id,

                // must satisfy amount_check
                'amount' => $amount,

                'payment_method_id' => $methodId,
                'payment_status_id' => $paymentStatusId,
                'payment_date' => $paymentDate,

                'transaction_reference' => 'TXN-' . substr(md5(uniqid((string)rand(), true)), 0, 10),
                'receipt_number' => 'OR-' . substr(md5(uniqid((string)rand(), true)), 0, 10),

                'subtotal' => $subtotal,
                'discount' => $discount,

                // Optional nullable admin fields
                'processed_by' => null,
                'approved_by' => null,
                'approved_at' => null,

                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $created++;
        }

        $this->command->info("PaymentSeeder: inserted {$created} payments successfully.");
    }
}