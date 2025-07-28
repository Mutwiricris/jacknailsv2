<template>
  <div class="min-h-screen bg-gradient-to-br from-pink-50 via-white to-purple-50">
    <div class="container mx-auto px-4 py-8">
      <!-- Header -->
      <div class="text-center mb-8">
        <h1 class="text-4xl font-bold text-gray-900 mb-4">Book Your Appointment</h1>
        <p class="text-lg text-gray-600">Schedule your nail art session</p>
      </div>

      <div class="max-w-4xl mx-auto">
        <form @submit.prevent="submitBooking" class="space-y-8">
          <!-- Service Selection -->
          <div class="bg-white rounded-2xl shadow-lg p-6">
            <h2 class="text-2xl font-semibold text-gray-900 mb-6 flex items-center">
              <Sparkles class="w-6 h-6 mr-2 text-pink-500" />
              Select Service(s) (Select up to 2)
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div
                v-for="service in services"
                :key="service.id"
                @click="selectService(service)"
                :class="[
                  'border-2 rounded-xl p-4 cursor-pointer transition-all duration-300 relative',
                  form.service_ids.includes(service.id)
                    ? 'border-pink-500 bg-pink-50 shadow-md'
                    : 'border-gray-200 hover:border-pink-300 hover:shadow-sm',
                  form.service_ids.length >= 2 && !form.service_ids.includes(service.id)
                    ? 'opacity-50 cursor-not-allowed'
                    : ''
                ]"
              >
                <div class="flex items-center space-x-4">
                  <img
                    :src="service.image_url"
                    :alt="service.name"
                    class="w-16 h-16 rounded-lg object-cover"
                  />
                  <div class="flex-1">
                    <h3 class="font-semibold text-gray-900">{{ service.name }}</h3>
                    <div class="text-sm text-gray-600 mb-1">{{ service.formatted_duration }}</div>
                    <div class="text-lg font-bold text-pink-600">{{ service.formatted_price }}</div>
                  </div>
                  <div v-if="form.service_ids.includes(service.id)" class="text-pink-500">
                    <CheckCircle class="w-6 h-6" />
                  </div>
                  
                  <!-- Service counter badge -->
                  <div 
                    v-if="form.service_ids.includes(service.id)" 
                    class="absolute -top-2 -right-2 w-6 h-6 bg-pink-500 text-white rounded-full flex items-center justify-center text-sm font-bold"
                  >
                    {{ form.service_ids.indexOf(service.id) + 1 }}
                  </div>
                </div>
              </div>
            </div>
            <div v-if="errors.service_ids" class="text-red-500 text-sm mt-2">
              {{ errors.service_ids }}
            </div>
            
            <!-- Selected services summary -->
            <div v-if="selectedServices.length > 0" class="mt-6 p-4 bg-pink-50 rounded-lg">
              <h4 class="font-medium text-gray-900 mb-2">Selected Services:</h4>
              <ul class="space-y-1">
                <li v-for="(service, index) in selectedServices" :key="service.id" class="flex justify-between text-sm">
                  <span>{{ index + 1 }}. {{ service.name }}</span>
                  <span class="font-medium">{{ service.formatted_price }}</span>
                </li>
              </ul>
            </div>
          </div>

          <!-- Date & Time Selection -->
          <div class="bg-white rounded-2xl shadow-lg p-6">
            <h2 class="text-2xl font-semibold text-gray-900 mb-6 flex items-center">
              <Calendar class="w-6 h-6 mr-2 text-pink-500" />
              Select Date & Time
            </h2>
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
              <!-- Date Picker -->
              <div>
                <Label for="appointment_date" class="text-sm font-medium text-gray-700 mb-2 block">
                  Appointment Date
                </Label>
                <Input
                  id="appointment_date"
                  v-model="form.appointment_date"
                  type="date"
                  :min="tomorrow"
                  @change="loadTimeSlots"
                  class="w-full rounded-xl border-gray-300"
                  :class="{ 'border-red-500': errors.appointment_date }"
                />
                <div v-if="errors.appointment_date" class="text-red-500 text-sm mt-1">
                  {{ errors.appointment_date }}
                </div>
              </div>

              <!-- Time Slots -->
              <div>
                <Label class="text-sm font-medium text-gray-700 mb-2 block">
                  Available Times
                </Label>
                
                <div v-if="loadingTimeSlots" class="flex items-center justify-center py-8">
                  <Loader class="w-6 h-6 animate-spin text-pink-500" />
                  <span class="ml-2 text-gray-600">Loading available times...</span>
                </div>
                
                <div v-else-if="timeSlots.length === 0 && form.appointment_date" class="text-center py-8 text-gray-500">
                  No available time slots for this date
                </div>
                
                <div v-else class="grid grid-cols-2 gap-2 max-h-64 overflow-y-auto">
                  <button
                    v-for="slot in timeSlots"
                    :key="slot.time"
                    type="button"
                    @click="selectTimeSlot(slot.time)"
                    :class="[
                      'p-3 rounded-lg text-sm font-medium transition-all duration-200',
                      form.start_time === slot.time
                        ? 'bg-pink-500 text-white shadow-md'
                        : 'bg-gray-100 text-gray-700 hover:bg-pink-100 hover:text-pink-700'
                    ]"
                  >
                    {{ slot.formatted_time }}
                  </button>
                </div>
                
                <div v-if="errors.start_time" class="text-red-500 text-sm mt-2">
                  {{ errors.start_time }}
                </div>
              </div>
            </div>
          </div>

          <!-- Client Information -->
          <div class="bg-white rounded-2xl shadow-lg p-6">
            <h2 class="text-2xl font-semibold text-gray-900 mb-6 flex items-center">
              <User class="w-6 h-6 mr-2 text-pink-500" />
              Your Information
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <Label for="client_name" class="text-sm font-medium text-gray-700 mb-2 block">
                  Full Name
                </Label>
                <Input
                  id="client_name"
                  v-model="form.client_name"
                  type="text"
                  placeholder="Enter your full name"
                  class="rounded-xl border-gray-300"
                  :class="{ 'border-red-500': errors.client_name }"
                />
                <div v-if="errors.client_name" class="text-red-500 text-sm mt-1">
                  {{ errors.client_name }}
                </div>
              </div>
              
              <div>
                <Label for="client_email" class="text-sm font-medium text-gray-700 mb-2 block">
                  Email Address
                </Label>
                <Input
                  id="client_email"
                  v-model="form.client_email"
                  type="email"
                  placeholder="Enter your email"
                  class="rounded-xl border-gray-300"
                  :class="{ 'border-red-500': errors.client_email }"
                />
                <div v-if="errors.client_email" class="text-red-500 text-sm mt-1">
                  {{ errors.client_email }}
                </div>
              </div>
              
              <div>
                <Label for="client_phone" class="text-sm font-medium text-gray-700 mb-2 block">
                  Phone Number
                </Label>
                <Input
                  id="client_phone"
                  v-model="form.client_phone"
                  type="tel"
                  placeholder="Enter your phone number"
                  class="rounded-xl border-gray-300"
                  :class="{ 'border-red-500': errors.client_phone }"
                />
                <div v-if="errors.client_phone" class="text-red-500 text-sm mt-1">
                  {{ errors.client_phone }}
                </div>
              </div>
              
              <div>
                <Label for="payment_method" class="text-sm font-medium text-gray-700 mb-2 block">
                  Payment Method
                </Label>
                <select
                  id="payment_method"
                  v-model="form.payment_method"
                  class="w-full rounded-xl border-gray-300 shadow-sm focus:border-pink-500 focus:ring-pink-500"
                  :class="{ 'border-red-500': errors.payment_method }"
                >
                  <option value="">Select payment method</option>
                  <option value="cash">Cash</option>
                  <option value="mpesa">M-Pesa</option>
                  <option value="card">Card</option>
                  <option value="bank_transfer">Bank Transfer</option>
                </select>
                <div v-if="errors.payment_method" class="text-red-500 text-sm mt-1">
                  {{ errors.payment_method }}
                </div>
              </div>
            </div>
            
            <div class="mt-6">
              <Label for="notes" class="text-sm font-medium text-gray-700 mb-2 block">
                Special Requests or Notes (Optional)
              </Label>
              <Textarea
                id="notes"
                v-model="form.notes"
                placeholder="Any special requests, allergies, or preferences..."
                rows="3"
                class="rounded-xl border-gray-300"
              />
              <div v-if="errors.notes" class="text-red-500 text-sm mt-1">
                {{ errors.notes }}
              </div>
            </div>
          </div>

          <!-- Booking Summary -->
          <div v-if="selectedServices.length > 0" class="bg-white rounded-2xl shadow-lg p-6">
            <h2 class="text-2xl font-semibold text-gray-900 mb-6 flex items-center">
              <Receipt class="w-6 h-6 mr-2 text-pink-500" />
              Booking Summary
            </h2>
            
            <div class="space-y-4">
              <div class="border-b border-gray-100 pb-4">
                <span class="text-gray-600 block mb-2">Selected Services</span>
                <div v-for="(service, index) in selectedServices" :key="service.id" class="flex justify-between items-center py-1">
                  <span class="font-medium">{{ index + 1 }}. {{ service.name }}</span>
                  <div class="text-right">
                    <div class="font-medium">{{ service.formatted_price }}</div>
                    <div class="text-sm text-gray-500">{{ service.formatted_duration }}</div>
                  </div>
                </div>
              </div>
              <div class="flex justify-between items-center py-2 border-b border-gray-100">
                <span class="text-gray-600">Total Duration</span>
                <span class="font-medium">{{ formattedTotalDuration }}</span>
              </div>
              <div v-if="form.appointment_date" class="flex justify-between items-center py-2 border-b border-gray-100">
                <span class="text-gray-600">Date</span>
                <span class="font-medium">{{ formatDate(form.appointment_date) }}</span>
              </div>
              <div v-if="form.start_time" class="flex justify-between items-center py-2 border-b border-gray-100">
                <span class="text-gray-600">Time</span>
                <span class="font-medium">{{ formatTime(form.start_time) }}</span>
              </div>
              <div class="flex justify-between items-center py-2 text-lg font-semibold">
                <span>Total Amount</span>
                <span class="text-pink-600">{{ formattedTotalPrice }}</span>
              </div>
            </div>
          </div>

          <!-- Submit Button -->
          <div class="flex justify-center">
            <Button
              type="submit"
              :disabled="processing || !canSubmit"
              class="bg-gradient-to-r from-pink-500 to-purple-500 hover:from-pink-600 hover:to-purple-600 text-white px-8 py-4 rounded-xl text-lg font-medium min-w-[200px] disabled:opacity-50"
            >
              <Loader v-if="processing" class="w-5 h-5 mr-2 animate-spin" />
              <CheckCircle v-else class="w-5 h-5 mr-2" />
              {{ processing ? 'Booking...' : 'Confirm Booking' }}
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
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'
import { 
  Calendar, 
  User, 
  Sparkles, 
  CheckCircle, 
  Receipt, 
  Loader 
} from 'lucide-vue-next'

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

