<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { useToast } from '@/components/ui/toast';
import CreatePaymentModal from '@/components/CreatePaymentModal.vue';
import { ref, computed } from 'vue';
import { 
    Calendar,
    Users,
    Search,
    Filter,
    Plus,
    Eye,
    Edit,
    Trash2,
    CheckCircle,
    Clock,
    XCircle,
    AlertCircle,
    MoreHorizontal,
    Download,
    Phone,
    Mail
} from 'lucide-vue-next';

interface Booking {
    id: number;
    booking_reference: string;
    client_name: string;
    client_email: string;
    client_phone: string;
    services: Array<{
        service: {
            id: number;
            name: string;
            formatted_price: string;
        }
    }>;
    appointment_date: string;
    start_time: string;
    end_time: string;
    status: string;
    payment_status: string;
    payment_method: string;
    has_payment_record: boolean;
    payment_id?: number;
    payment_reference?: string;
    total_amount: string;
    formatted_date: string;
    formatted_time_slot: string;
    created_at: string;
    notes?: string;
}

interface Props {
    bookings: {
        data: Booking[];
        links: any;
        meta: any;
    };
    filters: {
        search?: string;
        status?: string;
        date?: string;
    };
}

const props = defineProps<Props>();

const { success, error } = useToast();

const searchQuery = ref(props.filters?.search || '');
const statusFilter = ref(props.filters?.status || 'all');
const dateFilter = ref(props.filters?.date || '');

const showCreatePaymentModal = ref(false);
const selectedBookingForPayment = ref<Booking | null>(null);

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Bookings', href: '/dashboard/bookings' },
];

const getStatusIcon = (status: string) => {
    switch (status) {
        case 'confirmed': return CheckCircle;
        case 'pending': return Clock;
        case 'completed': return CheckCircle;
        case 'cancelled': return XCircle;
        default: return AlertCircle;
    }
};

const getStatusColor = (status: string) => {
    switch (status) {
        case 'confirmed': return 'bg-blue-100 text-blue-800 border-blue-200';
        case 'pending': return 'bg-yellow-100 text-yellow-800 border-yellow-200';
        case 'completed': return 'bg-green-100 text-green-800 border-green-200';
        case 'cancelled': return 'bg-red-100 text-red-800 border-red-200';
        default: return 'bg-gray-100 text-gray-800 border-gray-200';
    }
};

const getPaymentStatusIcon = (status: string) => {
    switch (status) {
        case 'completed': return CheckCircle;
        case 'pending': return Clock;
        case 'failed': return XCircle;
        case 'refunded': return AlertCircle;
        default: return AlertCircle;
    }
};

const getPaymentStatusColor = (status: string) => {
    switch (status) {
        case 'completed': return 'bg-green-100 text-green-800 border-green-200';
        case 'pending': return 'bg-yellow-100 text-yellow-800 border-yellow-200';
        case 'failed': return 'bg-red-100 text-red-800 border-red-200';
        case 'refunded': return 'bg-purple-100 text-purple-800 border-purple-200';
        default: return 'bg-gray-100 text-gray-800 border-gray-200';
    }
};

const filteredBookings = computed(() => {
    let filtered = props.bookings?.data || [];

    // Search filter
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        filtered = filtered.filter(booking => 
            booking.client_name.toLowerCase().includes(query) ||
            booking.booking_reference.toLowerCase().includes(query) ||
            booking.client_email.toLowerCase().includes(query) ||
            booking.services.some(s => s.service.name.toLowerCase().includes(query))
        );
    }

    // Status filter
    if (statusFilter.value && statusFilter.value !== 'all') {
        filtered = filtered.filter(booking => booking.status === statusFilter.value);
    }

    // Date filter
    if (dateFilter.value) {
        filtered = filtered.filter(booking => booking.appointment_date === dateFilter.value);
    }

    return filtered;
});

const updateStatus = (bookingId: number, newStatus: string) => {
    router.post(`/dashboard/bookings/${bookingId}/status`, {
        status: newStatus
    }, {
        onSuccess: () => {
            success(`Booking status updated to ${newStatus}`, 'Status Updated');
        },
        onError: () => {
            error('Failed to update booking status', 'Update Failed');
        }
    });
};

