<template>
  <div class="min-h-screen bg-gradient-to-br from-pink-50 via-white to-purple-50">
    <div class="container mx-auto px-4 py-8">
      <!-- Header -->
      <div class="text-center mb-8">
        <h1 class="text-4xl font-bold text-gray-900 mb-4">Book Your Appointment</h1>
        <p class="text-lg text-gray-600">Step 5: Review and confirm your booking</p>
      </div>

      <div class="max-w-4xl mx-auto">
        <!-- Progress Indicator -->
        <BookingProgress :current-step="currentStep" />

        <!-- Confirmation Card -->
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden mb-8">
          <!-- Header with Total -->
          <div class="bg-gradient-to-r from-pink-500 to-purple-500 p-6 text-white">
            <div class="flex items-center justify-between">
              <div>
                <h2 class="text-2xl font-bold mb-1">Almost Done!</h2>
                <p class="opacity-90">Please review your booking details before confirming</p>
              </div>
              <div class="text-right">
                <div class="text-3xl font-bold">{{ formattedTotalAmount }}</div>
                <div class="opacity-90">{{ formattedTotalDuration }}</div>
              </div>
            </div>
          </div>

          <div class="p-6 space-y-8">
            <!-- Services Section -->
            <div>
              <h3 class="text-xl font-semibold text-gray-900 mb-4 flex items-center">
                <Sparkles class="w-6 h-6 mr-3 text-pink-500" />
                Selected Services
              </h3>
              <div class="space-y-3">
                <div
                  v-for="(service, index) in selectedServices"
                  :key="service.id"
                  class="flex items-center justify-between p-4 bg-pink-50 rounded-xl"
                >
                  <div class="flex items-center space-x-4">
                    <div class="w-10 h-10 bg-pink-500 text-white rounded-full flex items-center justify-center font-bold">
                      {{ index + 1 }}
                    </div>
                    <div>
                      <h4 class="font-semibold text-gray-900">{{ service.name }}</h4>
                      <div class="text-sm text-gray-600">{{ service.formatted_duration }}</div>
                    </div>
                  </div>
                  <div class="text-lg font-bold text-pink-600">{{ service.formatted_price }}</div>
                </div>
              </div>
            </div>

            <!-- Appointment Details Section -->
            <div>
              <h3 class="text-xl font-semibold text-gray-900 mb-4 flex items-center">
                <Calendar class="w-6 h-6 mr-3 text-pink-500" />
                Appointment Details
              </h3>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-4">
                  <div class="flex items-center space-x-4 p-4 bg-gray-50 rounded-xl">
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                      <CalendarDays class="w-6 h-6 text-blue-600" />
                    </div>
                    <div>
                      <div class="text-sm text-gray-500 font-medium">Date & Time</div>
                      <div class="text-lg font-semibold text-gray-900">{{ formatAppointmentDateTime }}</div>
                    </div>
                  </div>
                </div>
                
                <div class="space-y-4">
                  <div class="flex items-center space-x-4 p-4 bg-gray-50 rounded-xl">
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                      <Clock class="w-6 h-6 text-green-600" />
                    </div>
                    <div>
                      <div class="text-sm text-gray-500 font-medium">Total Duration</div>
                      <div class="text-lg font-semibold text-gray-900">{{ formattedTotalDuration }}</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Client Information Section -->
            <div>
              <h3 class="text-xl font-semibold text-gray-900 mb-4 flex items-center">
                <User class="w-6 h-6 mr-3 text-pink-500" />
                Your Information
              </h3>
              <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="flex items-center space-x-3 p-4 bg-gray-50 rounded-xl">
                  <UserCheck class="w-5 h-5 text-gray-500" />
                  <div>
                    <div class="text-sm text-gray-500 font-medium">Name</div>
                    <div class="font-semibold text-gray-900">{{ bookingData.client_name }}</div>
                  </div>
                </div>
                
                <div class="flex items-center space-x-3 p-4 bg-gray-50 rounded-xl">
                  <Mail class="w-5 h-5 text-gray-500" />
                  <div>
                    <div class="text-sm text-gray-500 font-medium">Email</div>
                    <div class="font-semibold text-gray-900 text-sm">{{ bookingData.client_email }}</div>
                  </div>
                </div>
                
                <div class="flex items-center space-x-3 p-4 bg-gray-50 rounded-xl">
                  <Phone class="w-5 h-5 text-gray-500" />
                  <div>
                    <div class="text-sm text-gray-500 font-medium">Phone</div>
                    <div class="font-semibold text-gray-900">{{ bookingData.client_phone }}</div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Special Notes Section -->
            <div v-if="bookingData.notes">
              <h3 class="text-xl font-semibold text-gray-900 mb-4 flex items-center">
                <FileText class="w-6 h-6 mr-3 text-pink-500" />
                Special Notes
              </h3>
              <div class="p-4 bg-blue-50 border border-blue-200 rounded-xl">
                <p class="text-gray-700 leading-relaxed">{{ bookingData.notes }}</p>
              </div>
            </div>

            <!-- Payment Method Section -->
            <div>
              <h3 class="text-xl font-semibold text-gray-900 mb-4 flex items-center">
                <CreditCard class="w-6 h-6 mr-3 text-pink-500" />
                Payment Method
              </h3>
              <div class="flex items-center space-x-4 p-4 bg-gray-50 rounded-xl">
                <div 
                  :class="[
                    'w-12 h-12 rounded-lg flex items-center justify-center',
                    paymentMethodConfig.bgColor
                  ]"
                >
                  <component :is="paymentMethodConfig.icon" :class="['w-6 h-6', paymentMethodConfig.iconColor]" />
                </div>
                <div>
                  <div class="text-sm text-gray-500 font-medium">Payment Method</div>
                  <div class="font-semibold text-gray-900">{{ paymentMethodConfig.name }}</div>
                  <div class="text-sm text-gray-600">{{ paymentMethodConfig.description }}</div>
                </div>
              </div>
            </div>

            <!-- Total Summary -->
            <div class="border-t border-gray-200 pt-6">
              <div class="flex justify-between items-center text-2xl font-bold">
                <span>Total Amount</span>
                <span class="text-pink-600">{{ formattedTotalAmount }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Important Information -->
        <div class="bg-blue-50 border border-blue-200 rounded-2xl p-6 mb-8">
          <h3 class="text-lg font-semibold text-blue-900 mb-3 flex items-center">
            <Info class="w-5 h-5 mr-2" />
            Important Information
          </h3>
          <ul class="space-y-2 text-blue-800">
            <li class="flex items-start space-x-2">
              <div class="w-1.5 h-1.5 bg-blue-600 rounded-full mt-2 flex-shrink-0"></div>
              <span>Please arrive 10 minutes before your appointment time</span>
            </li>
            <li class="flex items-start space-x-2">
              <div class="w-1.5 h-1.5 bg-blue-600 rounded-full mt-2 flex-shrink-0"></div>
              <span>Cancellations must be made at least 24 hours in advance</span>
            </li>
            <li class="flex items-start space-x-2">
              <div class="w-1.5 h-1.5 bg-blue-600 rounded-full mt-2 flex-shrink-0"></div>
              <span>Please remove any existing nail polish before your appointment</span>
            </li>
            <li class="flex items-start space-x-2">
              <div class="w-1.5 h-1.5 bg-blue-600 rounded-full mt-2 flex-shrink-0"></div>
              <span>A confirmation email will be sent to {{ bookingData.client_email }}</span>
            </li>
          </ul>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row gap-4 justify-between items-center">
          <Button
            type="button"
            variant="outline"
            @click="$inertia.visit(route('booking.stepper.step', { step: 4 }))"
            class="w-full sm:w-auto px-6 py-3 rounded-xl"
          >
            <ArrowLeft class="w-4 h-4 mr-2" />
            Back to Payment
          </Button>

          <div class="flex flex-col sm:flex-row gap-4">
            <Button
              type="button"
              variant="outline"
              @click="$inertia.visit(route('home'))"
              class="w-full sm:w-auto px-6 py-3 rounded-xl"
            >
              <X class="w-4 h-4 mr-2" />
              Cancel Booking
            </Button>

            <Button
              @click="completeBooking"
              :disabled="processing"
              class="w-full sm:w-auto bg-gradient-to-r from-green-500 to-blue-500 hover:from-green-600 hover:to-blue-600 text-white px-8 py-4 rounded-xl text-lg font-semibold disabled:opacity-50 min-w-[200px]"
            >
              <Loader v-if="processing" class="w-5 h-5 mr-2 animate-spin" />
              <CheckCircle v-else class="w-5 h-5 mr-2" />
              {{ processing ? 'Confirming...' : 'Confirm Booking' }}
            </Button>
          </div>
        </div>

        <!-- Contact Information -->
        <div class="text-center mt-8 p-6 bg-white rounded-2xl shadow-lg">
          <h3 class="text-lg font-semibold text-gray-900 mb-3">Need Help?</h3>
          <p class="text-gray-600 mb-4">Contact us if you have any questions about your booking</p>
          <div class="flex flex-col sm:flex-row gap-3 justify-center">
            <a
              href="tel:+254700000000"
              class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors"
            >
              <Phone class="w-4 h-4 mr-2" />
              Call Us
            </a>
            <a
              href="https://wa.me/254700000000"
              target="_blank"
              class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors"
            >
              <MessageSquare class="w-4 h-4 mr-2" />
              WhatsApp
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import BookingProgress from '@/components/BookingProgress.vue'
import { useToast } from '@/components/ui/toast'
import { 
  Sparkles,
  Calendar,
  CalendarDays,
  Clock,
  User,
  UserCheck,
  Mail,
  Phone,
  FileText,
  CreditCard,
  CheckCircle,
  ArrowLeft,
  X,
  Info,
  Loader,
  MessageSquare,
  Banknote,
  Smartphone,
  Building
} from 'lucide-vue-next'

interface Service {
  id: number
  name: string
  price: number
  duration_minutes: number
  formatted_price: string
  formatted_duration: string
}

interface Props {
  currentStep: number
  bookingData: any
  selectedServices: Service[]
  totalAmount: number
  totalDuration: number
}

const props = defineProps<Props>()
const { success, error, info, warning } = useToast()

const processing = ref(false)

const form = useForm({})

const formattedTotalAmount = computed(() => {
  return 'KSh ' + new Intl.NumberFormat().format(props.totalAmount)
})

const formattedTotalDuration = computed(() => {
  const hours = Math.floor(props.totalDuration / 60)
  const minutes = props.totalDuration % 60
  
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
    weekday: 'long',
    month: 'long',
    day: 'numeric',
    year: 'numeric'
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

const paymentMethodConfig = computed(() => {
  switch (props.bookingData.payment_method) {
    case 'cash':
      return {
        name: 'Pay on Site',
        description: 'Cash or card payment at the salon',
        icon: Banknote,
        bgColor: 'bg-green-100',
        iconColor: 'text-green-600'
      }
    case 'mpesa':
      return {
        name: 'M-Pesa',
        description: 'Mobile money payment',
        icon: Smartphone,
        bgColor: 'bg-green-100',
        iconColor: 'text-green-600'
      }
    case 'card':
      return {
        name: 'Credit/Debit Card',
        description: 'Online card payment',
        icon: CreditCard,
        bgColor: 'bg-blue-100',
        iconColor: 'text-blue-600'
      }
    case 'bank_transfer':
      return {
        name: 'Bank Transfer',
        description: 'Direct bank transfer',
        icon: Building,
        bgColor: 'bg-purple-100',
        iconColor: 'text-purple-600'
      }
    default:
      return {
        name: 'Unknown',
        description: '',
        icon: CreditCard,
        bgColor: 'bg-gray-100',
        iconColor: 'text-gray-600'
      }
  }
})

const completeBooking = () => {
  processing.value = true
  info('Processing your booking...', 'Please Wait')
  
  form.post(route('booking.stepper.complete'), {
    onSuccess: () => {
      success('Booking confirmed successfully! 🎉', 'Congratulations!')
    },
    onError: (errors: any) => {
      error('Failed to complete booking. Please try again.', 'Booking Error')
      console.error('Booking errors:', errors)
    },
    onFinish: () => {
      processing.value = false
    }
  })
}

// Show final review message
onMounted(() => {
  const serviceCount = props.selectedServices.length
  const serviceText = serviceCount === 1 ? 'service' : 'services'
  info(`Review your ${serviceCount} ${serviceText} and confirm your booking`, 'Final Step!')
})
</script>

<style scoped>
/* Additional styling for the confirmation page */
.min-w-\[200px\] {
  min-width: 200px;
}
</style>