<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ServiceManagementController extends Controller
{
    public function index(Request $request)
    {
        $query = Service::query();

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $services = $query->orderBy('name')->get()->map(function ($service) {
            return [
                'id' => $service->id,
                'name' => $service->name,
                'description' => $service->description,
                'price' => $service->price,
                'duration_minutes' => $service->duration_minutes,
                'formatted_price' => 'KSh ' . number_format($service->price),
                'formatted_duration' => $this->formatDuration($service->duration_minutes),
                'status' => $service->status,
                'is_popular' => $service->is_popular,
                'is_active' => $service->is_active,
                'image_url' => $service->image_url,
                'created_at' => $service->created_at->toDateString(),
                'updated_at' => $service->updated_at->toDateString(),
            ];
        });

        return Inertia::render('Dashboard/Services', [
            'services' => $services,
            'filters' => $request->only(['search']),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'duration_minutes' => 'required|integer|min:15',
            'is_popular' => 'boolean',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $serviceData = $request->except(['image']);
        $serviceData['status'] = 'active'; // Set default status

        // Handle image upload
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('storage/services'), $imageName);
            $serviceData['image_url'] = '/storage/services/' . $imageName;
        }

        Service::create($serviceData);

        return back()->with('success', 'Service created successfully.');
    }

    public function show(Service $service)
    {
        return Inertia::render('Dashboard/ServiceDetails', [
            'service' => [
                'id' => $service->id,
                'name' => $service->name,
                'description' => $service->description,
                'price' => $service->price,
                'duration_minutes' => $service->duration_minutes,
                'formatted_price' => 'KSh ' . number_format($service->price),
                'formatted_duration' => $this->formatDuration($service->duration_minutes),
                'status' => $service->status,
                'is_popular' => $service->is_popular,
                'is_active' => $service->is_active,
                'image_url' => $service->image_url,
                'created_at' => $service->created_at,
                'updated_at' => $service->updated_at,
                // Get booking statistics for this service
                'total_bookings' => $service->bookingServices()->count(),
                'monthly_bookings' => $service->bookingServices()
                    ->whereHas('booking', fn($q) => $q->whereMonth('created_at', now()->month))
                    ->count(),
                'formatted_revenue' => 'KSh ' . number_format($service->bookingServices()
                    ->whereHas('booking', fn($q) => $q->where('status', 'completed'))
                    ->count() * $service->price),
                'popularity_rank' => 1, // We'll calculate this properly later
                'recent_bookings' => $service->bookingServices()
                    ->with(['booking' => function($query) {
                        $query->select('id', 'client_name', 'client_email', 'appointment_date', 'status');
                    }])
                    ->latest()
                    ->limit(5)
                    ->get()
                    ->map(function($bookingService) {
                        return [
                            'id' => $bookingService->booking->id,
                            'client_name' => $bookingService->booking->client_name,
                            'client_email' => $bookingService->booking->client_email,
                            'formatted_date' => $bookingService->booking->appointment_date,
                            'status' => $bookingService->booking->status,
                            'status_badge_class' => $this->getStatusBadgeClass($bookingService->booking->status),
                        ];
                    }),
            ]
        ]);
    }

    public function update(Request $request, Service $service)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'duration_minutes' => 'required|integer|min:15',
            'is_popular' => 'boolean',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $serviceData = $request->except(['image']);
        
        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($service->image_url && file_exists(public_path('storage/services/' . basename($service->image_url)))) {
                unlink(public_path('storage/services/' . basename($service->image_url)));
            }
            
            $image = $request->file('image');
            $imageName = time() . '_' . $service->id . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('storage/services'), $imageName);
            $serviceData['image_url'] = '/storage/services/' . $imageName;
        }

        $service->update($serviceData);

        return back()->with('success', 'Service updated successfully.');
    }

    public function destroy(Service $service)
    {
        // Check if service has any bookings
        if ($service->bookingServices()->exists()) {
            return back()->withErrors(['service' => 'Cannot delete service with existing bookings.']);
        }

        $service->delete();

        return back()->with('success', 'Service deleted successfully.');
    }

    public function toggleStatus(Request $request, Service $service)
    {
        $newStatus = $service->status === 'active' ? 'inactive' : 'active';
        $service->update([
            'status' => $newStatus
        ]);

        return back()->with('success', 'Service status updated successfully.');
    }

    public function bulkActivate(Request $request)
    {
        $request->validate([
            'service_ids' => 'required|array',
            'service_ids.*' => 'exists:services,id'
        ]);

        Service::whereIn('id', $request->service_ids)->update(['status' => 'active']);

        $count = count($request->service_ids);
        return back()->with('success', "{$count} services activated successfully.");
    }

    public function bulkDeactivate(Request $request)
    {
        $request->validate([
            'service_ids' => 'required|array',
            'service_ids.*' => 'exists:services,id'
        ]);

        Service::whereIn('id', $request->service_ids)->update(['status' => 'inactive']);

        $count = count($request->service_ids);
        return back()->with('success', "{$count} services deactivated successfully.");
    }

    public function uploadImage(Request $request, Service $service)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_' . $service->id . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('storage/services'), $imageName);
            
            // Delete old image if exists
            if ($service->image_url && file_exists(public_path('storage/services/' . basename($service->image_url)))) {
                unlink(public_path('storage/services/' . basename($service->image_url)));
            }
            
            $service->update([
                'image_url' => '/storage/services/' . $imageName
            ]);

            return back()->with('success', 'Service image updated successfully.');
        }

        return back()->withErrors(['image' => 'Failed to upload image.']);
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
            'confirmed' => 'bg-green-100 text-green-800',
            'pending' => 'bg-yellow-100 text-yellow-800',
            'completed' => 'bg-blue-100 text-blue-800',
            'cancelled' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }
}