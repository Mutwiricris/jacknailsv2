<template>
  <div class="min-h-screen bg-gradient-to-br from-pink-50 via-white to-purple-50">
    <div class="container mx-auto px-4 py-8">
      <!-- Header -->
      <div class="text-center mb-8">
        <h1 class="text-4xl font-bold text-gray-900 mb-4">Book Your Appointment</h1>
        <p class="text-lg text-gray-600">Step 4: Choose your payment method</p>
      </div>

      <div class="max-w-4xl mx-auto">
        <!-- Progress Indicator -->
        <BookingProgress :current-step="currentStep" />

        <form @submit.prevent="nextStep" class="space-y-8">
          <!-- Booking Summary -->
          <div class="bg-white rounded-2xl shadow-lg p-6">
            <h3 class="text-xl font-semibold text-gray-900 mb-4 flex items-center">
              <Receipt class="w-5 h-5 mr-2 text-green-500" />
              Booking Summary
            </h3>
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
              <!-- Services & Client Info -->
              <div class="space-y-4">
                <div>
                  <h4 class="font-medium text-gray-800 mb-2">Services</h4>
                  <div class="space-y-2">
                    <div v-for="(service, index) in selectedServices" :key="service.id" class="flex justify-between items-center text-sm">
                      <span class="text-gray-700">{{ index + 1 }}. {{ service.name }}</span>
                      <span class="font-medium text-pink-600">{{ service.formatted_price }}</span>
                    </div>
                  </div>
                </div>
                
                <div>
                  <h4 class="font-medium text-gray-800 mb-2">Client Information</h4>
                  <div class="text-sm text-gray-600 space-y-1">
                    <div>{{ bookingData.client_name }}</div>
                    <div>{{ bookingData.client_email }}</div>
                    <div>{{ bookingData.client_phone }}</div>
                  </div>
                </div>
              </div>
              
              <!-- Appointment Details -->
              <div class="space-y-4">
                <div>
                  <h4 class="font-medium text-gray-800 mb-2">Appointment Details</h4>
                  <div class="text-sm text-gray-600 space-y-1">
                    <div>{{ formatAppointmentDateTime }}</div>
                    <div>Duration: {{ formattedTotalDuration }}</div>
                  </div>
                </div>
                
                <div class="border-t pt-4">
                  <div class="flex justify-between items-center text-xl font-bold">
                    <span>Total Amount</span>
                    <span class="text-pink-600">{{ formattedTotalAmount }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Payment Methods -->
          <div class="bg-white rounded-2xl shadow-lg p-6">
            <h3 class="text-xl font-semibold text-gray-900 mb-6 flex items-center">
              <CreditCard class="w-5 h-5 mr-2 text-pink-500" />
              Select Payment Method
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <!-- Pay on Site (Recommended) -->
              <div
                @click="selectPaymentMethod('cash')"
                :class="[
                  'relative border-2 rounded-xl p-6 cursor-pointer transition-all duration-300',
                  selectedPaymentMethod === 'cash'
                    ? 'border-green-500 bg-green-50 shadow-md'
                    : 'border-gray-200 hover:border-green-300 hover:shadow-sm'
                ]"
              >
                <!-- Recommended Badge -->
                <div class="absolute -top-2 -right-2 bg-green-500 text-white text-xs font-bold px-3 py-1 rounded-full">
                  Recommended
                </div>
                
                <div class="flex items-start space-x-4">
                  <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <Banknote class="w-6 h-6 text-green-600" />
                  </div>
                  <div class="flex-1">
                    <h4 class="font-semibold text-gray-900 mb-2">Pay on Site</h4>
                    <p class="text-sm text-gray-600 mb-3">
                      Pay with cash or card when you arrive at the salon. Most convenient and secure option.
                    </p>
                    <div class="flex items-center space-x-2 text-xs text-green-600">
                      <CheckCircle class="w-4 h-4" />
                      <span>No online fees</span>
                    </div>
                  </div>
                  <div v-if="selectedPaymentMethod === 'cash'" class="text-green-500">
                    <CheckCircle class="w-6 h-6" />
                  </div>
                </div>
              </div>

              <!-- M-Pesa -->
              <div
                @click="selectPaymentMethod('mpesa')"
                :class="[
                  'border-2 rounded-xl p-6 cursor-pointer transition-all duration-300',
                  selectedPaymentMethod === 'mpesa'
                    ? 'border-green-500 bg-green-50 shadow-md'
                    : 'border-gray-200 hover:border-green-300 hover:shadow-sm'
                ]"
              >
                <div class="flex items-start space-x-4">
                  <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <Smartphone class="w-6 h-6 text-green-600" />
                  </div>
                  <div class="flex-1">
                    <h4 class="font-semibold text-gray-900 mb-2">M-Pesa</h4>
                    <p class="text-sm text-gray-600 mb-3">
                      Pay instantly with M-Pesa mobile money. You'll receive an STK push notification.
                    </p>
                    <div class="flex items-center space-x-2 text-xs text-gray-500">
                      <Zap class="w-4 h-4" />
                      <span>Instant payment</span>
                    </div>
                  </div>
                  <div v-if="selectedPaymentMethod === 'mpesa'" class="text-green-500">
                    <CheckCircle class="w-6 h-6" />
                  </div>
                </div>
              </div>

              <!-- Card Payment -->
              <div
                @click="selectPaymentMethod('card')"
                :class="[
                  'border-2 rounded-xl p-6 cursor-pointer transition-all duration-300',
                  selectedPaymentMethod === 'card'
                    ? 'border-blue-500 bg-blue-50 shadow-md'
                    : 'border-gray-200 hover:border-blue-300 hover:shadow-sm'
                ]"
              >
                <div class="flex items-start space-x-4">
                  <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <CreditCard class="w-6 h-6 text-blue-600" />
                  </div>
                  <div class="flex-1">
                    <h4 class="font-semibold text-gray-900 mb-2">Credit/Debit Card</h4>
                    <p class="text-sm text-gray-600 mb-3">
                      Pay securely online with your Visa, MasterCard, or other major cards.
                    </p>
                    <div class="flex items-center space-x-2 text-xs text-gray-500">
                      <Shield class="w-4 h-4" />
                      <span>Secure encryption</span>
                    </div>
                  </div>
                  <div v-if="selectedPaymentMethod === 'card'" class="text-blue-500">
                    <CheckCircle class="w-6 h-6" />
                  </div>
                </div>
              </div>

              <!-- Bank Transfer -->
              <div
                @click="selectPaymentMethod('bank_transfer')"
                :class="[
                  'border-2 rounded-xl p-6 cursor-pointer transition-all duration-300',
                  selectedPaymentMethod === 'bank_transfer'
                    ? 'border-purple-500 bg-purple-50 shadow-md'
                    : 'border-gray-200 hover:border-purple-300 hover:shadow-sm'
                ]"
              >
                <div class="flex items-start space-x-4">
                  <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                    <Building class="w-6 h-6 text-purple-600" />
                  </div>
                  <div class="flex-1">
                    <h4 class="font-semibold text-gray-900 mb-2">Bank Transfer</h4>
                    <p class="text-sm text-gray-600 mb-3">
                      Transfer directly from your bank account. Booking confirmed once payment is received.
                    </p>
                    <div class="flex items-center space-x-2 text-xs text-gray-500">
                      <Clock class="w-4 h-4" />
                      <span>1-2 business days</span>
                    </div>
                  </div>
                  <div v-if="selectedPaymentMethod === 'bank_transfer'" class="text-purple-500">
                    <CheckCircle class="w-6 h-6" />
                  </div>
                </div>
              </div>
            </div>

            <div v-if="errors?.payment_method" class="mt-4 text-red-500 text-sm flex items-center">
              <AlertCircle class="w-4 h-4 mr-2" />
              {{ errors.payment_method }}
            </div>
          </div>

          <!-- Payment Information -->
          <div v-if="selectedPaymentMethod && selectedPaymentMethod !== 'cash'" class="bg-blue-50 border border-blue-200 rounded-2xl p-6">
            <h4 class="font-semibold text-gray-900 mb-3 flex items-center">
              <Info class="w-5 h-5 mr-2 text-blue-500" />
              Payment Information
            </h4>
            
            <div v-if="selectedPaymentMethod === 'mpesa'" class="space-y-2 text-sm text-gray-700">
              <p>• You'll receive an M-Pesa STK push notification after clicking "Complete Booking"</p>
              <p>• Enter your M-Pesa PIN to complete the payment</p>
              <p>• Your booking will be confirmed once payment is successful</p>
            </div>
            
            <div v-else-if="selectedPaymentMethod === 'card'" class="space-y-2 text-sm text-gray-700">
              <p>• You'll be redirected to our secure payment gateway</p>
              <p>• Enter your card details to complete the payment</p>
              <p>• Your booking will be confirmed immediately after successful payment</p>
            </div>
            
            <div v-else-if="selectedPaymentMethod === 'bank_transfer'" class="space-y-2 text-sm text-gray-700">
              <p>• Bank transfer details will be provided after booking confirmation</p>
              <p>• Your appointment is reserved for 24 hours pending payment</p>
              <p>• Please complete the transfer within 24 hours to confirm your booking</p>
            </div>
          </div>

          <!-- Terms and Conditions -->
          <div class="bg-gray-50 border border-gray-200 rounded-2xl p-6">
            <h4 class="font-semibold text-gray-900 mb-3">Booking Terms & Conditions</h4>
            <div class="text-sm text-gray-600 space-y-2">
              <p>• Appointments can be cancelled up to 24 hours before the scheduled time</p>
              <p>• Late arrivals may result in shortened service time or rescheduling</p>
              <p>• Please arrive 10 minutes before your appointment time</p>
              <p>• Cancellation fees may apply for no-shows or last-minute cancellations</p>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="flex justify-between items-center">
            <Button
              type="button"
              variant="outline"
              @click="$inertia.visit(route('booking.stepper.step', { step: 3 }))"
              class="px-6 py-3 rounded-xl"
            >
              <ArrowLeft class="w-4 h-4 mr-2" />
              Back to Contact Info
            </Button>

            <Button
              type="submit"
              :disabled="!selectedPaymentMethod"
              class="bg-gradient-to-r from-pink-500 to-purple-500 hover:from-pink-600 hover:to-purple-600 text-white px-8 py-3 rounded-xl disabled:opacity-50"
            >
              Review & Confirm
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
import { useToast } from '@/components/ui/toast'
import { 
  Receipt,
  CreditCard,
  Banknote,
  Smartphone,
  Building,
  CheckCircle,
  ArrowLeft,
  ArrowRight,
  AlertCircle,
  Info,
  Zap,
  Shield,
  Clock
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
  errors?: Record<string, string>
}

