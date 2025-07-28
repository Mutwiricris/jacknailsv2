<template>
  <div class="min-h-screen bg-gradient-to-br from-pink-50 via-white to-purple-50">
    <div class="container mx-auto px-4 py-8">
      <!-- Header -->
      <div class="text-center mb-8">
        <h1 class="text-4xl font-bold text-gray-900 mb-4">Book Your Appointment</h1>
        <p class="text-lg text-gray-600">Step 1: Choose your nail services (select up to 2)</p>
      </div>

      <div class="max-w-6xl mx-auto">
        <!-- Progress Indicator -->
        <BookingProgress :current-step="currentStep" />

        <form @submit.prevent="nextStep" class="space-y-6">
          <!-- All Services -->
          <div class="bg-white rounded-xl shadow-md p-4">
            <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
              <Sparkles class="w-6 h-6 mr-2 text-pink-500" />
              All Services ({{ allServices.length }} available)
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
              <ServiceCard
                v-for="service in allServices"
                :key="service.id"
                :service="service"
                :selected="selectedServices.includes(service.id)"
                :disabled="!canSelectService(service.id)"
                :selection-order="selectedServices.includes(service.id) ? selectedServices.indexOf(service.id) + 1 : 0"
                @toggle="toggleService"
              />
            </div>
          </div>

          <!-- Service Categories (Optional - for organization) -->
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6" v-show="false">
            <!-- Category: Manicure Services -->
            <div v-if="groupedServices.manicure?.length" class="bg-white rounded-xl shadow-md p-4">
              <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <Sparkles class="w-6 h-6 mr-2 text-pink-500" />
                Manicure Services
              </h2>
              <div class="space-y-2">
                <ServiceCard
                  v-for="service in groupedServices.manicure"
                  :key="service.id"
                  :service="service"
                  :selected="selectedServices.includes(service.id)"
                  :disabled="!canSelectService(service.id)"
                  :selection-order="selectedServices.includes(service.id) ? selectedServices.indexOf(service.id) + 1 : 0"
                  @toggle="toggleService"
                />
              </div>
            </div>

            <!-- Category: Pedicure Services -->
            <div v-if="groupedServices.pedicure?.length" class="bg-white rounded-xl shadow-md p-4">
              <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <Heart class="w-6 h-6 mr-2 text-purple-500" />
                Pedicure Services
              </h2>
              <div class="space-y-2">
                <ServiceCard
                  v-for="service in groupedServices.pedicure"
                  :key="service.id"
                  :service="service"
                  :selected="selectedServices.includes(service.id)"
                  :disabled="!canSelectService(service.id)"
                  :selection-order="selectedServices.includes(service.id) ? selectedServices.indexOf(service.id) + 1 : 0"
                  @toggle="toggleService"
                />
              </div>
            </div>

            <!-- Category: Acrylic Services -->
            <div v-if="groupedServices.acrylics?.length" class="bg-white rounded-xl shadow-md p-4">
              <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <Crown class="w-6 h-6 mr-2 text-yellow-500" />
                Acrylic Services
              </h2>
              <div class="space-y-2">
                <ServiceCard
                  v-for="service in groupedServices.acrylics"
                  :key="service.id"
                  :service="service"
                  :selected="selectedServices.includes(service.id)"
                  :disabled="!canSelectService(service.id)"
                  :selection-order="selectedServices.includes(service.id) ? selectedServices.indexOf(service.id) + 1 : 0"
                  @toggle="toggleService"
                />
              </div>
            </div>

            <!-- Category: Enhancements -->
            <div v-if="groupedServices.enhancements?.length" class="bg-white rounded-xl shadow-md p-4">
              <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <Gem class="w-6 h-6 mr-2 text-blue-500" />
                Nail Enhancements & Art
              </h2>
              <div class="space-y-2">
                <ServiceCard
                  v-for="service in groupedServices.enhancements"
                  :key="service.id"
                  :service="service"
                  :selected="selectedServices.includes(service.id)"
                  :disabled="!canSelectService(service.id)"
                  :selection-order="selectedServices.includes(service.id) ? selectedServices.indexOf(service.id) + 1 : 0"
                  @toggle="toggleService"
                />
              </div>
            </div>

            <!-- Category: Removal Services -->
            <div v-if="groupedServices.removal?.length" class="bg-white rounded-xl shadow-md p-4">
              <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <Trash2 class="w-6 h-6 mr-2 text-red-500" />
                Removal Services
              </h2>
              <div class="space-y-2">
                <ServiceCard
                  v-for="service in groupedServices.removal"
                  :key="service.id"
                  :service="service"
                  :selected="selectedServices.includes(service.id)"
                  :disabled="!canSelectService(service.id)"
                  :selection-order="selectedServices.includes(service.id) ? selectedServices.indexOf(service.id) + 1 : 0"
                  @toggle="toggleService"
                />
              </div>
            </div>
          </div>

          <!-- Selected Services Summary -->
          <div v-if="selectedServices.length > 0" class="bg-white rounded-2xl shadow-lg p-6">
            <h3 class="text-xl font-semibold text-gray-900 mb-4 flex items-center">
              <CheckCircle class="w-5 h-5 mr-2 text-green-500" />
              Selected Services ({{ selectedServices.length }}/2)
            </h3>
            <div class="space-y-3">
              <div 
                v-for="(serviceId, index) in selectedServices" 
                :key="serviceId"
                class="flex items-center justify-between p-4 bg-pink-50 rounded-lg"
              >
                <div class="flex items-center space-x-4">
                  <div class="w-8 h-8 bg-pink-500 text-white rounded-full flex items-center justify-center text-sm font-bold">
                    {{ index + 1 }}
                  </div>
                  <div>
                    <h4 class="font-semibold text-gray-900">{{ getServiceById(serviceId)?.name }}</h4>
                    <div class="flex items-center space-x-4 text-sm text-gray-600">
                      <span>{{ getServiceById(serviceId)?.formatted_duration }}</span>
                      <span class="font-medium text-pink-600">{{ getServiceById(serviceId)?.formatted_price }}</span>
                    </div>
                  </div>
                </div>
                <Button
                  type="button"
                  @click="toggleService(serviceId)"
                  variant="ghost"
                  size="sm"
                  class="text-red-500 hover:text-red-700 hover:bg-red-50"
                >
                  <X class="w-5 h-5" />
                </Button>
              </div>
              
              <!-- Total Summary -->
              <div class="border-t border-gray-200 pt-4 flex justify-between items-center">
                <span class="font-semibold text-gray-900">Total</span>
                <div class="text-right">
                  <div class="text-xl font-bold text-pink-600">{{ formattedTotalPrice }}</div>
                  <div class="text-sm text-gray-600">{{ formattedTotalDuration }}</div>
                </div>
              </div>
            </div>
          </div>

          <!-- Error Message -->
          <div v-if="errors?.service_ids" class="bg-red-50 border border-red-200 rounded-lg p-4">
            <div class="flex items-center">
              <AlertCircle class="w-5 h-5 text-red-500 mr-2" />
              <span class="text-red-700">{{ errors.service_ids }}</span>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="flex justify-between items-center">
            <Button
              type="button"
              variant="outline"
              @click="$inertia.visit(route('home'))"
              class="px-6 py-3 rounded-xl"
            >
              <ArrowLeft class="w-4 h-4 mr-2" />
              Back to Home
            </Button>

            <Button
              type="submit"
              :disabled="selectedServices.length === 0"
              class="bg-gradient-to-r from-pink-500 to-purple-500 hover:from-pink-600 hover:to-purple-600 text-white px-8 py-3 rounded-xl disabled:opacity-50"
            >
              Continue to Date & Time
              <ArrowRight class="w-4 h-4 ml-2" />
            </Button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import BookingProgress from '@/components/BookingProgress.vue'
