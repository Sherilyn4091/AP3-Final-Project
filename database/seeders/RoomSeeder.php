<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoomSeeder extends Seeder
{
    /**
     * Seed the room table with 5 practice rooms.
     * Each room has a capacity of 4-6 persons for music lessons.
     */
    public function run(): void
    {
        // Room data with varying capacities and hourly rates
        $rooms = [
            [
                'room_number' => 'R001',
                'room_name' => 'Room 1',
                'capacity' => 4,
                'hourly_rate' => 250.00,
                'is_active' => true,
            ],
            [
                'room_number' => 'R002',
                'room_name' => 'Room 2',
                'capacity' => 5,
                'hourly_rate' => 300.00,
                'is_active' => true,
            ],
            [
                'room_number' => 'R003',
                'room_name' => 'Room 3',
                'capacity' => 6,
                'hourly_rate' => 350.00,
                'is_active' => true,
            ],
            [
                'room_number' => 'R004',
                'room_name' => 'Room 4',
                'capacity' => 4,
                'hourly_rate' => 250.00,
                'is_active' => true,
            ],
            [
                'room_number' => 'R005',
                'room_name' => 'Room 5',
                'capacity' => 5,
                'hourly_rate' => 300.00,
                'is_active' => true,
            ],
        ];

        // Insert all rooms into database
        foreach ($rooms as $room) {
            DB::table('room')->insert([
                'room_number' => $room['room_number'],
                'room_name' => $room['room_name'],
                'capacity' => $room['capacity'],
                'hourly_rate' => $room['hourly_rate'],
                'is_active' => $room['is_active'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Log success message
        $this->command->info('✓ Successfully seeded 5 rooms');
    }
}