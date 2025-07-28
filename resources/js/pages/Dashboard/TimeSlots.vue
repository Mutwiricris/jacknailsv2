<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { Label } from '@/components/ui/label';
import { useToast } from '@/components/ui/toast';
import { ref, computed, onMounted } from 'vue';
import { 
    Clock,
    Calendar,
    Plus,
    Edit,
    Trash2,
    CheckCircle,
    XCircle,
    AlertCircle,
    Filter,
    MoreHorizontal,
    RefreshCw,
    Eye,
    User,
    ChevronLeft,
    ChevronRight,
    X
} from 'lucide-vue-next';

interface TimeSlot {
    id: number;
    date: string;
    start_time: string;
    end_time: string;
    formatted_start_time: string;
    formatted_end_time: string;
    status: 'available' | 'booked' | 'unavailable';
    status_label: string;
    notes?: string;
    booking_id?: number;
    booking?: {
        id: number;
        booking_reference: string;
        client_name: string;
        client_email: string;
        total_amount: string;
    };
    is_available: boolean;
    is_booked: boolean;
    is_unavailable: boolean;
}

interface Stats {
    total: number;
    available: number;
    booked: number;
    unavailable: number;
}

interface Props {
    timeSlots: TimeSlot[];
    stats: Stats;
    selectedDate: string;
    selectedStatus: string;
    filters: {
        date: string;
        status: string;
    };
}

const props = defineProps<Props>();

const { success, error } = useToast();

const dateFilter = ref(props.filters.date);
const statusFilter = ref(props.filters.status);
const bulkAction = ref('');
const bulkNotes = ref('');
const showBulkForm = ref(false);

// Days navigation
const availableDays = ref<Array<{date: string, formatted: string, dayName: string, isToday: boolean, isPast: boolean}>>([]);
const currentDate = ref(props.selectedDate);

// Create time slot modal
const showCreateModal = ref(false);
const newTimeSlot = ref({
    date: props.selectedDate,
    start_time: '09:00',
    end_time: '09:30',
    status: 'available',
    notes: ''
});

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Time Slots', href: '/dashboard/timeslots' },
];

const getStatusIcon = (status: string) => {
    switch (status) {
        case 'available': return CheckCircle;
        case 'booked': return Clock;
        case 'unavailable': return XCircle;
        default: return AlertCircle;
    }
};

const getStatusColor = (status: string) => {
    switch (status) {
        case 'available': return 'bg-green-100 text-green-800 border-green-200';
        case 'booked': return 'bg-blue-100 text-blue-800 border-blue-200';
        case 'unavailable': return 'bg-red-100 text-red-800 border-red-200';
        default: return 'bg-gray-100 text-gray-800 border-gray-200';
    }
};

const applyFilters = () => {
    const params = new URLSearchParams();
    if (dateFilter.value) params.set('date', dateFilter.value);
    if (statusFilter.value && statusFilter.value !== 'all') params.set('status', statusFilter.value);
    
    router.get(`/dashboard/timeslots?${params.toString()}`);
};

const clearFilters = () => {
    dateFilter.value = new Date().toISOString().split('T')[0];
    statusFilter.value = 'all';
    router.get('/dashboard/timeslots');
};

const updateSlotStatus = (slotId: number, newStatus: string, notes: string = '') => {
    router.put(`/dashboard/timeslots/${slotId}/status`, {
        status: newStatus,
        notes: notes
    }, {
        onSuccess: () => {
            success(`Time slot status updated to ${newStatus}`, 'Status Updated');
        },
        onError: () => {
            error('Failed to update time slot status', 'Update Failed');
        }
    });
};

const executeBulkAction = () => {
    if (!bulkAction.value) {
        error('Please select an action', 'Action Required');
        return;
    }

    router.post('/dashboard/timeslots/bulk-update', {
        date: dateFilter.value,
        action: bulkAction.value,
        notes: bulkNotes.value
    }, {
        onSuccess: () => {
            success('Bulk action completed successfully', 'Success');
            showBulkForm.value = false;
            bulkAction.value = '';
            bulkNotes.value = '';
        },
        onError: () => {
            error('Failed to execute bulk action', 'Action Failed');
        }
    });
};