import ServiceCard from '@/components/ServiceCard.vue'
import { useToast } from '@/components/ui/toast'
import { 
  Sparkles, 
  Heart, 
  Crown, 
  Gem, 
  Trash2, 
  CheckCircle, 
  X, 
  ArrowLeft, 
  ArrowRight,
  AlertCircle 
} from 'lucide-vue-next'

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

interface GroupedServices {
  manicure?: Service[]
  pedicure?: Service[]
  acrylics?: Service[]
  enhancements?: Service[]
  removal?: Service[]
}

interface Props {
  currentStep: number
  bookingData: any
  groupedServices: GroupedServices
  allServices: Service[]
  errors?: Record<string, string>
}

const props = defineProps<Props>()
const { success, warning, info } = useToast()

const selectedServices = ref<number[]>(props.bookingData.service_ids || [])

const form = useForm({
  service_ids: selectedServices.value
})

// Show welcome toast on mount
onMounted(() => {
  info('Select up to 2 services for your appointment', 'Welcome to Jacknails!')
})

const canSelectService = (serviceId: number): boolean => {
  return selectedServices.value.includes(serviceId) || selectedServices.value.length < 2
}

const toggleService = (serviceId: number) => {
  const index = selectedServices.value.indexOf(serviceId)
  
  if (index > -1) {
    selectedServices.value.splice(index, 1)
  } else if (selectedServices.value.length < 2) {
    selectedServices.value.push(serviceId)
  }
  
  form.service_ids = selectedServices.value
}

const getServiceById = (serviceId: number): Service | undefined => {
  return props.allServices.find(service => service.id === serviceId)
}

const totalPrice = computed(() => {
  return selectedServices.value.reduce((total, serviceId) => {
    const service = getServiceById(serviceId)
    return total + (service?.price || 0)
  }, 0)
})

const totalDuration = computed(() => {
  return selectedServices.value.reduce((total, serviceId) => {
    const service = getServiceById(serviceId)
    return total + (service?.duration_minutes || 0)
  }, 0)
})

const formattedTotalPrice = computed(() => {
  return 'KSh ' + new Intl.NumberFormat().format(totalPrice.value)
})

const formattedTotalDuration = computed(() => {
  const hours = Math.floor(totalDuration.value / 60)
  const minutes = totalDuration.value % 60
  
  if (hours > 0 && minutes > 0) {
    return `${hours}h ${minutes}min`
  } else if (hours > 0) {
    return `${hours}h`
  } else {
    return `${minutes}min`
  }
})

const nextStep = () => {
  if (selectedServices.value.length === 0) {
    return
  }
  
  form.post(route('booking.stepper.update', { step: 1 }))
}
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>