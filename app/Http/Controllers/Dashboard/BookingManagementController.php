<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Service;
use App\Models\Payment;
use App\Services\AvailabilityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class BookingManagementController extends Controller
{
    protected AvailabilityService $availabilityService;

    public function __construct(AvailabilityService $availabilityService)
    {
        $this->availabilityService = $availabilityService;
    }

    public function index(Request $request)
    {
        $query = Booking::with(['services.service', 'latestPayment']);

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('client_name', 'like', "%{$search}%")
                  ->orWhere('booking_reference', 'like', "%{$search}%")
                  ->orWhere('client_email', 'like', "%{$search}%")
                  ->orWhereHas('services.service', function ($subQ) use ($search) {
                      $subQ->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date')) {
            $query->whereDate('appointment_date', $request->date);
        }

        $bookings = $query->latest()->paginate(20)->through(function ($booking) {
            return [
                'id' => $booking->id,
                'booking_reference' => $booking->booking_reference,
                'client_name' => $booking->client_name,
                'client_email' => $booking->client_email,
                'client_phone' => $booking->client_phone,
                'services' => $booking->services->map(function ($bookingService) {
                    return [
                        'service' => [
                            'id' => $bookingService->service->id,
                            'name' => $bookingService->service->name,
                            'formatted_price' => 'KSh ' . number_format($bookingService->service->price),
                        ]
                    ];
                }),
                'appointment_date' => $booking->appointment_date,
                'start_time' => $booking->start_time,
                'end_time' => $booking->end_time,
                'status' => $booking->status,
                'payment_status' => $booking->latestPayment?->status ?? $booking->payment_status,
                'payment_method' => $booking->payment_method,
                'has_payment_record' => $booking->latestPayment !== null,
                'payment_id' => $booking->latestPayment?->id,
                'payment_reference' => $booking->latestPayment?->payment_reference,
                'total_amount' => 'KSh ' . number_format($booking->total_amount),
                'formatted_date' => $booking->appointment_date ? \Carbon\Carbon::parse($booking->appointment_date)->format('l, M j, Y') : '',
                'formatted_time_slot' => $booking->start_time && $booking->end_time 
                    ? \Carbon\Carbon::parse($booking->start_time)->format('g:i A') . ' - ' . \Carbon\Carbon::parse($booking->end_time)->format('g:i A')
                    : '',
                'created_at' => $booking->created_at->toISOString(),
                'notes' => $booking->notes,
            ];
        });

        return Inertia::render('Dashboard/Bookings', [
            'bookings' => $bookings,
            'filters' => $request->only(['search', 'status', 'date']),
        ]);
    }

    public function show(Booking $booking)
    {
        $booking->load(['services.service']);
        
        return Inertia::render('Dashboard/BookingDetails', [
            'booking' => [
                'id' => $booking->id,
                'booking_reference' => $booking->booking_reference,
                'client_name' => $booking->client_name,
                'client_email' => $booking->client_email,
                'client_phone' => $booking->client_phone,
                'services' => $booking->services->map(function ($bookingService) {
                    return [
                        'service' => [
                            'id' => $bookingService->service->id,
                            'name' => $bookingService->service->name,
                            'description' => $bookingService->service->description,
                            'price' => $bookingService->service->price,
                            'duration_minutes' => $bookingService->service->duration_minutes,
                            'formatted_price' => 'KSh ' . number_format($bookingService->service->price),
                            'formatted_duration' => $this->formatDuration($bookingService->service->duration_minutes),
                        ]
                    ];
                }),
                'appointment_date' => $booking->appointment_date,
                'start_time' => $booking->start_time,
                'end_time' => $booking->end_time,
                'status' => $booking->status,
                'payment_status' => $booking->payment_status,
                'payment_method' => $booking->payment_method,
                'mpesa_transaction_id' => $booking->mpesa_transaction_id,
                'total_amount' => $booking->total_amount,
                'formatted_amount' => 'KSh ' . number_format($booking->total_amount),
                'formatted_date' => $booking->appointment_date ? \Carbon\Carbon::parse($booking->appointment_date)->format('l, F j, Y') : '',
                'formatted_time_slot' => $booking->start_time && $booking->end_time 
                    ? \Carbon\Carbon::parse($booking->start_time)->format('g:i A') . ' - ' . \Carbon\Carbon::parse($booking->end_time)->format('g:i A')
                    : '',
                'formatted_duration' => $this->formatDuration($booking->services->sum(fn($s) => $s->service->duration_minutes)),
                'notes' => $booking->notes,
                'created_at' => $booking->created_at,
                'updated_at' => $booking->updated_at,
                'status_badge_class' => $this->getStatusBadgeClass($booking->status),
            ]
        ]);
    }

    public function updateStatus(Request $request, Booking $booking)
    {
        $request->validate([
            'status' => 'required|in:pending,confirmed,completed,cancelled'
        ]);

        try {
            DB::beginTransaction();

            $oldStatus = $booking->status;
            $newStatus = $request->status;

            // Validation: Cannot complete booking without payment
            $latestPayment = $booking->latestPayment;
            $paymentStatus = $latestPayment?->status ?? $booking->payment_status;
            
            if ($newStatus === 'completed' && $paymentStatus !== 'completed') {
                return back()->withErrors(['status' => 'Cannot complete booking. Payment must be completed first.']);
            }

            // Update booking status
            $booking->update([
                'status' => $newStatus,
                'confirmed_at' => $newStatus === 'confirmed' ? now() : $booking->confirmed_at,
                'cancelled_at' => $newStatus === 'cancelled' ? now() : null,
                'cancellation_reason' => $newStatus === 'cancelled' ? 'Cancelled by admin' : null,
            ]);

            // Handle time slot logic based on status changes
            if ($newStatus === 'completed') {
                // Release time slot when booking is completed
                $timeSlotReleased = $this->availabilityService->releaseTimeSlot($booking);
                
                DB::commit();
                
                if ($timeSlotReleased) {
                    return back()->with('success', 'Booking marked as completed and time slot released for future bookings.');
                } else {
                    return back()->with('success', 'Booking marked as completed.');
                }
            } 
            elseif ($newStatus === 'cancelled' && in_array($oldStatus, ['pending', 'confirmed'])) {
                // Release time slot when booking is cancelled
                $this->availabilityService->releaseTimeSlot($booking);
                
                DB::commit();
                return back()->with('success', 'Booking cancelled and time slot made available.');
            }
            else {
                DB::commit();
                return back()->with('success', 'Booking status updated successfully.');
            }

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error updating booking status: ' . $e->getMessage());
            return back()->withErrors(['status' => 'Failed to update booking status. Please try again.']);
        }
    }

    public function createPayment(Request $request, Booking $booking)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0|max:' . $booking->total_amount,
            'method' => 'required|in:cash,mpesa,card,bank_transfer',
            'transaction_id' => 'nullable|string|max:100',
            'mpesa_transaction_id' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:500'
        ]);

        try {
            DB::beginTransaction();

            // Create payment record
            $payment = Payment::createForBooking($booking, $request->all());

            // If cash payment and full amount, mark as completed immediately
            if ($request->method === 'cash' && $request->amount == $booking->total_amount) {
                $payment->markAsCompleted();
            }

            DB::commit();

            return back()->with('success', 'Payment record created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error creating payment: ' . $e->getMessage());
            return back()->withErrors(['payment' => 'Failed to create payment record.']);
        }
    }

    public function destroy(Booking $booking)
    {
        DB::transaction(function () use ($booking) {
            // Delete related booking services
            $booking->services()->delete();
            // Delete the booking
            $booking->delete();
        });

        return back()->with('success', 'Booking deleted successfully.');
    }

    private function formatDuration($minutes)
    {
        $hours = floor($minutes / 60);
        $mins = $minutes % 60;
        
        if ($hours > 0 && $mins > 0) {
            return "{$hours}h {$mins}min";
        } elseif ($hours > 0) {
            return "{$hours}h";
        } else {
            return "{$mins}min";
        }
    }

    private function getStatusBadgeClass($status)
    {
        return match($status) {
            'confirmed' => 'bg-blue-100 text-blue-800 border-blue-200',
            'pending' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
            'completed' => 'bg-green-100 text-green-800 border-green-200',
            'cancelled' => 'bg-red-100 text-red-800 border-red-200',
            default => 'bg-gray-100 text-gray-800 border-gray-200',
        };
    }
}