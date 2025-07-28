<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\Booking;
use App\Models\User;
use App\Models\Payment;
use App\Http\Requests\BookingRequest;
use App\Services\AvailabilityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Carbon\Carbon;

class StepperBookingController extends Controller
{
    protected AvailabilityService $availabilityService;

    public function __construct(AvailabilityService $availabilityService)
    {
        $this->availabilityService = $availabilityService;
    }

    /**
     * Initialize booking flow and redirect to first step
     */
    public function start()
    {
        // Clear any existing booking data
        Session::forget('booking_data');
        
        // Initialize booking data
        Session::put('booking_data', [
            'step' => 1,
            'service_ids' => [],
            'appointment_date' => '',
            'start_time' => '',
            'client_name' => '',
            'client_email' => '',
            'client_phone' => '',
            'notes' => '',
            'payment_method' => 'cash'
        ]);

        return redirect()->route('booking.stepper.step', ['step' => 1]);
    }

    /**
     * Display specific step of booking process
     */
    public function showStep($step)
    {
        $step = (int) $step;
        
        // Validate step
        if ($step < 1 || $step > 5) {
            return redirect()->route('booking.stepper.step', ['step' => 1]);
        }

        $bookingData = Session::get('booking_data', []);
        
        // Redirect to correct step if accessing out of order
        $currentStep = $bookingData['step'] ?? 1;
        if ($step > $currentStep && $currentStep < 5) {
            return redirect()->route('booking.stepper.step', ['step' => $currentStep]);
        }

        switch ($step) {
            case 1:
                return $this->showServiceSelection($bookingData);
            case 2:
                return $this->showDateTimeSelection($bookingData);
            case 3:
                return $this->showClientInformation($bookingData);
            case 4:
                return $this->showPaymentMethod($bookingData);
            case 5:
                return $this->showConfirmation($bookingData);
            default:
                return redirect()->route('booking.stepper.step', ['step' => 1]);
        }
    }

    /**
     * Step 1: Service Selection
     */
    private function showServiceSelection($bookingData)
    {
        $services = Service::active()->get()->map(function ($service) {
            return [
                'id' => $service->id,
                'name' => $service->name,
                'description' => $service->description,
                'price' => $service->price,
                'duration_minutes' => $service->duration_minutes,
                'formatted_price' => $service->formatted_price,
                'formatted_duration' => $service->formatted_duration,
                'is_popular' => $service->is_popular,
                'image_url' => $service->image_url,
            ];
        });
        
        // Group services by type for better UX
        $groupedServices = [
            'manicure' => $services->filter(function ($service) {
                return str_contains(strtolower($service['name']), 'manicure') || 
                       str_contains(strtolower($service['name']), 'plain') ||
                       str_contains(strtolower($service['name']), 'overlay');
            })->values(),
            'pedicure' => $services->filter(function ($service) {
                return str_contains(strtolower($service['name']), 'pedicure') ||
                       str_contains(strtolower($service['name']), 'toes');
            })->values(),
            'acrylics' => $services->filter(function ($service) {
                return str_contains(strtolower($service['name']), 'acrylic') ||
                       str_contains(strtolower($service['name']), 'sculpted');
            })->values(),
            'enhancements' => $services->filter(function ($service) {
                return str_contains(strtolower($service['name']), 'tips') ||
                       str_contains(strtolower($service['name']), 'gel') ||
                       str_contains(strtolower($service['name']), 'chrome') ||
                       str_contains(strtolower($service['name']), 'ombre') ||
                       str_contains(strtolower($service['name']), 'art') ||
                       str_contains(strtolower($service['name']), 'powder');
            })->values(),
            'removal' => $services->filter(function ($service) {
                return str_contains(strtolower($service['name']), 'soak') ||
                       str_contains(strtolower($service['name']), 'removal');
            })->values()
        ];

        return Inertia::render('Booking/Stepper/ServiceSelection', [
            'currentStep' => 1,
            'bookingData' => $bookingData,
            'groupedServices' => $groupedServices,
            'allServices' => $services
        ]);
    }

