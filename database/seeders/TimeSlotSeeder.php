<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TimeSlot;
use Carbon\Carbon;

class TimeSlotSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing time slots
        TimeSlot::truncate();
        
        // Generate time slots for the next 30 days
        $startDate = Carbon::now();
        $endDate = Carbon::now()->addDays(30);

        // Define working hours (9 AM to 6 PM)
        $workingHours = [
            ['start' => '09:00', 'end' => '10:30'],
            ['start' => '10:30', 'end' => '12:00'],
            ['start' => '12:00', 'end' => '13:30'],
            ['start' => '14:00', 'end' => '15:30'], // 1 hour lunch break
            ['start' => '15:30', 'end' => '17:00'],
            ['start' => '17:00', 'end' => '18:30'],
        ];

        $current = $startDate->copy();
        
        while ($current->lte($endDate)) {
            // Skip Sundays (assuming salon is closed on Sundays)
            if ($current->isSunday()) {
                // Mark entire day as unavailable
                foreach ($workingHours as $slot) {
                    TimeSlot::create([
                        'date' => $current->toDateString(),
                        'start_time' => $slot['start'],
                        'end_time' => $slot['end'],
                        'status' => 'unavailable',
                        'notes' => 'Closed on Sundays'
                    ]);
                }
            } else {
                // Create available time slots for working days
                foreach ($workingHours as $slot) {
                    TimeSlot::create([
                        'date' => $current->toDateString(),
                        'start_time' => $slot['start'],
                        'end_time' => $slot['end'],
                        'status' => 'available'
                    ]);
                }
            }
            
            $current->addDay();
        }

        $this->command->info('Time slots generated for the next 30 days.');
    }
}