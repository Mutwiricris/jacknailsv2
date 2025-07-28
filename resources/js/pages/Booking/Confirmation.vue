<template>
  <div class="min-h-screen bg-gradient-to-br from-green-50 via-white to-blue-50">
    <div class="container mx-auto px-4 py-8">
      <div class="max-w-3xl mx-auto">
        <!-- Success Header with Animation -->
        <div class="text-center mb-8">
          <div class="relative w-24 h-24 mx-auto mb-6">
            <!-- Animated Success Circle -->
            <div class="w-24 h-24 bg-gradient-to-r from-green-400 to-green-600 rounded-full flex items-center justify-center shadow-xl animate-bounce">
              <CheckCircle class="w-14 h-14 text-white" />
            </div>
            <!-- Confetti Animation -->
            <div class="absolute -top-2 -right-2 w-6 h-6 bg-yellow-400 rounded-full animate-ping"></div>
            <div class="absolute -top-4 -left-2 w-4 h-4 bg-pink-400 rounded-full animate-ping" style="animation-delay: 0.5s"></div>
            <div class="absolute -bottom-2 -right-4 w-5 h-5 bg-blue-400 rounded-full animate-ping" style="animation-delay: 1s"></div>
          </div>
          <h1 class="text-5xl font-bold text-gray-900 mb-4">🎉 Booking Confirmed!</h1>
          <p class="text-xl text-gray-600 mb-2">Your appointment has been successfully scheduled</p>
          <div class="inline-flex items-center px-4 py-2 bg-green-100 text-green-800 rounded-full text-sm font-medium">
            <Sparkles class="w-4 h-4 mr-2" />
            Reference: {{ booking.booking_reference }}
          </div>
        </div>

        <!-- Booking Details Card -->
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden mb-8">
          <div class="bg-gradient-to-r from-green-500 to-blue-500 p-6 text-white">
            <div class="flex items-center justify-between">
              <div>
                <h2 class="text-2xl font-bold mb-1">{{ booking.services?.length > 1 ? `${booking.services.length} Services` : booking.services?.[0]?.service?.name || 'Nail Services' }}</h2>
                <p class="opacity-90">Booking Reference: {{ booking.booking_reference }}</p>
              </div>
              <div class="text-right">
                <div class="text-2xl font-bold">{{ booking.formatted_amount }}</div>
                <div class="opacity-90">{{ booking.formatted_duration || 'Duration varies' }}</div>
              </div>
            </div>
          </div>

          <div class="p-6 space-y-6">
            <!-- Appointment Details -->
            <div>
              <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <Calendar class="w-5 h-5 mr-2 text-green-500" />
                Appointment Details
              </h3>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg">
                  <CalendarDays class="w-5 h-5 text-gray-500" />
                  <div>
                    <div class="text-sm text-gray-500">Date</div>
                    <div class="font-medium">{{ booking.formatted_date }}</div>
                  </div>
                </div>
                <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg">
                  <Clock class="w-5 h-5 text-gray-500" />
                  <div>
                    <div class="text-sm text-gray-500">Time</div>
                    <div class="font-medium">{{ booking.formatted_time_slot }}</div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Client Information -->
            <div>
              <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <User class="w-5 h-5 mr-2 text-green-500" />
                Client Information
              </h3>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg">
                  <UserCheck class="w-5 h-5 text-gray-500" />
                  <div>
                    <div class="text-sm text-gray-500">Name</div>
                    <div class="font-medium">{{ booking.client_name }}</div>
                  </div>
                </div>
                <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg">
                  <Mail class="w-5 h-5 text-gray-500" />
                  <div>
                    <div class="text-sm text-gray-500">Email</div>
                    <div class="font-medium">{{ booking.client_email }}</div>
                  </div>
                </div>
                <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg">
                  <Phone class="w-5 h-5 text-gray-500" />
                  <div>
                    <div class="text-sm text-gray-500">Phone</div>
                    <div class="font-medium">{{ booking.client_phone }}</div>
                  </div>
                </div>
                <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg">
                  <CreditCard class="w-5 h-5 text-gray-500" />
                  <div>
                    <div class="text-sm text-gray-500">Payment Method</div>
                    <div class="font-medium capitalize">{{ booking.payment_method.replace('_', ' ') }}</div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Special Notes -->
            <div v-if="booking.notes">
              <h3 class="text-lg font-semibold text-gray-900 mb-3 flex items-center">
                <FileText class="w-5 h-5 mr-2 text-green-500" />
                Special Notes
              </h3>
              <div class="p-4 bg-gray-50 rounded-lg">
                <p class="text-gray-700">{{ booking.notes }}</p>
              </div>
            </div>

            <!-- Status Badge -->
            <div class="flex items-center justify-center">
              <Badge 
                :class="booking.status_badge_class"
                class="px-4 py-2 text-sm font-medium rounded-full"
              >
                {{ booking.status.replace('_', ' ').toUpperCase() }}
              </Badge>
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
              <span>A confirmation email has been sent to {{ booking.client_email }}</span>
            </li>
          </ul>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
          <Button
            @click="$inertia.visit(route('booking.show', booking.booking_reference))"
            variant="outline"
            class="rounded-xl px-6 py-3"
          >
            <FileText class="w-4 h-4 mr-2" />
            View Booking Details
          </Button>
          
          <Button
            @click="$inertia.visit(route('home'))"
            class="bg-gradient-to-r from-pink-500 to-purple-500 hover:from-pink-600 hover:to-purple-600 text-white rounded-xl px-6 py-3"
          >
            <Home class="w-4 h-4 mr-2" />
            Back to Home
          </Button>
          
          <Button
            @click="$inertia.visit(route('booking.create'))"
            variant="outline"
            class="rounded-xl px-6 py-3"
          >
            <Plus class="w-4 h-4 mr-2" />
            Book Another
          </Button>
        </div>

        <!-- Contact Information -->
        <div class="text-center mt-8 p-6 bg-white rounded-2xl shadow-lg">
          <h3 class="text-lg font-semibold text-gray-900 mb-3">Need to make changes?</h3>
          <p class="text-gray-600 mb-4">Contact us if you need to reschedule or cancel your appointment</p>
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
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { 
  CheckCircle,
  Calendar,
  CalendarDays,
  Clock,
  User,
  UserCheck,
  Mail,
  Phone,
  CreditCard,
  FileText,
  Info,
  Home,
  Plus,
  MessageSquare,
  Sparkles
} from 'lucide-vue-next'

interface Service {
  id: number
  name: string
  description: string
  price: number
  duration_minutes: number
  formatted_price: string
  formatted_duration: string
}

interface BookingService {
  id: number
  booking_id: number
  service_id: number
  service: Service
}

interface Booking {
  id: number
  booking_reference: string
  services: BookingService[]
  client_name: string
  client_email: string
  client_phone: string
  appointment_date: string
  start_time: string
  end_time: string
  status: string
  notes?: string
  payment_method: string
  total_amount: number
  formatted_amount: string
  formatted_date: string
  formatted_time_slot: string
  formatted_duration?: string
  status_badge_class: string
}

interface Props {
  booking: Booking
}

defineProps<Props>()
</script>