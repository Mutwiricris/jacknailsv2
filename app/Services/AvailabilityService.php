<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Service;
use App\Models\TimeSlot;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AvailabilityService
{
    // Business hours configuration
    private const BUSINESS_START_HOUR = 9;
    private const BUSINESS_END_HOUR = 18;
    private const SLOT_DURATION_MINUTES = 30;
    private const BUFFER_TIME_MINUTES = 15;
    private const MIN_BOOKING_NOTICE_HOURS = 2;
    private const CLOSED_DAYS = [0]; // Sunday = 0

    /**
     * Get available time slots for given date and services
     */
    public function getAvailableTimeSlots(string $date, array $serviceIds): Collection
    {
        try {
            $date = Carbon::parse($date);
            
            // Check if date is valid
            if (!$this->isDateAvailable($date)) {
                return collect();
            }

            // Get available time slots from database
            $timeSlots = TimeSlot::forDate($date->format('Y-m-d'))
                ->available()
                ->orderBy('start_time')
                ->get()
                ->map(function ($slot) {
                    $startTime = Carbon::parse($slot->start_time);
                    $endTime = Carbon::parse($slot->end_time);
                    
                    return [
                        'id' => $slot->id,
                        'time' => $startTime->format('H:i'),
                        'end_time' => $endTime->format('H:i'),
                        'formatted_time' => $startTime->format('g:i A'),
                        'formatted_end_time' => $endTime->format('g:i A'),
                        'available' => true,
                        'period' => $this->getTimePeriod($startTime),
                        'is_peak_time' => $this->isPeakTime($startTime)
                    ];
                });

            return $this->groupSlotsByPeriod($timeSlots);
            
        } catch (\Exception $e) {
            \Log::error('Error getting time slots: ' . $e->getMessage());
            return collect();
        }
    }

    /**
     * Check if a specific time slot is available
     */
    public function isTimeSlotAvailable(Carbon $date, string $startTime, string $endTime): bool
    {
        try {
            $slotStart = Carbon::createFromFormat('Y-m-d H:i', $date->format('Y-m-d') . ' ' . $startTime);
            
            // Check minimum notice requirement
            if ($slotStart->lt(now()->addHours(self::MIN_BOOKING_NOTICE_HOURS))) {
                return false;
            }

            // Check if time slot exists and is available in database
            $timeSlot = TimeSlot::forDate($date->format('Y-m-d'))
                ->where('start_time', $startTime)
                ->where('end_time', $endTime)
                ->first();

            return $timeSlot && $timeSlot->is_available;
            
        } catch (\Exception $e) {
            \Log::error('Error checking time slot availability: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if date is available for booking
     */
    public function isDateAvailable(Carbon $date): bool
    {
        // Check if date is in the past
        if ($date->lt(now()->startOfDay())) {
            return false;
        }

        // Check if date is too far in the future (optional - 3 months)
        if ($date->gt(now()->addMonths(3))) {
            return false;
        }

        // Check if date has any available time slots
        $hasAvailableSlots = TimeSlot::forDate($date->format('Y-m-d'))
            ->available()
            ->exists();

        return $hasAvailableSlots;
    }

    /**
     * Get available dates for the next few weeks
     */
    public function getAvailableDates(int $daysCount = 14): Collection
    {
        $dates = collect();
        $currentDate = now()->startOfDay();
        
        for ($i = 0; $i < $daysCount * 2; $i++) { // Check more days to account for closed days
            $date = $currentDate->copy()->addDays($i);
            
            if ($this->isDateAvailable($date) && $dates->count() < $daysCount) {
                $dates->push([
                    'date' => $date->format('Y-m-d'),
                    'formatted_date' => $date->format('l, M j'),
                    'short_date' => $date->format('M j'),
                    'day_name' => $date->format('D'),
                    'is_today' => $date->isToday(),
                    'is_tomorrow' => $date->isTomorrow(),
                    'has_availability' => $this->dateHasAvailability($date)
                ]);
            }
        }

        return $dates;
    }

    /**
     * Check if a date has any availability
     */
    private function dateHasAvailability(Carbon $date): bool
    {
        return TimeSlot::forDate($date->format('Y-m-d'))
            ->available()
            ->exists();
    }

    /**
     * Get time period (Morning/Afternoon/Evening)
     */
    private function getTimePeriod(Carbon $time): string
    {
        $hour = $time->hour;
        
        if ($hour < 12) {
            return 'Morning';
        } elseif ($hour < 17) {
            return 'Afternoon';
        } else {
            return 'Evening';
        }
    }

    /**
     * Check if time is during peak hours
     */
    private function isPeakTime(Carbon $time): bool
    {
        $hour = $time->hour;
        $dayOfWeek = $time->dayOfWeek;
        
        // Peak times: Weekends 10AM-4PM, Weekdays 5PM-7PM
        if (in_array($dayOfWeek, [5, 6])) { // Friday, Saturday
            return $hour >= 10 && $hour < 16;
        } else {
            return $hour >= 17 && $hour < 19;
        }
    }

    /**
     * Group time slots by period
     */
    private function groupSlotsByPeriod(Collection $timeSlots): Collection
    {
        return $timeSlots->groupBy('period')->map(function ($slots, $period) {
            return [
                'period' => $period,
                'slots' => $slots->values()
            ];
        })->values();
    }

    /**
     * Get booking statistics for a date
     */
    public function getBookingStats(string $date): array
    {
        $totalSlots = TimeSlot::forDate($date)->count();
        $bookedSlots = TimeSlot::forDate($date)->booked()->count();
        $unavailableSlots = TimeSlot::forDate($date)->unavailable()->count();
        $availableSlots = TimeSlot::forDate($date)->available()->count();

        return [
            'total_slots' => $totalSlots,
            'booked_slots' => $bookedSlots,
            'available_slots' => $availableSlots,
            'unavailable_slots' => $unavailableSlots,
            'booking_percentage' => $totalSlots > 0 ? round(($bookedSlots / $totalSlots) * 100) : 0
        ];
    }

    /**
     * Get total possible slots for a date
     */
    private function getTotalSlotsForDate(string $date): int
    {
        return TimeSlot::forDate($date)->count();
    }

    /**
     * Validate booking time constraints
     */
    public function validateBookingTime(string $date, string $startTime, string $endTime): array
    {
        $errors = [];
        
        try {
            $date = Carbon::parse($date);
            $startDateTime = Carbon::createFromFormat('Y-m-d H:i', $date->format('Y-m-d') . ' ' . $startTime);
            
            // Check if date is available
            if (!$this->isDateAvailable($date)) {
                $errors[] = 'Selected date is not available for booking.';
            }
            
            // Check minimum notice
            if ($startDateTime->lt(now()->addHours(self::MIN_BOOKING_NOTICE_HOURS))) {
                $errors[] = 'Booking must be made at least ' . self::MIN_BOOKING_NOTICE_HOURS . ' hours in advance.';
            }
            
            // Check if time slot exists and is available
            $timeSlot = TimeSlot::forDate($date->format('Y-m-d'))
                ->where('start_time', $startTime)
                ->where('end_time', $endTime)
                ->first();
            
            if (!$timeSlot) {
                $errors[] = 'Selected time slot does not exist.';
            } elseif (!$timeSlot->is_available) {
                $errors[] = 'Selected time slot is no longer available.';
            }
            
        } catch (\Exception $e) {
            $errors[] = 'Invalid date or time format.';
        }
        
        return $errors;
    }

    /**
     * Book a time slot
     */
    public function bookTimeSlot(int $timeSlotId, Booking $booking): bool
    {
        try {
            $timeSlot = TimeSlot::find($timeSlotId);
            
            if (!$timeSlot || !$timeSlot->is_available) {
                return false;
            }
            
            $timeSlot->markAsBooked($booking);
            return true;
            
        } catch (\Exception $e) {
            \Log::error('Error booking time slot: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Release a time slot when booking is cancelled
     */
    public function releaseTimeSlot(Booking $booking): bool
    {
        try {
            $timeSlot = TimeSlot::where('booking_id', $booking->id)->first();
            
            if ($timeSlot) {
                $timeSlot->markAsAvailable();
                return true;
            }
            
            return false;
            
        } catch (\Exception $e) {
            \Log::error('Error releasing time slot: ' . $e->getMessage());
            return false;
        }
    }
}