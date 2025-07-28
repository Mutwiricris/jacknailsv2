<template>
  <div class="min-h-screen bg-gradient-to-br from-pink-50 via-white to-purple-50">
    <div class="container mx-auto px-4 py-8">
      <div class="max-w-3xl mx-auto">
        <!-- Header -->
        <div class="text-center mb-8">
          <h1 class="text-4xl font-bold text-gray-900 mb-2">Booking Details</h1>
          <p class="text-lg text-gray-600">Reference: {{ booking.booking_reference }}</p>
        </div>

        <!-- Main Booking Card -->
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden mb-8">
          <!-- Header with Service Info -->
          <div class="bg-gradient-to-r from-pink-500 to-purple-500 p-6 text-white">
            <div class="flex items-center justify-between">
              <div>
                <h2 class="text-2xl font-bold mb-1">{{ booking.service.name }}</h2>
                <div class="flex items-center space-x-4 opacity-90">
                  <span>{{ booking.service.formatted_duration }}</span>
                  <span>•</span>
                  <span class="capitalize">{{ booking.payment_method.replace('_', ' ') }}</span>
                </div>
              </div>
              <div class="text-right">
                <div class="text-3xl font-bold">{{ booking.formatted_amount }}</div>
                <Badge 
                  :class="booking.status_badge_class"
                  class="px-3 py-1 text-xs font-medium rounded-full mt-2"
                >
                  {{ booking.status.replace('_', ' ').toUpperCase() }}
                </Badge>
              </div>
            </div>
          </div>

          <div class="p-6">
            <!-- Appointment Details -->
            <div class="mb-8">
              <h3 class="text-xl font-semibold text-gray-900 mb-6 flex items-center">
                <Calendar class="w-6 h-6 mr-3 text-pink-500" />
                Appointment Information
              </h3>
              
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-4">
                  <div class="flex items-center space-x-4 p-4 bg-gray-50 rounded-xl">
                    <div class="w-12 h-12 bg-pink-100 rounded-lg flex items-center justify-center">
                      <CalendarDays class="w-6 h-6 text-pink-600" />
                    </div>
                    <div>
                      <div class="text-sm text-gray-500 font-medium">Appointment Date</div>
                      <div class="text-lg font-semibold text-gray-900">{{ booking.formatted_date }}</div>
                    </div>
                  </div>
                  
                  <div class="flex items-center space-x-4 p-4 bg-gray-50 rounded-xl">
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                      <Clock class="w-6 h-6 text-blue-600" />
                    </div>
                    <div>
                      <div class="text-sm text-gray-500 font-medium">Time Slot</div>
                      <div class="text-lg font-semibold text-gray-900">{{ booking.formatted_time_slot }}</div>
                    </div>
                  </div>
                </div>
                
                <div class="space-y-4">
                  <div class="flex items-center space-x-4 p-4 bg-gray-50 rounded-xl">
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                      <CreditCard class="w-6 h-6 text-green-600" />
                    </div>
                    <div>
                      <div class="text-sm text-gray-500 font-medium">Payment Status</div>
                      <div class="text-lg font-semibold text-gray-900 capitalize">{{ booking.payment_status }}</div>
                    </div>
                  </div>
                  
                  <div class="flex items-center space-x-4 p-4 bg-gray-50 rounded-xl">
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                      <Receipt class="w-6 h-6 text-purple-600" />
                    </div>
                    <div>
                      <div class="text-sm text-gray-500 font-medium">Total Amount</div>
                      <div class="text-lg font-semibold text-gray-900">{{ booking.formatted_amount }}</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Client Information -->
            <div class="mb-8">
              <h3 class="text-xl font-semibold text-gray-900 mb-6 flex items-center">
                <User class="w-6 h-6 mr-3 text-pink-500" />
                Client Information
              </h3>
              
              <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="flex items-center space-x-3 p-4 bg-gray-50 rounded-xl">
                  <UserCheck class="w-5 h-5 text-gray-500" />
                  <div>
                    <div class="text-sm text-gray-500 font-medium">Name</div>
                    <div class="font-semibold text-gray-900">{{ booking.client_name }}</div>
                  </div>
                </div>
                
                <div class="flex items-center space-x-3 p-4 bg-gray-50 rounded-xl">
                  <Mail class="w-5 h-5 text-gray-500" />
                  <div>
                    <div class="text-sm text-gray-500 font-medium">Email</div>
                    <div class="font-semibold text-gray-900 text-sm">{{ booking.client_email }}</div>
                  </div>
                </div>
                
                <div class="flex items-center space-x-3 p-4 bg-gray-50 rounded-xl">
                  <Phone class="w-5 h-5 text-gray-500" />
                  <div>
                    <div class="text-sm text-gray-500 font-medium">Phone</div>
                    <div class="font-semibold text-gray-900">{{ booking.client_phone }}</div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Special Notes -->
            <div v-if="booking.notes" class="mb-8">
              <h3 class="text-xl font-semibold text-gray-900 mb-4 flex items-center">
                <FileText class="w-6 h-6 mr-3 text-pink-500" />
                Special Notes
              </h3>
              <div class="p-4 bg-blue-50 border border-blue-200 rounded-xl">
                <p class="text-gray-700 leading-relaxed">{{ booking.notes }}</p>
              </div>
            </div>

            <!-- Booking Timeline -->
            <div class="mb-6">
              <h3 class="text-xl font-semibold text-gray-900 mb-6 flex items-center">
                <Clock class="w-6 h-6 mr-3 text-pink-500" />
                Booking Timeline
              </h3>
              
              <div class="space-y-4">
                <div class="flex items-center space-x-4">
                  <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                  <div>
                    <div class="font-medium text-gray-900">Booking Created</div>
                    <div class="text-sm text-gray-500">{{ formatDateTime(booking.created_at) }}</div>
                  </div>
                </div>
                
                <div v-if="booking.confirmed_at" class="flex items-center space-x-4">
                  <div class="w-3 h-3 bg-blue-500 rounded-full"></div>
                  <div>
                    <div class="font-medium text-gray-900">Confirmed</div>
                    <div class="text-sm text-gray-500">{{ formatDateTime(booking.confirmed_at) }}</div>
                  </div>
                </div>
                
                <div v-if="booking.cancelled_at" class="flex items-center space-x-4">
                  <div class="w-3 h-3 bg-red-500 rounded-full"></div>
                  <div>
                    <div class="font-medium text-gray-900">Cancelled</div>
                    <div class="text-sm text-gray-500">{{ formatDateTime(booking.cancelled_at) }}</div>
                    <div v-if="booking.cancellation_reason" class="text-sm text-gray-600 mt-1">
                      Reason: {{ booking.cancellation_reason }}
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center mb-8">
          <Button
            @click="$inertia.visit(route('home'))"
            variant="outline"
            class="rounded-xl px-6 py-3"
          >
            <Home class="w-4 h-4 mr-2" />
            Back to Home
          </Button>
          
          <Button
            @click="$inertia.visit(route('booking.create'))"
            class="bg-gradient-to-r from-pink-500 to-purple-500 hover:from-pink-600 hover:to-purple-600 text-white rounded-xl px-6 py-3"
          >
            <Plus class="w-4 h-4 mr-2" />
            Book Another
          </Button>
          
          <Button
            v-if="booking.can_be_cancelled"
            @click="showCancelModal = true"
            variant="destructive"
            class="rounded-xl px-6 py-3"
          >
            <X class="w-4 h-4 mr-2" />
            Cancel Booking
          </Button>
        </div>

        <!-- Contact Information -->
        <div class="bg-white rounded-2xl shadow-lg p-6 text-center">
          <h3 class="text-lg font-semibold text-gray-900 mb-3">Need Help?</h3>
          <p class="text-gray-600 mb-4">Contact us for any questions or changes to your booking</p>
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

    <!-- Cancel Modal -->
    <div v-if="showCancelModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
      <div class="bg-white rounded-2xl shadow-xl max-w-md w-full p-6">
        <h3 class="text-xl font-semibold text-gray-900 mb-4">Cancel Booking</h3>
        <p class="text-gray-600 mb-6">
          Are you sure you want to cancel this booking? This action cannot be undone.
        </p>
        
        <form @submit.prevent="cancelBooking" class="space-y-4">
          <div>
            <Label for="cancellation_reason">Reason for cancellation (optional)</Label>
            <Textarea
              id="cancellation_reason"
              v-model="cancelForm.reason"
              placeholder="Please let us know why you're cancelling..."
              rows="3"
              class="rounded-lg border-gray-300 mt-1"
            />
          </div>
          
          <div class="flex gap-3 justify-end">
            <Button
              type="button"
              @click="showCancelModal = false"
              variant="outline"
              class="rounded-lg"
            >
              Keep Booking
            </Button>
            <Button
              type="submit"
              :disabled="cancelForm.processing"
              variant="destructive"
              class="rounded-lg"
            >
              <Loader v-if="cancelForm.processing" class="w-4 h-4 mr-2 animate-spin" />
              Cancel Booking
            </Button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'
