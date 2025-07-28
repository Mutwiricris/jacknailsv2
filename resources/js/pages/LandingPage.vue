<script setup lang="ts">
import { ref, watch, onMounted, onUnmounted } from 'vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import LandingLayout from '@/layouts/LandingLayout.vue';
import {
  Instagram,
  Phone,
  Mail,
  MapPin,
  Star,
  Sparkles,
  Crown,
  Palette,
  Clock,
  Award,
  Heart,
  Menu,
  X,
  ArrowRight,
  CheckCircle,
  Play,
  Zap,
  Gem,
  Shield,
  Users,
} from 'lucide-vue-next';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import ImageCarousel from '@/components/ImageCarousel.vue';
import AnimatedCounter from '@/components/AnimatedCounter.vue';
import FloatingElements from '@/components/FloatingElements.vue';
import ModernBadge from '@/components/ModernBadge.vue';
import { Link } from '@inertiajs/vue3';

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
    content: '+254 XXX XXX XXX',
    subtitle: 'Call us anytime',
  },
  {
    icon: Mail,
    title: 'Email',
    content: 'info@jacknailskenya.com',
    subtitle: 'We reply within 24hrs',
  },
  {
    icon: MapPin,
    title: 'Location',
    content: 'Kitengela, Kenya',
    subtitle: 'Easy to find location',
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
  'elegant french manicure with gold accents and crystals',
  'colorful ombre nail design with glitter gradient',
  'intricate floral nail art patterns with 3D elements',
  'geometric nail designs in pastel colors with metallic lines',
  'luxury chrome finish acrylic nails with mirror effect',
  'artistic marble effect nail design with gold veins',
  'delicate lace pattern nail art with pearl details',
  'bold abstract nail art with crystals and gems',
  'vintage inspired nail design with ornate patterns',
  'modern minimalist nail art with negative space',
  'tropical themed nail design with palm leaves',
  'galaxy inspired nail art with holographic effects',
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
    <div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-rose-50/30 relative overflow-hidden">
      <FloatingElements />

    <!-- Header -->
    <header 
      :class="[
        'sticky top-0 z-50 transition-all duration-300',
        isScrolled 
          ? 'bg-white/95 backdrop-blur-xl border-b border-gray-200/50 shadow-lg' 
          : 'bg-white/80 backdrop-blur-md border-b border-white/20 shadow-sm'
      ]"
    >
      <div 
        :class="[
          'container mx-auto px-4 lg:px-8 transition-all duration-300',
          isScrolled ? 'py-3' : 'py-4'
        ]"
      >
        <div class="flex items-center justify-between">
          <div class="flex items-center space-x-4">
            <div class="relative">
              <div 
                :class="[
                  'bg-gradient-to-br from-rose-500 via-pink-500 to-purple-600 rounded-3xl flex items-center justify-center shadow-xl transition-all duration-300',
                  isScrolled ? 'w-12 h-12' : 'w-14 h-14'
                ]"
              >
                <Crown :class="isScrolled ? 'h-6 w-6' : 'h-8 w-8'" class="text-white transition-all duration-300" />
              </div>
              <div 
                :class="[
                  'absolute -top-1 -right-1 bg-gradient-to-r from-yellow-400 to-yellow-500 rounded-full animate-pulse shadow-lg transition-all duration-300',
                  isScrolled ? 'w-4 h-4' : 'w-5 h-5'
                ]" 
              />
            </div>
            <div>
              <h1 
                :class="[
                  'font-bold bg-gradient-to-r from-rose-600 to-pink-600 bg-clip-text text-transparent transition-all duration-300',
                  isScrolled ? 'text-xl lg:text-2xl' : 'text-2xl lg:text-3xl'
                ]"
              >
                {{ name }}
              </h1>
              <p 
                :class="[
                  'text-gray-600 font-medium transition-all duration-300',
                  isScrolled ? 'text-xs' : 'text-xs lg:text-sm'
                ]"
              >
                Kitengela's Premier Nail Studio
              </p>
            </div>
          </div>

          <nav class="hidden lg:flex items-center space-x-8">
            <a
              v-for="(item, index) in navItems"
              :key="item"
              :href="`#${item.toLowerCase()}`"
              class="text-gray-700 hover:text-rose-600 transition-all duration-300 font-medium relative group"
            >
              {{ item }}
              <span class="absolute -bottom-1 left-0 w-0 h-0.5 bg-gradient-to-r from-rose-600 to-pink-600 group-hover:w-full transition-all duration-300" />
            </a>
            <div class="flex items-center space-x-4">
              <Link :href="route('booking.stepper.start')">
                <Button class="bg-gradient-to-r from-rose-500 to-pink-600 hover:from-rose-600 hover:to-pink-700 text-white rounded-full px-6 py-3 font-medium shadow-lg hover:shadow-xl transition-all duration-300">
                  <Phone class="h-4 w-4 mr-2" />
                  Book Now
                </Button>
              </Link>
              <Link 
                :href="route('login')" 
                class="text-gray-600 hover:text-rose-600 font-medium transition-colors duration-300"
              >
                Login
              </Link>
            </div>
          </nav>

          <Button 
            variant="ghost" 
            size="icon" 
            class="lg:hidden glass-morphism"
            @click="toggleMobileMenu"
          >
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
      <div 
        v-if="isMobileMenuOpen" 
        class="fixed inset-0 z-40 lg:hidden"
        @click="closeMobileMenu"
      >
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
            class="relative bg-white/95 backdrop-blur-xl border-b border-white/20 shadow-2xl min-h-screen"
            @click.stop
          >
            <!-- Header with logo -->
            <div class="flex items-center justify-between p-6 border-b border-gray-200/50">
              <div class="flex items-center space-x-3">
                <div class="w-12 h-12 bg-gradient-to-br from-rose-500 via-pink-500 to-purple-600 rounded-2xl flex items-center justify-center shadow-lg">
                  <Crown class="h-6 w-6 text-white" />
                </div>
                <div>
                  <h3 class="font-bold text-gray-900">{{ name }}</h3>
                  <p class="text-xs text-gray-600">Luxury Nail Artistry</p>
                </div>
              </div>
              <Button 
                variant="ghost" 
                size="icon" 
                class="glass-morphism"
                @click="closeMobileMenu"
              >
                <X class="h-6 w-6" />
              </Button>
            </div>

            <!-- Navigation Links -->
            <nav class="p-6 space-y-2">
              <button
                v-for="(item, index) in navItems"
                :key="item"
                @click="scrollToSection(item)"
                class="block w-full text-left text-xl font-medium text-gray-700 hover:text-rose-600 transition-all duration-300 py-4 px-6 rounded-2xl hover:bg-gradient-to-r hover:from-rose-50 hover:to-pink-50 relative group neumorphism hover-lift"
                :class="`slide-up stagger-${index + 1}`"
              >
                <span class="relative z-10">{{ item }}</span>
                <div class="absolute inset-0 bg-gradient-to-r from-rose-500/10 to-pink-500/10 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300" />
              </button>
            </nav>

            <!-- Action Buttons -->
            <div class="p-6 space-y-4 border-t border-gray-200/50 mt-auto">
              <Link :href="route('booking.stepper.start')" @click="closeMobileMenu">
                <Button 
                  class="w-full btn-modern py-4 text-lg font-medium relative overflow-hidden group"
                >
                  <Phone class="h-5 w-5 mr-2 relative z-10" />
                  <span class="relative z-10">Book Appointment</span>
                  <ArrowRight class="h-5 w-5 ml-2 group-hover:translate-x-1 transition-transform relative z-10" />
                </Button>
              </Link>
              <Link 
                :href="route('login')" 
                class="block w-full text-center py-4 px-6 text-gray-600 hover:text-rose-600 font-medium transition-all duration-300 rounded-2xl border-2 border-gray-200 hover:border-rose-200 glass-morphism hover:shadow-lg"
                @click="closeMobileMenu"
              >
                Login to Dashboard
              </Link>
            </div>

            <!-- Decorative Elements -->
            <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 opacity-5 pointer-events-none">
              <Sparkles class="h-32 w-32 text-rose-500" />
            </div>
          </div>
        </Transition>
      </div>
    </Transition>

    <!-- Hero Section -->
    <section id="home" class="relative py-20 lg:py-32 overflow-hidden">
      <div class="container mx-auto px-4 lg:px-8 relative">
        <div class="grid lg:grid-cols-2 gap-12 lg:gap-20 items-center">
          <!-- Left Content -->
          <div class="space-y-8 lg:space-y-12 order-2 lg:order-1">
            <div class="space-y-6 lg:space-y-8">
              <ModernBadge variant="soft" class="slide-up stagger-1">
                <Sparkles class="h-5 w-5 mr-2" />
                Premium Nail Artistry Since 2021
              </ModernBadge>

              <div class="space-y-6">
                <h2 class="text-4xl sm:text-5xl lg:text-7xl xl:text-8xl font-bold text-gray-900 leading-[0.85] slide-up stagger-2">
                  Elevate Your
                  <span class="text-gradient block mt-4 relative">
                    Natural Beauty
                    <div class="absolute -bottom-4 left-0 w-full h-2 bg-gradient-to-r from-rose-500/20 to-pink-500/20 rounded-full blur-sm" />
                  </span>
                </h2>

                <p class="text-lg lg:text-xl text-gray-600 leading-relaxed max-w-xl slide-up stagger-3">
                  Experience luxury nail artistry in Kitengela. From stunning acrylics to intricate designs, we craft
                  perfection at your fingertips with unmatched expertise and elegance.
                </p>
              </div>
            </div>

            <div class="flex flex-col sm:flex-row gap-6 slide-up stagger-4">
              <Link :href="route('booking.stepper.start')">
                <Button size="lg" class="btn-modern group relative overflow-hidden">
                  <Phone class="h-5 w-5 mr-2 group-hover:animate-pulse relative z-10" />
                  <span class="relative z-10">Book Appointment</span>
                  <ArrowRight class="h-5 w-5 ml-2 group-hover:translate-x-1 transition-transform relative z-10" />
                </Button>
              </Link>
              <Button
                size="lg"
                variant="outline"
                class="border-2 border-rose-300/50 text-rose-700 hover:bg-rose-50 px-8 py-4 text-lg backdrop-blur-sm hover:border-rose-400 transition-all duration-300 rounded-full shadow-lg hover:shadow-xl hover:scale-105 bg-transparent"
              >
                <Play class="h-5 w-5 mr-2" />
                Watch Process
              </Button>
            </div>

            <!-- Enhanced Stats -->
            <div class="grid grid-cols-3 gap-6 lg:gap-8 pt-8 slide-up stagger-5">
              <div
                v-for="(stat, index) in stats"
                :key="index"
                class="text-center p-6 neumorphism rounded-3xl hover-lift group"
              >
                <div class="flex justify-center mb-4">
                  <div
                    :class="`w-14 h-14 bg-gradient-to-br ${stat.color} rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300 shadow-lg`"
                  >
                    <component :is="stat.icon" class="h-7 w-7 text-white" />
                  </div>
                </div>
                <div class="text-3xl lg:text-4xl font-bold text-gray-900 mb-2">
                  <AnimatedCounter :end="stat.number" :suffix="stat.suffix" />
                </div>
                <div class="text-sm lg:text-base text-gray-600 font-medium">{{ stat.label }}</div>
              </div>
            </div>

            <!-- Trust Indicators -->
            <div class="flex flex-wrap items-center gap-6 pt-6 slide-up stagger-6">
              <div
                v-for="(item, index) in trustIndicators"
                :key="index"
                class="flex items-center space-x-3 glass-morphism px-4 py-2 rounded-full"
              >
                <div :class="`w-8 h-8 ${item.color} rounded-full flex items-center justify-center`">
                  <component :is="item.icon" class="h-4 w-4" />
                </div>
                <span class="text-sm font-medium text-gray-700">{{ item.label }}</span>
              </div>
            </div>
          </div>

          <!-- Right Image/Carousel -->
          <div class="order-1 lg:order-2 relative scale-in">
            <div class="relative">
              <ImageCarousel />

              <!-- Enhanced Floating Badge -->
              <div class="absolute -bottom-8 -left-8 lg:-bottom-10 lg:-left-10 glass-morphism rounded-3xl p-6 shadow-2xl float-animation hidden sm:block border border-white/20">
                <div class="flex items-center space-x-4">
                  <div class="w-14 h-14 bg-gradient-to-br from-rose-500 to-pink-600 rounded-2xl flex items-center justify-center shadow-lg">
                    <Award class="h-7 w-7 text-white" />
                  </div>
                  <div>
                    <div class="font-bold text-gray-900 text-lg">Certified Professional</div>
                    <div class="text-sm text-gray-600">Expert Nail Technician</div>
                  </div>
                </div>
              </div>

              <!-- Service Quick Access -->
              <div
                class="absolute top-8 -right-8 lg:top-10 lg:-right-10 glass-morphism rounded-2xl p-5 shadow-xl float-animation border border-white/20"
                style="animation-delay: 0.5s"
              >
                <div class="flex flex-col items-center space-y-3">
                  <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-rose-600 rounded-xl flex items-center justify-center shadow-lg">
                    <Gem class="h-6 w-6 text-white" />
                  </div>
                  <div class="text-xs font-bold text-gray-700">Premium</div>
                  <div class="text-xs text-gray-600">Quality</div>
                </div>
              </div>

              <!-- Additional floating elements -->
              <div
                class="absolute top-1/2 -left-6 w-4 h-4 bg-gradient-to-br from-rose-400 to-pink-500 rounded-full shadow-lg bounce-gentle"
                style="animation-delay: 1s"
              />
              <div
                class="absolute bottom-1/4 -right-4 w-3 h-3 bg-gradient-to-br from-purple-400 to-rose-500 rounded-full shadow-lg bounce-gentle"
                style="animation-delay: 2s"
              />
            </div>
          </div>
        </div>

        <!-- Scroll Indicator -->
        <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2 slide-up stagger-6">
          <div class="flex flex-col items-center space-y-3 bounce-gentle">
            <div class="w-8 h-12 border-2 border-rose-300 rounded-full flex justify-center glass-morphism">
              <div class="w-2 h-4 bg-gradient-to-b from-rose-500 to-pink-500 rounded-full mt-2 animate-pulse" />
            </div>
            <span class="text-xs text-gray-500 font-medium">Scroll to explore</span>
          </div>
        </div>
      </div>
    </section>

    <!-- Services Section -->
    <section id="services" class="py-20 lg:py-32 bg-gradient-to-br from-white to-rose-50/30 relative">
      <div class="container mx-auto px-4 lg:px-8">
        <div class="text-center mb-16 lg:mb-24">
          <ModernBadge variant="premium" class="mb-8">
            <Palette class="h-4 w-4 mr-2" />
            Our Expertise
          </ModernBadge>
          <h2 class="text-4xl lg:text-6xl font-bold text-gray-900 mb-8">
            Premium Nail
            <span class="text-gradient block mt-2">Services</span>
          </h2>
          <p class="text-lg lg:text-xl text-gray-600 max-w-3xl mx-auto leading-relaxed">
            Discover our comprehensive range of luxury nail services, each crafted with precision and artistry
          </p>
        </div>

        <!-- Dynamic Services from Database -->
        <div v-if="featuredServices" class="space-y-16">
          <!-- First Row -->
          <div v-if="featuredServices.firstRow" class="space-y-8">
            <div class="text-center">
              <ModernBadge variant="soft" class="mb-4">
                <Sparkles class="h-4 w-4 mr-2" />
                {{ featuredServices.firstRow.category }}
              </ModernBadge>
              <h3 class="text-2xl lg:text-3xl font-bold text-gray-900">
                {{ featuredServices.firstRow.category }} Collection
              </h3>
            </div>
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-8 lg:gap-10">
              <Card
                v-for="(service, index) in featuredServices.firstRow.services"
                :key="`first-${service.id}`"
                class="group hover:shadow-2xl transition-all duration-700 border-0 shadow-lg overflow-hidden neumorphism hover-lift relative"
              >
                <div class="relative h-56 lg:h-64 overflow-hidden">
                  <img 
                    :src="service.image_url" 
                    :alt="service.title"
                    class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                    loading="lazy"
                  />
                  <div class="absolute inset-0 bg-gradient-to-t from-black/40 via-transparent to-transparent" />
                  <div
                    :class="`absolute top-4 left-4 bg-gradient-to-br ${service.color} rounded-2xl p-3 text-white shadow-lg group-hover:scale-110 transition-transform duration-300`"
                  >
                    <Sparkles class="h-6 w-6" />
                  </div>
                  <ModernBadge variant="glow" class="absolute top-4 right-4">
                    {{ service.price }}
                  </ModernBadge>
                </div>
                <CardContent class="p-6 lg:p-8">
                  <h3 class="text-xl lg:text-2xl font-bold text-gray-900 mb-4">
                    {{ service.title }}
                  </h3>
                  <p class="text-gray-600 mb-6 leading-relaxed">{{ service.description }}</p>

                  <div class="grid grid-cols-2 gap-3 mb-6">
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
                      :class="`w-full bg-gradient-to-r ${service.color} hover:shadow-xl transition-all duration-300 group text-white rounded-2xl py-3`"
                    >
                      Book Service
                      <ArrowRight class="h-4 w-4 ml-2 group-hover:translate-x-1 transition-transform" />
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
                <Crown class="h-4 w-4 mr-2" />
                {{ featuredServices.secondRow.category }}
              </ModernBadge>
              <h3 class="text-2xl lg:text-3xl font-bold text-gray-900">
                {{ featuredServices.secondRow.category }} Collection
              </h3>
            </div>
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-8 lg:gap-10">
              <Card
                v-for="(service, index) in featuredServices.secondRow.services"
                :key="`second-${service.id}`"
                class="group hover:shadow-2xl transition-all duration-700 border-0 shadow-lg overflow-hidden neumorphism hover-lift relative"
              >
                <div class="relative h-56 lg:h-64 overflow-hidden">
                  <img 
                    :src="service.image_url" 
                    :alt="service.title"
                    class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                    loading="lazy"
                  />
                  <div class="absolute inset-0 bg-gradient-to-t from-black/40 via-transparent to-transparent" />
                  <div
                    :class="`absolute top-4 left-4 bg-gradient-to-br ${service.color} rounded-2xl p-3 text-white shadow-lg group-hover:scale-110 transition-transform duration-300`"
                  >
                    <Crown class="h-6 w-6" />
                  </div>
                  <ModernBadge variant="glow" class="absolute top-4 right-4">
                    {{ service.price }}
                  </ModernBadge>
                </div>
                <CardContent class="p-6 lg:p-8">
                  <h3 class="text-xl lg:text-2xl font-bold text-gray-900 mb-4">
                    {{ service.title }}
                  </h3>
                  <p class="text-gray-600 mb-6 leading-relaxed">{{ service.description }}</p>

                  <div class="grid grid-cols-2 gap-3 mb-6">
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
                      :class="`w-full bg-gradient-to-r ${service.color} hover:shadow-xl transition-all duration-300 group text-white rounded-2xl py-3`"
                    >
                      Book Service
                      <ArrowRight class="h-4 w-4 ml-2 group-hover:translate-x-1 transition-transform" />
                    </Button>
                  </Link>
                </CardContent>
              </Card>
            </div>
          </div>
        </div>

        <!-- Fallback to static services if no dynamic data -->
        <div v-else class="grid sm:grid-cols-2 lg:grid-cols-3 gap-8 lg:gap-10">
          <Card
            v-for="(service, index) in services"
            :key="index"
            class="group hover:shadow-2xl transition-all duration-700 border-0 shadow-lg overflow-hidden neumorphism hover-lift relative"
          >
            <div class="relative h-56 lg:h-64 overflow-hidden bg-gradient-to-br from-rose-100 to-pink-100">
              <div class="w-full h-full flex items-center justify-center text-gray-400">
                <component :is="service.icon" class="h-16 w-16" />
              </div>
              <div class="absolute inset-0 bg-gradient-to-t from-black/30 via-transparent to-transparent" />
              <div
                :class="`absolute top-4 left-4 bg-gradient-to-br ${service.color} rounded-2xl p-3 text-white shadow-lg group-hover:scale-110 transition-transform duration-300`"
              >
                <component :is="service.icon" class="h-8 w-8" />
              </div>
              <ModernBadge variant="glow" class="absolute top-4 right-4">
                {{ service.price }}
              </ModernBadge>
            </div>
            <CardContent class="p-8">
              <h3 class="text-xl lg:text-2xl font-bold text-gray-900 mb-4">
                {{ service.title }}
              </h3>
              <p class="text-gray-600 mb-6 leading-relaxed">{{ service.description }}</p>

              <div class="grid grid-cols-2 gap-3 mb-8">
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
                  :class="`w-full bg-gradient-to-r ${service.color} hover:shadow-xl transition-all duration-300 group text-white rounded-2xl py-3`"
                >
                  Book Service
                  <ArrowRight class="h-4 w-4 ml-2 group-hover:translate-x-1 transition-transform" />
                </Button>
              </Link>
            </CardContent>
          </Card>
        </div>

        <!-- Call to Action -->
        <div class="text-center mt-16">
          <Link :href="route('booking.stepper.start')">
            <Button size="lg" class="btn-modern group px-8 py-4">
              <Phone class="h-5 w-5 mr-2" />
              View All Services & Book Now
              <ArrowRight class="h-5 w-5 ml-2 group-hover:translate-x-1 transition-transform" />
            </Button>
          </Link>
        </div>
      </div>
    </section>

    <!-- Gallery Section -->
    <section id="gallery" class="py-16 lg:py-24 bg-gradient-to-br from-rose-50 to-pink-50">
      <div class="container mx-auto px-4 lg:px-8">
        <div class="text-center mb-12 lg:mb-20">
          <ModernBadge variant="premium" class="mb-6">
            <Star class="h-4 w-4 mr-2" />
            Portfolio
          </ModernBadge>
          <h2 class="text-3xl lg:text-5xl font-bold text-gray-900 mb-6">Our Masterpieces</h2>
          <p class="text-lg lg:text-xl text-gray-600 max-w-3xl mx-auto leading-relaxed">
            Explore our stunning collection of nail artistry that showcases our commitment to excellence
          </p>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 lg:gap-6">
          <div
            v-for="(query, index) in galleryImages"
            :key="index"
            class="group relative overflow-hidden rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-500 aspect-square neumorphism hover-lift"
          >
            <div class="w-full h-full flex items-center justify-center text-gray-400">
              <Sparkles class="h-8 w-8" />
            </div>
            <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-all duration-500" />
            <div class="absolute bottom-4 left-4 right-4 text-white opacity-0 group-hover:opacity-100 transition-all duration-500 transform translate-y-4 group-hover:translate-y-0">
              <p class="text-sm font-semibold">Premium Design #{{ index + 1 }}</p>
              <p class="text-xs opacity-90">Luxury Nail Art</p>
            </div>
          </div>
        </div>

        <div class="text-center mt-12 lg:mt-16">
          <Button
            size="lg"
            variant="outline"
            class="border-2 border-rose-300 text-rose-700 hover:bg-rose-50 bg-white/80 backdrop-blur-sm hover:border-rose-400 px-8 py-4 text-lg transition-all duration-300"
          >
            <Instagram class="h-5 w-5 mr-2" />
            View More on Instagram
          </Button>
        </div>
      </div>
    </section>

    <!-- About Section -->
    <section id="about" class="py-16 lg:py-24 bg-white">
      <div class="container mx-auto px-4 lg:px-8">
        <div class="grid lg:grid-cols-2 gap-12 lg:gap-20 items-center">
          <div class="relative order-2 lg:order-1">
            <div class="relative rounded-3xl overflow-hidden shadow-2xl bg-gradient-to-br from-rose-100 to-pink-100 aspect-[4/5]">
              <div class="w-full h-full flex items-center justify-center text-gray-400">
                <div class="text-center">
                  <Crown class="h-16 w-16 mx-auto mb-4" />
                  <p class="text-lg font-medium">Professional Service</p>
                </div>
              </div>
              <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent" />
            </div>

            <div class="absolute -top-6 -right-6 bg-gradient-to-r from-rose-600 to-pink-600 text-white rounded-2xl p-6 shadow-xl">
              <Clock class="h-8 w-8 mb-2" />
              <div class="font-bold text-lg">3+ Years</div>
              <div class="text-sm opacity-90">Experience</div>
            </div>
          </div>

          <div class="space-y-6 lg:space-y-8 order-1 lg:order-2">
            <div>
              <ModernBadge variant="premium" class="mb-6">
                <Crown class="h-4 w-4 mr-2" />
                About Jacknails Kenya
              </ModernBadge>
              <h2 class="text-3xl lg:text-5xl font-bold text-gray-900 mb-6 leading-tight">
                Crafting Beauty with
                <span class="text-gradient block mt-2">Passion & Precision</span>
              </h2>
              <p class="text-lg lg:text-xl text-gray-600 leading-relaxed">
                Located in the heart of Kitengela, Jacknails Kenya has been the premier destination for luxury nail
                services. Our commitment to excellence and attention to detail has made us the trusted choice for
                clients who demand nothing but the best.
              </p>
            </div>

            <div class="grid grid-cols-2 gap-4 lg:gap-6">
              <div class="text-center p-6 bg-white/80 backdrop-blur-sm rounded-2xl shadow-sm hover:shadow-lg transition-all duration-300 hover:-translate-y-2">
                <Award class="h-10 w-10 text-rose-600 mx-auto mb-3" />
                <div class="font-bold text-lg text-gray-900">Certified</div>
                <div class="text-sm text-gray-600">Professional Training</div>
              </div>
              <div class="text-center p-6 bg-white/80 backdrop-blur-sm rounded-2xl shadow-sm hover:shadow-lg transition-all duration-300 hover:-translate-y-2">
                <Heart class="h-10 w-10 text-rose-600 mx-auto mb-3" />
                <div class="font-bold text-lg text-gray-900">500+</div>
                <div class="text-sm text-gray-600">Satisfied Clients</div>
              </div>
            </div>

            <div class="space-y-4">
              <h3 class="text-xl lg:text-2xl font-bold text-gray-900">Why Choose Us?</h3>
              <div class="grid gap-3">
                <div
                  v-for="(item, index) in whyChooseUs"
                  :key="index"
                  class="flex items-center space-x-3 p-4 bg-white/50 backdrop-blur-sm rounded-lg hover:bg-white/80 hover:shadow-md transition-all duration-300"
                >
                  <CheckCircle class="h-5 w-5 text-rose-600 flex-shrink-0" />
                  <span class="text-gray-700 font-medium">{{ item }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="py-16 lg:py-24 bg-gradient-to-br from-rose-50 to-pink-50">
      <div class="container mx-auto px-4 lg:px-8">
        <div class="text-center mb-12 lg:mb-20">
          <div class="inline-flex items-center bg-gradient-to-r from-blue-100 to-purple-100 rounded-full px-4 py-2 text-sm font-medium text-blue-700 border border-blue-200 mb-6">
            <Phone class="h-4 w-4 mr-2" />
            Get In Touch
          </div>
          <h2 class="text-3xl lg:text-5xl font-bold text-gray-900 mb-6">
            Book Your Appointment
          </h2>
          <p class="text-lg lg:text-xl text-gray-600 max-w-3xl mx-auto leading-relaxed">
            Ready to transform your nails? Contact us today to schedule your luxury nail experience
          </p>
        </div>

        <div class="grid lg:grid-cols-2 gap-8 lg:gap-16">
          <div class="space-y-6 lg:space-y-8">
            <div class="grid gap-6">
              <Card
                v-for="(item, index) in contactInfo"
                :key="index"
                class="p-6 border-0 shadow-lg bg-white/80 backdrop-blur-sm hover:shadow-xl transition-all duration-300 hover:-translate-y-2"
              >
                <div class="flex items-center space-x-4">
                  <div class="bg-gradient-to-r from-rose-100 to-pink-100 p-4 rounded-2xl">
                    <component :is="item.icon" class="h-6 w-6 text-rose-600" />
                  </div>
                  <div>
                    <h3 class="font-bold text-gray-900 text-lg">{{ item.title }}</h3>
                    <p class="text-gray-900 font-medium">{{ item.content }}</p>
                    <p class="text-gray-600 text-sm">{{ item.subtitle }}</p>
                  </div>
                </div>
              </Card>
            </div>
          </div>

          <Card class="p-6 lg:p-8 border-0 shadow-2xl bg-white/90 backdrop-blur-sm">
            <h3 class="text-2xl lg:text-3xl font-bold text-gray-900 mb-6">
              Send us a Message
            </h3>
            <form class="space-y-6">
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-semibold text-gray-700 mb-2">First Name</label>
                  <Input
                    placeholder="Your first name"
                    class="border-gray-200 focus:border-rose-500 focus:ring-rose-500 h-12 bg-white/80 backdrop-blur-sm"
                  />
                </div>
                <div>
                  <label class="block text-sm font-semibold text-gray-700 mb-2">Last Name</label>
                  <Input
                    placeholder="Your last name"
                    class="border-gray-200 focus:border-rose-500 focus:ring-rose-500 h-12 bg-white/80 backdrop-blur-sm"
                  />
                </div>
              </div>
              <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Email</label>
                <Input
                  type="email"
                  placeholder="your.email@example.com"
                  class="border-gray-200 focus:border-rose-500 focus:ring-rose-500 h-12 bg-white/80 backdrop-blur-sm"
                />
              </div>
              <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Phone</label>
                <Input
                  placeholder="+254 XXX XXX XXX"
                  class="border-gray-200 focus:border-rose-500 focus:ring-rose-500 h-12 bg-white/80 backdrop-blur-sm"
                />
              </div>
              <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                  Service Interested In
                </label>
                <Input
                  placeholder="e.g., Acrylic Nails, Gel Tips"
                  class="border-gray-200 focus:border-rose-500 focus:ring-rose-500 h-12 bg-white/80 backdrop-blur-sm"
                />
              </div>
              <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Message</label>
                <Textarea
                  placeholder="Tell us about your nail goals and preferred appointment time..."
                  class="border-gray-200 focus:border-rose-500 focus:ring-rose-500 min-h-[120px] resize-none bg-white/80 backdrop-blur-sm"
                />
              </div>
              <Button class="w-full bg-gradient-to-r from-rose-500 to-pink-600 hover:from-rose-600 hover:to-pink-700 text-white py-4 text-lg group rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300">
                <Mail class="h-5 w-5 mr-2 group-hover:animate-pulse" />
                Send Message
                <ArrowRight class="h-5 w-5 ml-2 group-hover:translate-x-1 transition-transform" />
              </Button>
            </form>
          </Card>
        </div>
      </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-12 lg:py-16">
      <div class="container mx-auto px-4 lg:px-8">
        <div class="grid md:grid-cols-3 gap-8 lg:gap-12">
          <div>
            <div class="flex items-center space-x-3 mb-6">
              <Crown class="h-10 w-10 text-rose-400" />
              <div>
                <h3 class="text-2xl font-bold">{{ name }}</h3>
                <p class="text-sm text-gray-400">Luxury Nail Artistry</p>
              </div>
            </div>
            <p class="text-gray-400 mb-6 leading-relaxed">
              Elevating beauty through expert nail artistry in Kitengela. Your satisfaction is our masterpiece.
            </p>
            <div class="flex space-x-4">
              <Button
                v-for="Icon in [Instagram, Phone, Mail]"
                :key="Icon"
                size="icon"
                variant="ghost"
                class="text-gray-400 hover:text-rose-400 hover:bg-rose-400/10 transition-all duration-300"
              >
                <component :is="Icon" class="h-5 w-5" />
              </Button>
            </div>
          </div>

          <div>
            <h4 class="font-bold text-lg mb-6">Services</h4>
            <ul class="space-y-3 text-gray-400">
              <li v-for="service in services" :key="service.title">
                <a
                  href="#"
                  class="hover:text-rose-400 transition-colors duration-300 flex items-center group"
                >
                  <ArrowRight class="h-4 w-4 mr-2 opacity-0 group-hover:opacity-100 transition-all duration-300" />
                  {{ service.title }}
                </a>
              </li>
            </ul>
          </div>

          <div>
            <h4 class="font-bold text-lg mb-6">Contact Info</h4>
            <div class="space-y-4 text-gray-400">
              <div
                v-for="(item, index) in [
                  { icon: MapPin, text: 'Kitengela, Kenya' },
                  { icon: Phone, text: '+254 XXX XXX XXX' },
                  { icon: Mail, text: 'info@jacknailskenya.com' },
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

        <div class="border-t border-gray-800 mt-12 pt-8 text-center text-gray-400">
          <p class="font-medium">
            &copy; {{ new Date().getFullYear() }} {{ name }}. All rights reserved. Crafted with ❤️ for beautiful
            nails.
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
  0%, 100% {
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