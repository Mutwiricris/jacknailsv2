<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->get('period', 'last_month');
        $dateRange = $this->getDateRange($period);

        $analytics = [
            'revenue' => $this->getRevenueAnalytics($dateRange),
            'bookings' => $this->getBookingAnalytics($dateRange),
            'clients' => $this->getClientAnalytics($dateRange),
            'performance' => $this->getPerformanceAnalytics($dateRange),
        ];

        return Inertia::render('Dashboard/Analytics', [
            'analytics' => $analytics,
            'period' => $period,
        ]);
    }

    private function getDateRange($period)
    {
        return match($period) {
            'last_week' => [Carbon::now()->subWeek(), Carbon::now()],
            'last_month' => [Carbon::now()->subMonth(), Carbon::now()],
            'last_quarter' => [Carbon::now()->subQuarter(), Carbon::now()],
            'last_year' => [Carbon::now()->subYear(), Carbon::now()],
            default => [Carbon::now()->subMonth(), Carbon::now()],
        };
    }

    private function getRevenueAnalytics($dateRange)
    {
        [$startDate, $endDate] = $dateRange;
        
        $totalRevenue = Booking::where('status', 'completed')
            ->whereBetween('appointment_date', [$startDate, $endDate])
            ->sum('total_amount');

        $previousPeriodRevenue = Booking::where('status', 'completed')
            ->whereBetween('appointment_date', [
                $startDate->copy()->sub($endDate->diff($startDate)),
                $startDate
            ])
            ->sum('total_amount');

        $growth = $previousPeriodRevenue > 0 
            ? (($totalRevenue - $previousPeriodRevenue) / $previousPeriodRevenue) * 100 
            : 0;

        // Monthly revenue for chart
        $monthlyRevenue = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $revenue = Booking::where('status', 'completed')
                ->whereYear('appointment_date', $month->year)
                ->whereMonth('appointment_date', $month->month)
                ->sum('total_amount');
            
            $monthlyRevenue[] = [
                'month' => $month->format('M'),
                'amount' => $revenue,
            ];
        }

        return [
            'total' => 'KSh ' . number_format($totalRevenue),
            'growth' => round($growth, 1),
            'monthly' => $monthlyRevenue,
        ];
    }

    private function getBookingAnalytics($dateRange)
    {
        [$startDate, $endDate] = $dateRange;
        
        $totalBookings = Booking::whereBetween('appointment_date', [$startDate, $endDate])->count();
        
        $previousPeriodBookings = Booking::whereBetween('appointment_date', [
            $startDate->copy()->sub($endDate->diff($startDate)),
            $startDate
        ])->count();

        $growth = $previousPeriodBookings > 0 
            ? (($totalBookings - $previousPeriodBookings) / $previousPeriodBookings) * 100 
            : 0;

        // Booking by status
        $byStatus = Booking::whereBetween('appointment_date', [$startDate, $endDate])
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get()
            ->map(function ($item) use ($totalBookings) {
                return [
                    'status' => $item->status,
                    'count' => $item->count,
                    'percentage' => $totalBookings > 0 ? round(($item->count / $totalBookings) * 100) : 0,
                ];
            });

        // Booking by service
        $byService = DB::table('bookings')
            ->join('booking_services', 'bookings.id', '=', 'booking_services.booking_id')
            ->join('services', 'booking_services.service_id', '=', 'services.id')
            ->whereBetween('bookings.appointment_date', [$startDate, $endDate])
            ->select(
                'services.name as service',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(CASE WHEN bookings.status = "completed" THEN services.price ELSE 0 END) as revenue')
            )
            ->groupBy('services.id', 'services.name', 'services.price')
            ->orderBy('count', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                return [
                    'service' => $item->service,
                    'count' => $item->count,
                    'revenue' => 'KSh ' . number_format($item->revenue),
                ];
            });

        return [
            'total' => $totalBookings,
            'growth' => round($growth, 1),
            'by_status' => $byStatus,
            'by_service' => $byService,
        ];
    }

    private function getClientAnalytics($dateRange)
    {
        [$startDate, $endDate] = $dateRange;
        
        $totalClients = Booking::whereBetween('appointment_date', [$startDate, $endDate])
            ->distinct('client_email')
            ->count();

        $newClients = Booking::whereBetween('created_at', [$startDate, $endDate])
            ->distinct('client_email')
            ->count();

        // Calculate retention rate (clients who made multiple bookings)
        $repeatedClients = DB::table('bookings')
            ->select('client_email', DB::raw('COUNT(*) as booking_count'))
            ->whereBetween('appointment_date', [$startDate, $endDate])
            ->groupBy('client_email')
            ->having('booking_count', '>', 1)
            ->count();

        $retentionRate = $totalClients > 0 ? round(($repeatedClients / $totalClients) * 100) : 0;

        // Top clients
        $topClients = DB::table('bookings')
            ->select(
                'client_name',
                DB::raw('COUNT(*) as bookings'),
                DB::raw('SUM(CASE WHEN status = "completed" THEN total_amount ELSE 0 END) as spent')
            )
            ->whereBetween('appointment_date', [$startDate, $endDate])
            ->groupBy('client_email', 'client_name')
            ->orderBy('spent', 'desc')
            ->limit(3)
            ->get()
            ->map(function ($client) {
                return [
                    'name' => $client->client_name,
                    'bookings' => $client->bookings,
                    'spent' => 'KSh ' . number_format($client->spent),
                ];
            });

        return [
            'total' => $totalClients,
            'new' => $newClients,
            'retention_rate' => $retentionRate,
            'top_clients' => $topClients,
        ];
    }

    private function getPerformanceAnalytics($dateRange)
    {
        [$startDate, $endDate] = $dateRange;
        
        $completedBookings = Booking::where('status', 'completed')
            ->whereBetween('appointment_date', [$startDate, $endDate]);

        $avgAppointmentValue = $completedBookings->avg('total_amount');

        // Peak hours
        $peakHours = Booking::whereBetween('appointment_date', [$startDate, $endDate])
            ->select(
                DB::raw('HOUR(start_time) as hour'),
                DB::raw('COUNT(*) as bookings')
            )
            ->groupBy('hour')
            ->orderBy('bookings', 'desc')
            ->limit(3)
            ->get()
            ->map(function ($item) {
                $hour = Carbon::createFromTime($item->hour, 0);
                return [
                    'hour' => $hour->format('g:i A'),
                    'bookings' => $item->bookings,
                ];
            });

        // Popular services
        $popularServices = DB::table('bookings')
            ->join('booking_services', 'bookings.id', '=', 'booking_services.booking_id')
            ->join('services', 'booking_services.service_id', '=', 'services.id')
            ->whereBetween('bookings.appointment_date', [$startDate, $endDate])
            ->select(
                'services.name as service',
                DB::raw('COUNT(*) as bookings'),
                DB::raw('SUM(CASE WHEN bookings.status = "completed" THEN services.price ELSE 0 END) as revenue')
            )
            ->groupBy('services.id', 'services.name', 'services.price')
            ->orderBy('revenue', 'desc')
            ->limit(3)
            ->get()
            ->map(function ($item) {
                return [
                    'service' => $item->service,
                    'bookings' => $item->bookings,
                    'revenue' => 'KSh ' . number_format($item->revenue),
                ];
            });

        return [
            'avg_appointment_value' => 'KSh ' . number_format($avgAppointmentValue ?: 0),
            'peak_hours' => $peakHours,
            'popular_services' => $popularServices,
        ];
    }
}