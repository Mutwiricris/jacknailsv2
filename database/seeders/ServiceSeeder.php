<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Service;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        // Clear existing services first
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Service::truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $services = [
            [
                'name' => 'Gel/Stickons Soak Off',
                'description' => 'Professional removal of gel polish or stick-on nails with cuticle care and nail preparation.',
                'price' => 200,
                'duration_minutes' => 30,
                'image_url' => '/images/services/gel-soak-off.jpg',
                'is_popular' => false,
                'status' => 'active'
            ],
            [
                'name' => 'Acrylics, Gumgel Soak Off',
                'description' => 'Safe removal of acrylic nails and gum gel with nail restoration treatment.',
                'price' => 500,
                'duration_minutes' => 45,
                'image_url' => '/images/services/acrylic-soak-off.jpg',
                'is_popular' => false,
                'status' => 'active'
            ],
            [
                'name' => 'Plain Manicure',
                'description' => 'Classic manicure with nail shaping, cuticle care, buffing and plain polish application.',
                'price' => 500,
                'duration_minutes' => 45,
                'image_url' => '/images/services/plain-manicure.jpg',
                'is_popular' => true,
                'status' => 'active'
            ],
            [
                'name' => 'Manicure + Gel',
                'description' => 'Complete manicure service with long-lasting gel polish application for superior durability.',
                'price' => 1500,
                'duration_minutes' => 60,
                'image_url' => '/images/services/manicure-gel.jpg',
                'is_popular' => true,
                'status' => 'active'
            ],
            [
                'name' => 'Plain Pedicure',
                'description' => 'Relaxing foot care with nail trimming, cuticle treatment, callus removal and plain polish.',
                'price' => 1000,
                'duration_minutes' => 60,
                'image_url' => '/images/services/plain-pedicure.jpg',
                'is_popular' => true,
                'status' => 'active'
            ],
            [
                'name' => 'Pedicure + Gel',
                'description' => 'Comprehensive pedicure treatment finished with chip-resistant gel polish.',
                'price' => 2000,
                'duration_minutes' => 75,
                'image_url' => '/images/services/pedicure-gel.jpg',
                'is_popular' => true,
                'status' => 'active'
            ],
            [
                'name' => 'Jelly Pedicure + Scrub + Gel',
                'description' => 'Luxurious jelly pedicure with exfoliating scrub treatment and gel polish finish.',
                'price' => 3000,
                'duration_minutes' => 90,
                'image_url' => '/images/services/jelly-pedicure-gel.jpg',
                'is_popular' => false,
                'status' => 'active'
            ],
            [
                'name' => 'Jelly Pedicure + Scrub + Honey Massage + Gel',
                'description' => 'Ultimate spa pedicure with jelly treatment, scrub, honey massage and gel polish.',
                'price' => 3500,
                'duration_minutes' => 105,
                'image_url' => '/images/services/deluxe-jelly-pedicure.jpg',
                'is_popular' => false,
                'status' => 'active'
            ],
            [
                'name' => 'Tips with Gel',
                'description' => 'Nail tip extensions with gel overlay for natural length and beautiful finish.',
                'price' => 2000,
                'duration_minutes' => 75,
                'image_url' => '/images/services/tips-gel.jpg',
                'is_popular' => false,
                'status' => 'active'
            ],
            [
                'name' => 'Plain Sculpted Acrylics',
                'description' => 'Hand-sculpted acrylic nails with natural finish and perfect shape.',
                'price' => 4000,
                'duration_minutes' => 120,
                'image_url' => '/images/services/plain-sculpted-acrylics.jpg',
                'is_popular' => true,
                'status' => 'active'
            ],
            [
                'name' => 'Sculpted Ombre',
                'description' => 'Beautiful gradient effect on sculpted acrylic nails with seamless color transition.',
                'price' => 4500,
                'duration_minutes' => 135,
                'image_url' => '/images/services/sculpted-ombre.jpg',
                'is_popular' => true,
                'status' => 'active'
            ],
            [
                'name' => 'Encapsulated Nail Art',
                'description' => 'Stunning 3D nail art with embedded designs, glitters, and decorative elements.',
                'price' => 4500,
                'duration_minutes' => 150,
                'image_url' => '/images/services/encapsulated-nail-art.jpg',
                'is_popular' => true,
                'status' => 'active'
            ],
            [
                'name' => 'Gel on Toes',
                'description' => 'Long-lasting gel polish application on natural toenails for perfect pedicure finish.',
                'price' => 1000,
                'duration_minutes' => 45,
                'image_url' => '/images/services/gel-toes.jpg',
                'is_popular' => false,
                'status' => 'active'
            ],
            [
                'name' => 'Tips on Toes + Gel',
                'description' => 'Toenail extensions with tip application and gel polish for enhanced length.',
                'price' => 2000,
                'duration_minutes' => 75,
                'image_url' => '/images/services/tips-toes-gel.jpg',
                'is_popular' => false,
                'status' => 'active'
            ],
            [
                'name' => 'Plain Acrylic Toe Nails',
                'description' => 'Acrylic enhancement for toenails with natural finish and proper shaping.',
                'price' => 2500,
                'duration_minutes' => 90,
                'image_url' => '/images/services/acrylic-toe-nails.jpg',
                'is_popular' => false,
                'status' => 'active'
            ],
            [
                'name' => 'French Toe Nails',
                'description' => 'Classic French manicure style applied to toenails for elegant, timeless look.',
                'price' => 3000,
                'duration_minutes' => 90,
                'image_url' => '/images/services/french-toe-nails.jpg',
                'is_popular' => false,
                'status' => 'active'
            ],
            [
                'name' => 'Overlay with Gel',
                'description' => 'Strengthening gel overlay on natural nails for added durability and shine.',
                'price' => 2000,
                'duration_minutes' => 60,
                'image_url' => '/images/services/overlay-gel.jpg',
                'is_popular' => false,
                'status' => 'active'
            ],
            [
                'name' => 'Powder Overlays',
                'description' => 'Dipping powder system overlay for strong, lightweight and long-lasting nails.',
                'price' => 2500,
                'duration_minutes' => 75,
                'image_url' => '/images/services/powder-overlays.jpg',
                'is_popular' => false,
                'status' => 'active'
            ],
            [
                'name' => 'Plain Colour Acrylics',
                'description' => 'Acrylic nail extensions with solid color polish in your choice of shade.',
                'price' => 3000,
                'duration_minutes' => 105,
                'image_url' => '/images/services/plain-colour-acrylics.jpg',
                'is_popular' => true,
                'status' => 'active'
            ],
            [
                'name' => 'Coloured Acrylics',
                'description' => 'Vibrant colored acrylic nails with premium pigments and perfect finish.',
                'price' => 3500,
                'duration_minutes' => 120,
                'image_url' => '/images/services/coloured-acrylics.jpg',
                'is_popular' => true,
                'status' => 'active'
            ],
            [
                'name' => 'Acrylics Refill',
                'description' => 'Professional refill service for existing acrylic nails with shape correction.',
                'price' => 2500,
                'duration_minutes' => 90,
                'image_url' => '/images/services/acrylics-refill.jpg',
                'is_popular' => true,
                'status' => 'active'
            ],
            [
                'name' => 'Acrylics Long Nails',
                'description' => 'Extra-long acrylic nail extensions for dramatic length and stunning appearance.',
                'price' => 4000,
                'duration_minutes' => 135,
                'image_url' => '/images/services/acrylics-long-nails.jpg',
                'is_popular' => false,
                'status' => 'active'
            ],
            [
                'name' => 'Acrylics (Extra Art, Design, Stones & Glitters)',
                'description' => 'Premium acrylic nails with intricate nail art, rhinestones, glitters and custom designs.',
                'price' => 4500,
                'duration_minutes' => 150,
                'image_url' => '/images/services/acrylics-extra-art.jpg',
                'is_popular' => true,
                'status' => 'active'
            ]
        ];

        foreach ($services as $service) {
            Service::create($service);
        }
    }
}