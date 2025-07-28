<template>
  <DashboardLayout>
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold tracking-tight">Service Details</h1>
          <p class="text-muted-foreground">
            Detailed information for {{ service.name }}
          </p>
        </div>
        <div class="flex items-center gap-2">
          <Button variant="outline" @click="$inertia.visit(route('dashboard.services'))">
            <ArrowLeft class="h-4 w-4 mr-2" />
            Back to Services
          </Button>
          <Button @click="showEditModal = true">
            <Edit class="h-4 w-4 mr-2" />
            Edit Service
          </Button>
        </div>
      </div>

      <!-- Service Overview -->
      <div class="grid gap-6 md:grid-cols-3">
        <Card class="md:col-span-2">
          <CardHeader>
            <div class="flex items-center justify-between">
              <CardTitle class="flex items-center gap-2">
                <Scissors class="h-5 w-5" />
                {{ service.name }}
              </CardTitle>
              <Badge :class="service.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'">
                {{ service.status }}
              </Badge>
            </div>
          </CardHeader>
          <CardContent class="space-y-4">
            <div>
              <label class="text-sm font-medium text-muted-foreground">Description</label>
              <p class="mt-1">{{ service.description }}</p>
            </div>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="text-sm font-medium text-muted-foreground">Status</label>
                <Badge :class="service.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'">
                  {{ service.status }}
                </Badge>
              </div>
              <div>
                <label class="text-sm font-medium text-muted-foreground">Duration</label>
                <p class="font-medium">{{ service.formatted_duration }}</p>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle class="flex items-center gap-2">
              <DollarSign class="h-5 w-5" />
              Pricing
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div class="text-center">
              <p class="text-3xl font-bold">{{ service.formatted_price }}</p>
              <p class="text-sm text-muted-foreground mt-1">per session</p>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Service Image -->
      <Card v-if="service.image_url">
        <CardHeader>
          <CardTitle class="flex items-center gap-2">
            <Eye class="h-5 w-5" />
            Service Image
          </CardTitle>
        </CardHeader>
        <CardContent>
          <div class="flex justify-center">
            <img 
              :src="service.image_url" 
              :alt="service.name"
              class="max-w-full h-auto rounded-lg shadow-md max-h-96 object-cover"
            />
          </div>
        </CardContent>
      </Card>

      <!-- No Image Placeholder -->
      <Card v-else>
        <CardHeader>
          <CardTitle class="flex items-center gap-2">
            <Eye class="h-5 w-5" />
            Service Image
          </CardTitle>
        </CardHeader>
        <CardContent>
          <div class="flex flex-col items-center justify-center py-8 text-muted-foreground">
            <Upload class="h-12 w-12 mb-2" />
            <p>No image uploaded yet</p>
            <Button 
              variant="outline" 
              size="sm" 
              class="mt-2"
              @click="showImageUpload = true"
            >
              Upload Image
            </Button>
          </div>
        </CardContent>
      </Card>

      <div class="grid gap-6 md:grid-cols-3">
      </div>

      <!-- Statistics -->
      <div class="grid gap-6 md:grid-cols-4">
        <Card>
          <CardContent class="p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-muted-foreground">Total Bookings</p>
                <p class="text-2xl font-bold">{{ service.total_bookings }}</p>
              </div>
              <Calendar class="h-8 w-8 text-muted-foreground" />
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent class="p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-muted-foreground">Revenue</p>
                <p class="text-2xl font-bold">{{ service.formatted_revenue }}</p>
              </div>
              <TrendingUp class="h-8 w-8 text-muted-foreground" />
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent class="p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-muted-foreground">This Month</p>
                <p class="text-2xl font-bold">{{ service.monthly_bookings }}</p>
              </div>
              <BarChart class="h-8 w-8 text-muted-foreground" />
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent class="p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-muted-foreground">Popularity</p>
                <p class="text-2xl font-bold">{{ service.popularity_rank }}{{ getOrdinalSuffix(service.popularity_rank) }}</p>
              </div>
              <Star class="h-8 w-8 text-muted-foreground" />
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Recent Bookings -->
      <Card>
        <CardHeader>
          <CardTitle class="flex items-center gap-2">
            <Clock class="h-5 w-5" />
            Recent Bookings
          </CardTitle>
        </CardHeader>
        <CardContent>
          <div class="space-y-4">
            <div 
              v-for="booking in service.recent_bookings" 
              :key="booking.id"
              class="flex items-center justify-between p-4 border rounded-lg hover:bg-muted/50 cursor-pointer"
              @click="$inertia.visit(route('dashboard.bookings.show', booking.id))"
            >
              <div class="flex-1">
                <div class="flex items-center gap-3">
                  <div>
                    <p class="font-medium">{{ booking.client_name }}</p>
                    <p class="text-sm text-muted-foreground">{{ booking.client_email }}</p>
                  </div>
                </div>
              </div>
              <div class="text-right">
                <p class="font-medium">{{ booking.formatted_date }}</p>
                <div class="flex items-center gap-2 mt-1">
                  <Badge :class="booking.status_badge_class">
                    {{ booking.status }}
                  </Badge>
                </div>
              </div>
            </div>
            <div v-if="service.recent_bookings.length === 0" class="text-center py-8 text-muted-foreground">
              No recent bookings for this service
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Service History -->
      <Card>
        <CardHeader>
          <CardTitle class="flex items-center gap-2">
            <History class="h-5 w-5" />
            Service Information
          </CardTitle>
        </CardHeader>
        <CardContent class="space-y-4">
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="text-sm font-medium text-muted-foreground">Created</label>
              <p class="font-medium">{{ formatDate(service.created_at) }}</p>
            </div>
            <div>
              <label class="text-sm font-medium text-muted-foreground">Last Updated</label>
              <p class="font-medium">{{ formatDate(service.updated_at) }}</p>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Action Buttons -->
      <div class="flex items-center gap-2">
        <Button @click="showEditModal = true">
          <Edit class="h-4 w-4 mr-2" />
          Edit Service
        </Button>
        <Button 
          variant="outline"
          @click="toggleStatus"
          :disabled="statusLoading"
        >
          {{ service.status === 'active' ? 'Deactivate' : 'Activate' }} Service
        </Button>
        <Button 
          variant="destructive" 
          @click="showDeleteModal = true"
          :disabled="service.total_bookings > 0"
        >
          <Trash2 class="h-4 w-4 mr-2" />
          Delete Service
        </Button>
      </div>
    </div>

    <!-- Edit Service Modal -->
    <Dialog v-model:open="showEditModal">
      <DialogContent class="sm:max-w-lg">
        <DialogHeader>
          <DialogTitle>Edit Service</DialogTitle>
          <DialogDescription>
            Update the service information.
          </DialogDescription>
        </DialogHeader>
        <form @submit.prevent="updateService" class="space-y-4">
          <div>
            <Label for="name">Service Name</Label>
            <Input v-model="editForm.name" id="name" required />
          </div>
          <div>
            <Label for="description">Description</Label>
            <Textarea v-model="editForm.description" id="description" />
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <Label for="price">Price (KSh)</Label>
              <Input v-model.number="editForm.price" id="price" type="number" min="0" required />
            </div>
            <div>
              <Label for="duration">Duration (minutes)</Label>
              <Input v-model.number="editForm.duration_minutes" id="duration" type="number" min="15" step="15" required />
            </div>
          </div>
          <div>
          </div>
          <DialogFooter>
            <Button type="button" variant="outline" @click="showEditModal = false">
              Cancel
            </Button>
            <Button type="submit" :disabled="editLoading">
              Update Service
            </Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>

    <!-- Delete Confirmation Modal -->
    <Dialog v-model:open="showDeleteModal">
      <DialogContent class="sm:max-w-md">
        <DialogHeader>
          <DialogTitle>Delete Service</DialogTitle>
          <DialogDescription>
            Are you sure you want to delete this service? This action cannot be undone.
          </DialogDescription>
        </DialogHeader>
        <DialogFooter>
          <Button type="button" variant="outline" @click="showDeleteModal = false">
            Cancel
          </Button>
          <Button type="button" variant="destructive" @click="deleteService">
            Delete
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>

    <!-- Image Upload Modal -->
    <Dialog v-model:open="showImageUpload">
      <DialogContent class="sm:max-w-md">
        <DialogHeader>
          <DialogTitle>Upload Service Image</DialogTitle>
          <DialogDescription>
            Upload an image for {{ service.name }}
          </DialogDescription>
        </DialogHeader>
        <div class="space-y-4">
          <div class="grid w-full max-w-sm items-center gap-1.5">
            <Label for="image">Image File</Label>
            <Input 
              id="image" 
              type="file" 
              accept="image/*"
              @change="uploadImage"
              class="cursor-pointer"
            />
            <p class="text-xs text-muted-foreground">
              Supported formats: JPG, PNG, GIF. Max size: 2MB
            </p>
          </div>
        </div>
        <DialogFooter>
          <Button type="button" variant="outline" @click="showImageUpload = false">
            Cancel
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </DashboardLayout>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue'
import { router } from '@inertiajs/vue3'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog'
import { Input } from '@/components/ui/input'
import { Textarea } from '@/components/ui/textarea'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Label } from '@/components/ui/label'
import { 
  ArrowLeft, 
  Edit,
  Scissors, 
  DollarSign,
  Calendar,
  TrendingUp,
  BarChart,
  Star,
  Clock,
  History,
  Trash2,
  Eye,
  Upload
} from 'lucide-vue-next'

