<template>
  <div class="min-h-screen bg-gradient-to-br from-pink-50 via-white to-purple-50">
    <div class="container mx-auto px-4 py-8">
      <!-- Header -->
      <div class="text-center mb-12">
        <h1 class="text-4xl font-bold text-gray-900 mb-4">Book Your Appointment</h1>
        <p class="text-lg text-gray-600">Choose from our premium nail art services</p>
      </div>

      <!-- Services Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 max-w-7xl mx-auto">
        <div
          v-for="service in services"
          :key="service.id"
          class="bg-white rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 overflow-hidden group"
        >
          <!-- Service Image -->
          <div class="relative h-48 overflow-hidden">
            <img
              :src="service.image_url"
              :alt="service.name"
              class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
            />
            <div v-if="service.is_popular" class="absolute top-4 left-4">
              <Badge variant="premium" class="bg-gradient-to-r from-pink-500 to-purple-500 text-white border-0">
                <Star class="w-3 h-3 mr-1" />
                Popular
              </Badge>
            </div>
          </div>

          <!-- Service Content -->
          <div class="p-6">
            <div class="flex items-start justify-between mb-3">
              <h3 class="text-xl font-semibold text-gray-900 group-hover:text-pink-600 transition-colors">
                {{ service.name }}
              </h3>
              <div class="text-right">
                <div class="text-2xl font-bold text-pink-600">{{ service.formatted_price }}</div>
                <div class="text-sm text-gray-500">{{ service.formatted_duration }}</div>
              </div>
            </div>
            
            <p class="text-gray-600 mb-6 line-clamp-3">{{ service.description }}</p>
            
            <Button
              @click="bookService(service.id)"
              class="w-full bg-gradient-to-r from-pink-500 to-purple-500 hover:from-pink-600 hover:to-purple-600 text-white border-0 rounded-xl py-3 font-medium transition-all duration-300"
            >
              <Calendar class="w-4 h-4 mr-2" />
              Book Now
            </Button>
          </div>
        </div>
      </div>

      <!-- Call to Action -->
      <div class="text-center mt-12">
        <div class="bg-white rounded-2xl shadow-lg p-8 max-w-2xl mx-auto">
          <h2 class="text-2xl font-bold text-gray-900 mb-4">Need Help Choosing?</h2>
          <p class="text-gray-600 mb-6">
            Our nail artists are happy to help you select the perfect service for your style and occasion.
          </p>
          <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <Button variant="outline" class="rounded-xl">
              <Phone class="w-4 h-4 mr-2" />
              Call Us
            </Button>
            <Button variant="outline" class="rounded-xl">
              <MessageCircle class="w-4 h-4 mr-2" />
              WhatsApp
            </Button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { router } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Calendar, Star, Phone, MessageCircle } from 'lucide-vue-next'

interface Service {
  id: number
  name: string
  description: string
  price: number
  duration_minutes: number
  image_url: string
  is_popular: boolean
  status: string
  formatted_price: string
  formatted_duration: string
}

interface Props {
  services: Service[]
}

defineProps<Props>()

const bookService = (serviceId: number) => {
  router.visit(route('booking.create', { service_id: serviceId }))
}
</script>

<style scoped>
.line-clamp-3 {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>