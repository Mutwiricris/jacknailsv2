<template>
  <div class="min-h-screen bg-gradient-to-br from-pink-50 via-white to-purple-50">
    <div class="container mx-auto px-4 py-8">
      <!-- Header -->
      <div class="text-center mb-8">
        <h1 class="text-4xl font-bold text-gray-900 mb-4">Book Your Appointment</h1>
        <p class="text-lg text-gray-600">Step 3: Tell us about yourself</p>
      </div>

      <div class="max-w-4xl mx-auto">
        <!-- Progress Indicator -->
        <BookingProgress :current-step="currentStep" />

        <form @submit.prevent="nextStep" class="space-y-8">
          <!-- Appointment Summary -->
          <div class="bg-white rounded-2xl shadow-lg p-6">
            <h3 class="text-xl font-semibold text-gray-900 mb-4 flex items-center">
              <Calendar class="w-5 h-5 mr-2 text-green-500" />
              Your Appointment
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div class="space-y-3">
                <div class="flex justify-between items-center">
                  <span class="text-gray-600">Services</span>
                  <span class="font-medium">{{ selectedServices.length }} selected</span>
                </div>
                <div v-for="(service, index) in selectedServices" :key="service.id" class="text-sm">
                  <div class="flex justify-between items-center py-1">
                    <span class="text-gray-700">{{ index + 1 }}. {{ service.name }}</span>
                    <span class="text-pink-600 font-medium">{{ service.formatted_price }}</span>
                  </div>
                </div>
              </div>
              
              <div class="space-y-3">
                <div class="flex justify-between items-center">
                  <span class="text-gray-600">Date & Time</span>
                  <span class="font-medium">{{ formatAppointmentDateTime }}</span>
                </div>
                <div class="flex justify-between items-center">
                  <span class="text-gray-600">Duration</span>
                  <span class="font-medium">{{ formattedTotalDuration }}</span>
                </div>
                <div class="flex justify-between items-center text-lg font-semibold border-t pt-2">
                  <span>Total</span>
                  <span class="text-pink-600">{{ formattedTotalPrice }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Client Information Form -->
          <div class="bg-white rounded-2xl shadow-lg p-6">
            <h3 class="text-xl font-semibold text-gray-900 mb-6 flex items-center">
              <User class="w-5 h-5 mr-2 text-pink-500" />
              Contact Information
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Full Name -->
              <div class="md:col-span-2">
                <Label for="client_name" class="text-sm font-medium text-gray-700 mb-2 block">
                  Full Name *
                </Label>
                <Input
                  id="client_name"
                  v-model="form.client_name"
                  type="text"
                  placeholder="Enter your full name"
                  class="w-full rounded-xl border-gray-300 focus:border-pink-500 focus:ring-pink-500"
                  :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-500': errors?.client_name }"
                />
                <div v-if="errors?.client_name" class="text-red-500 text-sm mt-1 flex items-center">
                  <AlertCircle class="w-4 h-4 mr-1" />
                  {{ errors.client_name }}
                </div>
              </div>

              <!-- Email -->
              <div>
                <Label for="client_email" class="text-sm font-medium text-gray-700 mb-2 block">
                  Email Address *
                </Label>
                <Input
                  id="client_email"
                  v-model="form.client_email"
                  type="email"
                  placeholder="your.email@example.com"
                  class="w-full rounded-xl border-gray-300 focus:border-pink-500 focus:ring-pink-500"
                  :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-500': errors?.client_email }"
                />
                <div v-if="errors?.client_email" class="text-red-500 text-sm mt-1 flex items-center">
                  <AlertCircle class="w-4 h-4 mr-1" />
                  {{ errors.client_email }}
                </div>
                <p class="text-xs text-gray-500 mt-1">We'll send your booking confirmation here</p>
              </div>

              <!-- Phone Number -->
              <div>
                <Label for="client_phone" class="text-sm font-medium text-gray-700 mb-2 block">
                  Phone Number *
                </Label>
                <Input
                  id="client_phone"
                  v-model="form.client_phone"
                  type="tel"
                  placeholder="0701 234 567"
                  class="w-full rounded-xl border-gray-300 focus:border-pink-500 focus:ring-pink-500"
                  :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-500': errors?.client_phone }"
                  @input="formatPhoneNumber"
                />
                <div v-if="errors?.client_phone" class="text-red-500 text-sm mt-1 flex items-center">
                  <AlertCircle class="w-4 h-4 mr-1" />
                  {{ errors.client_phone }}
                </div>
                <p class="text-xs text-gray-500 mt-1">For appointment reminders and updates</p>
              </div>
            </div>

            <!-- Special Notes -->
            <div class="mt-6">
              <Label for="notes" class="text-sm font-medium text-gray-700 mb-2 block">
                Special Requests or Notes (Optional)
              </Label>
              <Textarea
                id="notes"
                v-model="form.notes"
                placeholder="Any allergies, preferences, or special requests you'd like us to know about..."
                rows="4"
                class="w-full rounded-xl border-gray-300 focus:border-pink-500 focus:ring-pink-500"
                :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-500': errors?.notes }"
              />
              <div v-if="errors?.notes" class="text-red-500 text-sm mt-1 flex items-center">
                <AlertCircle class="w-4 h-4 mr-1" />
                {{ errors.notes }}
              </div>
              <p class="text-xs text-gray-500 mt-1">Help us provide the best service for you</p>
            </div>
          </div>

          <!-- Account Benefits (for guest users) -->
          <div v-if="!user" class="bg-gradient-to-r from-blue-50 to-purple-50 rounded-2xl shadow-lg p-6 border border-blue-200">
            <h3 class="text-xl font-semibold text-gray-900 mb-4 flex items-center">
              <Crown class="w-5 h-5 mr-2 text-purple-500" />
              Create an Account & Save
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div class="space-y-3">
                <h4 class="font-medium text-gray-800">Benefits of having an account:</h4>
                <ul class="space-y-2 text-sm text-gray-600">
                  <li class="flex items-center">
                    <CheckCircle class="w-4 h-4 mr-2 text-green-500" />
                    View booking history
                  </li>
                  <li class="flex items-center">
                    <CheckCircle class="w-4 h-4 mr-2 text-green-500" />
                    Quick rebooking
                  </li>
                  <li class="flex items-center">
                    <CheckCircle class="w-4 h-4 mr-2 text-green-500" />
                    Exclusive offers
                  </li>
                  <li class="flex items-center">
                    <CheckCircle class="w-4 h-4 mr-2 text-green-500" />
                    Appointment reminders
                  </li>
                </ul>
              </div>
              
              <div class="flex flex-col justify-center">
                <p class="text-sm text-gray-600 mb-4">
                  We'll automatically create an account for you using the email address above.
                </p>
                <div class="flex items-center space-x-2">
                  <input
                    id="create_account"
                    v-model="createAccount"
                    type="checkbox"
                    class="rounded border-gray-300 text-purple-600 focus:ring-purple-500"
                  />
                  <Label for="create_account" class="text-sm font-medium text-gray-700 cursor-pointer">
                    Yes, create my account (recommended)
                  </Label>
                </div>
              </div>
            </div>
          </div>

          <!-- Existing User Info -->
          <div v-else class="bg-green-50 border border-green-200 rounded-2xl p-6">
            <div class="flex items-center">
              <CheckCircle class="w-6 h-6 text-green-500 mr-3" />
              <div>
                <h3 class="font-semibold text-gray-900">Welcome back, {{ user.name }}!</h3>
                <p class="text-sm text-gray-600">Your account information will be used for this booking.</p>
              </div>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="flex justify-between items-center">
            <Button
              type="button"
              variant="outline"
              @click="$inertia.visit(route('booking.stepper.step', { step: 2 }))"
              class="px-6 py-3 rounded-xl"
            >
              <ArrowLeft class="w-4 h-4 mr-2" />
              Back to Date & Time
            </Button>

            <Button
              type="submit"
              :disabled="!isFormValid"
              class="bg-gradient-to-r from-pink-500 to-purple-500 hover:from-pink-600 hover:to-purple-600 text-white px-8 py-3 rounded-xl disabled:opacity-50"
            >
              Continue to Payment
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
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'
import BookingProgress from '@/components/BookingProgress.vue'
import { useToast } from '@/components/ui/toast'
import { 
  Calendar,
  User,
  Crown,
  CheckCircle,
  ArrowLeft,
  ArrowRight,
  AlertCircle
} from 'lucide-vue-next'

interface Service {
  id: number
  name: string
  price: number
  duration_minutes: number
  formatted_price: string
  formatted_duration: string
}

interface User {
  id: number
  name: string
  email: string
}

interface Props {
  currentStep: number
  bookingData: any
  selectedServices: Service[]
  user?: User | null
  errors?: Record<string, string>
}

const props = defineProps<Props>()
const { success, error, info, warning } = useToast()

const createAccount = ref(true) // Default to creating account

const form = useForm({
  client_name: props.bookingData.client_name || props.user?.name || '',
  client_email: props.bookingData.client_email || props.user?.email || '',
  client_phone: props.bookingData.client_phone || '',
  notes: props.bookingData.notes || ''
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

const formatAppointmentDateTime = computed(() => {
  if (!props.bookingData.appointment_date || !props.bookingData.start_time) return ''
  
  const date = new Date(props.bookingData.appointment_date)
  const [hours, minutes] = props.bookingData.start_time.split(':')
  
  const dateStr = date.toLocaleDateString('en-US', {
    weekday: 'short',
    month: 'short',
    day: 'numeric'
  })
  
  const time = new Date()
  time.setHours(parseInt(hours), parseInt(minutes))
  const timeStr = time.toLocaleTimeString('en-US', {
    hour: 'numeric',
    minute: '2-digit',
    hour12: true
  })
  
  return `${dateStr} at ${timeStr}`
})

const isFormValid = computed(() => {
  return form.client_name.trim().length >= 2 &&
         form.client_email.trim().length > 0 &&
         form.client_phone.trim().length >= 10 &&
         /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.client_email)
})

const formatPhoneNumber = (event: Event) => {
  const target = event.target as HTMLInputElement
  let value = target.value.replace(/\D/g, '') // Remove non-digits
  
  // Auto-format Kenyan phone numbers
  if (value.startsWith('254')) {
    // International format
    value = value.substring(0, 12)
    if (value.length > 3) {
      value = value.replace(/^254(\d{3})(\d{3})(\d{3})/, '+254 $1 $2 $3')
    }
  } else if (value.startsWith('0')) {
    // Local format
    value = value.substring(0, 10)
    if (value.length > 4) {
      value = value.replace(/^0(\d{3})(\d{3})(\d{3})/, '0$1 $2 $3')
    }
  }
  
  form.client_phone = value
}

const nextStep = () => {
  if (!isFormValid.value) {
    warning('Please fill in all required fields correctly', 'Form Incomplete')
    return
  }
  
  success('Contact information saved!', 'Ready for Payment')
  form.post(route('booking.stepper.update', { step: 3 }))
}

// Show welcome message and pre-fill info for existing users
onMounted(() => {
  if (props.user) {
    info(`Welcome back, ${props.user.name}! Your details are pre-filled.`, 'Existing User')
  } else {
    info('Please provide your contact information for booking confirmation', 'Contact Info')
  }
})
</script>

<style scoped>
/* Custom focus styles for form inputs */
.focus\:ring-pink-500:focus {
  --tw-ring-color: rgb(236 72 153 / 0.5);
}

.focus\:border-pink-500:focus {
  border-color: rgb(236 72 153);
}
</style>