const props = defineProps<Props>()
const { success, error, info, warning } = useToast()

const selectedPaymentMethod = ref<string>(props.bookingData.payment_method || '')

const form = useForm({
  payment_method: selectedPaymentMethod.value
})

const formattedTotalAmount = computed(() => {
  return 'KSh ' + new Intl.NumberFormat().format(props.totalAmount)
})

const formattedTotalDuration = computed(() => {
  const totalDuration = props.selectedServices.reduce((total, service) => total + service.duration_minutes, 0)
  const hours = Math.floor(totalDuration / 60)
  const minutes = totalDuration % 60
  
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

const selectPaymentMethod = (method: string) => {
  selectedPaymentMethod.value = method
  form.payment_method = method
  
  const paymentLabels: Record<string, string> = {
    cash: 'Pay on Site',
    mpesa: 'M-Pesa',
    card: 'Credit/Debit Card',
    bank_transfer: 'Bank Transfer'
  }
  
  success(`${paymentLabels[method]} selected!`, 'Payment Method')
}

const nextStep = () => {
  if (!selectedPaymentMethod.value) {
    warning('Please select a payment method to continue', 'Payment Required')
    return
  }
  
  info('Proceeding to final confirmation...', 'Almost Done!')
  form.post(route('booking.stepper.update', { step: 4 }))
}

// Show welcome message
onMounted(() => {
  info('Choose your preferred payment method', 'Payment Options')
  if (selectedPaymentMethod.value) {
    const paymentLabels: Record<string, string> = {
      cash: 'Pay on Site',
      mpesa: 'M-Pesa',
      card: 'Credit/Debit Card',
      bank_transfer: 'Bank Transfer'
    }
    info(`Previously selected: ${paymentLabels[selectedPaymentMethod.value]}`, 'Saved Selection')
  }
})
</script>

<style scoped>
/* Custom hover effects for payment methods */
.hover\:border-green-300:hover {
  border-color: rgb(134 239 172);
}

.hover\:border-blue-300:hover {
  border-color: rgb(147 197 253);
}

.hover\:border-purple-300:hover {
  border-color: rgb(196 181 253);
}
</style>