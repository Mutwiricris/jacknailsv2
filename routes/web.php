<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    // Get random services by category for homepage display
    $services = \App\Models\Service::active()->get();
    
    // Group services by category based on keywords
    $categorizedServices = [
        'manicure' => $services->filter(function ($service) {
            return str_contains(strtolower($service->name), 'manicure') || 
                   str_contains(strtolower($service->name), 'plain') ||
                   str_contains(strtolower($service->name), 'overlay');
        }),
        'pedicure' => $services->filter(function ($service) {
            return str_contains(strtolower($service->name), 'pedicure') ||
                   str_contains(strtolower($service->name), 'toes');
        }),
        'acrylics' => $services->filter(function ($service) {
            return str_contains(strtolower($service->name), 'acrylic') ||
                   str_contains(strtolower($service->name), 'sculpted');
        }),
        'enhancements' => $services->filter(function ($service) {
            return str_contains(strtolower($service->name), 'tips') ||
                   str_contains(strtolower($service->name), 'gel') ||
                   str_contains(strtolower($service->name), 'chrome') ||
                   str_contains(strtolower($service->name), 'ombre') ||
                   str_contains(strtolower($service->name), 'art') ||
                   str_contains(strtolower($service->name), 'powder');
        }),
        'removal' => $services->filter(function ($service) {
            return str_contains(strtolower($service->name), 'soak') ||
                   str_contains(strtolower($service->name), 'removal');
        })
    ];
    
    // Filter out empty categories
    $categorizedServices = array_filter($categorizedServices, function ($categoryServices) {
        return $categoryServices->count() > 0;
    });
    
    // Get up to 2 categories with services for two rows
    $availableCategories = array_keys($categorizedServices);
    $selectedCategories = array_slice($availableCategories, 0, 2);
    
    $featuredServices = [
        'firstRow' => [],
        'secondRow' => []
    ];
    
    // Get 3 random services from each selected category (one category per row)
    foreach ($selectedCategories as $index => $category) {
        $categoryServices = $categorizedServices[$category]->shuffle()->take(3)->map(function ($service) use ($category, $index) {
            return [
                'id' => $service->id,
                'title' => $service->name,
                'description' => $service->description,
                'price' => 'From ' . $service->formatted_price,
                'image_url' => $service->image_url,
                'category' => ucfirst($category),
                'category_key' => $category,
                'features' => [
                    'Professional Quality',
                    'Long-lasting',
                    'Custom Design',
                    'Expert Application'
                ],
                'color' => $index === 0 ? 'from-rose-500 to-pink-600' : 'from-purple-500 to-rose-600'
            ];
        })->toArray();
        
        // Assign to rows - first category to first row, second category to second row
        if ($index === 0) {
            $featuredServices['firstRow'] = [
                'category' => ucfirst($category),
                'category_key' => $category,
                'services' => $categoryServices,
                'color' => 'from-rose-500 to-pink-600'
            ];
        } else {
            $featuredServices['secondRow'] = [
                'category' => ucfirst($category),
                'category_key' => $category,
                'services' => $categoryServices,
                'color' => 'from-purple-500 to-rose-600'
            ];
        }
    }
    
    // If we don't have enough categories, fill with random services
    if (count($selectedCategories) < 2) {
        $allServices = $services->shuffle()->take(3)->map(function ($service) {
            return [
                'id' => $service->id,
                'title' => $service->name,
                'description' => $service->description,
                'price' => 'From ' . $service->formatted_price,
                'image_url' => $service->image_url,
                'category' => 'Featured',
                'category_key' => 'featured',
                'features' => [
                    'Professional Quality',
                    'Long-lasting',
                    'Custom Design',
                    'Expert Application'
                ],
                'color' => 'from-purple-500 to-rose-600'
            ];
        })->toArray();
        
        if (empty($featuredServices['secondRow'])) {
            $featuredServices['secondRow'] = [
                'category' => 'Featured Services',
                'category_key' => 'featured',
                'services' => $allServices,
                'color' => 'from-purple-500 to-rose-600'
            ];
        }
    }
    
    return Inertia::render('LandingPage', [
        'name' => 'Jacknails Kenya',
        'quote' => [
            'message' => 'Elevate your natural beauty with expert nail artistry.',
            'author' => 'Jacknails Kenya Team'
        ],
        'featuredServices' => $featuredServices
    ]);
})->name('home');

