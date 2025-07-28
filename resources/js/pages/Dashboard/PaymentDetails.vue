<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Separator } from '@/components/ui/separator';
import { useToast } from '@/components/ui/toast';
import { ref } from 'vue';
import { 
    CreditCard,
    ArrowLeft,
    CheckCircle,
    Clock,
    XCircle,
    AlertCircle,
    Calendar,
    User,
    Mail,
    Phone,
    Edit,
    RefreshCw,
    Receipt
} from 'lucide-vue-next';

interface Payment {
    id: number;
    payment_reference: string;
    amount: number;
    formatted_amount: string;
    method: string;
    status: string;
    status_badge_class: string;
    transaction_id?: string;
    mpesa_transaction_id?: string;
    provider?: string;
    provider_response?: any;
    notes?: string;
    processed_at?: string;
    failed_at?: string;
    created_at: string;
    updated_at: string;
    can_be_refunded: boolean;
    booking: {
        id: number;
        booking_reference: string;
        client_name: string;
        client_email: string;
        client_phone: string;
        appointment_date: string;
        start_time: string;
        end_time: string;
        status: string;
        services: Array<{
            service: {
                id: number;
                name: string;
                formatted_price: string;
            }
        }>;
    };
}

interface Props {
    payment: Payment;
}

const props = defineProps<Props>();
const { success, error } = useToast();

const showUpdateModal = ref(false);
const updateForm = ref({
    status: '',
    transaction_id: '',
    notes: ''
});

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Payments', href: '/dashboard/payments' },
    { title: props.payment.payment_reference, href: '' },
];

const getStatusIcon = (status: string) => {
    switch (status) {
        case 'completed': return CheckCircle;
        case 'pending': return Clock;
        case 'processing': return Clock;
        case 'failed': return XCircle;
        case 'cancelled': return XCircle;
        case 'refunded': return AlertCircle;
        default: return AlertCircle;
    }
};

const getStatusColor = (status: string) => {
    switch (status) {
        case 'completed': return 'text-green-600';
        case 'pending': return 'text-yellow-600';
        case 'processing': return 'text-blue-600';
        case 'failed': return 'text-red-600';
        case 'cancelled': return 'text-gray-600';
        case 'refunded': return 'text-purple-600';
        default: return 'text-gray-600';
    }
};

const formatDateTime = (dateString: string) => {
    return new Date(dateString).toLocaleString();
};

const openUpdateModal = (status: string) => {
    updateForm.value = {
        status: status,
        transaction_id: props.payment.transaction_id || '',
        notes: props.payment.notes || ''
    };
    showUpdateModal.value = true;
};

const updatePaymentStatus = () => {
    router.post(`/dashboard/payments/${props.payment.id}/status`, updateForm.value, {
        onSuccess: () => {
            success('Payment status updated successfully', 'Status Updated');
            showUpdateModal.value = false;
        },
        onError: () => {
            error('Failed to update payment status', 'Update Failed');
        }
    });
};

const canUpdateStatus = (status: string) => {
    const currentStatus = props.payment.status;
    
    // Define allowed status transitions
    const allowedTransitions: { [key: string]: string[] } = {
        'pending': ['processing', 'completed', 'failed', 'cancelled'],
        'processing': ['completed', 'failed'],
        'completed': ['refunded'],
        'failed': ['pending'],
        'cancelled': ['pending']
    };
    
    return allowedTransitions[currentStatus]?.includes(status) || false;
};
</script>