    /**
     * Step 2: Date & Time Selection
     */
    private function showDateTimeSelection($bookingData)
    {
        if (empty($bookingData['service_ids'])) {
            return redirect()->route('booking.stepper.step', ['step' => 1])
                ->with('error', 'Please select at least one service first.');
        }

        $selectedServices = Service::whereIn('id', $bookingData['service_ids'])->get();
        $availableDates = $this->availabilityService->getAvailableDates(14);

        return Inertia::render('Booking/Stepper/DateTimeSelection', [
            'currentStep' => 2,
            'bookingData' => $bookingData,
            'selectedServices' => $selectedServices,
            'availableDates' => $availableDates
        ]);
    }

    /**
     * Step 3: Client Information
     */
    private function showClientInformation($bookingData)
    {
        if (empty($bookingData['service_ids']) || empty($bookingData['appointment_date']) || empty($bookingData['start_time'])) {
            $nextStep = empty($bookingData['service_ids']) ? 1 : 2;
            return redirect()->route('booking.stepper.step', ['step' => $nextStep])
                ->with('error', 'Please complete the previous steps first.');
        }

        $selectedServices = Service::whereIn('id', $bookingData['service_ids'])->get();

        return Inertia::render('Booking/Stepper/ClientInformation', [
            'currentStep' => 3,
            'bookingData' => $bookingData,
            'selectedServices' => $selectedServices,
            'user' => auth()->user()
        ]);
    }

    /**
     * Step 4: Payment Method
     */
    private function showPaymentMethod($bookingData)
    {
        if (empty($bookingData['client_name']) || empty($bookingData['client_email'])) {
            return redirect()->route('booking.stepper.step', ['step' => 3])
                ->with('error', 'Please complete your contact information first.');
        }

        $selectedServices = Service::whereIn('id', $bookingData['service_ids'])->get();
        $totalAmount = $selectedServices->sum('price');

        return Inertia::render('Booking/Stepper/PaymentMethod', [
            'currentStep' => 4,
            'bookingData' => $bookingData,
            'selectedServices' => $selectedServices,
            'totalAmount' => $totalAmount
        ]);
    }

    /**
     * Step 5: Confirmation
     */
    private function showConfirmation($bookingData)
    {
        if (empty($bookingData['payment_method'])) {
            return redirect()->route('booking.stepper.step', ['step' => 4])
                ->with('error', 'Please select a payment method first.');
        }

        $selectedServices = Service::whereIn('id', $bookingData['service_ids'])->get();

        return Inertia::render('Booking/Stepper/Confirmation', [
            'currentStep' => 5,
            'bookingData' => $bookingData,
            'selectedServices' => $selectedServices,
            'totalAmount' => $selectedServices->sum('price'),
            'totalDuration' => $selectedServices->sum('duration_minutes')
        ]);
    }

    /**
     * Update step data
     */
    public function updateStep(Request $request, $step)
    {
        $step = (int) $step;
        $bookingData = Session::get('booking_data', []);

        switch ($step) {
            case 1:
                $validated = $request->validate([
                    'service_ids' => 'required|array|min:1|max:2',
                    'service_ids.*' => 'exists:services,id'
                ]);
                $bookingData['service_ids'] = $validated['service_ids'];
                $bookingData['step'] = 2;
                break;

            case 2:
                $validated = $request->validate([
                    'appointment_date' => 'required|date|after_or_equal:today',
                    'start_time' => 'required|date_format:H:i',
                    'end_time' => 'required|date_format:H:i',
                    'time_slot_id' => 'required|exists:time_slots,id'
                ]);
                
                // Validate availability
                $errors = $this->availabilityService->validateBookingTime(
                    $validated['appointment_date'],
                    $validated['start_time'],
                    $validated['end_time']
                );
                
                if (!empty($errors)) {
                    return back()->withErrors(['availability' => implode(' ', $errors)]);
                }
                
                $bookingData['appointment_date'] = $validated['appointment_date'];
                $bookingData['start_time'] = $validated['start_time'];
                $bookingData['end_time'] = $validated['end_time'];
                $bookingData['time_slot_id'] = $validated['time_slot_id'];
                $bookingData['step'] = 3;
                break;

            case 3:
                $validated = $request->validate([
                    'client_name' => 'required|string|min:2|max:100',
                    'client_email' => 'required|email|max:100',
                    'client_phone' => 'required|string|min:10|max:15',
                    'notes' => 'nullable|string|max:500'
                ]);
                
                $bookingData = array_merge($bookingData, $validated);
                $bookingData['step'] = 4;
                break;

            case 4:
                $validated = $request->validate([
                    'payment_method' => 'required|in:cash,mpesa,card,bank_transfer'
                ]);
                
                $bookingData['payment_method'] = $validated['payment_method'];
                $bookingData['step'] = 5;
                break;
        }

        Session::put('booking_data', $bookingData);

        if ($step < 5) {
            return redirect()->route('booking.stepper.step', ['step' => $bookingData['step']]);
        } else {
            return redirect()->route('booking.stepper.step', ['step' => 5]);
        }
    }

