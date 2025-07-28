<script setup lang="ts">
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { Button } from '@/components/ui/button';
import { ChevronLeft, ChevronRight } from 'lucide-vue-next';

interface CarouselImage {
  src: string;
  alt: string;
  title: string;
  description: string;
}

const carouselImages: CarouselImage[] = [
  {
    src: '/placeholder.svg?height=600&width=500',
    alt: 'Luxury acrylic nails with gold foil',
    title: 'Gold Foil Elegance',
    description: 'Premium acrylic with 24k gold accents',
  },
  {
    src: '/placeholder.svg?height=600&width=500',
    alt: 'French manicure with crystals',
    title: 'Crystal French',
    description: 'Classic French with Swarovski crystals',
  },
  {
    src: '/placeholder.svg?height=600&width=500',
    alt: 'Ombre nails with glitter',
    title: 'Ombre Perfection',
    description: 'Seamless color transition with sparkle',
  },
  {
    src: '/placeholder.svg?height=600&width=500',
    alt: '3D floral nail art',
    title: '3D Floral Art',
    description: 'Hand-painted florals with dimension',
  },
  {
    src: '/placeholder.svg?height=600&width=500',
    alt: 'Chrome mirror nails',
    title: 'Chrome Luxury',
    description: 'Mirror finish chrome perfection',
  },
];

const currentIndex = ref(0);
const isAutoPlaying = ref(true);
let intervalId: number | null = null;

const progressWidth = computed(() => {
  return `${((currentIndex.value + 1) / carouselImages.length) * 100}%`;
});

const translateX = computed(() => {
  return `translateX(-${currentIndex.value * 100}%)`;
});

const startAutoPlay = () => {
  if (!isAutoPlaying.value) return;
  
  intervalId = window.setInterval(() => {
    currentIndex.value = currentIndex.value === carouselImages.length - 1 ? 0 : currentIndex.value + 1;
  }, 4000);
};

const stopAutoPlay = () => {
  if (intervalId) {
    clearInterval(intervalId);
    intervalId = null;
  }
};

const goToPrevious = () => {
  currentIndex.value = currentIndex.value === 0 ? carouselImages.length - 1 : currentIndex.value - 1;
  isAutoPlaying.value = false;
  stopAutoPlay();
};

const goToNext = () => {
  currentIndex.value = currentIndex.value === carouselImages.length - 1 ? 0 : currentIndex.value + 1;
  isAutoPlaying.value = false;
  stopAutoPlay();
};

const goToSlide = (index: number) => {
  currentIndex.value = index;
  isAutoPlaying.value = false;
  stopAutoPlay();
};

onMounted(() => {
  startAutoPlay();
});

onUnmounted(() => {
  stopAutoPlay();
});
</script>

<template>
  <div class="relative w-full h-full group">
    <!-- Main Image Container -->
    <div class="relative w-full h-[500px] md:h-[600px] lg:h-[700px] overflow-hidden rounded-3xl shadow-2xl">
      <div
        class="flex transition-transform duration-700 ease-in-out h-full"
        :style="{ transform: translateX }"
      >
        <div
          v-for="(image, index) in carouselImages"
          :key="index"
          class="w-full h-full flex-shrink-0 relative bg-gradient-to-br from-rose-100 to-pink-100"
        >
          <!-- Placeholder for actual image -->
          <div class="w-full h-full flex items-center justify-center text-gray-400">
            <div class="text-center">
              <div class="text-6xl mb-4">💅</div>
              <h3 class="text-xl md:text-2xl font-bold mb-2 text-gray-700">{{ image.title }}</h3>
              <p class="text-sm md:text-base text-gray-600">{{ image.description }}</p>
            </div>
          </div>
          <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent" />

          <!-- Image Info Overlay -->
          <div class="absolute bottom-6 left-6 right-6 text-white">
            <h3 class="text-xl md:text-2xl font-bold mb-2">{{ image.title }}</h3>
            <p class="text-sm md:text-base opacity-90">{{ image.description }}</p>
          </div>
        </div>
      </div>

      <!-- Navigation Arrows -->
      <Button
        variant="ghost"
        size="icon"
        class="absolute left-4 top-1/2 -translate-y-1/2 bg-white/20 backdrop-blur-sm hover:bg-white/30 text-white border-0 opacity-0 group-hover:opacity-100 transition-all duration-300"
        @click="goToPrevious"
      >
        <ChevronLeft class="h-6 w-6" />
      </Button>

      <Button
        variant="ghost"
        size="icon"
        class="absolute right-4 top-1/2 -translate-y-1/2 bg-white/20 backdrop-blur-sm hover:bg-white/30 text-white border-0 opacity-0 group-hover:opacity-100 transition-all duration-300"
        @click="goToNext"
      >
        <ChevronRight class="h-6 w-6" />
      </Button>

      <!-- Progress Bar -->
      <div class="absolute bottom-0 left-0 right-0 h-1 bg-black/20">
        <div
          class="h-full bg-gradient-to-r from-rose-400 to-pink-400 transition-all duration-700 ease-out"
          :style="{ width: progressWidth }"
        />
      </div>
    </div>

    <!-- Dot Indicators -->
    <div class="flex justify-center space-x-3 mt-6">
      <button
        v-for="(_, index) in carouselImages"
        :key="index"
        :class="[
          'w-3 h-3 rounded-full transition-all duration-300',
          index === currentIndex
            ? 'bg-gradient-to-r from-rose-500 to-pink-500 scale-125'
            : 'bg-gray-300 hover:bg-gray-400'
        ]"
        @click="goToSlide(index)"
      />
    </div>

    <!-- Thumbnail Strip -->
    <div class="hidden lg:flex justify-center space-x-2 mt-4 overflow-x-auto">
      <button
        v-for="(image, index) in carouselImages"
        :key="index"
        :class="[
          'relative w-16 h-16 rounded-lg overflow-hidden flex-shrink-0 transition-all duration-300 bg-gradient-to-br from-rose-100 to-pink-100',
          index === currentIndex ? 'ring-2 ring-rose-500 scale-110' : 'opacity-60 hover:opacity-100'
        ]"
        @click="goToSlide(index)"
      >
        <div class="w-full h-full flex items-center justify-center text-gray-400 text-xs">
          {{ index + 1 }}
        </div>
      </button>
    </div>
  </div>
</template>

<style scoped>
.carousel-container::-webkit-scrollbar {
  display: none;
}

.carousel-container {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
</style>