import { 
  Calendar,
  CalendarDays,
  Clock,
  User,
  UserCheck,
  Mail,
  Phone,
  CreditCard,
  FileText,
  Receipt,
  Home,
  Plus,
  X,
  MessageSquare,
  Loader
} from 'lucide-vue-next'

interface Service {
  id: number
  name: string
  formatted_duration: string
}

interface Booking {
  id: number
  booking_reference: string
  service: Service
  client_name: string
  client_email: string
  client_phone: string
  appointment_date: string
  start_time: string
  end_time: string
  status: string
  payment_status: string
  notes?: string
  payment_method: string
  total_amount: number
  formatted_amount: string
  formatted_date: string
  formatted_time_slot: string
  status_badge_class: string
  can_be_cancelled: boolean
  created_at: string
  confirmed_at?: string
  cancelled_at?: string
  cancellation_reason?: string
}

interface Props {
  booking: Booking
}

defineProps<Props>()

const showCancelModal = ref(false)

const cancelForm = useForm({
  reason: ''
})

const cancelBooking = () => {
  cancelForm.post(route('booking.cancel', booking.booking_reference), {
    onSuccess: () => {
      showCancelModal.value = false
    }
  })
}

const formatDateTime = (dateTimeString: string) => {
  return new Date(dateTimeString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: 'numeric',
    minute: '2-digit',
    hour12: true
  })
}
</script>