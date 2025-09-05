<script setup lang="ts">
import AnimatedCounter from '@/components/AnimatedCounter.vue';
import FloatingElements from '@/components/FloatingElements.vue';
import ImageCarousel from '@/components/ImageCarousel.vue';
import ModernBadge from '@/components/ModernBadge.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import LandingLayout from '@/layouts/LandingLayout.vue';
import { Link } from '@inertiajs/vue3';
import {
    ArrowRight,
    Award,
    CheckCircle,
    Clock,
    Crown,
    Gem,
    Heart,
    Instagram,
    Mail,
    MapPin,
    Menu,
    Palette,
    Phone,
    Play,
    Shield,
    Sparkles,
    Star,
    Users,
    X,
    Zap,
} from 'lucide-vue-next';
import { onMounted, onUnmounted, ref, watch } from 'vue';

defineProps<{
    name: string;
    quote?: {
        message: string;
        author: string;
    };
    featuredServices?: {
        firstRow?: {
            category: string;
            category_key: string;
            services: Array<{
                id: number;
                title: string;
                description: string;
                price: string;
                image_url: string;
                category: string;
                category_key: string;
                features: string[];
                color: string;
            }>;
            color: string;
        };
        secondRow?: {
            category: string;
            category_key: string;
            services: Array<{
                id: number;
                title: string;
                description: string;
                price: string;
                image_url: string;
                category: string;
                category_key: string;
                features: string[];
                color: string;
            }>;
            color: string;
        };
    };
}>();

const stats = [
    { number: 500, suffix: '+', label: 'Happy Clients', icon: Heart, color: 'from-rose-500 to-pink-600' },
    { number: 3, suffix: '+', label: 'Years Experience', icon: Award, color: 'from-purple-500 to-rose-600' },
    { number: 5, suffix: '★', label: 'Star Rating', icon: Star, color: 'from-yellow-500 to-orange-500' },
];

const services = [
    {
        icon: Sparkles,
        title: 'Acrylic Nails',
        description: 'Durable, long-lasting acrylic extensions with flawless finish and custom shapes',
        price: 'From KSh 2,500',
        features: ['Custom Length', 'Shape Design', 'Premium Quality', 'Long-lasting'],
        color: 'from-rose-500 to-pink-600',
    },
    {
        icon: Crown,
        title: 'Gel Tips',
        description: 'Natural-looking gel extensions that provide strength while maintaining flexibility',
        price: 'From KSh 2,000',
        features: ['Natural Look', 'Flexible', 'Quick Application', 'Chip Resistant'],
        color: 'from-purple-500 to-rose-600',
    },
    {
        icon: Heart,
        title: 'Stick-On Designs',
        description: 'Quick and stylish nail art solutions with premium adhesive designs',
        price: 'From KSh 1,500',
        features: ['Quick Service', 'Variety Designs', 'Affordable', 'Easy Maintenance'],
        color: 'from-pink-500 to-rose-600',
    },
    {
        icon: Palette,
        title: 'Gum Gel',
        description: 'Advanced gel technology for superior adhesion and long-lasting wear',
        price: 'From KSh 1,800',
        features: ['Superior Adhesion', 'Long-lasting', 'Glossy Finish', 'Professional Grade'],
        color: 'from-rose-500 to-purple-600',
    },
    {
        icon: Star,
        title: 'Artistic Designs',
        description: 'Custom nail art creations tailored to your personal style and preferences',
        price: 'From KSh 3,000',
        features: ['Custom Art', '3D Elements', 'Unique Designs', 'Personal Style'],
        color: 'from-purple-500 to-pink-600',
    },
    {
        icon: Award,
        title: 'Luxury Manicure',
        description: 'Complete nail care experience with cuticle treatment and premium polish',
        price: 'From KSh 1,200',
        features: ['Complete Care', 'Cuticle Treatment', 'Hand Massage', 'Premium Polish'],
        color: 'from-rose-500 to-pink-500',
    },
];

const contactInfo = [
    {
        icon: Phone,
        title: 'Phone',
        content: '+254 714 931250',
        subtitle: 'Call us anytime',
    },
    {
        icon: Mail,
        title: 'Email',
        content: 'info@jacknails.co.ke',
        subtitle: 'We reply within 24hrs',
    },
    {
        icon: MapPin,
        title: 'Location',
        content: 'Kitengela, Kenya',
        subtitle: 'Chuna mall',
    },
    {
        icon: Clock,
        title: 'Hours',
        content: 'Mon-Sat: 9AM-7PM',
        subtitle: 'Sunday: By Appointment',
    },
];

const trustIndicators = [
    { icon: Shield, label: 'Certified Professional', color: 'bg-green-100 text-green-600' },
    { icon: Zap, label: 'Same Day Booking', color: 'bg-blue-100 text-blue-600' },
    { icon: Users, label: '500+ Satisfied Clients', color: 'bg-purple-100 text-purple-600' },
];

const galleryImages = [
    { src: '/images/image1.jpeg', alt: 'elegant french manicure with gold accents and crystals' },
    { src: '/images/image2.jpeg', alt: 'colorful ombre nail design with glitter gradient' },
    { src: '/images/image3.jpeg', alt: 'intricate floral nail art patterns with 3D elements' },
    { src: '/images/image4.jpeg', alt: 'geometric nail designs in pastel colors with metallic lines' },
    { src: '/images/image5.jpeg', alt: 'luxury chrome finish acrylic nails with mirror effect' },
    { src: '/images/image6.jpeg', alt: 'artistic marble effect nail design with gold veins' },
    { src: '/images/image7.jpeg', alt: 'delicate lace pattern nail art with pearl details' },
    { src: '/images/image8.jpeg', alt: 'bold abstract nail art with crystals and gems' },
    { src: '/images/image9.jpeg', alt: 'vintage inspired nail design with ornate patterns' },
    { src: '/images/image10.jpeg', alt: 'modern minimalist nail art with negative space' },
    { src: '/images/image11.jpeg', alt: 'tropical themed nail design with palm leaves' },
    { src: '/images/image12.jpeg', alt: 'galaxy inspired nail art with holographic effects' },
];