<template>
    <Head :title="`Payment ${payment.payment_reference}`" />
    
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Button variant="outline" size="sm" as-child>
                        <Link href="/dashboard/payments">
                            <ArrowLeft class="w-4 h-4 mr-2" />
                            Back to Payments
                        </Link>
                    </Button>
                    <div>
                        <h1 class="text-3xl font-bold tracking-tight">Payment Details</h1>
                        <p class="text-muted-foreground mt-1">{{ payment.payment_reference }}</p>
                    </div>
                </div>
                
                <div class="flex items-center gap-2">
                    <Button 
                        v-if="canUpdateStatus('completed')"
                        @click="openUpdateModal('completed')"
                        class="bg-green-600 hover:bg-green-700"
                    >
                        <CheckCircle class="w-4 h-4 mr-2" />
                        Mark Completed
                    </Button>
                    
                    <Button 
                        v-if="canUpdateStatus('failed')"
                        @click="openUpdateModal('failed')"
                        class="bg-red-600 hover:bg-red-700"
                    >
                        <XCircle class="w-4 h-4 mr-2" />
                        Mark Failed
                    </Button>
                    
                    <Button 
                        v-if="payment.can_be_refunded"
                        @click="openUpdateModal('refunded')"
                        variant="outline"
                    >
                        <RefreshCw class="w-4 h-4 mr-2" />
                        Process Refund
                    </Button>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Payment Information -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Payment Status Card -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <component :is="getStatusIcon(payment.status)" :class="[getStatusColor(payment.status), 'w-5 h-5']" />
                                Payment Information
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm font-medium text-muted-foreground">Reference</p>
                                    <p class="text-lg font-mono">{{ payment.payment_reference }}</p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-muted-foreground">Status</p>
                                    <Badge :class="payment.status_badge_class" class="text-sm">
                                        {{ payment.status.toUpperCase() }}
                                    </Badge>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-muted-foreground">Amount</p>
                                    <p class="text-2xl font-bold text-green-600">{{ payment.formatted_amount }}</p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-muted-foreground">Method</p>
                                    <p class="capitalize">{{ payment.method.replace('_', ' ') }}</p>
                                </div>
                            </div>

                            <Separator />

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div v-if="payment.transaction_id">
                                    <p class="text-sm font-medium text-muted-foreground">Transaction ID</p>
                                    <p class="font-mono text-sm">{{ payment.transaction_id }}</p>
                                </div>
                                <div v-if="payment.mpesa_transaction_id">
                                    <p class="text-sm font-medium text-muted-foreground">M-Pesa Transaction</p>
                                    <p class="font-mono text-sm">{{ payment.mpesa_transaction_id }}</p>
                                </div>
                                <div v-if="payment.provider">
                                    <p class="text-sm font-medium text-muted-foreground">Provider</p>
                                    <p class="capitalize">{{ payment.provider }}</p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-muted-foreground">Created</p>
                                    <p class="text-sm">{{ formatDateTime(payment.created_at) }}</p>
                                </div>
                                <div v-if="payment.processed_at">
                                    <p class="text-sm font-medium text-muted-foreground">Processed</p>
                                    <p class="text-sm">{{ formatDateTime(payment.processed_at) }}</p>
                                </div>
                                <div v-if="payment.failed_at">
                                    <p class="text-sm font-medium text-muted-foreground">Failed</p>
                                    <p class="text-sm">{{ formatDateTime(payment.failed_at) }}</p>
                                </div>
                            </div>

                            <div v-if="payment.notes">
                                <p class="text-sm font-medium text-muted-foreground">Notes</p>
                                <p class="text-sm bg-muted p-3 rounded">{{ payment.notes }}</p>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Provider Response (if available) -->
                    <Card v-if="payment.provider_response">
                        <CardHeader>
                            <CardTitle>Provider Response</CardTitle>
                            <CardDescription>Raw response from payment provider</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <pre class="bg-muted p-4 rounded text-xs overflow-auto">{{ JSON.stringify(payment.provider_response, null, 2) }}</pre>
                        </CardContent>
                    </Card>
                </div>

                <!-- Booking Information Sidebar -->
                <div class="space-y-6">
                    <!-- Related Booking -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <Calendar class="w-5 h-5" />
                                Related Booking
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div>
                                <p class="text-sm font-medium text-muted-foreground">Booking Reference</p>
                                <Button variant="link" class="p-0 h-auto font-mono" as-child>
                                    <Link :href="`/dashboard/bookings/${payment.booking.id}`">
                                        {{ payment.booking.booking_reference }}
                                    </Link>
                                </Button>
                            </div>

                            <div>
                                <p class="text-sm font-medium text-muted-foreground">Status</p>
                                <Badge class="bg-blue-100 text-blue-800 border-blue-200">
                                    {{ payment.booking.status }}
                                </Badge>
                            </div>

                            <div>
                                <p class="text-sm font-medium text-muted-foreground">Date & Time</p>
                                <p class="text-sm">{{ new Date(payment.booking.appointment_date).toLocaleDateString() }}</p>
                                <p class="text-sm text-muted-foreground">
                                    {{ payment.booking.start_time }} - {{ payment.booking.end_time }}
                                </p>
                            </div>

                            <Separator />

                            <!-- Client Information -->
                            <div>
                                <h4 class="font-medium flex items-center gap-2 mb-2">
                                    <User class="w-4 h-4" />
                                    Client Information
                                </h4>
                                <div class="space-y-2">
                                    <p class="font-medium">{{ payment.booking.client_name }}</p>
                                    <div class="flex items-center gap-1 text-sm text-muted-foreground">
                                        <Mail class="w-3 h-3" />
                                        {{ payment.booking.client_email }}
                                    </div>
                                    <div class="flex items-center gap-1 text-sm text-muted-foreground">
                                        <Phone class="w-3 h-3" />
                                        {{ payment.booking.client_phone }}
                                    </div>
                                </div>
                            </div>

                            <Separator />

                            <!-- Services -->
                            <div>
                                <h4 class="font-medium flex items-center gap-2 mb-2">
                                    <Receipt class="w-4 h-4" />
                                    Services
                                </h4>
                                <div class="space-y-2">
                                    <div 
                                        v-for="bookingService in payment.booking.services" 
                                        :key="bookingService.service.id"
                                        class="flex justify-between items-center text-sm"
                                    >
                                        <span>{{ bookingService.service.name }}</span>
                                        <span class="font-medium">{{ bookingService.service.formatted_price }}</span>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Quick Actions -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Quick Actions</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-2">
                            <Button 
                                variant="outline" 
                                class="w-full justify-start"
                                @click="openUpdateModal(payment.status)"
                            >
                                <Edit class="w-4 h-4 mr-2" />
                                Update Payment
                            </Button>
                            
                            <Button 
                                variant="outline" 
                                class="w-full justify-start"
                                as-child
                            >
                                <Link :href="`/dashboard/bookings/${payment.booking.id}`">
                                    <Calendar class="w-4 h-4 mr-2" />
                                    View Booking
                                </Link>
                            </Button>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>

        <!-- Update Payment Modal -->
        <div v-if="showUpdateModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" @click.self="showUpdateModal = false">
            <Card class="w-full max-w-md mx-4">
                <CardHeader>
                    <CardTitle>Update Payment Status</CardTitle>
                    <CardDescription>Change the payment status and add notes</CardDescription>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div>
                        <label class="text-sm font-medium">Status</label>
                        <select v-model="updateForm.status" class="w-full mt-1 p-2 border rounded">
                            <option value="pending">Pending</option>
                            <option value="processing">Processing</option>
                            <option value="completed">Completed</option>
                            <option value="failed">Failed</option>
                            <option value="cancelled">Cancelled</option>
                            <option value="refunded">Refunded</option>
                        </select>
                    </div>

                    <div>
                        <label class="text-sm font-medium">Transaction ID</label>
                        <input 
                            v-model="updateForm.transaction_id" 
                            class="w-full mt-1 p-2 border rounded" 
                            placeholder="Optional transaction ID"
                        />
                    </div>

                    <div>
                        <label class="text-sm font-medium">Notes</label>
                        <textarea 
                            v-model="updateForm.notes" 
                            class="w-full mt-1 p-2 border rounded" 
                            rows="3"
                            placeholder="Optional notes or reason for change"
                        ></textarea>
                    </div>

                    <div class="flex gap-2 pt-2">
                        <Button @click="updatePaymentStatus" class="flex-1">
                            Update Payment
                        </Button>
                        <Button variant="outline" @click="showUpdateModal = false">
                            Cancel
                        </Button>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>