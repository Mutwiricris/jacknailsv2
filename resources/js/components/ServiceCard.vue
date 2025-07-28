<template>
  <div
    @click="!disabled && $emit('toggle', service.id)"
    :class="[
      'border-2 rounded-lg p-3 cursor-pointer transition-all duration-300 relative group flex items-center space-x-3 h-24',
      selected
        ? 'border-pink-500 bg-pink-50 shadow-md'
        : 'border-gray-200 hover:border-pink-300 hover:shadow-sm',
      disabled && !selected
        ? 'opacity-50 cursor-not-allowed'
        : ''
    ]"
  >
    <!-- Popular Badge -->
    <div v-if="service.is_popular" class="absolute -top-2 -left-2 z-10">
      <div class="bg-gradient-to-r from-yellow-400 to-orange-500 text-white text-xs font-bold px-2 py-1 rounded-full shadow-lg">
        <Star class="w-3 h-3 inline mr-1" />
        Popular
      </div>
    </div>

    <!-- Selection Badge -->
    <div 
      v-if="selected" 
      class="absolute -top-2 -right-2 w-8 h-8 bg-gradient-to-r from-pink-500 to-purple-500 text-white rounded-full flex items-center justify-center text-sm font-bold shadow-lg z-10"
    >
      <CheckCircle class="w-5 h-5" />
    </div>

    <!-- Selection Number Badge (when multiple services selected) -->
    <div 
      v-if="selected && selectionOrder" 
      class="absolute top-1 left-1 w-5 h-5 bg-pink-500 text-white rounded-full flex items-center justify-center text-xs font-bold z-10"
    >
      {{ selectionOrder }}
    </div>

    <!-- Service Image -->
    <div class="relative w-16 h-16 bg-gradient-to-br from-pink-100 to-purple-100 rounded-lg overflow-hidden flex-shrink-0">
      <img
        :src="service.image_url"
        :alt="service.name"
        class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-110"
        @error="handleImageError"
      />
      <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300" />
    </div>
    
    <!-- Service Info -->
    <div class="flex-1 min-w-0">
      <div class="flex items-center justify-between mb-1">
        <h3 :class="[
          'font-semibold text-sm text-gray-900 transition-colors duration-300 truncate',
          selected ? 'text-pink-700' : 'group-hover:text-pink-600'
        ]">
          {{ service.name }}
        </h3>
        <div class="text-right ml-2">
          <div class="text-sm font-bold text-pink-600">{{ service.formatted_price }}</div>
        </div>
      </div>
      
      <p class="text-xs text-gray-600 line-clamp-1 mb-2">
        {{ service.description }}
      </p>
      
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-1">
          <Clock class="w-3 h-3 text-gray-400" />
          <span class="text-xs text-gray-500">{{ service.formatted_duration }}</span>
        </div>
        <div v-if="selected" class="flex items-center text-pink-600 text-xs font-medium">
          <CheckCircle class="w-3 h-3 mr-1" />
          Selected
        </div>
        <div v-else-if="service.is_popular" class="text-xs text-yellow-600 font-medium">
          Popular
        </div>
      </div>
    </div>

    <!-- Hover Effect Overlay -->
    <div 
      :class="[
        'absolute inset-0 rounded-xl transition-all duration-300',
        selected 
          ? 'bg-pink-500/5 border-2 border-pink-500' 
          : 'bg-transparent border-2 border-transparent hover:border-pink-300'
      ]" 
    />
  </div>
</template>

<script setup lang="ts">
import { CheckCircle, Star, Clock } from 'lucide-vue-next'

interface Service {
  id: number
  name: string
  description: string
  price: number
  duration_minutes: number
  formatted_price: string
  formatted_duration: string
  is_popular: boolean
  image_url: string
}

interface Props {
  service: Service
  selected: boolean
  disabled?: boolean
  selectionOrder?: number
}

interface Emits {
  (e: 'toggle', serviceId: number): void
}

defineProps<Props>()
defineEmits<Emits>()

const handleImageError = (event: Event) => {
  const target = event.target as HTMLImageElement
  target.src = `/placeholder.svg?height=128&width=200&text=${encodeURIComponent(target.alt)}`
}
</script>

<style scoped>
.line-clamp-1 {
  display: -webkit-box;
  -webkit-line-clamp: 1;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>