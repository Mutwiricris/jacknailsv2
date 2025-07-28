<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ClientManagementController extends Controller
{
    public function index(Request $request)
    {
        $query = $this->getClientsQuery();

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('client_name', 'like', "%{$search}%")
                  ->orWhere('client_email', 'like', "%{$search}%")
                  ->orWhere('client_phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status') && $request->status !== 'all') {
            if ($request->status === 'vip') {
                $query->having('total_spent', '>', 20000); // VIP threshold
            } elseif ($request->status === 'active') {
                $query->having('last_visit', '>', now()->subMonths(3));
            } elseif ($request->status === 'inactive') {
                $query->having('last_visit', '<=', now()->subMonths(3));
            }
        }

        $clients = $query->get()->map(function ($client) {
            $favoriteServices = $this->getFavoriteServices($client->client_email);
            $notes = $this->getClientNotes($client->client_email);
            
            return [
                'id' => $client->id,
                'name' => $client->client_name,
                'email' => $client->client_email,
                'phone' => $client->client_phone,
                'total_bookings' => $client->total_bookings,
                'total_spent' => 'KSh ' . number_format($client->total_spent),
                'last_visit' => $client->last_visit,
                'status' => $this->getClientStatus($client->total_spent, $client->last_visit),
                'created_at' => $client->first_booking,
                'favorite_services' => $favoriteServices,
                'notes' => $notes,
            ];
        });

        $stats = $this->getClientStats();

        return Inertia::render('Dashboard/Clients', [
            'clients' => $clients,
            'stats' => $stats,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function show($email)
    {
        $clientBookings = Booking::with(['services.service'])
            ->where('client_email', $email)
            ->orderBy('appointment_date', 'desc')
            ->get();

        if ($clientBookings->isEmpty()) {
            abort(404, 'Client not found');
        }

        $client = $clientBookings->first();
        $stats = [
            'total_bookings' => $clientBookings->count(),
            'total_spent' => $clientBookings->where('status', 'completed')->sum('total_amount'),
            'last_visit' => $clientBookings->where('status', 'completed')->first()?->appointment_date,
            'favorite_services' => $this->getFavoriteServices($email),
            'upcoming_bookings' => $clientBookings->where('appointment_date', '>=', now())->count(),
        ];

        return Inertia::render('Dashboard/ClientDetails', [
            'client' => [
                'name' => $client->client_name,
                'email' => $client->client_email,
                'phone' => $client->client_phone,
                'stats' => $stats,
                'status' => $this->getClientStatus($stats['total_spent'], $stats['last_visit']),
            ],
            'bookings' => $clientBookings->map(function ($booking) {
                return [
                    'id' => $booking->id,
                    'booking_reference' => $booking->booking_reference,
                    'appointment_date' => $booking->appointment_date,
                    'start_time' => $booking->start_time,
                    'status' => $booking->status,
                    'total_amount' => 'KSh ' . number_format($booking->total_amount),
                    'services' => $booking->services->map(fn($s) => $s->service->name)->implode(', '),
                    'formatted_date' => \Carbon\Carbon::parse($booking->appointment_date)->format('M j, Y'),
                ];
            })
        ]);
    }

    private function getClientsQuery()
    {
        return DB::table('bookings')
            ->select([
                'client_name',
                'client_email',
                'client_phone',
                DB::raw('COUNT(*) as total_bookings'),
                DB::raw('SUM(CASE WHEN status = "completed" THEN total_amount ELSE 0 END) as total_spent'),
                DB::raw('MAX(CASE WHEN status = "completed" THEN appointment_date END) as last_visit'),
                DB::raw('MIN(created_at) as first_booking'),
                DB::raw('MIN(id) as id')
            ])
            ->groupBy('client_email', 'client_name', 'client_phone')
            ->orderBy('total_spent', 'desc');
    }

    private function getFavoriteServices($email)
    {
        return DB::table('bookings')
            ->join('booking_services', 'bookings.id', '=', 'booking_services.booking_id')
            ->join('services', 'booking_services.service_id', '=', 'services.id')
            ->where('bookings.client_email', $email)
            ->where('bookings.status', 'completed')
            ->select('services.name', DB::raw('COUNT(*) as count'))
            ->groupBy('services.id', 'services.name')
            ->orderBy('count', 'desc')
            ->limit(3)
            ->pluck('name')
            ->toArray();
    }

    private function getClientNotes($email)
    {
        return Booking::where('client_email', $email)
            ->whereNotNull('notes')
            ->latest()
            ->value('notes');
    }

    private function getClientStatus($totalSpent, $lastVisit)
    {
        if ($totalSpent > 20000) {
            return 'vip';
        } elseif ($lastVisit && \Carbon\Carbon::parse($lastVisit)->isAfter(now()->subMonths(3))) {
            return 'active';
        } else {
            return 'inactive';
        }
    }

    private function getClientStats()
    {
        $totalClients = DB::table('bookings')->distinct('client_email')->count();
        $newThisMonth = DB::table('bookings')
            ->select('client_email')
            ->where('created_at', '>=', now()->startOfMonth())
            ->distinct()
            ->count();
        
        $vipClients = DB::table('bookings')
            ->select('client_email', DB::raw('SUM(CASE WHEN status = "completed" THEN total_amount ELSE 0 END) as total_spent'))
            ->groupBy('client_email')
            ->having('total_spent', '>', 20000)
            ->count();

        $averageSpent = DB::table('bookings')
            ->select('client_email', DB::raw('SUM(CASE WHEN status = "completed" THEN total_amount ELSE 0 END) as total_spent'))
            ->groupBy('client_email')
            ->get()
            ->avg('total_spent');

        return [
            'total_clients' => $totalClients,
            'new_this_month' => $newThisMonth,
            'vip_clients' => $vipClients,
            'average_spent' => 'KSh ' . number_format($averageSpent ?: 0),
        ];
    }
}