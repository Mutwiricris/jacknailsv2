<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\Booking;
use App\Models\User;
use App\Http\Requests\BookingRequest;
use App\Services\AvailabilityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Carbon\Carbon;

class BookingController extends Controller
{
    protected AvailabilityService $availabilityService;

    public function __construct(AvailabilityService $availabilityService)
    {
        $this->availabilityService = $availabilityService;
    }

    public function index()
    {
        $services = Service::active()->get();
        
        return Inertia::render('Booking/Index', [
            'services' => $services,
        ]);
    }

    public function create()
    {
        $services = Service::active()->get();
        
        return Inertia::render('Booking/Create', [
            'services' => $services,
        ]);
    }

    public function getAvailableTimeSlots(Request $request)
    {
        $request->validate([
            'date' => 'required|date|after_or_equal:today',
            'service_ids' => 'required|array|min:1|max:2',
            'service_ids.*' => 'required|exists:services,id'
        ]);

        try {
            $timeSlots = $this->availabilityService->getAvailableTimeSlots(
                $request->date,
                $request->service_ids
            );

            // Transform the grouped data to flat array for compatibility
            $flatTimeSlots = [];
            foreach ($timeSlots as $group) {
                foreach ($group['slots'] as $slot) {
                    $flatTimeSlots[] = [
                        'id' => $slot['id'],
                        'time' => $slot['time'],
                        'end_time' => $slot['end_time'],
                        'formatted_time' => $slot['formatted_time'],
                        'available' => $slot['available']
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'timeSlots' => $flatTimeSlots,
                'stats' => $this->availabilityService->getBookingStats($request->date),
                'message' => count($flatTimeSlots) > 0 ? 'Available time slots loaded.' : 'No available time slots for this date.'
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting time slots: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Unable to load available time slots.'
            ], 500);
        }
    }

    public function store(BookingRequest $request)
    {
        try {
            DB::beginTransaction();

            $services = Service::whereIn('id', $request->service_ids)->get();
            $totalDuration = $services->sum('duration_minutes');
            $totalAmount = $services->sum('price');
            
            // Calculate end time
            $startTime = Carbon::createFromFormat('H:i', $request->start_time);
            $endTime = $startTime->copy()->addMinutes($totalDuration);

            // Validate time slot availability
            $errors = $this->availabilityService->validateBookingTime(
                $request->appointment_date,
                $request->start_time,
                $endTime->format('H:i')
            );
            
            if (!empty($errors)) {
                DB::rollBack();
                return back()->withErrors(['availability' => implode(' ', $errors)]);
            }

            // Create or get user (for guest bookings, client_id can be null)
            $user = null;
            if (auth()->check()) {
                $user = auth()->user();
            } else {
                // For guest bookings, try to find existing user by email
                $user = User::where('email', $request->client_email)->first();
            }

            $booking = Booking::create([
                'client_id' => $user?->id,
                'client_name' => $request->client_name,
                'client_email' => $request->client_email,
                'client_phone' => $request->client_phone,
                'appointment_date' => $request->appointment_date,
                'start_time' => $request->start_time,
                'end_time' => $endTime->format('H:i'),
                'total_amount' => $totalAmount,
                'payment_method' => $request->payment_method,
                'notes' => $request->notes,
                'status' => 'pending',
                'payment_status' => 'pending'
            ]);

            // Book the time slot if time_slot_id is provided
            if ($request->has('time_slot_id')) {
                $timeSlotBooked = $this->availabilityService->bookTimeSlot(
                    $request->time_slot_id,
                    $booking
                );

                if (!$timeSlotBooked) {
                    DB::rollBack();
                    return back()->with('error', 'Time slot is no longer available. Please select another time.');
                }
            }

            // Create booking service records
            foreach ($services as $service) {
                $booking->services()->create([
                    'service_id' => $service->id,
                    'service_price' => $service->price,
                    'service_duration_minutes' => $service->duration_minutes
                ]);
            }

            DB::commit();

            return redirect()->route('booking.confirmation', $booking->booking_reference)
                ->with('success', 'Your appointment has been successfully booked!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating booking: ' . $e->getMessage());
            return back()->with('error', 'Unable to complete your booking. Please try again.');
        }
    }

    public function confirmation($reference)
    {
        try {
            $booking = Booking::where('booking_reference', $reference)
                ->with(['services.service'])
                ->firstOrFail();

            return Inertia::render('Booking/Confirmation', [
                'booking' => $booking
            ]);

        } catch (\Exception $e) {
            Log::error('Error loading confirmation: ' . $e->getMessage());
            return redirect()->route('booking.index')->with('error', 'Booking not found.');
        }
    }

    public function show($reference)
    {
        try {
            $booking = Booking::where('booking_reference', $reference)
                ->with(['services.service'])
                ->firstOrFail();

            return Inertia::render('Booking/Show', [
                'booking' => $booking
            ]);

        } catch (\Exception $e) {
            Log::error('Error loading booking: ' . $e->getMessage());
            return redirect()->route('booking.index')->with('error', 'Booking not found.');
        }
    }

    public function cancel(Request $request, $reference)
    {
        try {
            DB::beginTransaction();
            
            $booking = Booking::where('booking_reference', $reference)->firstOrFail();

            if (!$booking->canBeCancelled()) {
                return back()->with('error', 'This booking cannot be cancelled.');
            }

            // Release the time slot
            $timeSlotReleased = $this->availabilityService->releaseTimeSlot($booking);

            $booking->update([
                'status' => 'cancelled',
                'cancellation_reason' => $request->reason ?? 'Cancelled by client',
                'cancelled_at' => now()
            ]);

            DB::commit();

            if ($timeSlotReleased) {
                return back()->with('success', 'Your booking has been cancelled successfully and the time slot is now available for other clients.');
            } else {
                return back()->with('success', 'Your booking has been cancelled successfully.');
            }

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error cancelling booking: ' . $e->getMessage());
            return back()->with('error', 'Unable to cancel booking. Please try again.');
        }
    }
}