const whyChooseUs = [
    'Premium quality products and materials',
    'Hygienic and sanitized environment',
    'Personalized service and consultation',
    'Competitive pricing with exceptional value',
    'Flexible scheduling and convenient location',
];

const navItems = ['Home', 'Services', 'Gallery', 'About', 'Contact'];

// Mobile menu state
const isMobileMenuOpen = ref(false);
const isScrolled = ref(false);

const toggleMobileMenu = () => {
    isMobileMenuOpen.value = !isMobileMenuOpen.value;
};

const closeMobileMenu = () => {
    isMobileMenuOpen.value = false;
};

const scrollToSection = (sectionId: string) => {
    const element = document.getElementById(sectionId.toLowerCase());
    if (element) {
        element.scrollIntoView({ behavior: 'smooth' });
        closeMobileMenu();
    }
};

// Body scroll lock when mobile menu is open
watch(isMobileMenuOpen, (isOpen) => {
    if (typeof document !== 'undefined') {
        if (isOpen) {
            document.body.style.overflow = 'hidden';
        } else {
            document.body.style.overflow = '';
        }
    }
});

// Scroll event handler
const handleScroll = () => {
    if (typeof window !== 'undefined') {
        isScrolled.value = window.scrollY > 50;
    }
};

// Keyboard event handler
const handleKeydown = (event: KeyboardEvent) => {
    if (event.key === 'Escape' && isMobileMenuOpen.value) {
        closeMobileMenu();
    }
};

// Setup keyboard and scroll listeners
onMounted(() => {
    if (typeof document !== 'undefined') {
        document.addEventListener('keydown', handleKeydown);
        window.addEventListener('scroll', handleScroll);
    }
});

// Cleanup on unmount
onUnmounted(() => {
    if (typeof document !== 'undefined') {
        document.body.style.overflow = '';
        document.removeEventListener('keydown', handleKeydown);
        window.removeEventListener('scroll', handleScroll);
    }
});
</script>

