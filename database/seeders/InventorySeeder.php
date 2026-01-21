<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * ============================================================================
 * INVENTORY SEEDER
 * ============================================================================
 * Generates 50 inventory items with:
 * - Unique item codes (continues from last existing code)
 * - Realistic music instruments and accessories
 * - Stock quantities and pricing
 * - Supplier relationships (FK)
 * - Re-runnable without errors
 * ============================================================================
 */
class InventorySeeder extends Seeder
{
    public function run(): void
    {
        // Check if suppliers exist
        $supplierIds = DB::table('supplier')
            ->where('is_active', true)
            ->pluck('supplier_id')
            ->toArray();

        if (empty($supplierIds)) {
            $this->command->error('❌ No active suppliers found. Run SupplierSeeder first.');
            return;
        }

        // Find the highest existing item code number
        $lastCode = DB::table('inventory')
            ->where('item_code', 'LIKE', 'ITEM-%')
            ->orderByRaw("CAST(SUBSTRING(item_code FROM 6) AS INTEGER) DESC")
            ->value('item_code');

        $startNumber = 1;
        if ($lastCode) {
            $startNumber = intval(substr($lastCode, 5)) + 1;
        }

        // Number of items to create
        $count = 50;

        $this->command->info("📦 Seeding {$count} inventory items starting from ITEM-" . str_pad($startNumber, 5, '0', STR_PAD_LEFT) . "...");

        for ($i = 0; $i < $count; $i++) {
            $itemNumber = $startNumber + $i;
            $itemData = $this->randomItem();

            // Random stock quantity
            $quantity = rand(0, 50);
            $lowStockThreshold = rand(3, 10);

            // Pricing
            $unitPrice = $itemData['unit_price'];
            $retailPrice = round($unitPrice * rand(12, 18) / 10, 2); // 20-80% markup

            // Supplier
            $supplierId = $supplierIds[array_rand($supplierIds)];

            DB::table('inventory')->insert([
                'item_code' => 'ITEM-' . str_pad($itemNumber, 5, '0', STR_PAD_LEFT),
                'item_name' => $itemData['name'],
                'item_type' => $itemData['type'],
                'brand' => $itemData['brand'],
                'model' => $itemData['model'],
                'quantity' => $quantity,
                'unit_of_measure' => $itemData['unit'],
                'unit_price' => $unitPrice,
                'retail_price' => $retailPrice,
                'low_stock_threshold' => $lowStockThreshold,
                'reorder_quantity' => rand(10, 30),
                'supplier_id' => $supplierId,
                'supplier_product_code' => 'SP-' . strtoupper(substr($itemData['brand'], 0, 3)) . '-' . rand(1000, 9999),
                'location' => $this->randomLocation(),
                'warranty_period' => $itemData['warranty'],
                'last_restocked_date' => $quantity > 0 ? now()->subDays(rand(1, 90))->toDateString() : null,
                'last_ordered_date' => rand(1, 10) > 3 ? now()->subDays(rand(1, 180))->toDateString() : null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Progress indicator
            if (($i + 1) % 10 === 0) {
                $this->command->info("✓ Created " . ($i + 1) . " items...");
            }
        }

        $this->command->info("✅ Successfully seeded {$count} inventory items!");
    }

    /**
     * Get random inventory item with realistic data
     */
    private function randomItem(): array
    {
        $items = [
            // Guitars
            [
                'name' => 'Acoustic Guitar',
                'type' => 'String Instrument',
                'brand' => $this->randomBrand(['Yamaha', 'Fender', 'Cort', 'Ibanez']),
                'model' => 'FG-' . rand(100, 900),
                'unit_price' => rand(8000, 35000),
                'unit' => 'piece',
                'warranty' => '1 year',
            ],
            [
                'name' => 'Electric Guitar',
                'type' => 'String Instrument',
                'brand' => $this->randomBrand(['Fender', 'Gibson', 'Ibanez', 'PRS']),
                'model' => 'STR-' . rand(100, 500),
                'unit_price' => rand(15000, 50000),
                'unit' => 'piece',
                'warranty' => '1 year',
            ],
            [
                'name' => 'Bass Guitar',
                'type' => 'String Instrument',
                'brand' => $this->randomBrand(['Fender', 'Ibanez', 'Yamaha']),
                'model' => 'BS-' . rand(200, 600),
                'unit_price' => rand(12000, 40000),
                'unit' => 'piece',
                'warranty' => '1 year',
            ],
            // Keyboards
            [
                'name' => 'Digital Piano',
                'type' => 'Keyboard',
                'brand' => $this->randomBrand(['Yamaha', 'Casio', 'Roland', 'Korg']),
                'model' => 'P-' . rand(45, 515),
                'unit_price' => rand(25000, 80000),
                'unit' => 'piece',
                'warranty' => '2 years',
            ],
            [
                'name' => 'MIDI Keyboard',
                'type' => 'Keyboard',
                'brand' => $this->randomBrand(['M-Audio', 'Akai', 'Novation']),
                'model' => 'MK-' . rand(25, 88),
                'unit_price' => rand(8000, 25000),
                'unit' => 'piece',
                'warranty' => '1 year',
            ],
            // Drums
            [
                'name' => 'Drum Set',
                'type' => 'Percussion',
                'brand' => $this->randomBrand(['Pearl', 'Tama', 'Mapex', 'Ludwig']),
                'model' => 'DS-' . rand(500, 1500),
                'unit_price' => rand(30000, 120000),
                'unit' => 'set',
                'warranty' => '1 year',
            ],
            [
                'name' => 'Snare Drum',
                'type' => 'Percussion',
                'brand' => $this->randomBrand(['Pearl', 'Tama', 'Ludwig']),
                'model' => 'SN-' . rand(100, 500),
                'unit_price' => rand(5000, 20000),
                'unit' => 'piece',
                'warranty' => '1 year',
            ],
            // Accessories
            [
                'name' => 'Guitar Strings Set',
                'type' => 'Accessory',
                'brand' => $this->randomBrand(['D\'Addario', 'Ernie Ball', 'Elixir']),
                'model' => 'GS-' . rand(9, 13),
                'unit_price' => rand(300, 800),
                'unit' => 'pack',
                'warranty' => null,
            ],
            [
                'name' => 'Drumsticks Pair',
                'type' => 'Accessory',
                'brand' => $this->randomBrand(['Vic Firth', 'Zildjian', 'Promark']),
                'model' => '5A',
                'unit_price' => rand(400, 1200),
                'unit' => 'pair',
                'warranty' => null,
            ],
            [
                'name' => 'Music Stand',
                'type' => 'Accessory',
                'brand' => $this->randomBrand(['K&M', 'Hercules', 'Manhasset']),
                'model' => 'MS-' . rand(100, 300),
                'unit_price' => rand(800, 3000),
                'unit' => 'piece',
                'warranty' => '6 months',
            ],
            [
                'name' => 'Guitar Tuner',
                'type' => 'Accessory',
                'brand' => $this->randomBrand(['Boss', 'TC Electronic', 'Korg']),
                'model' => 'TU-' . rand(2, 12),
                'unit_price' => rand(500, 2500),
                'unit' => 'piece',
                'warranty' => '1 year',
            ],
            [
                'name' => 'Microphone',
                'type' => 'Audio Equipment',
                'brand' => $this->randomBrand(['Shure', 'Audio-Technica', 'Sennheiser']),
                'model' => 'SM-' . rand(57, 87),
                'unit_price' => rand(3000, 15000),
                'unit' => 'piece',
                'warranty' => '2 years',
            ],
        ];

        return $items[array_rand($items)];
    }

    /**
     * Select random brand from array
     */
    private function randomBrand(array $brands): string
    {
        return $brands[array_rand($brands)];
    }

    /**
     * Get random storage location
     */
    private function randomLocation(): string
    {
        $locations = [
            'Warehouse Shelf A1',
            'Warehouse Shelf A2',
            'Warehouse Shelf B1',
            'Warehouse Shelf B2',
            'Display Room',
            'Storage Room 1',
            'Storage Room 2',
        ];

        return $locations[array_rand($locations)];
    }
}