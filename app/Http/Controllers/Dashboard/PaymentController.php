<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = Payment::with(['booking']);

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('payment_reference', 'like', "%{$search}%")
                  ->orWhere('transaction_id', 'like', "%{$search}%")
                  ->orWhere('mpesa_transaction_id', 'like', "%{$search}%")
                  ->orWhereHas('booking', function ($subQ) use ($search) {
                      $subQ->where('booking_reference', 'like', "%{$search}%")
                           ->orWhere('client_name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('method')) {
            $query->where('method', $request->method);
        }

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        $payments = $query->latest()->paginate(20)->through(function ($payment) {
            return [
                'id' => $payment->id,
                'payment_reference' => $payment->payment_reference,
                'booking_reference' => $payment->booking->booking_reference,
                'client_name' => $payment->booking->client_name,
                'amount' => $payment->amount,
                'formatted_amount' => $payment->formatted_amount,
                'method' => $payment->method,
                'status' => $payment->status,
                'status_badge_class' => $payment->status_badge_class,
                'transaction_id' => $payment->transaction_id,
                'mpesa_transaction_id' => $payment->mpesa_transaction_id,
                'provider' => $payment->provider,
                'notes' => $payment->notes,
                'processed_at' => $payment->processed_at?->toISOString(),
                'created_at' => $payment->created_at->toISOString(),
            ];
        });

        return Inertia::render('Dashboard/Payments', [
            'payments' => $payments,
            'filters' => $request->only(['search', 'status', 'method', 'date']),
        ]);
    }

    public function show(Payment $payment)
    {
        $payment->load(['booking.services.service']);
        
        return Inertia::render('Dashboard/PaymentDetails', [
            'payment' => [
                'id' => $payment->id,
                'payment_reference' => $payment->payment_reference,
                'amount' => $payment->amount,
                'formatted_amount' => $payment->formatted_amount,
                'method' => $payment->method,
                'status' => $payment->status,
                'status_badge_class' => $payment->status_badge_class,
                'transaction_id' => $payment->transaction_id,
                'mpesa_transaction_id' => $payment->mpesa_transaction_id,
                'provider' => $payment->provider,
                'provider_response' => $payment->provider_response,
                'notes' => $payment->notes,
                'processed_at' => $payment->processed_at,
                'failed_at' => $payment->failed_at,
                'created_at' => $payment->created_at,
                'updated_at' => $payment->updated_at,
                'can_be_refunded' => $payment->canBeRefunded(),
                'booking' => [
                    'id' => $payment->booking->id,
                    'booking_reference' => $payment->booking->booking_reference,
                    'client_name' => $payment->booking->client_name,
                    'client_email' => $payment->booking->client_email,
                    'client_phone' => $payment->booking->client_phone,
                    'appointment_date' => $payment->booking->appointment_date,
                    'start_time' => $payment->booking->start_time,
                    'end_time' => $payment->booking->end_time,
                    'status' => $payment->booking->status,
                    'services' => $payment->booking->services->map(function ($bookingService) {
                        return [
                            'service' => [
                                'id' => $bookingService->service->id,
                                'name' => $bookingService->service->name,
                                'formatted_price' => 'KSh ' . number_format($bookingService->service->price),
                            ]
                        ];
                    }),
                ]
            ]
        ]);
    }

    public function create(Request $request, Booking $booking)
    {
        return Inertia::render('Dashboard/CreatePayment', [
            'booking' => [
                'id' => $booking->id,
                'booking_reference' => $booking->booking_reference,
                'client_name' => $booking->client_name,
                'total_amount' => $booking->total_amount,
                'formatted_amount' => 'KSh ' . number_format($booking->total_amount),
                'payment_method' => $booking->payment_method,
            ]
        ]);
    }

    public function store(Request $request, Booking $booking)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0|max:' . $booking->total_amount,
            'method' => 'required|in:cash,mpesa,card,bank_transfer',
            'transaction_id' => 'nullable|string|max:100',
            'mpesa_transaction_id' => 'nullable|string|max:50',
            'provider' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:500'
        ]);

        try {
            DB::beginTransaction();

            $payment = Payment::createForBooking($booking, $request->all());

            // If amount matches booking total and payment method is cash, mark as completed immediately
            if ($request->method === 'cash' && $request->amount == $booking->total_amount) {
                $payment->markAsCompleted();
            }

            DB::commit();

            return redirect()->route('dashboard.payments.show', $payment)
                ->with('success', 'Payment created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error creating payment: ' . $e->getMessage());
            return back()->withErrors(['payment' => 'Failed to create payment. Please try again.']);
        }
    }

    public function updateStatus(Request $request, Payment $payment)
    {
        $request->validate([
            'status' => 'required|in:pending,processing,completed,failed,cancelled,refunded',
            'transaction_id' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:500'
        ]);

        try {
            DB::beginTransaction();

            switch ($request->status) {
                case 'completed':
                    $payment->markAsCompleted($request->transaction_id);
                    break;
                case 'failed':
                    $payment->markAsFailed($request->notes);
                    break;
                case 'refunded':
                    $payment->markAsRefunded($request->notes);
                    break;
                default:
                    $payment->update([
                        'status' => $request->status,
                        'transaction_id' => $request->transaction_id ?? $payment->transaction_id,
                        'notes' => $request->notes ?? $payment->notes
                    ]);
            }

            DB::commit();

            return back()->with('success', 'Payment status updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error updating payment status: ' . $e->getMessage());
            return back()->withErrors(['payment' => 'Failed to update payment status.']);
        }
    }
}