const openCreatePaymentModal = (booking: Booking) => {
    selectedBookingForPayment.value = {
        id: booking.id,
        booking_reference: booking.booking_reference,
        client_name: booking.client_name,
        total_amount: parseFloat(booking.total_amount.replace(/[^\d.]/g, '')),
        formatted_amount: booking.total_amount,
        payment_method: booking.payment_method
    };
    showCreatePaymentModal.value = true;
};

const closeCreatePaymentModal = () => {
    showCreatePaymentModal.value = false;
    selectedBookingForPayment.value = null;
};

const onPaymentCreated = () => {
    closeCreatePaymentModal();
    // Refresh the page to show updated data
    router.reload();
};

const updatePaymentStatus = (paymentId: number, status: string) => {
    router.post(`/dashboard/payments/${paymentId}/status`, {
        status: status
    }, {
        onSuccess: () => {
            success(`Payment status updated to ${status}`, 'Payment Updated');
        },
        onError: () => {
            error('Failed to update payment status', 'Update Failed');
        }
    });
};

const deleteBooking = (bookingId: number, reference: string) => {
    if (confirm(`Are you sure you want to delete booking ${reference}? This action cannot be undone.`)) {
        router.delete(`/dashboard/bookings/${bookingId}`, {
            onSuccess: () => {
                success('Booking deleted successfully', 'Booking Deleted');
            },
            onError: () => {
                error('Failed to delete booking', 'Delete Failed');
            }
        });
    }
};

const applyFilters = () => {
    const params = new URLSearchParams();
    if (searchQuery.value) params.set('search', searchQuery.value);
    if (statusFilter.value && statusFilter.value !== 'all') params.set('status', statusFilter.value);
    if (dateFilter.value) params.set('date', dateFilter.value);
    
    router.get(`/dashboard/bookings?${params.toString()}`);
};

const clearFilters = () => {
    searchQuery.value = '';
    statusFilter.value = 'all';
    dateFilter.value = '';
    router.get('/dashboard/bookings');
};
</script>

