<template>
  <div class="min-h-screen bg-gradient-to-br from-pink-50 via-white to-purple-50">
    <div class="container mx-auto px-4 py-8">
      <!-- Header -->
      <div class="text-center mb-8">
        <h1 class="text-4xl font-bold text-gray-900 mb-4">Book Your Appointment</h1>
        <p class="text-lg text-gray-600">Step 2: Choose your preferred date and time</p>
      </div>

      <div class="max-w-6xl mx-auto">
        <!-- Progress Indicator -->
        <BookingProgress :current-step="currentStep" />

        <form @submit.prevent="nextStep" class="space-y-8">
          <!-- Selected Services Summary -->
          <div class="bg-white rounded-2xl shadow-lg p-6">
            <h3 class="text-xl font-semibold text-gray-900 mb-4 flex items-center">
              <Sparkles class="w-5 h-5 mr-2 text-green-500" />
              Selected Services
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div
                v-for="(service, index) in selectedServices"
                :key="service.id"
                class="flex items-center space-x-4 p-4 bg-pink-50 rounded-lg"
              >
                <div class="w-8 h-8 bg-pink-500 text-white rounded-full flex items-center justify-center text-sm font-bold">
                  {{ index + 1 }}
                </div>
                <div class="flex-1">
                  <h4 class="font-semibold text-gray-900">{{ service.name }}</h4>
                  <div class="flex items-center space-x-4 text-sm text-gray-600">
                    <span>{{ service.formatted_duration }}</span>
                    <span class="font-medium text-pink-600">{{ service.formatted_price }}</span>
                  </div>
                </div>
              </div>
            </div>
            <div class="mt-4 pt-4 border-t border-gray-200 flex justify-between items-center">
              <span class="font-semibold text-gray-900">Total Duration</span>
              <span class="text-lg font-bold text-pink-600">{{ formattedTotalDuration }}</span>
            </div>
          </div>

          <!-- Date Selection -->
          <div class="bg-white rounded-2xl shadow-lg p-6">
            <h3 class="text-xl font-semibold text-gray-900 mb-6 flex items-center">
              <Calendar class="w-5 h-5 mr-2 text-pink-500" />
              Select Date
            </h3>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-7 gap-3">
              <div
                v-for="date in availableDates"
                :key="date.date"
                @click="selectDate(date.date)"
                :class="[
                  'p-4 rounded-xl border-2 cursor-pointer transition-all duration-300 text-center',
                  selectedDate === date.date
                    ? 'border-pink-500 bg-pink-50 shadow-md transform scale-105'
                    : 'border-gray-200 hover:border-pink-300 hover:shadow-sm',
                  !date.has_availability
                    ? 'opacity-50 cursor-not-allowed'
                    : ''
                ]"
              >
                <div class="text-sm text-gray-500 mb-1">{{ date.day_name }}</div>
                <div class="text-lg font-bold text-gray-900">{{ date.short_date }}</div>
                <div v-if="date.is_today" class="text-xs text-blue-600 font-medium mt-1">Today</div>
                <div v-else-if="date.is_tomorrow" class="text-xs text-green-600 font-medium mt-1">Tomorrow</div>
                <div v-if="!date.has_availability" class="text-xs text-red-500 mt-1">Fully Booked</div>
              </div>
            </div>

            <div v-if="errors?.appointment_date" class="mt-4 text-red-500 text-sm flex items-center">
              <AlertCircle class="w-4 h-4 mr-2" />
              {{ errors.appointment_date }}
            </div>
          </div>

          <!-- Time Selection -->
          <div v-if="selectedDate" class="bg-white rounded-2xl shadow-lg p-6">
            <h3 class="text-xl font-semibold text-gray-900 mb-6 flex items-center">
              <Clock class="w-5 h-5 mr-2 text-pink-500" />
              Select Time
            </h3>

            <!-- Loading State -->
            <div v-if="loadingTimeSlots" class="flex items-center justify-center py-12">
              <div class="text-center">
                <Loader class="w-8 h-8 animate-spin text-pink-500 mx-auto mb-4" />
                <p class="text-gray-600">Loading available times...</p>
              </div>
            </div>

            <!-- No Slots Available -->
            <div v-else-if="timeSlots.length === 0" class="text-center py-12">
              <CalendarX class="w-12 h-12 text-gray-400 mx-auto mb-4" />
              <p class="text-gray-600">No available time slots for this date</p>
              <p class="text-sm text-gray-500 mt-2">Please select a different date</p>
            </div>

            <!-- Time Slots by Period -->
            <div v-else class="space-y-8">
              <div
                v-for="period in timeSlots"
                :key="period.period"
                class="space-y-4"
              >
                <h4 class="text-lg font-semibold text-gray-800 flex items-center">
                  <Sun v-if="period.period === 'Morning'" class="w-5 h-5 mr-2 text-yellow-500" />
                  <CloudSun v-else-if="period.period === 'Afternoon'" class="w-5 h-5 mr-2 text-orange-500" />
                  <Moon v-else class="w-5 h-5 mr-2 text-blue-500" />
                  {{ period.period }}
                  <span class="ml-2 text-sm text-gray-500 font-normal">({{ period.slots.length }} slots available)</span>
                </h4>
                
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">
                  <button
                    v-for="slot in period.slots"
                    :key="slot.id"
                    type="button"
                    @click="selectTimeSlot(slot)"
                    :class="[
                      'p-3 rounded-lg text-sm font-medium transition-all duration-200 border-2',
                      selectedTime === slot.time
                        ? 'bg-pink-500 text-white border-pink-500 shadow-md transform scale-105'
                        : 'bg-gray-50 text-gray-700 border-gray-200 hover:bg-pink-100 hover:border-pink-300 hover:text-pink-700',
                      slot.is_peak_time
                        ? 'ring-2 ring-yellow-300 ring-opacity-50'
                        : ''
                    ]"
                  >
                    <div>{{ slot.formatted_time }}</div>
                    <div v-if="slot.is_peak_time" class="text-xs opacity-75 mt-1">
                      Peak Time
                    </div>
                  </button>
                </div>
              </div>
            </div>

            <div v-if="errors?.start_time" class="mt-4 text-red-500 text-sm flex items-center">
              <AlertCircle class="w-4 h-4 mr-2" />
              {{ errors.start_time }}
            </div>
          </div>

          <!-- Booking Summary -->
          <div v-if="selectedDate && selectedTime" class="bg-white rounded-2xl shadow-lg p-6">
            <h3 class="text-xl font-semibold text-gray-900 mb-4 flex items-center">
              <CheckCircle class="w-5 h-5 mr-2 text-green-500" />
              Appointment Summary
            </h3>
            
            <div class="space-y-4">
              <div class="flex justify-between items-center py-2 border-b border-gray-100">
                <span class="text-gray-600">Date</span>
                <span class="font-medium">{{ formatSelectedDate }}</span>
              </div>
              <div class="flex justify-between items-center py-2 border-b border-gray-100">
                <span class="text-gray-600">Time</span>
                <span class="font-medium">{{ formatSelectedTime }}</span>
              </div>
              <div class="flex justify-between items-center py-2 border-b border-gray-100">
                <span class="text-gray-600">Duration</span>
                <span class="font-medium">{{ formattedTotalDuration }}</span>
              </div>
              <div class="flex justify-between items-center py-2 text-lg font-semibold">
                <span>Total Amount</span>
                <span class="text-pink-600">{{ formattedTotalPrice }}</span>
              </div>
            </div>
          </div>

          <!-- Error Messages -->
          <div v-if="errors?.availability" class="bg-red-50 border border-red-200 rounded-lg p-4">
            <div class="flex items-center">
              <AlertCircle class="w-5 h-5 text-red-500 mr-2" />
              <span class="text-red-700">{{ errors.availability }}</span>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="flex justify-between items-center">
            <Button
              type="button"
              variant="outline"
              @click="$inertia.visit(route('booking.stepper.step', { step: 1 }))"
              class="px-6 py-3 rounded-xl"
            >
              <ArrowLeft class="w-4 h-4 mr-2" />
              Back to Services
            </Button>

            <Button
              type="submit"
              :disabled="!selectedDate || !selectedTime || loadingTimeSlots"
              class="bg-gradient-to-r from-pink-500 to-purple-500 hover:from-pink-600 hover:to-purple-600 text-white px-8 py-3 rounded-xl disabled:opacity-50"
            >
              Continue to Contact Info
              <ArrowRight class="w-4 h-4 ml-2" />
            </Button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import BookingProgress from '@/components/BookingProgress.vue'
