<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

/**
 * ============================================================================
 * SUPPLIER SEEDER
 * ============================================================================
 * Generates 50 music equipment suppliers with:
 * - Unique supplier codes (continues from last existing code)
 * - Filipino company names and addresses
 * - Realistic contact information
 * - Music instrument/equipment products
 * - Re-runnable without errors
 * ============================================================================
 */
class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('en_PH'); // Filipino locale

        // Find the highest existing supplier code number
        $lastCode = DB::table('supplier')
            ->where('supplier_code', 'LIKE', 'SUP-%')
            ->orderByRaw("CAST(SUBSTRING(supplier_code FROM 5) AS INTEGER) DESC")
            ->value('supplier_code');

        $startNumber = 1;
        if ($lastCode) {
            $startNumber = intval(substr($lastCode, 4)) + 1;
        }

        // Number of suppliers to create
        $count = 50;

        $this->command->info("🏢 Seeding {$count} suppliers starting from SUP-" . str_pad($startNumber, 4, '0', STR_PAD_LEFT) . "...");

        // Philippine music store names
        $storeNames = [
            'JB Music', 'Lyric Piano', 'Audiophile', 'Lazer Music',
            'Music One', 'Perfect Pitch', 'Muse & Co', 'Crescendo',
            'Guitar Pusher', 'Backstage Music', 'Studio Maven',
            'Forte Music', 'Harmony Hub', 'Music Gallery',
            'Sound Canvas', 'Allegro Music', 'Melody Shop',
        ];

        for ($i = 0; $i < $count; $i++) {
            $supplierNumber = $startNumber + $i;

            // Generate unique supplier name
            $baseName = $storeNames[array_rand($storeNames)];
            $suffix = ['Inc.', 'Corp.', 'Trading', 'Enterprises', 'Co.', 'Supply'];
            $supplierName = $baseName . ' ' . $suffix[array_rand($suffix)];

            // Ensure unique supplier name
            $counter = 1;
            while (DB::table('supplier')->where('supplier_name', $supplierName)->exists()) {
                $supplierName = $baseName . ' ' . $suffix[array_rand($suffix)] . ' ' . $counter;
                $counter++;
            }

            // Product categories
            $productCategories = [
                'String Instruments', 'Percussion Instruments', 'Wind Instruments',
                'Keyboard Instruments', 'Audio Equipment', 'Guitar Accessories',
                'Drum Accessories', 'Sheet Music', 'Music Books', 'Cables and Stands'
            ];

            $numCategories = rand(2, 4);
            $categories = implode(', ', $faker->randomElements($productCategories, $numCategories));

            DB::table('supplier')->insert([
                'supplier_name' => $supplierName,
                'supplier_code' => 'SUP-' . str_pad($supplierNumber, 4, '0', STR_PAD_LEFT),
                'contact_person' => $faker->name,
                'contact_position' => $faker->randomElement(['Sales Manager', 'Account Executive', 'Owner', 'General Manager']),
                'email' => strtolower(str_replace(' ', '', $baseName)) . $supplierNumber . '@musicstore.ph',
                'phone' => '09' . rand(100000000, 999999999),
                'website' => 'www.' . strtolower(str_replace(' ', '', $baseName)) . '.com.ph',
                'address_line1' => $faker->streetAddress,
                'address_line2' => rand(1, 10) > 7 ? 'Unit ' . rand(1, 50) : null,
                'city' => $faker->randomElement(['Cebu City', 'Mandaue City', 'Lapu-Lapu City', 'Talisay City', 'Manila', 'Makati', 'Quezon City']),
                'province' => $faker->randomElement(['Cebu', 'Metro Manila', 'Davao del Sur']),
                'postal_code' => rand(6000, 6050),
                'country' => 'Philippines',
                'products_supplied' => $this->randomProducts(),
                'product_categories' => $categories,
                'payment_terms' => $this->randomPaymentTerms(),
                'delivery_terms' => $faker->randomElement(['Next Day Delivery', 'Same Day (Metro areas)', '2-3 Business Days', 'Standard Shipping']),
                'minimum_order_amount' => rand(1, 10) > 7 ? rand(5000, 20000) : null,
                'rating' => round(rand(30, 50) / 10, 1), // 3.0 to 5.0
                'total_orders' => rand(0, 100),
                'last_order_date' => rand(1, 10) > 3 ? now()->subDays(rand(1, 365))->toDateString() : null,
                'is_active' => rand(1, 10) > 1, // 90% active
                'notes' => rand(1, 10) > 8 ? 'Preferred supplier for guitars' : null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Progress indicator
            if (($i + 1) % 10 === 0) {
                $this->command->info("✓ Created " . ($i + 1) . " suppliers...");
            }
        }

        $this->command->info("✅ Successfully seeded {$count} suppliers!");
    }

    /**
     * Get random product description
     */
    private function randomProducts(): string
    {
        $products = [
            'Guitars (acoustic, electric), bass guitars, ukuleles',
            'Digital pianos, keyboards, MIDI controllers',
            'Drum sets, percussion instruments, cymbals',
            'Violins, cellos, violas, string accessories',
            'Microphones, audio interfaces, studio monitors',
            'Guitar strings, picks, capos, tuners',
            'Drumsticks, drum heads, practice pads',
            'Music stands, guitar stands, keyboard stands',
            'Sheet music, method books, instructional DVDs',
            'Cables, adapters, connectors, power supplies',
        ];

        return $products[array_rand($products)];
    }

    /**
     * Get random payment terms
     */
    private function randomPaymentTerms(): string
    {
        $terms = [
            'Net 30 days',
            'Net 15 days',
            'COD (Cash on Delivery)',
            '50% deposit, 50% on delivery',
            'Net 7 days',
            'Payment upon receipt',
            '30% deposit, balance before delivery',
        ];

        return $terms[array_rand($terms)];
    }
}