interface RecentBooking {
  id: number
  client_name: string
  client_email: string
  formatted_date: string
  status: string
  status_badge_class: string
}

interface Service {
  id: number
  name: string
  description: string
  price: number
  duration_minutes: number
  status: string
  status: string
  formatted_price: string
  formatted_duration: string
  total_bookings: number
  monthly_bookings: number
  formatted_revenue: string
  popularity_rank: number
  recent_bookings: RecentBooking[]
  created_at: string
  updated_at: string
}

interface Props {
  service: Service
}

const props = defineProps<Props>()

const showEditModal = ref(false)
const showDeleteModal = ref(false)
const showImageUpload = ref(false)
const editLoading = ref(false)
const statusLoading = ref(false)

const editForm = reactive({
  name: props.service.name,
  description: props.service.description,
  price: props.service.price,
  duration_minutes: props.service.duration_minutes,
  status: props.service.status
})

const updateService = () => {
  editLoading.value = true
  router.put(route('dashboard.services.update', props.service.id), editForm, {
    onFinish: () => {
      editLoading.value = false
      showEditModal.value = false
    }
  })
}

const toggleStatus = () => {
  statusLoading.value = true
  router.post(route('dashboard.services.toggle-status', props.service.id), {}, {
    onFinish: () => {
      statusLoading.value = false
    }
  })
}

const deleteService = () => {
  router.delete(route('dashboard.services.destroy', props.service.id), {
    onSuccess: () => {
      router.visit(route('dashboard.services'))
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

const uploadImage = (event: Event) => {
  const file = (event.target as HTMLInputElement).files?.[0];
  if (!file) return;
  
  const formData = new FormData();
  formData.append('image', file);
  
  router.post(`/dashboard/services/${props.service.id}/upload-image`, formData, {
    onSuccess: () => {
      showImageUpload.value = false;
    }
  });
};

const getOrdinalSuffix = (num: number) => {
  const j = num % 10
  const k = num % 100
  if (j == 1 && k != 11) return 'st'
  if (j == 2 && k != 12) return 'nd'
  if (j == 3 && k != 13) return 'rd'
  return 'th'
}
</script>