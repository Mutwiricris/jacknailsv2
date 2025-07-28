<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\TimeSlot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Carbon\Carbon;

class TimeSlotController extends Controller
{
    /**
     * Display time slot management interface
     */
    public function index(Request $request)
    {
        $date = $request->get('date', now()->format('Y-m-d'));
        $status = $request->get('status', 'all');
        
        try {
            $query = TimeSlot::forDate($date)->orderBy('start_time');
            
            if ($status !== 'all') {
                $query->where('status', $status);
            }
            
            $timeSlots = $query->with('booking')->get()->map(function ($slot) {
                return [
                    'id' => $slot->id,
                    'date' => $slot->date,
                    'start_time' => $slot->start_time,
                    'end_time' => $slot->end_time,
                    'formatted_start_time' => Carbon::parse($slot->start_time)->format('g:i A'),
                    'formatted_end_time' => Carbon::parse($slot->end_time)->format('g:i A'),
                    'status' => $slot->status,
                    'status_label' => ucfirst($slot->status),
                    'notes' => $slot->notes,
                    'booking_id' => $slot->booking_id,
                    'booking' => $slot->booking ? [
                        'id' => $slot->booking->id,
                        'booking_reference' => $slot->booking->booking_reference,
                        'client_name' => $slot->booking->client_name,
                        'client_email' => $slot->booking->client_email,
                        'total_amount' => $slot->booking->total_amount
                    ] : null,
                    'is_available' => $slot->is_available,
                    'is_booked' => $slot->is_booked,
                    'is_unavailable' => $slot->is_unavailable,
                ];
            });
            
            // Get stats for the selected date
            $stats = [
                'total' => TimeSlot::forDate($date)->count(),
                'available' => TimeSlot::forDate($date)->available()->count(),
                'booked' => TimeSlot::forDate($date)->booked()->count(),
                'unavailable' => TimeSlot::forDate($date)->unavailable()->count(),
            ];
            
            return Inertia::render('Dashboard/TimeSlots', [
                'timeSlots' => $timeSlots,
                'stats' => $stats,
                'selectedDate' => $date,
                'selectedStatus' => $status,
                'filters' => [
                    'date' => $date,
                    'status' => $status
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error loading time slots: ' . $e->getMessage());
            return back()->with('error', 'Unable to load time slots.');
        }
    }

    /**
     * Store a new time slot
     */
    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'status' => 'required|in:available,unavailable',
            'notes' => 'nullable|string|max:500'
        ]);

        try {
            // Check if time slot already exists
            $existingSlot = TimeSlot::where('date', $request->date)
                ->where('start_time', $request->start_time)
                ->where('end_time', $request->end_time)
                ->first();

            if ($existingSlot) {
                return back()->with('error', 'A time slot with the same date and time already exists.');
            }

            TimeSlot::create([
                'date' => $request->date,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'status' => $request->status,
                'notes' => $request->notes
            ]);

            return back()->with('success', 'Time slot created successfully.');

        } catch (\Exception $e) {
            Log::error('Error creating time slot: ' . $e->getMessage());
            return back()->with('error', 'Unable to create time slot.');
        }
    }

    /**
     * Update time slot status
     */
    public function updateStatus(Request $request, TimeSlot $timeSlot)
    {
        $request->validate([
            'status' => 'required|in:available,booked,unavailable',
            'notes' => 'nullable|string|max:500'
        ]);

        try {
            DB::beginTransaction();

            $oldStatus = $timeSlot->status;
            $newStatus = $request->status;

            // If changing to booked status, ensure there's a booking
            if ($newStatus === 'booked' && !$timeSlot->booking_id) {
                return back()->with('error', 'Cannot mark time slot as booked without an associated booking.');
            }

            // If changing from booked to available, clear booking association
            if ($oldStatus === 'booked' && $newStatus === 'available') {
                $timeSlot->booking_id = null;
            }

            $timeSlot->update([
                'status' => $newStatus,
                'notes' => $request->notes
            ]);

            DB::commit();

            return back()->with('success', "Time slot status updated to {$newStatus}.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating time slot status: ' . $e->getMessage());
            return back()->with('error', 'Unable to update time slot status.');
        }
    }

    /**
     * Bulk update time slots for a day
     */
    public function bulkUpdate(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'action' => 'required|in:mark_available,mark_unavailable,generate_slots',
            'notes' => 'nullable|string|max:500'
        ]);

        try {
            DB::beginTransaction();

            $date = $request->date;
            $action = $request->action;
            $notes = $request->notes;

            if ($action === 'generate_slots') {
                // Generate time slots for the day if they don't exist
                $this->generateTimeSlotsForDate($date);
                DB::commit();
                return back()->with('success', 'Time slots generated for ' . Carbon::parse($date)->format('M j, Y'));
            }

            // Get all time slots for the date (excluding booked ones)
            $timeSlots = TimeSlot::forDate($date)
                ->where('status', '!=', 'booked')
                ->get();

            if ($timeSlots->isEmpty()) {
                return back()->with('error', 'No time slots found for the selected date.');
            }

            $newStatus = $action === 'mark_available' ? 'available' : 'unavailable';
            $updatedCount = 0;

            foreach ($timeSlots as $slot) {
                $slot->update([
                    'status' => $newStatus,
                    'notes' => $notes
                ]);
                $updatedCount++;
            }

            DB::commit();

            $statusLabel = $newStatus === 'available' ? 'available' : 'unavailable';
            return back()->with('success', "Marked {$updatedCount} time slots as {$statusLabel} for " . Carbon::parse($date)->format('M j, Y'));

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error bulk updating time slots: ' . $e->getMessage());
            return back()->with('error', 'Unable to update time slots.');
        }
    }

    /**
     * Generate time slots for a specific date
     */
    private function generateTimeSlotsForDate(string $date): void
    {
        $date = Carbon::parse($date);
        
        // Don't generate for Sundays (business is closed)
        if ($date->dayOfWeek === 0) {
            return;
        }

        // Check if slots already exist for this date
        $existingSlots = TimeSlot::forDate($date->format('Y-m-d'))->count();
        if ($existingSlots > 0) {
            return;
        }

        $startHour = 9; // 9 AM
        $endHour = 18;  // 6 PM
        $slotDuration = 30; // 30 minutes per slot

        $currentTime = $date->copy()->setTime($startHour, 0);
        $endTime = $date->copy()->setTime($endHour, 0);

        while ($currentTime->lt($endTime)) {
            $slotEnd = $currentTime->copy()->addMinutes($slotDuration);
            
            TimeSlot::create([
                'date' => $date->format('Y-m-d'),
                'start_time' => $currentTime->format('H:i:s'),
                'end_time' => $slotEnd->format('H:i:s'),
                'status' => 'available'
            ]);

            $currentTime->addMinutes($slotDuration);
        }
    }

    /**
     * Delete time slot
     */
    public function destroy(TimeSlot $timeSlot)
    {
        try {
            if ($timeSlot->is_booked) {
                return back()->with('error', 'Cannot delete a booked time slot.');
            }

            $timeSlot->delete();
            return back()->with('success', 'Time slot deleted successfully.');

        } catch (\Exception $e) {
            Log::error('Error deleting time slot: ' . $e->getMessage());
            return back()->with('error', 'Unable to delete time slot.');
        }
    }
}