const deleteSlot = (slotId: number, timeSlot: string) => {
    if (confirm(`Are you sure you want to delete the ${timeSlot} time slot? This action cannot be undone.`)) {
        router.delete(`/dashboard/timeslots/${slotId}`, {
            onSuccess: () => {
                success('Time slot deleted successfully', 'Slot Deleted');
            },
            onError: () => {
                error('Failed to delete time slot', 'Delete Failed');
            }
        });
    }
};

const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};

const availabilityPercentage = computed(() => {
    if (props.stats.total === 0) return 0;
    return Math.round((props.stats.available / props.stats.total) * 100);
});

const bookingPercentage = computed(() => {
    if (props.stats.total === 0) return 0;
    return Math.round((props.stats.booked / props.stats.total) * 100);
});

// Check if previous day navigation should be disabled
const isPreviousDisabled = computed(() => {
    const currentIndex = availableDays.value.findIndex(day => day.date === currentDate.value);
    if (currentIndex <= 0) return true;
    const previousDay = availableDays.value[currentIndex - 1];
    return previousDay?.isPast || false;
});

// Generate available days for navigation
const generateAvailableDays = () => {
    const days = [];
    const today = new Date();
    today.setHours(0, 0, 0, 0); // Set to start of day for accurate comparison
    
    for (let i = -3; i <= 14; i++) {
        const date = new Date(today);
        date.setDate(today.getDate() + i);
        
        // Skip Sundays (0 = Sunday)
        if (date.getDay() === 0) continue;
        
        const dateStr = date.toISOString().split('T')[0];
        const isToday = i === 0;
        const isPast = i < 0;
        
        days.push({
            date: dateStr,
            formatted: date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' }),
            dayName: date.toLocaleDateString('en-US', { weekday: 'short' }),
            isToday: isToday,
            isPast: isPast
        });
    }
    
    availableDays.value = days;
};

// Navigate to specific date
const navigateToDate = (date: string) => {
    // Find the day object to check if it's in the past
    const day = availableDays.value.find(d => d.date === date);
    if (day && day.isPast) {
        return; // Don't navigate to past dates
    }
    
    currentDate.value = date;
    dateFilter.value = date;
    applyFilters();
};

// Navigate to previous day
const goToPreviousDay = () => {
    const currentIndex = availableDays.value.findIndex(day => day.date === currentDate.value);
    if (currentIndex > 0) {
        const previousDay = availableDays.value[currentIndex - 1];
        if (!previousDay.isPast) {
            navigateToDate(previousDay.date);
        }
    }
};

// Navigate to next day
const goToNextDay = () => {
    const currentIndex = availableDays.value.findIndex(day => day.date === currentDate.value);
    if (currentIndex < availableDays.value.length - 1) {
        navigateToDate(availableDays.value[currentIndex + 1].date);
    }
};

// Open create modal
const openCreateModal = () => {
    newTimeSlot.value = {
        date: currentDate.value,
        start_time: '09:00',
        end_time: '09:30',
        status: 'available',
        notes: ''
    };
    showCreateModal.value = true;
};

// Close create modal
const closeCreateModal = () => {
    showCreateModal.value = false;
    newTimeSlot.value = {
        date: currentDate.value,
        start_time: '09:00',
        end_time: '09:30',
        status: 'available',
        notes: ''
    };
};

// Create new time slot
const createTimeSlot = () => {
    router.post('/dashboard/timeslots', newTimeSlot.value, {
        onSuccess: () => {
            success('Time slot created successfully', 'Slot Created');
            closeCreateModal();
        },
        onError: () => {
            error('Failed to create time slot', 'Creation Failed');
        }
    });
};

// Initialize days on component mount
onMounted(() => {
    generateAvailableDays();
    
    // If current date is in the past, navigate to today
    const selectedDate = new Date(currentDate.value);
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    selectedDate.setHours(0, 0, 0, 0);
    
    if (selectedDate < today) {
        const todayStr = today.toISOString().split('T')[0];
        navigateToDate(todayStr);
    }
});
</script>

