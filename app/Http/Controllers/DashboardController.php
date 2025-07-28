<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Service;
use App\Models\User;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth();

        // Get statistics with real payment data
        $stats = [
            'totalBookings' => Booking::count(),
            'todayBookings' => Booking::whereDate('appointment_date', $today)->count(),
            'totalClients' => $this->getTotalUniqueClients(),
            'totalRevenue' => 'KSh ' . number_format(Payment::completed()->sum('amount')),
            'upcomingBookings' => Booking::where('appointment_date', '>=', $today)
                ->whereIn('status', ['confirmed', 'pending'])
                ->count(),
            'completedBookings' => Booking::where('status', 'completed')->count(),
            'cancelledBookings' => Booking::where('status', 'cancelled')->count(),
            'pendingBookings' => Booking::where('status', 'pending')->count(),
            // New payment-related stats
            'totalPayments' => Payment::count(),
            'pendingPayments' => Payment::pending()->count(),
            'completedPayments' => Payment::completed()->count(),
            'failedPayments' => Payment::failed()->count(),
            'todayRevenue' => 'KSh ' . number_format(Payment::completed()->whereDate('processed_at', $today)->sum('amount')),
            'monthlyRevenue' => 'KSh ' . number_format(Payment::completed()->whereMonth('processed_at', $thisMonth->month)->sum('amount')),
        ];

        // Get recent bookings
        $recentBookings = Booking::with(['services.service'])
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($booking) {
                return [
                    'id' => $booking->id,
                    'booking_reference' => $booking->booking_reference,
                    'client_name' => $booking->client_name,
                    'service_name' => $booking->services->first()?->service->name ?? 'Multiple Services',
                    'appointment_date' => $booking->appointment_date,
                    'start_time' => $booking->start_time,
                    'status' => $booking->status,
                    'total_amount' => 'KSh ' . number_format($booking->total_amount),
                ];
            });

        // Get today's bookings
        $todaysBookings = Booking::with(['services.service'])
            ->whereDate('appointment_date', $today)
            ->orderBy('start_time')
            ->get()
            ->map(function ($booking) {
                return [
                    'id' => $booking->id,
                    'booking_reference' => $booking->booking_reference,
                    'client_name' => $booking->client_name,
                    'service_name' => $booking->services->first()?->service->name ?? 'Multiple Services',
                    'appointment_date' => $booking->appointment_date,
                    'start_time' => $booking->start_time,
                    'status' => $booking->status,
                    'total_amount' => 'KSh ' . number_format($booking->total_amount),
                ];
            });

        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'recentBookings' => $recentBookings,
            'todaysBookings' => $todaysBookings,
        ]);
    }

    private function getTotalUniqueClients()
    {
        return Booking::distinct('client_email')->count('client_email');
    }
}