interface TimeSlot {
  time: string
  end_time: string
  formatted_time: string
  available: boolean
}

interface Props {
  services: Service[]
  errors?: Record<string, string>
}

const props = defineProps<Props>()

const form = useForm({
  service_ids: [] as number[],
  client_name: '',
  client_email: '',
  client_phone: '',
  appointment_date: '',
  start_time: '',
  notes: '',
  payment_method: ''
})

const timeSlots = ref<TimeSlot[]>([])
const loadingTimeSlots = ref(false)
const processing = ref(false)

const selectedServices = computed(() => {
  return props.services.filter(service => form.service_ids.includes(service.id))
})

const totalPrice = computed(() => {
  return selectedServices.value.reduce((total, service) => total + service.price, 0)
})

const totalDuration = computed(() => {
  return selectedServices.value.reduce((total, service) => total + service.duration_minutes, 0)
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

const tomorrow = computed(() => {
  const date = new Date()
  date.setDate(date.getDate() + 1)
  return date.toISOString().split('T')[0]
})

const canSubmit = computed(() => {
  return form.service_ids.length > 0 && 
         form.appointment_date && 
         form.start_time && 
         form.client_name && 
         form.client_email && 
         form.client_phone && 
         form.payment_method
})

const selectService = (service: Service) => {
  const index = form.service_ids.indexOf(service.id)
  
  if (index > -1) {
    // Remove service if already selected
    form.service_ids.splice(index, 1)
  } else {
    // Add service if not selected and under limit
    if (form.service_ids.length < 2) {
      form.service_ids.push(service.id)
    }
  }
  
  if (form.appointment_date && form.service_ids.length > 0) {
    loadTimeSlots()
  }
}

const selectTimeSlot = (time: string) => {
  form.start_time = time
}

const loadTimeSlots = async () => {
  if (form.service_ids.length === 0 || !form.appointment_date) return
  
  loadingTimeSlots.value = true
  timeSlots.value = []
  form.start_time = ''
  
  try {
    const params = new URLSearchParams({
      date: form.appointment_date
    })
    
    form.service_ids.forEach(id => {
      params.append('service_ids[]', id.toString())
    })
    
    const response = await fetch(`${route('booking.time-slots')}?${params}`, {
      method: 'GET',
      headers: {
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      }
    })
    
    const data = await response.json()
    
    if (data.success) {
      timeSlots.value = data.timeSlots
    }
  } catch (error) {
    console.error('Error loading time slots:', error)
  } finally {
    loadingTimeSlots.value = false
  }
}

const submitBooking = () => {
  processing.value = true
  form.post(route('booking.store'), {
    onFinish: () => {
      processing.value = false
    }
  })
}

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

const formatTime = (timeString: string) => {
  const [hours, minutes] = timeString.split(':')
  const time = new Date()
  time.setHours(parseInt(hours), parseInt(minutes))
  return time.toLocaleTimeString('en-US', {
    hour: 'numeric',
    minute: '2-digit',
    hour12: true
  })
}

onMounted(() => {
  // Pre-select service if provided in URL
  const urlParams = new URLSearchParams(window.location.search)
  const serviceId = urlParams.get('service_id')
  if (serviceId) {
    const service = props.services.find(s => s.id === parseInt(serviceId))
    if (service) {
      form.service_ids.push(service.id)
    }
  }
})
</script>