    /**
     * Get available time slots via AJAX
     */
    public function getTimeSlots(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'service_ids' => 'required|array|min:1|max:2',
            'service_ids.*' => 'exists:services,id'
        ]);

        try {
            $timeSlots = $this->availabilityService->getAvailableTimeSlots(
                $request->date,
                $request->service_ids
            );

            return response()->json([
                'success' => true,
                'timeSlots' => $timeSlots,
                'stats' => $this->availabilityService->getBookingStats($request->date)
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching time slots: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Unable to load available time slots.'
            ], 500);
        }
    }

    /**
     * Complete booking
     */
    public function complete(Request $request)
    {
        $bookingData = Session::get('booking_data', []);
        
        if (empty($bookingData) || $bookingData['step'] != 5) {
            return redirect()->route('booking.stepper.start')
                ->with('error', 'Invalid booking session. Please start over.');
        }

        try {
            DB::beginTransaction();

            $services = Service::whereIn('id', $bookingData['service_ids'])->get();
            $totalDuration = $services->sum('duration_minutes');
            $totalAmount = $services->sum('price');
            
            // Calculate end time
            $startTime = Carbon::createFromFormat('H:i', $bookingData['start_time']);
            $endTime = $startTime->copy()->addMinutes($totalDuration);

            // Final availability check
            $errors = $this->availabilityService->validateBookingTime(
                $bookingData['appointment_date'],
                $bookingData['start_time'],
                $bookingData['end_time']
            );
            
            if (!empty($errors)) {
                DB::rollBack();
                return back()->withErrors(['availability' => implode(' ', $errors)]);
            }

            // Create or get user
            $user = null;
            if (auth()->check()) {
                $user = auth()->user();
            } else {
                $user = User::where('email', $bookingData['client_email'])->first();
            }

            // Create booking
            $booking = Booking::create([
                'client_id' => $user?->id,
                'client_name' => $bookingData['client_name'],
                'client_email' => $bookingData['client_email'],
                'client_phone' => $bookingData['client_phone'],
                'appointment_date' => $bookingData['appointment_date'],
                'start_time' => $bookingData['start_time'],
                'end_time' => $bookingData['end_time'],
                'total_amount' => $totalAmount,
                'payment_method' => $bookingData['payment_method'],
                'notes' => $bookingData['notes'],
                'status' => 'pending',
                'payment_status' => 'pending'
            ]);

            // Book the time slot
            $timeSlotBooked = $this->availabilityService->bookTimeSlot(
                $bookingData['time_slot_id'],
                $booking
            );

            if (!$timeSlotBooked) {
                DB::rollBack();
                return back()->withErrors(['availability' => 'Time slot is no longer available.']);
            }

            // Create booking service records
            foreach ($services as $service) {
                $booking->services()->create([
                    'service_id' => $service->id,
                    'service_price' => $service->price,
                    'service_duration_minutes' => $service->duration_minutes
                ]);
            }

            // Create initial payment record
            $payment = Payment::createForBooking($booking, [
                'method' => $bookingData['payment_method'],
                'amount' => $totalAmount
            ]);

            // If cash payment, mark as completed immediately
            if ($bookingData['payment_method'] === 'cash') {
                $payment->markAsCompleted();
            }

            DB::commit();

            // Clear booking session data
            Session::forget('booking_data');

            return redirect()->route('booking.confirmation', $booking->booking_reference)
                ->with('success', 'Your appointment has been successfully booked!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error completing booking: ' . $e->getMessage());
            return back()->with('error', 'Unable to complete your booking. Please try again.');
        }
    }

    /**
     * Go back to previous step
     */
    public function previousStep($step)
    {
        $step = (int) $step;
        $prevStep = max(1, $step - 1);
        
        $bookingData = Session::get('booking_data', []);
        $bookingData['step'] = $prevStep;
        Session::put('booking_data', $bookingData);
        
        return redirect()->route('booking.stepper.step', ['step' => $prevStep]);
    }
}