<template>
    <Head title="Time Slot Management" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-6 overflow-x-auto">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Time Slot Management</h1>
                    <p class="text-muted-foreground">Manage availability and time slots</p>
                </div>
                <div class="flex gap-2">
                    <Button @click="openCreateModal" class="bg-blue-600 hover:bg-blue-700">
                        <Plus class="w-4 h-4 mr-2" />
                        Add Time Slot
                    </Button>
                    <Button @click="showBulkForm = !showBulkForm" variant="outline">
                        <Edit class="w-4 h-4 mr-2" />
                        Bulk Actions
                    </Button>
                    <Button @click="applyFilters" variant="outline">
                        <RefreshCw class="w-4 h-4 mr-2" />
                        Refresh
                    </Button>
                </div>
            </div>

            <!-- Days Navigation -->
            <Card>
                <CardHeader>
                    <CardTitle class="text-lg">Quick Date Navigation</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="flex items-center space-x-4">
                        <Button 
                            @click="goToPreviousDay" 
                            variant="outline" 
                            size="sm"
                            :disabled="isPreviousDisabled"
                        >
                            <ChevronLeft class="w-4 h-4" />
                        </Button>
                        
                        <div class="flex-1 flex items-center space-x-2 overflow-x-auto pb-2">
                            <Button
                                v-for="day in availableDays"
                                :key="day.date"
                                @click="navigateToDate(day.date)"
                                :variant="day.date === currentDate ? 'default' : 'outline'"
                                :disabled="day.isPast"
                                :class="[
                                    'flex-shrink-0 min-w-[80px] flex flex-col items-center px-3 py-2 h-auto transition-all duration-200',
                                    day.isToday && day.date !== currentDate ? 'ring-2 ring-blue-400 border-blue-300' : '',
                                    day.date === currentDate ? 'bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white shadow-lg transform scale-105 border-blue-600' : '',
                                    day.isPast ? 'opacity-50 cursor-not-allowed text-gray-400 hover:bg-transparent' : '',
                                    !day.isPast && day.date !== currentDate ? 'hover:bg-blue-50 hover:border-blue-300 hover:shadow-md' : ''
                                ]"
                                size="sm"
                            >
                                <span :class="[
                                    'text-xs font-medium',
                                    day.date === currentDate ? 'text-white' : ''
                                ]">{{ day.dayName }}</span>
                                <span :class="[
                                    'text-sm font-bold',
                                    day.date === currentDate ? 'text-white' : ''
                                ]">{{ day.formatted }}</span>
                                <!-- Selected indicator -->
                                <div 
                                    v-if="day.date === currentDate" 
                                    class="absolute -bottom-1 left-1/2 transform -translate-x-1/2 w-2 h-2 bg-white rounded-full shadow-sm"
                                />
                            </Button>
                        </div>
                        
                        <Button 
                            @click="goToNextDay" 
                            variant="outline" 
                            size="sm"
                            :disabled="availableDays.findIndex(day => day.date === currentDate) >= availableDays.length - 1"
                        >
                            <ChevronRight class="w-4 h-4" />
                        </Button>
                    </div>
                </CardContent>
            </Card>

            <!-- Filters -->
            <Card>
                <CardHeader>
                    <CardTitle class="text-lg">Filters</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="grid gap-4 md:grid-cols-3">
                        <div class="space-y-2">
                            <Label>Date</Label>
                            <Input
                                v-model="dateFilter"
                                type="date"
                                placeholder="Select date"
                            />
                        </div>

                        <div class="space-y-2">
                            <Label>Status</Label>
                            <Select v-model="statusFilter">
                                <SelectTrigger>
                                    <SelectValue placeholder="All Statuses" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">All Statuses</SelectItem>
                                    <SelectItem value="available">Available</SelectItem>
                                    <SelectItem value="booked">Booked</SelectItem>
                                    <SelectItem value="unavailable">Unavailable</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <div class="space-y-2 flex items-end gap-2">
                            <Button @click="applyFilters" class="flex-1">
                                <Filter class="w-4 h-4 mr-2" />
                                Apply
                            </Button>
                            <Button @click="clearFilters" variant="outline">
                                Clear
                            </Button>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Bulk Actions Form -->
            <Card v-if="showBulkForm">
                <CardHeader>
                    <CardTitle class="text-lg">Bulk Actions for {{ formatDate(dateFilter) }}</CardTitle>
                    <CardDescription>Apply actions to multiple time slots at once</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="space-y-2">
                            <Label>Action</Label>
                            <Select v-model="bulkAction">
                                <SelectTrigger>
                                    <SelectValue placeholder="Select action" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="mark_available">Mark all as Available</SelectItem>
                                    <SelectItem value="mark_unavailable">Mark all as Unavailable</SelectItem>
                                    <SelectItem value="generate_slots">Generate Time Slots</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <div class="space-y-2">
                            <Label>Notes (Optional)</Label>
                            <Textarea
                                v-model="bulkNotes"
                                placeholder="Add notes for this bulk action..."
                                class="resize-none"
                                rows="2"
                            />
                        </div>
                    </div>

                    <div class="flex gap-2 mt-4">
                        <Button @click="executeBulkAction" :disabled="!bulkAction">
                            Execute Action
                        </Button>
                        <Button @click="showBulkForm = false" variant="outline">
                            Cancel
                        </Button>
                    </div>
                </CardContent>
            </Card>

            <!-- Stats Cards -->
            <div class="grid gap-4 md:grid-cols-4">
                <Card>
                    <CardContent class="p-4">
                        <div class="flex items-center gap-2">
                            <Clock class="h-5 w-5 text-blue-600" />
                            <div>
                                <p class="text-sm text-muted-foreground">Total Slots</p>
                                <p class="text-2xl font-bold">{{ stats.total }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent class="p-4">
                        <div class="flex items-center gap-2">
                            <CheckCircle class="h-5 w-5 text-green-600" />
                            <div>
                                <p class="text-sm text-muted-foreground">Available</p>
                                <p class="text-2xl font-bold">{{ stats.available }}</p>
                                <p class="text-xs text-muted-foreground">{{ availabilityPercentage }}%</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent class="p-4">
                        <div class="flex items-center gap-2">
                            <Calendar class="h-5 w-5 text-blue-600" />
                            <div>
                                <p class="text-sm text-muted-foreground">Booked</p>
                                <p class="text-2xl font-bold">{{ stats.booked }}</p>
                                <p class="text-xs text-muted-foreground">{{ bookingPercentage }}%</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent class="p-4">
                        <div class="flex items-center gap-2">
                            <XCircle class="h-5 w-5 text-red-600" />
                            <div>
                                <p class="text-sm text-muted-foreground">Unavailable</p>
                                <p class="text-2xl font-bold">{{ stats.unavailable }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Time Slots List -->
            <Card>
                <CardHeader>
                    <CardTitle>Time Slots for {{ formatDate(selectedDate) }} ({{ timeSlots.length }})</CardTitle>
                    <CardDescription>Manage individual time slot availability</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="space-y-4">
                        <div v-if="timeSlots.length === 0" class="text-center py-12 text-muted-foreground">
                            <Clock class="h-12 w-12 mx-auto mb-4 opacity-50" />
                            <h3 class="font-semibold mb-2">No time slots found</h3>
                            <p>No time slots exist for the selected date and filters.</p>
                        </div>

                        <div v-else class="grid gap-3 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                            <div 
                                v-for="slot in timeSlots" 
                                :key="slot.id" 
                                class="border rounded-lg p-4 hover:bg-muted/50 transition-colors"
                            >
                                <div class="flex items-start justify-between mb-3">
                                    <div class="flex items-center gap-2">
                                        <component :is="getStatusIcon(slot.status)" class="h-4 w-4" />
                                        <div>
                                            <p class="font-semibold">{{ slot.formatted_start_time }}</p>
                                            <p class="text-sm text-muted-foreground">to {{ slot.formatted_end_time }}</p>
                                        </div>
                                    </div>
                                    <Badge :class="getStatusColor(slot.status)" class="text-xs">
                                        {{ slot.status_label }}
                                    </Badge>
                                </div>

                                <!-- Booking Information -->
                                <div v-if="slot.booking" class="mb-3 p-2 bg-blue-50 rounded border">
                                    <div class="flex items-center gap-1 mb-1">
                                        <User class="h-3 w-3" />
                                        <p class="text-sm font-medium">{{ slot.booking.client_name }}</p>
                                    </div>
                                    <p class="text-xs text-muted-foreground">{{ slot.booking.booking_reference }}</p>
                                    <p class="text-xs font-medium">{{ slot.booking.total_amount }}</p>
                                </div>

                                <!-- Notes -->
                                <div v-if="slot.notes" class="mb-3">
                                    <p class="text-xs text-muted-foreground mb-1">Notes:</p>
                                    <p class="text-sm">{{ slot.notes }}</p>
                                </div>

                                <!-- Actions -->
                                <div class="flex flex-wrap gap-1">
                                    <Button 
                                        v-if="slot.is_unavailable"
                                        @click="updateSlotStatus(slot.id, 'available')"
                                        size="sm"
                                        class="bg-green-600 hover:bg-green-700"
                                    >
                                        <CheckCircle class="w-3 h-3 mr-1" />
                                        Available
                                    </Button>

                                    <Button 
                                        v-if="slot.is_available"
                                        @click="updateSlotStatus(slot.id, 'unavailable')"
                                        size="sm"
                                        variant="destructive"
                                    >
                                        <XCircle class="w-3 h-3 mr-1" />
                                        Unavailable
                                    </Button>

                                    <Button 
                                        v-if="slot.is_booked"
                                        @click="updateSlotStatus(slot.id, 'available')"
                                        size="sm"
                                        variant="outline"
                                        class="text-green-600 border-green-200"
                                    >
                                        <CheckCircle class="w-3 h-3 mr-1" />
                                        Release
                                    </Button>

                                    <Button 
                                        v-if="slot.booking"
                                        variant="outline"
                                        size="sm"
                                        as-child
                                    >
                                        <a :href="`/dashboard/bookings/${slot.booking.id}`">
                                            <Eye class="w-3 h-3 mr-1" />
                                            View
                                        </a>
                                    </Button>

                                    <Button 
                                        v-if="!slot.is_booked"
                                        @click="deleteSlot(slot.id, slot.formatted_start_time)"
                                        size="sm"
                                        variant="destructive"
                                    >
                                        <Trash2 class="w-3 h-3" />
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Create Time Slot Modal -->
        <div 
            v-if="showCreateModal" 
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
            @click="closeCreateModal"
        >
            <Card 
                class="w-full max-w-md bg-white shadow-2xl"
                @click.stop
            >
                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-4">
                    <CardTitle class="text-xl font-bold">Create New Time Slot</CardTitle>
                    <Button 
                        variant="ghost" 
                        size="sm" 
                        @click="closeCreateModal"
                        class="h-8 w-8 p-0"
                    >
                        <X class="h-4 w-4" />
                    </Button>
                </CardHeader>
                <CardContent class="space-y-6">
                    <div class="grid gap-4">
                        <div class="space-y-2">
                            <Label for="date">Date</Label>
                            <Input
                                id="date"
                                v-model="newTimeSlot.date"
                                type="date"
                                required
                            />
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label for="start_time">Start Time</Label>
                                <Input
                                    id="start_time"
                                    v-model="newTimeSlot.start_time"
                                    type="time"
                                    required
                                />
                            </div>
                            <div class="space-y-2">
                                <Label for="end_time">End Time</Label>
                                <Input
                                    id="end_time"
                                    v-model="newTimeSlot.end_time"
                                    type="time"
                                    required
                                />
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="status">Status</Label>
                            <Select v-model="newTimeSlot.status">
                                <SelectTrigger>
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="available">Available</SelectItem>
                                    <SelectItem value="unavailable">Unavailable</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <div class="space-y-2">
                            <Label for="notes">Notes (Optional)</Label>
                            <Textarea
                                id="notes"
                                v-model="newTimeSlot.notes"
                                placeholder="Add any notes for this time slot..."
                                class="resize-none"
                                rows="3"
                            />
                        </div>
                    </div>

                    <div class="flex gap-3 pt-4">
                        <Button @click="createTimeSlot" class="flex-1 bg-blue-600 hover:bg-blue-700">
                            <Plus class="w-4 h-4 mr-2" />
                            Create Time Slot
                        </Button>
                        <Button @click="closeCreateModal" variant="outline" class="flex-1">
                            Cancel
                        </Button>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>