<template>
    <Head title="Manage Bookings" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-6 overflow-x-auto">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Manage Bookings</h1>
                    <p class="text-muted-foreground">View and manage all appointments</p>
                </div>
                <div class="flex gap-2">
                    <Button variant="outline">
                        <Download class="w-4 h-4 mr-2" />
                        Export
                    </Button>
                    <Button as-child>
                        <Link href="/booking/stepper/start">
                            <Plus class="w-4 h-4 mr-2" />
                            New Booking
                        </Link>
                    </Button>
                </div>
            </div>

            <!-- Filters -->
            <Card>
                <CardHeader>
                    <CardTitle class="text-lg">Filters</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="grid gap-4 md:grid-cols-4">
                        <div class="space-y-2">
                            <label class="text-sm font-medium">Search</label>
                            <div class="relative">
                                <Search class="absolute left-3 top-3 h-4 w-4 text-muted-foreground" />
                                <Input
                                    v-model="searchQuery"
                                    placeholder="Search bookings..."
                                    class="pl-9"
                                    @keyup.enter="applyFilters"
                                />
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-medium">Status</label>
                            <Select v-model="statusFilter">
                                <SelectTrigger>
                                    <SelectValue placeholder="All Statuses" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">All Statuses</SelectItem>
                                    <SelectItem value="pending">Pending</SelectItem>
                                    <SelectItem value="confirmed">Confirmed</SelectItem>
                                    <SelectItem value="completed">Completed</SelectItem>
                                    <SelectItem value="cancelled">Cancelled</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-medium">Date</label>
                            <Input
                                v-model="dateFilter"
                                type="date"
                                placeholder="Select date"
                            />
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

            <!-- Stats Cards -->
            <div class="grid gap-4 md:grid-cols-4">
                <Card>
                    <CardContent class="p-4">
                        <div class="flex items-center gap-2">
                            <Calendar class="h-5 w-5 text-blue-600" />
                            <div>
                                <p class="text-sm text-muted-foreground">Total Bookings</p>
                                <p class="text-2xl font-bold">{{ filteredBookings.length }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent class="p-4">
                        <div class="flex items-center gap-2">
                            <CheckCircle class="h-5 w-5 text-green-600" />
                            <div>
                                <p class="text-sm text-muted-foreground">Confirmed</p>
                                <p class="text-2xl font-bold">{{ filteredBookings.filter(b => b.status === 'confirmed').length }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent class="p-4">
                        <div class="flex items-center gap-2">
                            <Clock class="h-5 w-5 text-yellow-600" />
                            <div>
                                <p class="text-sm text-muted-foreground">Pending</p>
                                <p class="text-2xl font-bold">{{ filteredBookings.filter(b => b.status === 'pending').length }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent class="p-4">
                        <div class="flex items-center gap-2">
                            <CheckCircle class="h-5 w-5 text-green-600" />
                            <div>
                                <p class="text-sm text-muted-foreground">Completed</p>
                                <p class="text-2xl font-bold">{{ filteredBookings.filter(b => b.status === 'completed').length }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Bookings Table -->
            <Card>
                <CardHeader>
                    <CardTitle>Bookings ({{ filteredBookings.length }})</CardTitle>
                    <CardDescription>Manage and track all appointments</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="space-y-4">
                        <div v-if="filteredBookings.length === 0" class="text-center py-12 text-muted-foreground">
                            <Calendar class="h-12 w-12 mx-auto mb-4 opacity-50" />
                            <h3 class="font-semibold mb-2">No bookings found</h3>
                            <p>No bookings match your current filters.</p>
                        </div>

                        <div v-else class="space-y-4">
                            <div 
                                v-for="booking in filteredBookings" 
                                :key="booking.id" 
                                class="border rounded-lg p-4 hover:bg-muted/50 transition-colors"
                            >
                                <div class="flex items-start justify-between">
                                    <div class="flex items-start gap-4 flex-1">
                                        <component :is="getStatusIcon(booking.status)" class="h-5 w-5 mt-1 text-muted-foreground" />
                                        
                                        <div class="flex-1 min-w-0 space-y-2">
                                            <!-- Header Row -->
                                            <div class="flex items-center gap-4 flex-wrap">
                                                <div>
                                                    <h3 class="font-semibold text-lg">{{ booking.client_name }}</h3>
                                                    <p class="text-sm text-muted-foreground">{{ booking.booking_reference }}</p>
                                                </div>
                                                <Badge :class="getStatusColor(booking.status)" class="text-xs">
                                                    {{ booking.status }}
                                                </Badge>
                                                <Badge :class="getPaymentStatusColor(booking.payment_status)" class="text-xs">
                                                    Payment: {{ booking.payment_status }}
                                                </Badge>
                                                <div class="text-right">
                                                    <p class="font-bold text-lg">{{ booking.total_amount }}</p>
                                                    <p class="text-sm text-muted-foreground capitalize">{{ booking.payment_method.replace('_', ' ') }}</p>
                                                </div>
                                            </div>

                                            <!-- Services -->
                                            <div>
                                                <p class="text-sm font-medium mb-1">Services:</p>
                                                <div class="flex flex-wrap gap-2">
                                                    <Badge 
                                                        v-for="serviceBooking in booking.services" 
                                                        :key="serviceBooking.service.id"
                                                        variant="outline"
                                                        class="text-xs"
                                                    >
                                                        {{ serviceBooking.service.name }} - {{ serviceBooking.service.formatted_price }}
                                                    </Badge>
                                                </div>
                                            </div>

                                            <!-- Appointment Details -->
                                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                                                <div>
                                                    <p class="font-medium text-muted-foreground">Date & Time</p>
                                                    <p>{{ booking.formatted_date }}</p>
                                                    <p>{{ booking.formatted_time_slot }}</p>
                                                </div>
                                                <div>
                                                    <p class="font-medium text-muted-foreground">Contact</p>
                                                    <p class="flex items-center gap-1">
                                                        <Mail class="h-3 w-3" />
                                                        {{ booking.client_email }}
                                                    </p>
<a v-if="booking.client_phone" :href="'tel:' + booking.client_phone" class="bg-purple-700 rounded-lg mt-3 text-lg font-bold text-white p-3 w-fit px-7 flex items-center gap-1">
    <Phone class="h-6 w-6" />
    Call Client</a>
                                                </div>
                                                <div v-if="booking.notes">
                                                    <p class="font-medium text-muted-foreground">Notes</p>
                                                    <p class="text-sm">{{ booking.notes }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Actions -->
                                    <div class="flex items-center gap-2 ml-4">
                                        <Button variant="outline" size="sm" as-child>
                                            <Link :href="`/booking/confirmation/${booking.booking_reference}`">
                                                <Eye class="w-4 h-4 mr-1" />
                                                View
                                            </Link>
                                        </Button>

                                        <div class="flex items-center gap-1">
                                            <Button 
                                                v-if="booking.status === 'pending'"
                                                @click="updateStatus(booking.id, 'confirmed')"
                                                size="sm"
                                                class="bg-blue-600 hover:bg-blue-700"
                                            >
                                                <CheckCircle class="w-4 h-4 mr-1" />
                                                Confirm
                                            </Button>

                                            <Button 
                                                v-if="booking.status === 'confirmed'"
                                                @click="updateStatus(booking.id, 'completed')"
                                                size="sm"
                                                :disabled="booking.payment_status !== 'completed'"
                                                :class="booking.payment_status === 'completed' ? 'bg-green-600 hover:bg-green-700' : 'bg-gray-400 cursor-not-allowed'"
                                                :title="booking.payment_status !== 'completed' ? 'Payment must be completed before marking booking as complete' : ''"
                                            >
                                                <CheckCircle class="w-4 h-4 mr-1" />
                                                Complete
                                            </Button>

                                            <Button 
                                                v-if="booking.status !== 'cancelled' && booking.status !== 'completed'"
                                                @click="updateStatus(booking.id, 'cancelled')"
                                                size="sm"
                                                variant="destructive"
                                            >
                                                <XCircle class="w-4 h-4 mr-1" />
                                                Cancel
                                            </Button>

                                            <!-- Payment Action Buttons -->
                                            <Button 
                                                v-if="!booking.has_payment_record"
                                                @click="openCreatePaymentModal(booking)"
                                                size="sm"
                                                class="bg-purple-600 hover:bg-purple-700"
                                            >
                                                <Plus class="w-4 h-4 mr-1" />
                                                Create Payment
                                            </Button>

                                            <Button 
                                                v-if="booking.has_payment_record && booking.payment_status === 'pending'"
                                                @click="updatePaymentStatus(booking.payment_id!, 'completed')"
                                                size="sm"
                                                class="bg-green-600 hover:bg-green-700"
                                            >
                                                <CheckCircle class="w-4 h-4 mr-1" />
                                                Mark Paid
                                            </Button>

                                            <Button 
                                                v-if="booking.has_payment_record && booking.payment_status === 'pending'"
                                                @click="updatePaymentStatus(booking.payment_id!, 'failed')"
                                                size="sm"
                                                class="bg-red-600 hover:bg-red-700"
                                            >
                                                <XCircle class="w-4 h-4 mr-1" />
                                                Mark Failed
                                            </Button>

                                            <Button 
                                                v-if="booking.has_payment_record"
                                                variant="outline"
                                                size="sm"
                                                as-child
                                            >
                                                <Link :href="`/dashboard/payments/${booking.payment_id}`">
                                                    <Eye class="w-4 h-4 mr-1" />
                                                    Payment Details
                                                </Link>
                                            </Button>

                                            <Button 
                                                @click="deleteBooking(booking.id, booking.booking_reference)"
                                                size="sm"
                                                variant="destructive"
                                            >
                                                <Trash2 class="w-4 h-4" />
                                            </Button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Create Payment Modal -->
        <CreatePaymentModal 
            v-if="selectedBookingForPayment"
            :booking="selectedBookingForPayment"
            :show="showCreatePaymentModal"
            @close="closeCreatePaymentModal"
            @created="onPaymentCreated"
        />
    </AppLayout>
</template>