import { useToast } from '@/components/ui/toast'
import { 
  Calendar,
  Clock,
  Sparkles,
  CheckCircle,
  ArrowLeft,
  ArrowRight,
  AlertCircle,
  Loader,
  CalendarX,
  Sun,
  CloudSun,
  Moon
} from 'lucide-vue-next'

interface Service {
  id: number
  name: string
  price: number
  duration_minutes: number
  formatted_price: string
  formatted_duration: string
}

interface AvailableDate {
  date: string
  formatted_date: string
  short_date: string
  day_name: string
  is_today: boolean
  is_tomorrow: boolean
  has_availability: boolean
}

interface TimeSlot {
  id: number
  time: string
  end_time: string
  formatted_time: string
  formatted_end_time: string
  available: boolean
  period: string
  is_peak_time: boolean
}

interface TimeSlotPeriod {
  period: string
  slots: TimeSlot[]
}

interface Props {
  currentStep: number
  bookingData: any
  selectedServices: Service[]
  availableDates: AvailableDate[]
  errors?: Record<string, string>
}

const props = defineProps<Props>()
const { success, error, info, warning } = useToast()

const selectedDate = ref<string>(props.bookingData.appointment_date || '')
const selectedTime = ref<string>(props.bookingData.start_time || '')
const selectedTimeSlot = ref<TimeSlot | null>(null)
const timeSlots = ref<TimeSlotPeriod[]>([])
const loadingTimeSlots = ref(false)

