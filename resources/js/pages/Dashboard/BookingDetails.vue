<template>
  <DashboardLayout>
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold tracking-tight">Booking Details</h1>
          <p class="text-muted-foreground">
            Detailed information for booking {{ booking.booking_reference }}
          </p>
        </div>
        <div class="flex items-center gap-2">
          <Button variant="outline" @click="$inertia.visit(route('dashboard.bookings'))">
            <ArrowLeft class="h-4 w-4 mr-2" />
            Back to Bookings
          </Button>
        </div>
      </div>

      <!-- Booking Information Cards -->
      <div class="grid gap-6 md:grid-cols-2">
        <!-- Client Information -->
        <Card>
          <CardHeader>
            <CardTitle class="flex items-center gap-2">
              <User class="h-5 w-5" />
              Client Information
            </CardTitle>
          </CardHeader>
          <CardContent class="space-y-4">
            <div>
              <label class="text-sm font-medium text-muted-foreground">Name</label>
              <p class="font-medium">{{ booking.client_name }}</p>
            </div>
            <div>
              <label class="text-sm font-medium text-muted-foreground">Email</label>
              <p class="font-medium">{{ booking.client_email }}</p>
            </div>
            <div>
              <label class="text-sm font-medium text-muted-foreground">Phone</label>
              <p class="font-medium">{{ booking.client_phone || 'Not provided' }}</p>
            </div>
          </CardContent>
        </Card>

        <!-- Appointment Details -->
        <Card>
          <CardHeader>
            <CardTitle class="flex items-center gap-2">
              <Calendar class="h-5 w-5" />
              Appointment Details
            </CardTitle>
          </CardHeader>
          <CardContent class="space-y-4">
            <div>
              <label class="text-sm font-medium text-muted-foreground">Date</label>
              <p class="font-medium">{{ booking.formatted_date }}</p>
            </div>
            <div>
              <label class="text-sm font-medium text-muted-foreground">Time</label>
              <p class="font-medium">{{ booking.formatted_time_slot }}</p>
            </div>
            <div>
              <label class="text-sm font-medium text-muted-foreground">Duration</label>
              <p class="font-medium">{{ booking.formatted_duration }}</p>
            </div>
            <div>
              <label class="text-sm font-medium text-muted-foreground">Status</label>
              <div class="flex items-center gap-2">
                <Badge :class="booking.status_badge_class">
                  {{ booking.status }}
                </Badge>
                <Button 
                  v-if="booking.status !== 'completed' && booking.status !== 'cancelled'"
                  variant="outline" 
                  size="sm"
                  @click="showStatusModal = true"
                >
                  Update Status
                </Button>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Services Information -->
      <Card>
        <CardHeader>
          <CardTitle class="flex items-center gap-2">
            <Scissors class="h-5 w-5" />
            Services Booked
          </CardTitle>
        </CardHeader>
        <CardContent>
          <div class="space-y-4">
            <div 
              v-for="(bookingService, index) in booking.services" 
              :key="index"
              class="flex items-center justify-between p-4 border rounded-lg"
            >
              <div class="flex-1">
                <h3 class="font-medium">{{ bookingService.service.name }}</h3>
                <p class="text-sm text-muted-foreground mt-1">
                  {{ bookingService.service.description }}
                </p>
                <div class="flex items-center gap-4 mt-2 text-sm text-muted-foreground">
                  <span class="flex items-center gap-1">
                    <Clock class="h-4 w-4" />
                    {{ bookingService.service.formatted_duration }}
                  </span>
                </div>
              </div>
              <div class="text-right">
                <p class="font-semibold">{{ bookingService.service.formatted_price }}</p>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Payment & Summary -->
      <div class="grid gap-6 md:grid-cols-2">
        <!-- Payment Information -->
        <Card>
          <CardHeader>
            <CardTitle class="flex items-center gap-2">
              <CreditCard class="h-5 w-5" />
              Payment Information
            </CardTitle>
          </CardHeader>
          <CardContent class="space-y-4">
            <div>
              <label class="text-sm font-medium text-muted-foreground">Payment Method</label>
              <p class="font-medium capitalize">{{ booking.payment_method || 'Not specified' }}</p>
            </div>
            <div>
              <label class="text-sm font-medium text-muted-foreground">Total Amount</label>
              <p class="text-2xl font-bold">{{ booking.formatted_amount }}</p>
            </div>
          </CardContent>
        </Card>

        <!-- Booking Metadata -->
        <Card>
          <CardHeader>
            <CardTitle class="flex items-center gap-2">
              <FileText class="h-5 w-5" />
              Booking Information
            </CardTitle>
          </CardHeader>
          <CardContent class="space-y-4">
            <div>
              <label class="text-sm font-medium text-muted-foreground">Reference</label>
              <p class="font-medium font-mono">{{ booking.booking_reference }}</p>
            </div>
            <div>
              <label class="text-sm font-medium text-muted-foreground">Created</label>
              <p class="font-medium">{{ formatDate(booking.created_at) }}</p>
            </div>
            <div>
              <label class="text-sm font-medium text-muted-foreground">Last Updated</label>
              <p class="font-medium">{{ formatDate(booking.updated_at) }}</p>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Notes -->
      <Card v-if="booking.notes">
        <CardHeader>
          <CardTitle class="flex items-center gap-2">
            <MessageSquare class="h-5 w-5" />
            Notes
          </CardTitle>
        </CardHeader>
        <CardContent>
          <p class="whitespace-pre-wrap">{{ booking.notes }}</p>
        </CardContent>
      </Card>

      <!-- Action Buttons -->
      <div class="flex items-center gap-2">
        <Button 
          variant="outline" 
          @click="showStatusModal = true"
          v-if="booking.status !== 'completed' && booking.status !== 'cancelled'"
        >
          Update Status
        </Button>
        <Button 
          variant="destructive" 
          @click="showDeleteModal = true"
          v-if="booking.status === 'cancelled'"
        >
          <Trash2 class="h-4 w-4 mr-2" />
          Delete Booking
        </Button>
      </div>
    </div>

    <!-- Status Update Modal -->
    <Dialog v-model:open="showStatusModal">
      <DialogContent class="sm:max-w-md">
        <DialogHeader>
          <DialogTitle>Update Booking Status</DialogTitle>
          <DialogDescription>
            Change the status of this booking.
          </DialogDescription>
        </DialogHeader>
        <form @submit.prevent="updateStatus" class="space-y-4">
          <div>
            <Label for="status">Status</Label>
            <Select v-model="selectedStatus">
              <SelectTrigger>
                <SelectValue placeholder="Select status" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="pending">Pending</SelectItem>
                <SelectItem value="confirmed">Confirmed</SelectItem>
                <SelectItem value="completed">Completed</SelectItem>
                <SelectItem value="cancelled">Cancelled</SelectItem>
              </SelectContent>
            </Select>
          </div>
          <DialogFooter>
            <Button type="button" variant="outline" @click="showStatusModal = false">
              Cancel
            </Button>
            <Button type="submit" :disabled="!selectedStatus">
              Update Status
            </Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>

    <!-- Delete Confirmation Modal -->
    <Dialog v-model:open="showDeleteModal">
      <DialogContent class="sm:max-w-md">
        <DialogHeader>
          <DialogTitle>Delete Booking</DialogTitle>
          <DialogDescription>
            Are you sure you want to delete this booking? This action cannot be undone.
          </DialogDescription>
        </DialogHeader>
        <DialogFooter>
          <Button type="button" variant="outline" @click="showDeleteModal = false">
            Cancel
          </Button>
          <Button type="button" variant="destructive" @click="deleteBooking">
            Delete
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </DashboardLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Label } from '@/components/ui/label'
import { 
  ArrowLeft, 
  User, 
  Calendar, 
  Scissors, 
  Clock, 
  CreditCard, 
  FileText, 
  MessageSquare,
  Trash2
} from 'lucide-vue-next'

interface BookingService {
  service: {
    id: number
    name: string
    description: string
    price: number
    duration_minutes: number
    formatted_price: string
    formatted_duration: string
  }
}

interface Booking {
  id: number
  booking_reference: string
  client_name: string
  client_email: string
  client_phone: string
  services: BookingService[]
  appointment_date: string
  start_time: string
  end_time: string
  status: string
  payment_method: string
  total_amount: number
  formatted_amount: string
  formatted_date: string
  formatted_time_slot: string
  formatted_duration: string
  notes: string
  created_at: string
  updated_at: string
  status_badge_class: string
}

interface Props {
  booking: Booking
}

const props = defineProps<Props>()

const showStatusModal = ref(false)
const showDeleteModal = ref(false)
const selectedStatus = ref('')

const updateStatus = () => {
  router.post(route('dashboard.bookings.status', props.booking.id), {
    status: selectedStatus.value
  }, {
    onSuccess: () => {
      showStatusModal.value = false
      selectedStatus.value = ''
    }
  })
}

const deleteBooking = () => {
  router.delete(route('dashboard.bookings.destroy', props.booking.id), {
    onSuccess: () => {
      router.visit(route('dashboard.bookings'))
    }
  })
}

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}
</script>