<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * database/seeders/BookingSeeder.php
 *
 * Purpose:
 * - Creates 50 safe demo room bookings for Music Lab.
 * - Uses existing user_account/student records.
 * - Uses existing room records.
 * - Prevents overlapping bookings in the same room.
 * - Prevents booking conflicts with lesson schedules in the same room.
 *
 * Important relationships:
 * booking.user_id      -> user_account.user_id
 * booking.room_number  -> room.room_number
 * booking.confirmed_by -> user_account.user_id
 * booking.cancelled_by -> user_account.user_id
 */
class BookingSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Safety Check
        |--------------------------------------------------------------------------
        |
        | Avoid duplicate demo bookings if this seeder is accidentally run again.
        */
        if (DB::table('booking')->exists()) {
            $this->command->warn('Bookings already exist. BookingSeeder skipped to avoid duplicates.');
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Parent Records
        |--------------------------------------------------------------------------
        */
        $rooms = DB::table('room')
            ->where('is_active', true)
            ->select('room_number', 'hourly_rate')
            ->orderBy('room_number')
            ->get();

        $studentUsers = DB::table('student as s')
            ->join('user_account as ua', 'ua.user_id', '=', 's.user_id')
            ->select(
                'ua.user_id',
                's.first_name',
                's.last_name',
                's.phone',
                's.email'
            )
            ->orderBy('s.student_id')
            ->get();

        $adminUser = DB::table('user_account')
            ->where('is_super_admin', true)
            ->first();

        if ($rooms->isEmpty()) {
            $this->command->error('No active rooms found. Run RoomSeeder first.');
            return;
        }

        if ($studentUsers->isEmpty()) {
            $this->command->error('No student users found. Run StudentSeeder first.');
            return;
        }

        if (!$adminUser) {
            $this->command->error('No super admin found. Run UserAccountSeeder first.');
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Existing Lesson Schedule Conflicts
        |--------------------------------------------------------------------------
        |
        | Booking should not use a room that is already used by a lesson schedule.
        | This loads existing schedules into memory so the seeder avoids them.
        */
        $roomReservations = [];

        $existingSchedules = DB::table('schedule')
            ->whereNotNull('room_number')
            ->whereNotIn('status', ['cancelled', 'no_class', 'rescheduled'])
            ->select('room_number', 'schedule_date', 'start_time', 'end_time')
            ->get();

        foreach ($existingSchedules as $schedule) {
            $this->addReservation(
                $roomReservations,
                $schedule->room_number,
                (string) $schedule->schedule_date,
                (string) $schedule->start_time,
                (string) $schedule->end_time
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Demo Content
        |--------------------------------------------------------------------------
        */
        $bandNames = [
            'Sugbo Rhythm',
            'Mango Square Band',
            'Cebu Strings',
            'Visayan Echo',
            'Harana Collective',
            'Kanta Sugbo',
            'Sinulog Session',
            'Bisdak Beats',
            'Huni Collective',
            'Tono Cebu',
            'Lakbay Musika',
            'Kwerdas Cebu',
            'Tugtog Kabataan',
            'Harmoniya',
            'Tinig Sugbo',
        ];

        $purposes = [
            'Band practice',
            'Solo practice',
            'Vocal practice',
            'Instrument rehearsal',
            'Performance preparation',
            'Music lesson practice',
            'Group rehearsal',
            'Audition preparation',
            'Recital preparation',
            'Song arrangement practice',
        ];

        $equipmentOptions = [
            'Microphone, amplifier',
            'Keyboard stand, microphone',
            'Drum set, microphone',
            'Guitar amplifier, music stand',
            'Bass amplifier, microphone',
            'Violin stand, music stand',
            'Ukulele stand, microphone',
            'Microphone only',
            'Music stands only',
            'No extra equipment needed',
        ];

        $specialRequests = [
            null,
            null,
            null,
            'Please prepare the room before the scheduled time.',
            'Prefer a quieter room if available.',
            'Need assistance with microphone setup.',
            'Need extra chairs for group practice.',
            'Please reserve available music stands.',
        ];

        /*
        |--------------------------------------------------------------------------
        | Booking Time Slots
        |--------------------------------------------------------------------------
        |
        | Some are 1-hour, some are 2-hour bookings.
        | The overlap checker below will prevent conflicts.
        */
        $timeSlots = [
            ['start' => '09:00:00', 'end' => '10:00:00', 'hours' => 1],
            ['start' => '10:00:00', 'end' => '11:00:00', 'hours' => 1],
            ['start' => '11:00:00', 'end' => '12:00:00', 'hours' => 1],
            ['start' => '13:00:00', 'end' => '14:00:00', 'hours' => 1],
            ['start' => '14:00:00', 'end' => '15:00:00', 'hours' => 1],
            ['start' => '15:00:00', 'end' => '16:00:00', 'hours' => 1],
            ['start' => '16:00:00', 'end' => '17:00:00', 'hours' => 1],
            ['start' => '17:00:00', 'end' => '18:00:00', 'hours' => 1],
            ['start' => '18:00:00', 'end' => '19:00:00', 'hours' => 1],
            ['start' => '09:00:00', 'end' => '11:00:00', 'hours' => 2],
            ['start' => '13:00:00', 'end' => '15:00:00', 'hours' => 2],
            ['start' => '15:00:00', 'end' => '17:00:00', 'hours' => 2],
        ];

        $rows = [];
        $created = 0;
        $attempts = 0;
        $maxAttempts = 3000;

        while ($created < 50 && $attempts < $maxAttempts) {
            $attempts++;

            $student = $studentUsers->random();
            $room = $rooms->random();
            $slot = $timeSlots[array_rand($timeSlots)];

            $bookingDate = Carbon::today()
                ->addDays(rand(-10, 45))
                ->toDateString();

            /*
            |--------------------------------------------------------------------------
            | Strict Overlap Check
            |--------------------------------------------------------------------------
            |
            | Prevents this:
            | - 09:00–11:00
            | - 10:00–11:00
            |
            | Overlap formula:
            | new_start < existing_end AND existing_start < new_end
            */
            if ($this->hasOverlap(
                $roomReservations,
                $room->room_number,
                $bookingDate,
                $slot['start'],
                $slot['end']
            )) {
                continue;
            }

            $this->addReservation(
                $roomReservations,
                $room->room_number,
                $bookingDate,
                $slot['start'],
                $slot['end']
            );

            $status = $this->pickStatus($bookingDate);

            $confirmedBy = null;
            $confirmedAt = null;
            $cancelledBy = null;
            $cancelledAt = null;
            $cancellationReason = null;

            if (in_array($status, ['confirmed', 'completed'], true)) {
                $confirmedBy = $adminUser->user_id;
                $confirmedAt = Carbon::parse($bookingDate)
                    ->subDays(rand(1, 3))
                    ->setTime(rand(8, 17), rand(0, 59));
            }

            if ($status === 'cancelled') {
                $cancelledBy = $student->user_id;
                $cancelledAt = Carbon::parse($bookingDate)
                    ->subDays(rand(1, 2))
                    ->setTime(rand(8, 17), rand(0, 59));

                $cancellationReason = $this->randomElement([
                    'Schedule conflict',
                    'Student requested cancellation',
                    'Band members unavailable',
                    'Moved to another date',
                    'Personal reason',
                ]);
            }

            $hourlyRate = $room->hourly_rate ?? 250.00;
            $durationHours = $slot['hours'];
            $totalAmount = $hourlyRate * $durationHours;

            $rows[] = [
                'booking_id' => $this->generateBookingId($created + 1),
                'user_id' => $student->user_id,
                'room_number' => $room->room_number,
                'booking_date' => $bookingDate,
                'start_time' => $slot['start'],
                'end_time' => $slot['end'],
                'duration_hours' => $durationHours,
                'hourly_rate' => $hourlyRate,
                'total_amount' => $totalAmount,
                'booking_status' => $status,
                'purpose' => $this->randomElement($purposes),
                'number_of_people' => rand(1, 6),
                'band_name' => rand(1, 100) <= 45 ? $this->randomElement($bandNames) : null,
                'equipment_needed' => $this->randomElement($equipmentOptions),
                'special_requests' => $this->randomElement($specialRequests),
                'contact_name' => $student->first_name . ' ' . $student->last_name,
                'contact_phone' => $student->phone,
                'contact_email' => $student->email,
                'confirmed_by' => $confirmedBy,
                'confirmed_at' => $confirmedAt,
                'cancelled_by' => $cancelledBy,
                'cancelled_at' => $cancelledAt,
                'cancellation_reason' => $cancellationReason,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $created++;
        }

        if ($created < 50) {
            $this->command->warn("Only created {$created} bookings because available room slots were limited.");
        }

        if (!empty($rows)) {
            DB::table('booking')->insert($rows);
        }

        $this->command->info("Successfully seeded {$created} safe room bookings.");
    }

    /**
     * Add one occupied room time range into the in-memory reservation list.
     */
    private function addReservation(
        array &$roomReservations,
        string $roomNumber,
        string $date,
        string $startTime,
        string $endTime
    ): void {
        $key = $roomNumber . '|' . $date;

        if (!isset($roomReservations[$key])) {
            $roomReservations[$key] = [];
        }

        $roomReservations[$key][] = [
            'start' => $this->normalizeTime($startTime),
            'end' => $this->normalizeTime($endTime),
        ];
    }

    /**
     * Check if the requested booking overlaps an existing room reservation.
     */
    private function hasOverlap(
        array $roomReservations,
        string $roomNumber,
        string $date,
        string $startTime,
        string $endTime
    ): bool {
        $key = $roomNumber . '|' . $date;

        if (!isset($roomReservations[$key])) {
            return false;
        }

        $newStart = $this->normalizeTime($startTime);
        $newEnd = $this->normalizeTime($endTime);

        foreach ($roomReservations[$key] as $reservation) {
            if ($newStart < $reservation['end'] && $reservation['start'] < $newEnd) {
                return true;
            }
        }

        return false;
    }

    /**
     * Normalize time to HH:MM:SS for safe string comparison.
     */
    private function normalizeTime(string $time): string
    {
        return Carbon::parse($time)->format('H:i:s');
    }

    /**
     * Pick realistic booking status.
     */
    private function pickStatus(string $bookingDate): string
    {
        $date = Carbon::parse($bookingDate);

        if ($date->isPast()) {
            return $this->randomElement(['completed', 'completed', 'completed', 'cancelled']);
        }

        return $this->randomElement(['pending', 'confirmed', 'confirmed', 'confirmed', 'cancelled']);
    }

    /**
     * Generate readable booking ID.
     *
     * Example:
     * BKG-202605-0001
     */
    private function generateBookingId(int $number): string
    {
        return 'BKG-' . now()->format('Ym') . '-' . str_pad((string) $number, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Small helper for random selection.
     */
    private function randomElement(array $items)
    {
        return $items[array_rand($items)];
    }
}