Route::get('dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])->name('dashboard');

// Dashboard management routes
Route::prefix('dashboard')->name('dashboard.')->middleware(['auth', 'verified'])->group(function () {
    // Booking Management
    Route::get('/bookings', [\App\Http\Controllers\Dashboard\BookingManagementController::class, 'index'])->name('bookings');
    Route::get('/bookings/{booking}', [\App\Http\Controllers\Dashboard\BookingManagementController::class, 'show'])->name('bookings.show');
    Route::post('/bookings/{booking}/status', [\App\Http\Controllers\Dashboard\BookingManagementController::class, 'updateStatus'])->name('bookings.status');
    Route::post('/bookings/{booking}/create-payment', [\App\Http\Controllers\Dashboard\BookingManagementController::class, 'createPayment'])->name('bookings.create-payment');
    Route::delete('/bookings/{booking}', [\App\Http\Controllers\Dashboard\BookingManagementController::class, 'destroy'])->name('bookings.destroy');
    
    // Payment Management
    Route::get('/payments', [\App\Http\Controllers\Dashboard\PaymentController::class, 'index'])->name('payments');
    Route::get('/payments/{payment}', [\App\Http\Controllers\Dashboard\PaymentController::class, 'show'])->name('payments.show');
    Route::get('/bookings/{booking}/payments/create', [\App\Http\Controllers\Dashboard\PaymentController::class, 'create'])->name('payments.create');
    Route::post('/bookings/{booking}/payments', [\App\Http\Controllers\Dashboard\PaymentController::class, 'store'])->name('payments.store');
    Route::post('/payments/{payment}/status', [\App\Http\Controllers\Dashboard\PaymentController::class, 'updateStatus'])->name('payments.status');
    
    // Service Management
    Route::get('/services', [\App\Http\Controllers\Dashboard\ServiceManagementController::class, 'index'])->name('services');
    Route::post('/services', [\App\Http\Controllers\Dashboard\ServiceManagementController::class, 'store'])->name('services.store');
    Route::get('/services/{service}', [\App\Http\Controllers\Dashboard\ServiceManagementController::class, 'show'])->name('services.show');
    Route::put('/services/{service}', [\App\Http\Controllers\Dashboard\ServiceManagementController::class, 'update'])->name('services.update');
    Route::delete('/services/{service}', [\App\Http\Controllers\Dashboard\ServiceManagementController::class, 'destroy'])->name('services.destroy');
    Route::post('/services/{service}/toggle-status', [\App\Http\Controllers\Dashboard\ServiceManagementController::class, 'toggleStatus'])->name('services.toggle-status');
    Route::post('/services/bulk-activate', [\App\Http\Controllers\Dashboard\ServiceManagementController::class, 'bulkActivate'])->name('services.bulk-activate');
    Route::post('/services/bulk-deactivate', [\App\Http\Controllers\Dashboard\ServiceManagementController::class, 'bulkDeactivate'])->name('services.bulk-deactivate');
    Route::post('/services/{service}/upload-image', [\App\Http\Controllers\Dashboard\ServiceManagementController::class, 'uploadImage'])->name('services.upload-image');
    
    // Client Management
    Route::get('/clients', [\App\Http\Controllers\Dashboard\ClientManagementController::class, 'index'])->name('clients');
    Route::get('/clients/{email}', [\App\Http\Controllers\Dashboard\ClientManagementController::class, 'show'])->name('clients.show');
    
    // Analytics
    Route::get('/analytics', [\App\Http\Controllers\Dashboard\AnalyticsController::class, 'index'])->name('analytics');
    
    // Time Slots Management
    Route::get('/timeslots', [\App\Http\Controllers\Dashboard\TimeSlotController::class, 'index'])->name('timeslots');
    Route::post('/timeslots', [\App\Http\Controllers\Dashboard\TimeSlotController::class, 'store'])->name('timeslots.store');
    Route::put('/timeslots/{timeSlot}/status', [\App\Http\Controllers\Dashboard\TimeSlotController::class, 'updateStatus'])->name('timeslots.update-status');
    Route::post('/timeslots/bulk-update', [\App\Http\Controllers\Dashboard\TimeSlotController::class, 'bulkUpdate'])->name('timeslots.bulk-update');
    Route::delete('/timeslots/{timeSlot}', [\App\Http\Controllers\Dashboard\TimeSlotController::class, 'destroy'])->name('timeslots.destroy');
    
    // Settings (placeholder - implement as needed)
    Route::get('/settings', function () {
        return Inertia::render('Dashboard/Settings');
    })->name('settings');
});

// Booking routes
Route::prefix('booking')->name('booking.')->group(function () {
    Route::get('/', [\App\Http\Controllers\BookingController::class, 'index'])->name('index');
    Route::get('/create', [\App\Http\Controllers\BookingController::class, 'create'])->name('create');
    Route::post('/store', [\App\Http\Controllers\BookingController::class, 'store'])->name('store');
    Route::get('/time-slots', [\App\Http\Controllers\BookingController::class, 'getAvailableTimeSlots'])->name('time-slots');
    Route::get('/confirmation/{reference}', [\App\Http\Controllers\BookingController::class, 'confirmation'])->name('confirmation');
    Route::get('/{reference}', [\App\Http\Controllers\BookingController::class, 'show'])->name('show');
    Route::post('/{reference}/cancel', [\App\Http\Controllers\BookingController::class, 'cancel'])->name('cancel');
    
    // Stepper booking routes
    Route::prefix('stepper')->name('stepper.')->group(function () {
        Route::get('/start', [\App\Http\Controllers\StepperBookingController::class, 'start'])->name('start');
        Route::get('/step/{step}', [\App\Http\Controllers\StepperBookingController::class, 'showStep'])->name('step');
        Route::post('/step/{step}', [\App\Http\Controllers\StepperBookingController::class, 'updateStep'])->name('update');
        Route::get('/step/{step}/back', [\App\Http\Controllers\StepperBookingController::class, 'previousStep'])->name('back');
        Route::get('/time-slots-stepper', [\App\Http\Controllers\StepperBookingController::class, 'getTimeSlots'])->name('time-slots');
        Route::post('/complete', [\App\Http\Controllers\StepperBookingController::class, 'complete'])->name('complete');
    });
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