<template>
    <LandingLayout
        title="Luxury Nail Artistry"
        description="Experience premium nail services in Kitengela. From stunning acrylics to intricate designs, we craft perfection at your fingertips."
    >
        <div class="relative min-h-screen overflow-hidden bg-gradient-to-br from-slate-50 via-white to-rose-50/30">
            <FloatingElements />

            <!-- Header -->
            <header
                :class="[
                    'sticky top-0 z-50 transition-all duration-300',
                    isScrolled
                        ? 'border-b border-gray-200/50 bg-white/95 shadow-lg backdrop-blur-xl'
                        : 'border-b border-white/20 bg-white/80 shadow-sm backdrop-blur-md',
                ]"
            >
                <div :class="['container mx-auto px-4 transition-all duration-300 lg:px-8', isScrolled ? 'py-3' : 'py-4']">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <div class="relative">
                                <div
                                    :class="[
                                        'flex items-center justify-center rounded-3xl bg-gradient-to-br from-rose-500 via-pink-500 to-purple-600 shadow-xl transition-all duration-300',
                                        isScrolled ? 'h-12 w-12' : 'h-14 w-14',
                                    ]"
                                >
                                    <Crown :class="isScrolled ? 'h-6 w-6' : 'h-8 w-8'" class="text-white transition-all duration-300" />
                                </div>
                                <div
                                    :class="[
                                        'absolute -top-1 -right-1 animate-pulse rounded-full bg-gradient-to-r from-yellow-400 to-yellow-500 shadow-lg transition-all duration-300',
                                        isScrolled ? 'h-4 w-4' : 'h-5 w-5',
                                    ]"
                                />
                            </div>
                            <div>
                                <h1
                                    :class="[
                                        'bg-gradient-to-r from-rose-600 to-pink-600 bg-clip-text font-bold text-transparent transition-all duration-300',
                                        isScrolled ? 'text-xl lg:text-2xl' : 'text-2xl lg:text-3xl',
                                    ]"
                                >
                                    {{ name }}
                                </h1>
                                <p :class="['font-medium text-gray-600 transition-all duration-300', isScrolled ? 'text-xs' : 'text-xs lg:text-sm']">
                                    Kitengela's Premier Nail Studio
                                </p>
                            </div>
                        </div>

                        <nav class="hidden items-center space-x-8 lg:flex">
                            <a
                                v-for="(item, index) in navItems"
                                :key="item"
                                :href="`#${item.toLowerCase()}`"
                                class="group relative font-medium text-gray-700 transition-all duration-300 hover:text-rose-600"
                            >
                                {{ item }}
                                <span
                                    class="absolute -bottom-1 left-0 h-0.5 w-0 bg-gradient-to-r from-rose-600 to-pink-600 transition-all duration-300 group-hover:w-full"
                                />
                            </a>
                            <div class="flex items-center space-x-4">
                                <Link :href="route('booking.stepper.start')">
                                    <Button
                                        class="rounded-full bg-gradient-to-r from-rose-500 to-pink-600 px-6 py-3 font-medium text-white shadow-lg transition-all duration-300 hover:from-rose-600 hover:to-pink-700 hover:shadow-xl"
                                    >
                                        <Phone class="mr-2 h-4 w-4" />
                                        Book Now
                                    </Button>
                                </Link>
                                <Link :href="route('login')" class="font-medium text-gray-600 transition-colors duration-300 hover:text-rose-600">
                                    Login
                                </Link>
                            </div>
                        </nav>

                        <Button variant="ghost" size="icon" class="glass-morphism lg:hidden" @click="toggleMobileMenu">
                            <Menu v-if="!isMobileMenuOpen" class="h-6 w-6" />
                            <X v-else class="h-6 w-6" />
                        </Button>
                    </div>
                </div>
            </header>

            <!-- Mobile Menu Overlay -->
            <Transition
                enter-active-class="transition-all duration-500 ease-out"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition-all duration-300 ease-in"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div v-if="isMobileMenuOpen" class="fixed inset-0 z-40 lg:hidden" @click="closeMobileMenu">
                    <!-- Background overlay -->
                    <div class="absolute inset-0 bg-black/60 backdrop-blur-md" />

                    <!-- Menu content -->
                    <Transition
                        enter-active-class="transition-all duration-500 ease-out"
                        enter-from-class="translate-y-[-100%]"
                        enter-to-class="translate-y-0"
                        leave-active-class="transition-all duration-300 ease-in"
                        leave-from-class="translate-y-0"
                        leave-to-class="translate-y-[-100%]"
                    >
                        <div
                            v-if="isMobileMenuOpen"
                            class="relative min-h-screen border-b border-white/20 bg-white/95 shadow-2xl backdrop-blur-xl"
                            @click.stop
                        >
                            <!-- Header with logo -->
                            <div class="flex items-center justify-between border-b border-gray-200/50 p-6">
                                <div class="flex items-center space-x-3">
                                    <div
                                        class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-rose-500 via-pink-500 to-purple-600 shadow-lg"
                                    >
                                        <Crown class="h-6 w-6 text-white" />
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-gray-900">{{ name }}</h3>
                                        <p class="text-xs text-gray-600">Luxury Nail Artistry</p>
                                    </div>
                                </div>
                                <Button variant="ghost" size="icon" class="glass-morphism" @click="closeMobileMenu">
                                    <X class="h-6 w-6" />
                                </Button>
                            </div>

                            <!-- Navigation Links -->
                            <nav class="space-y-2 p-6">
                                <button
                                    v-for="(item, index) in navItems"
                                    :key="item"
                                    @click="scrollToSection(item)"
                                    class="group neumorphism hover-lift relative block w-full rounded-2xl px-6 py-4 text-left text-xl font-medium text-gray-700 transition-all duration-300 hover:bg-gradient-to-r hover:from-rose-50 hover:to-pink-50 hover:text-rose-600"
                                    :class="`slide-up stagger-${index + 1}`"
                                >
                                    <span class="relative z-10">{{ item }}</span>
                                    <div
                                        class="absolute inset-0 rounded-2xl bg-gradient-to-r from-rose-500/10 to-pink-500/10 opacity-0 transition-opacity duration-300 group-hover:opacity-100"
                                    />
                                </button>
                            </nav>

                            <!-- Action Buttons -->
                            <div class="mt-auto space-y-4 border-t border-gray-200/50 p-6">
                                <Link :href="route('booking.stepper.start')" @click="closeMobileMenu">
                                    <Button class="btn-modern group relative w-full overflow-hidden py-4 text-lg font-medium">
                                        <Phone class="relative z-10 mr-2 h-5 w-5" />
                                        <span class="relative z-10">Book Appointment</span>
                                        <ArrowRight class="relative z-10 ml-2 h-5 w-5 transition-transform group-hover:translate-x-1" />
                                    </Button>
                                </Link>
                                <Link
                                    :href="route('login')"
                                    class="glass-morphism block w-full rounded-2xl border-2 border-gray-200 px-6 py-4 text-center font-medium text-gray-600 transition-all duration-300 hover:border-rose-200 hover:text-rose-600 hover:shadow-lg"
                                    @click="closeMobileMenu"
                                >
                                    Login to Dashboard
                                </Link>
                            </div>

                            <!-- Decorative Elements -->
                            <div class="pointer-events-none absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 transform opacity-5">
                                <Sparkles class="h-32 w-32 text-rose-500" />
                            </div>
                        </div>
                    </Transition>
                </div>
            </Transition>

            <!-- Hero Section -->
            <section id="home" class="relative overflow-hidden py-20 lg:py-32">
                <div class="relative container mx-auto px-4 lg:px-8">
                    <div class="grid items-center gap-12 lg:grid-cols-2 lg:gap-20">
                        <!-- Left Content -->
                        <div class="order-2 space-y-8 lg:order-1 lg:space-y-12">
                            <div class="space-y-6 lg:space-y-8">
                                <ModernBadge variant="soft" class="slide-up stagger-1">
                                    <Sparkles class="mr-2 h-5 w-5" />
                                    Premium Nail Artistry Since 2021
                                </ModernBadge>

                                <div class="space-y-6">
                                    <h2
                                        class="slide-up stagger-2 text-4xl leading-[0.85] font-bold text-gray-900 sm:text-5xl lg:text-7xl xl:text-8xl"
                                    >
                                        Elevate Your
                                        <span class="text-gradient relative mt-4 block">
                                            Natural Beauty
                                            <div
                                                class="absolute -bottom-4 left-0 h-2 w-full rounded-full bg-gradient-to-r from-rose-500/20 to-pink-500/20 blur-sm"
                                            />
                                        </span>
                                    </h2>

                                    <p class="slide-up stagger-3 max-w-xl text-lg leading-relaxed text-gray-600 lg:text-xl">
                                        Experience luxury nail artistry in Kitengela. From stunning acrylics to intricate designs, we craft perfection
                                        at your fingertips with unmatched expertise and elegance.
                                    </p>
                                </div>
                            </div>

                            <div class="slide-up stagger-4 flex flex-col gap-6 sm:flex-row">
                                <Link :href="route('booking.stepper.start')">
                                    <Button size="lg" class="btn-modern group relative overflow-hidden">
                                        <Phone class="relative z-10 mr-2 h-5 w-5 group-hover:animate-pulse" />
                                        <span class="relative z-10">Book Appointment</span>
                                        <ArrowRight class="relative z-10 ml-2 h-5 w-5 transition-transform group-hover:translate-x-1" />
                                    </Button>
                                </Link>
                                <Button
                                    size="lg"
                                    variant="outline"
                                    class="rounded-full border-2 border-rose-300/50 bg-transparent px-8 py-4 text-lg text-rose-700 shadow-lg backdrop-blur-sm transition-all duration-300 hover:scale-105 hover:border-rose-400 hover:bg-rose-50 hover:shadow-xl"
                                >
                                    <Play class="mr-2 h-5 w-5" />
                                    Watch Process
                                </Button>
                            </div>

                            <!-- Enhanced Stats -->
                            <div class="slide-up stagger-5 grid grid-cols-3 gap-6 pt-8 lg:gap-8">
                                <div v-for="(stat, index) in stats" :key="index" class="neumorphism hover-lift group rounded-3xl p-6 text-center">
                                    <div class="mb-4 flex justify-center">
                                        <div
                                            :class="`h-14 w-14 bg-gradient-to-br ${stat.color} flex items-center justify-center rounded-2xl shadow-lg transition-transform duration-300 group-hover:scale-110`"
                                        >
                                            <component :is="stat.icon" class="h-7 w-7 text-white" />
                                        </div>
                                    </div>
                                    <div class="mb-2 text-3xl font-bold text-gray-900 lg:text-4xl">
                                        <AnimatedCounter :end="stat.number" :suffix="stat.suffix" />
                                    </div>
                                    <div class="text-sm font-medium text-gray-600 lg:text-base">{{ stat.label }}</div>
                                </div>
                            </div>

                            <!-- Trust Indicators -->
                            <div class="slide-up stagger-6 flex flex-wrap items-center gap-6 pt-6">
                                <div
                                    v-for="(item, index) in trustIndicators"
                                    :key="index"
                                    class="glass-morphism flex items-center space-x-3 rounded-full px-4 py-2"
                                >
                                    <div :class="`h-8 w-8 ${item.color} flex items-center justify-center rounded-full`">
                                        <component :is="item.icon" class="h-4 w-4" />
                                    </div>
                                    <span class="text-sm font-medium text-gray-700">{{ item.label }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Right Image/Carousel -->
                        <div class="scale-in relative order-1 lg:order-2">
                            <div class="relative">
                                <ImageCarousel />

                                <!-- Enhanced Floating Badge -->
                                <div
                                    class="glass-morphism float-animation absolute -bottom-8 -left-8 hidden rounded-3xl border border-white/20 p-6 shadow-2xl sm:block lg:-bottom-10 lg:-left-10"
                                >
                                    <div class="flex items-center space-x-4">
                                        <div
                                            class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-rose-500 to-pink-600 shadow-lg"
                                        >
                                            <Award class="h-7 w-7 text-white" />
                                        </div>
                                        <div>
                                            <div class="text-lg font-bold text-gray-900">Certified Professional</div>
                                            <div class="text-sm text-gray-600">Expert Nail Technician</div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Service Quick Access -->
                                <div
                                    class="glass-morphism float-animation absolute top-8 -right-8 rounded-2xl border border-white/20 p-5 shadow-xl lg:top-10 lg:-right-10"
                                    style="animation-delay: 0.5s"
                                >
                                    <div class="flex flex-col items-center space-y-3">
                                        <div
                                            class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-purple-500 to-rose-600 shadow-lg"
                                        >
                                            <Gem class="h-6 w-6 text-white" />
                                        </div>
                                        <div class="text-xs font-bold text-gray-700">Premium</div>
                                        <div class="text-xs text-gray-600">Quality</div>
                                    </div>
                                </div>

                                <!-- Additional floating elements -->
                                <div
                                    class="bounce-gentle absolute top-1/2 -left-6 h-4 w-4 rounded-full bg-gradient-to-br from-rose-400 to-pink-500 shadow-lg"
                                    style="animation-delay: 1s"
                                />
                                <div
                                    class="bounce-gentle absolute -right-4 bottom-1/4 h-3 w-3 rounded-full bg-gradient-to-br from-purple-400 to-rose-500 shadow-lg"
                                    style="animation-delay: 2s"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Scroll Indicator -->
                    <div class="slide-up stagger-6 absolute bottom-8 left-1/2 -translate-x-1/2 transform">
                        <div class="bounce-gentle flex flex-col items-center space-y-3">
                            <div class="glass-morphism flex h-12 w-8 justify-center rounded-full border-2 border-rose-300">
                                <div class="mt-2 h-4 w-2 animate-pulse rounded-full bg-gradient-to-b from-rose-500 to-pink-500" />
                            </div>
                            <span class="text-xs font-medium text-gray-500">Scroll to explore</span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Services Section -->
            <section id="services" class="relative bg-gradient-to-br from-white to-rose-50/30 py-20 lg:py-32">
                <div class="container mx-auto px-4 lg:px-8">
                    <div class="mb-16 text-center lg:mb-24">
                        <ModernBadge variant="premium" class="mb-8">
                            <Palette class="mr-2 h-4 w-4" />
                            Our Expertise
                        </ModernBadge>
                        <h2 class="mb-8 text-4xl font-bold text-gray-900 lg:text-6xl">
                            Premium Nail
                            <span class="text-gradient mt-2 block">Services</span>
                        </h2>
                        <p class="mx-auto max-w-3xl text-lg leading-relaxed text-gray-600 lg:text-xl">
                            Discover our comprehensive range of luxury nail services, each crafted with precision and artistry
                        </p>
                    </div>

                    <!-- Dynamic Services from Database -->
                    <div v-if="featuredServices" class="space-y-16">
                        <!-- First Row -->
                        <div v-if="featuredServices.firstRow" class="space-y-8">
                            <div class="text-center">
                                <ModernBadge variant="soft" class="mb-4">
                                    <Sparkles class="mr-2 h-4 w-4" />
                                    {{ featuredServices.firstRow.category }}
                                </ModernBadge>
                                <h3 class="text-2xl font-bold text-gray-900 lg:text-3xl">{{ featuredServices.firstRow.category }} Collection</h3>
                            </div>
                            <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3 lg:gap-10">
                                <Card
                                    v-for="(service, index) in featuredServices.firstRow.services"
                                    :key="`first-${service.id}`"
                                    class="group neumorphism hover-lift relative overflow-hidden border-0 shadow-lg transition-all duration-700 hover:shadow-2xl"
                                >
                                    <div class="relative h-56 overflow-hidden lg:h-64">
                                        <img
                                            :src="service.image_url"
                                            :alt="service.title"
                                            class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110"
                                            loading="lazy"
                                        />
                                        <div class="absolute inset-0 bg-gradient-to-t from-black/40 via-transparent to-transparent" />
                                        <div
                                            :class="`absolute top-4 left-4 bg-gradient-to-br ${service.color} rounded-2xl p-3 text-white shadow-lg transition-transform duration-300 group-hover:scale-110`"
                                        >
                                            <Sparkles class="h-6 w-6" />
                                        </div>
                                        <ModernBadge variant="glow" class="absolute top-4 right-4">
                                            {{ service.price }}
                                        </ModernBadge>
                                    </div>
                                    <CardContent class="p-6 lg:p-8">
                                        <h3 class="mb-4 text-xl font-bold text-gray-900 lg:text-2xl">
                                            {{ service.title }}
                                        </h3>
                                        <p class="mb-6 leading-relaxed text-gray-600">{{ service.description }}</p>

                                        <div class="mb-6 grid grid-cols-2 gap-3">
                                            <div
                                                v-for="(feature, idx) in service.features"
                                                :key="idx"
                                                class="flex items-center space-x-2 text-sm text-gray-600"
                                            >
                                                <CheckCircle class="h-4 w-4 text-rose-500" />
                                                <span>{{ feature }}</span>
                                            </div>
                                        </div>

                                        <Link :href="route('booking.stepper.start')">
                                            <Button
                                                :class="`w-full bg-gradient-to-r ${service.color} group rounded-2xl py-3 text-white transition-all duration-300 hover:shadow-xl`"
                                            >
                                                Book Service
                                                <ArrowRight class="ml-2 h-4 w-4 transition-transform group-hover:translate-x-1" />
                                            </Button>
                                        </Link>
                                    </CardContent>
                                </Card>
                            </div>
                        </div>

                        <!-- Second Row -->
                        <div v-if="featuredServices.secondRow" class="space-y-8">
                            <div class="text-center">
                                <ModernBadge variant="premium" class="mb-4">
                                    <Crown class="mr-2 h-4 w-4" />
                                    {{ featuredServices.secondRow.category }}
                                </ModernBadge>
                                <h3 class="text-2xl font-bold text-gray-900 lg:text-3xl">{{ featuredServices.secondRow.category }} Collection</h3>
                            </div>
                            <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3 lg:gap-10">
                                <Card
                                    v-for="service in featuredServices.secondRow.services"
                                    :key="`second-${service.id}`"
                                    class="group neumorphism hover-lift relative overflow-hidden border-0 shadow-lg transition-all duration-700 hover:shadow-2xl"
                                >
                                    <div class="relative h-56 overflow-hidden lg:h-64">
                                        <img
                                            :src="service.image_url"
                                            :alt="service.title"
                                            class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110"
                                            loading="lazy"
                                        />
                                        <div class="absolute inset-0 bg-gradient-to-t from-black/40 via-transparent to-transparent" />
                                        <div
                                            :class="`absolute top-4 left-4 bg-gradient-to-br ${service.color} rounded-2xl p-3 text-white shadow-lg transition-transform duration-300 group-hover:scale-110`"
                                        >
                                            <Crown class="h-6 w-6" />
                                        </div>
                                        <ModernBadge variant="glow" class="absolute top-4 right-4">
                                            {{ service.price }}
                                        </ModernBadge>
                                    </div>
                                    <CardContent class="p-6 lg:p-8">
                                        <h3 class="mb-4 text-xl font-bold text-gray-900 lg:text-2xl">
                                            {{ service.title }}
                                        </h3>
                                        <p class="mb-6 leading-relaxed text-gray-600">{{ service.description }}</p>

                                        <div class="mb-6 grid grid-cols-2 gap-3">
                                            <div
                                                v-for="(feature, idx) in service.features"
                                                :key="idx"
                                                class="flex items-center space-x-2 text-sm text-gray-600"
                                            >
                                                <CheckCircle class="h-4 w-4 text-rose-500" />
                                                <span>{{ feature }}</span>
                                            </div>
                                        </div>

                                        <Link :href="route('booking.stepper.start')">
                                            <Button
                                                :class="`w-full bg-gradient-to-r ${service.color} group rounded-2xl py-3 text-white transition-all duration-300 hover:shadow-xl`"
                                            >
                                                Book Service
                                                <ArrowRight class="ml-2 h-4 w-4 transition-transform group-hover:translate-x-1" />
                                            </Button>
                                        </Link>
                                    </CardContent>
                                </Card>
                            </div>
                        </div>
                    </div>

                    <!-- Fallback to static services if no dynamic data -->
                    <div v-else class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3 lg:gap-10">
                        <Card
                            v-for="(service, index) in services"
                            :key="index"
                            class="group neumorphism hover-lift relative overflow-hidden border-0 shadow-lg transition-all duration-700 hover:shadow-2xl"
                        >
                            <div class="relative h-56 overflow-hidden bg-gradient-to-br from-rose-100 to-pink-100 lg:h-64">
                                <div class="flex h-full w-full items-center justify-center text-gray-400">
                                    <component :is="service.icon" class="h-16 w-16" />
                                </div>
                                <div class="absolute inset-0 bg-gradient-to-t from-black/30 via-transparent to-transparent" />
                                <div
                                    :class="`absolute top-4 left-4 bg-gradient-to-br ${service.color} rounded-2xl p-3 text-white shadow-lg transition-transform duration-300 group-hover:scale-110`"
                                >
                                    <component :is="service.icon" class="h-8 w-8" />
                                </div>
                                <ModernBadge variant="glow" class="absolute top-4 right-4">
                                    {{ service.price }}
                                </ModernBadge>
                            </div>
                            <CardContent class="p-8">
                                <h3 class="mb-4 text-xl font-bold text-gray-900 lg:text-2xl">
                                    {{ service.title }}
                                </h3>
                                <p class="mb-6 leading-relaxed text-gray-600">{{ service.description }}</p>

                                <div class="mb-8 grid grid-cols-2 gap-3">
                                    <div
                                        v-for="(feature, idx) in service.features"
                                        :key="idx"
                                        class="flex items-center space-x-2 text-sm text-gray-600"
                                    >
                                        <CheckCircle class="h-4 w-4 text-rose-500" />
                                        <span>{{ feature }}</span>
                                    </div>
                                </div>

                                <Link :href="route('booking.stepper.start')">
                                    <Button
                                        :class="`w-full bg-gradient-to-r ${service.color} group rounded-2xl py-3 text-white transition-all duration-300 hover:shadow-xl`"
                                    >
                                        Book Service
                                        <ArrowRight class="ml-2 h-4 w-4 transition-transform group-hover:translate-x-1" />
                                    </Button>
                                </Link>
                            </CardContent>
                        </Card>
                    </div>

                    <!-- Call to Action -->
                    <div class="mt-16 text-center">
                        <Link :href="route('booking.stepper.start')">
                            <Button size="lg" class="btn-modern group px-8 py-4">
                                <Phone class="mr-2 h-5 w-5" />
                                View All Services & Book Now
                                <ArrowRight class="ml-2 h-5 w-5 transition-transform group-hover:translate-x-1" />
                            </Button>
                        </Link>
                    </div>
                </div>
            </section>

            <!-- Gallery Section -->
            <section id="gallery" class="bg-gradient-to-br from-rose-50 to-pink-50 py-16 lg:py-24">
                <div class="container mx-auto px-4 lg:px-8">
                    <div class="mb-12 text-center lg:mb-20">
                        <ModernBadge variant="premium" class="mb-6">
                            <Star class="mr-2 h-4 w-4" />
                            Portfolio
                        </ModernBadge>
                        <h2 class="mb-6 text-3xl font-bold text-gray-900 lg:text-5xl">Our Masterpieces</h2>
                        <p class="mx-auto max-w-3xl text-lg leading-relaxed text-gray-600 lg:text-xl">
                            Explore our stunning collection of nail artistry that showcases our commitment to excellence
                        </p>
                    </div>

                    <div class="grid grid-cols-2 gap-3 md:grid-cols-3 lg:grid-cols-4 lg:gap-6">
                        <div
                            v-for="(image, index) in galleryImages"
                            :key="index"
                            class="group neumorphism hover-lift relative aspect-square overflow-hidden rounded-2xl shadow-lg transition-all duration-500 hover:shadow-2xl"
                        >
                            <img :src="image.src" :alt="image.alt" class="h-full w-full object-cover" />
                            <div
                                class="absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-transparent opacity-0 transition-all duration-500 group-hover:opacity-100"
                            />
                            <div
                                class="absolute right-4 bottom-4 left-4 translate-y-4 transform text-white opacity-0 transition-all duration-500 group-hover:translate-y-0 group-hover:opacity-100"
                            >
                                <p class="text-sm font-semibold">Premium Design #{{ index + 1 }}</p>
                                <p class="text-xs opacity-90">{{ image.alt }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-12 text-center lg:mt-16">
                        <Button
                            size="lg"
                            variant="outline"
                            class="border-2 border-rose-300 bg-white/80 px-8 py-4 text-lg text-rose-700 backdrop-blur-sm transition-all duration-300 hover:border-rose-400 hover:bg-rose-50"
                        >
                            <Instagram class="mr-2 h-5 w-5" />
                            View More on Instagram
                        </Button>
                    </div>
                </div>
            </section>

            <!-- About Section -->
            <section id="about" class="bg-white py-16 lg:py-24">
                <div class="container mx-auto px-4 lg:px-8">
                    <div class="grid items-center gap-12 lg:grid-cols-2 lg:gap-20">
                        <div class="relative order-2 lg:order-1">
                            <div class="relative aspect-[4/5] overflow-hidden rounded-3xl bg-gradient-to-br from-rose-100 to-pink-100 shadow-2xl">
                                <div class="flex h-full w-full items-center justify-center text-gray-400">
                                    <div class="text-center">
                                        <Crown class="mx-auto mb-4 h-16 w-16" />
                                        <p class="text-lg font-medium">Professional Service</p>
                                    </div>
                                </div>
                                <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent" />
                            </div>

                            <div class="absolute -top-6 -right-6 rounded-2xl bg-gradient-to-r from-rose-600 to-pink-600 p-6 text-white shadow-xl">
                                <Clock class="mb-2 h-8 w-8" />
                                <div class="text-lg font-bold">3+ Years</div>
                                <div class="text-sm opacity-90">Experience</div>
                            </div>
                        </div>

                        <div class="order-1 space-y-6 lg:order-2 lg:space-y-8">
                            <div>
                                <ModernBadge variant="premium" class="mb-6">
                                    <Crown class="mr-2 h-4 w-4" />
                                    About Jacknails Kenya
                                </ModernBadge>
                                <h2 class="mb-6 text-3xl leading-tight font-bold text-gray-900 lg:text-5xl">
                                    Crafting Beauty with
                                    <span class="text-gradient mt-2 block">Passion & Precision</span>
                                </h2>
                                <p class="text-lg leading-relaxed text-gray-600 lg:text-xl">
                                    Located in the heart of Kitengela, Jacknails Kenya has been the premier destination for luxury nail services. Our
                                    commitment to excellence and attention to detail has made us the trusted choice for clients who demand nothing but
                                    the best.
                                </p>
                            </div>

                            <div class="grid grid-cols-2 gap-4 lg:gap-6">
                                <div
                                    class="rounded-2xl bg-white/80 p-6 text-center shadow-sm backdrop-blur-sm transition-all duration-300 hover:-translate-y-2 hover:shadow-lg"
                                >
                                    <Award class="mx-auto mb-3 h-10 w-10 text-rose-600" />
                                    <div class="text-lg font-bold text-gray-900">Certified</div>
                                    <div class="text-sm text-gray-600">Professional Training</div>
                                </div>
                                <div
                                    class="rounded-2xl bg-white/80 p-6 text-center shadow-sm backdrop-blur-sm transition-all duration-300 hover:-translate-y-2 hover:shadow-lg"
                                >
                                    <Heart class="mx-auto mb-3 h-10 w-10 text-rose-600" />
                                    <div class="text-lg font-bold text-gray-900">500+</div>
                                    <div class="text-sm text-gray-600">Satisfied Clients</div>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <h3 class="text-xl font-bold text-gray-900 lg:text-2xl">Why Choose Us?</h3>
                                <div class="grid gap-3">
                                    <div
                                        v-for="(item, index) in whyChooseUs"
                                        :key="index"
                                        class="flex items-center space-x-3 rounded-lg bg-white/50 p-4 backdrop-blur-sm transition-all duration-300 hover:bg-white/80 hover:shadow-md"
                                    >
                                        <CheckCircle class="h-5 w-5 flex-shrink-0 text-rose-600" />
                                        <span class="font-medium text-gray-700">{{ item }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Contact Section -->
            <section id="contact" class="bg-gradient-to-br from-rose-50 to-pink-50 py-16 lg:py-24">
                <div class="container mx-auto px-4 lg:px-8">
                    <div class="mb-12 text-center lg:mb-20">
                        <div
                            class="mb-6 inline-flex items-center rounded-full border border-blue-200 bg-gradient-to-r from-blue-100 to-purple-100 px-4 py-2 text-sm font-medium text-blue-700"
                        >
                            <Phone class="mr-2 h-4 w-4" />
                            Get In Touch
                        </div>
                        <h2 class="mb-6 text-3xl font-bold text-gray-900 lg:text-5xl">Book Your Appointment</h2>
                        <p class="mx-auto max-w-3xl text-lg leading-relaxed text-gray-600 lg:text-xl">
                            Ready to transform your nails? Contact us today to schedule your luxury nail experience
                        </p>
                    </div>

                    <div class="grid gap-8 lg:grid-cols-2 lg:gap-16">
                        <div class="space-y-6 lg:space-y-8">
                            <div class="grid gap-6">
                                <Card
                                    v-for="(item, index) in contactInfo"
                                    :key="index"
                                    class="border-0 bg-white/80 p-6 shadow-lg backdrop-blur-sm transition-all duration-300 hover:-translate-y-2 hover:shadow-xl"
                                >
                                    <div class="flex items-center space-x-4">
                                        <div class="rounded-2xl bg-gradient-to-r from-rose-100 to-pink-100 p-4">
                                            <component :is="item.icon" class="h-6 w-6 text-rose-600" />
                                        </div>
                                        <div>
                                            <h3 class="text-lg font-bold text-gray-900">{{ item.title }}</h3>
                                            <p class="font-medium text-gray-900">{{ item.content }}</p>
                                            <p class="text-sm text-gray-600">{{ item.subtitle }}</p>
                                        </div>
                                    </div>
                                </Card>
                            </div>
                        </div>

                        <Card class="border-0 bg-white/90 p-6 shadow-2xl backdrop-blur-sm lg:p-8">
                            <h3 class="mb-6 text-2xl font-bold text-gray-900 lg:text-3xl">Send us a Message</h3>
                            <form class="space-y-6">
                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                    <div>
                                        <label class="mb-2 block text-sm font-semibold text-gray-700">First Name</label>
                                        <Input
                                            placeholder="Your first name"
                                            class="h-12 border-gray-200 bg-white/80 backdrop-blur-sm focus:border-rose-500 focus:ring-rose-500"
                                        />
                                    </div>
                                    <div>
                                        <label class="mb-2 block text-sm font-semibold text-gray-700">Last Name</label>
                                        <Input
                                            placeholder="Your last name"
                                            class="h-12 border-gray-200 bg-white/80 backdrop-blur-sm focus:border-rose-500 focus:ring-rose-500"
                                        />
                                    </div>
                                </div>
                                <div>
                                    <label class="mb-2 block text-sm font-semibold text-gray-700">Email</label>
                                    <Input
                                        type="email"
                                        placeholder="your.email@example.com"
                                        class="h-12 border-gray-200 bg-white/80 backdrop-blur-sm focus:border-rose-500 focus:ring-rose-500"
                                    />
                                </div>
                                <div>
                                    <label class="mb-2 block text-sm font-semibold text-gray-700">Phone</label>
                                    <Input
                                        placeholder="+254 714 931250"
                                        class="h-12 border-gray-200 bg-white/80 backdrop-blur-sm focus:border-rose-500 focus:ring-rose-500"
                                    />
                                </div>
                                <div>
                                    <label class="mb-2 block text-sm font-semibold text-gray-700"> Service Interested In </label>
                                    <Input
                                        placeholder="e.g., Acrylic Nails, Gel Tips"
                                        class="h-12 border-gray-200 bg-white/80 backdrop-blur-sm focus:border-rose-500 focus:ring-rose-500"
                                    />
                                </div>
                                <div>
                                    <label class="mb-2 block text-sm font-semibold text-gray-700">Message</label>
                                    <Textarea
                                        placeholder="Tell us about your nail goals and preferred appointment time..."
                                        class="min-h-[120px] resize-none border-gray-200 bg-white/80 backdrop-blur-sm focus:border-rose-500 focus:ring-rose-500"
                                    />
                                </div>
                                <Button
                                    class="group w-full rounded-2xl bg-gradient-to-r from-rose-500 to-pink-600 py-4 text-lg text-white shadow-lg transition-all duration-300 hover:from-rose-600 hover:to-pink-700 hover:shadow-xl"
                                >
                                    <Mail class="mr-2 h-5 w-5 group-hover:animate-pulse" />
                                    Send Message
                                    <ArrowRight class="ml-2 h-5 w-5 transition-transform group-hover:translate-x-1" />
                                </Button>
                            </form>
                        </Card>
                    </div>
                </div>
            </section>

            <!-- Footer -->
            <footer class="bg-gray-900 py-12 text-white lg:py-16">
                <div class="container mx-auto px-4 lg:px-8">
                    <div class="grid gap-8 md:grid-cols-3 lg:gap-12">
                        <div>
                            <div class="mb-6 flex items-center space-x-3">
                                <Crown class="h-10 w-10 text-rose-400" />
                                <div>
                                    <h3 class="text-2xl font-bold">{{ name }}</h3>
                                    <p class="text-sm text-gray-400">Luxury Nail Artistry</p>
                                </div>
                            </div>
                            <p class="mb-6 leading-relaxed text-gray-400">
                                Elevating beauty through expert nail artistry in Kitengela. Your satisfaction is our masterpiece.
                            </p>
                            <div class="flex space-x-4">
                                <Button
                                    v-for="Icon in [Instagram, Phone, Mail]"
                                    :key="Icon"
                                    size="icon"
                                    variant="ghost"
                                    class="text-gray-400 transition-all duration-300 hover:bg-rose-400/10 hover:text-rose-400"
                                >
                                    <component :is="Icon" class="h-5 w-5" />
                                </Button>
                            </div>
                        </div>

                        <div>
                            <h4 class="mb-6 text-lg font-bold">Services</h4>
                            <ul class="space-y-3 text-gray-400">
                                <li v-for="service in services" :key="service.title">
                                    <a href="#" class="group flex items-center transition-colors duration-300 hover:text-rose-400">
                                        <ArrowRight class="mr-2 h-4 w-4 opacity-0 transition-all duration-300 group-hover:opacity-100" />
                                        {{ service.title }}
                                    </a>
                                </li>
                            </ul>
                        </div>

                        <div>
                            <h4 class="mb-6 text-lg font-bold">Contact Info</h4>
                            <div class="space-y-4 text-gray-400">
                                <div
                                    v-for="(item, index) in [
                                        { icon: MapPin, text: 'Kitengela, Kenya' },
                                        { icon: Phone, text: '+254 714 931250' },
                                        { icon: Mail, text: 'info@jacknails.co.ke' },
                                        { icon: Clock, text: 'Mon-Sat: 9AM-7PM' },
                                    ]"
                                    :key="index"
                                    class="flex items-center space-x-3"
                                >
                                    <component :is="item.icon" class="h-5 w-5 text-rose-400" />
                                    <span>{{ item.text }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-12 border-t border-gray-800 pt-8 text-center text-gray-400">
                        <p class="font-medium">
                            &copy; {{ new Date().getFullYear() }} {{ name }}. All rights reserved. Crafted with ❤️ By Ascend stratus Africa.
                        </p>
                    </div>
                </div>
            </footer>
        </div>
    </LandingLayout>
</template>

<style scoped>
.floating-shape {
    animation: float 6s ease-in-out infinite;
}

@keyframes float {
    0%,
    100% {
        transform: translateY(0px) rotate(0deg);
    }
    33% {
        transform: translateY(-15px) rotate(1deg);
    }
    66% {
        transform: translateY(-8px) rotate(-1deg);
    }
}
</style>