const form = useForm({
  appointment_date: selectedDate.value,
  start_time: selectedTime.value,
  end_time: props.bookingData.end_time || '',
  time_slot_id: props.bookingData.time_slot_id || null
})

const totalPrice = computed(() => {
  return props.selectedServices.reduce((total, service) => total + service.price, 0)
})

const totalDuration = computed(() => {
  return props.selectedServices.reduce((total, service) => total + service.duration_minutes, 0)
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

const formatSelectedDate = computed(() => {
  if (!selectedDate.value) return ''
  
  const date = new Date(selectedDate.value)
  return date.toLocaleDateString('en-US', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
})

const formatSelectedTime = computed(() => {
  if (!selectedTime.value) return ''
  
  const [hours, minutes] = selectedTime.value.split(':')
  const time = new Date()
  time.setHours(parseInt(hours), parseInt(minutes))
  return time.toLocaleTimeString('en-US', {
    hour: 'numeric',
    minute: '2-digit',
    hour12: true
  })
})

const selectDate = (date: string) => {
  selectedDate.value = date
  selectedTime.value = '' // Reset time when date changes
  selectedTimeSlot.value = null
  form.appointment_date = date
  form.start_time = ''
  form.end_time = ''
  form.time_slot_id = null
  
  const selectedDateObj = props.availableDates.find(d => d.date === date)
  if (selectedDateObj) {
    if (selectedDateObj.is_today) {
      info('Loading today\'s available times...', 'Selected Date')
    } else if (selectedDateObj.is_tomorrow) {
      info('Loading tomorrow\'s available times...', 'Selected Date')
    } else {
      success(`Loading times for ${selectedDateObj.day_name}...`, 'Date Selected')
    }
  }
  
  loadTimeSlots()
}

const selectTimeSlot = (slot: TimeSlot) => {
  selectedTime.value = slot.time
  selectedTimeSlot.value = slot
  form.start_time = slot.time
  form.end_time = slot.end_time
  form.time_slot_id = slot.id
  
  const [hours, minutes] = slot.time.split(':')
  const timeObj = new Date()
  timeObj.setHours(parseInt(hours), parseInt(minutes))
  const formattedTime = timeObj.toLocaleTimeString('en-US', {
    hour: 'numeric',
    minute: '2-digit',
    hour12: true
  })
  
  success(`Time slot ${formattedTime} selected!`, 'Ready to Continue')
}

const loadTimeSlots = async () => {
  if (!selectedDate.value) return
  
  loadingTimeSlots.value = true
  timeSlots.value = []
  
  try {
    const serviceIds = props.selectedServices.map(service => service.id)
    const params = new URLSearchParams({
      date: selectedDate.value
    })
    
    serviceIds.forEach(id => {
      params.append('service_ids[]', id.toString())
    })
    
    const response = await fetch(`${route('booking.stepper.time-slots')}?${params}`, {
      method: 'GET',
      headers: {
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      }
    })
    
    const data = await response.json()
    
    if (data.success) {
      timeSlots.value = data.timeSlots
      if (data.timeSlots.length === 0) {
        warning('No available time slots for this date. Please try another date.', 'Fully Booked')
      } else {
        const totalSlots = data.timeSlots.reduce((total: number, period: any) => total + period.slots.length, 0)
        info(`${totalSlots} time slots available`, 'Times Loaded')
      }
    } else {
      error(data.message || 'Failed to load time slots', 'Loading Error')
    }
  } catch (error) {
    error('Unable to load available times. Please try again.', 'Network Error')
  } finally {
    loadingTimeSlots.value = false
  }
}

const nextStep = () => {
  if (!selectedDate.value || !selectedTime.value) {
    return
  }
  
  form.post(route('booking.stepper.update', { step: 2 }))
}

// Load time slots if date is already selected
onMounted(() => {
  if (selectedDate.value) {
    loadTimeSlots()
  }
})
</script>

<style scoped>
/* Custom styles for enhanced visual feedback */
.ring-yellow-300 {
  --tw-ring-color: rgb(253 224 71 / 0.5);
}
</style>