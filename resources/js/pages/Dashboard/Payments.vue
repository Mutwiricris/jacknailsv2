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
import { ref, computed } from 'vue';
import { 
    CreditCard,
    Search,
    Filter,
    Plus,
    Eye,
    CheckCircle,
    Clock,
    XCircle,
    AlertCircle,
    Download,
    Calendar,
    DollarSign
} from 'lucide-vue-next';

interface Payment {
    id: number;
    payment_reference: string;
    booking_reference: string;
    client_name: string;
    amount: number;
    formatted_amount: string;
    method: string;
    status: string;
    status_badge_class: string;
    transaction_id?: string;
    mpesa_transaction_id?: string;
    provider?: string;
    notes?: string;
    processed_at?: string;
    created_at: string;
}

interface Props {
    payments: {
        data: Payment[];
        links: any;
        meta: any;
    };
    filters: {
        search?: string;
        status?: string;
        method?: string;
        date?: string;
    };
}

const props = defineProps<Props>();

const { success, error } = useToast();

const searchQuery = ref(props.filters?.search || '');
const statusFilter = ref(props.filters?.status || 'all');
const methodFilter = ref(props.filters?.method || 'all');
const dateFilter = ref(props.filters?.date || '');

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Payments', href: '/dashboard/payments' },
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

const getMethodIcon = (method: string) => {
    switch (method) {
        case 'mpesa': return DollarSign;
        case 'card': return CreditCard;
        case 'cash': return DollarSign;
        case 'bank_transfer': return DollarSign;
        default: return DollarSign;
    }
};

const filteredPayments = computed(() => {
    let filtered = props.payments?.data || [];

    // Search filter
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        filtered = filtered.filter(payment => 
            payment.payment_reference.toLowerCase().includes(query) ||
            payment.booking_reference.toLowerCase().includes(query) ||
            payment.client_name.toLowerCase().includes(query) ||
            (payment.transaction_id && payment.transaction_id.toLowerCase().includes(query)) ||
            (payment.mpesa_transaction_id && payment.mpesa_transaction_id.toLowerCase().includes(query))
        );
    }

    // Status filter
    if (statusFilter.value && statusFilter.value !== 'all') {
        filtered = filtered.filter(payment => payment.status === statusFilter.value);
    }

    // Method filter
    if (methodFilter.value && methodFilter.value !== 'all') {
        filtered = filtered.filter(payment => payment.method === methodFilter.value);
    }

    // Date filter
    if (dateFilter.value) {
        filtered = filtered.filter(payment => {
            const paymentDate = new Date(payment.created_at).toISOString().split('T')[0];
            return paymentDate === dateFilter.value;
        });
    }

    return filtered;
});

const updatePaymentStatus = (paymentId: number, status: string) => {
    router.post(`/dashboard/payments/${paymentId}/status`, {
        status: status
    }, {
        onSuccess: () => {
            success(`Payment status updated to ${status}`, 'Status Updated');
        },
        onError: () => {
            error('Failed to update payment status', 'Update Failed');
        }
    });
};

const applyFilters = () => {
    const params = new URLSearchParams();
    if (searchQuery.value) params.set('search', searchQuery.value);
    if (statusFilter.value && statusFilter.value !== 'all') params.set('status', statusFilter.value);
    if (methodFilter.value && methodFilter.value !== 'all') params.set('method', methodFilter.value);
    if (dateFilter.value) params.set('date', dateFilter.value);
    
    router.get(`/dashboard/payments?${params.toString()}`);
};

const clearFilters = () => {
    searchQuery.value = '';
    statusFilter.value = 'all';
    methodFilter.value = 'all';
    dateFilter.value = '';
    router.get('/dashboard/payments');
};

const exportPayments = () => {
    // TODO: Implement export functionality
    success('Export feature coming soon', 'Export');
};
</script>

<template>
    <Head title="Payment Management" />
    
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Payment Management</h1>
                    <p class="text-muted-foreground mt-2">
                        Track and manage all payment transactions
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <Button variant="outline" @click="exportPayments">
                        <Download class="w-4 h-4 mr-2" />
                        Export
                    </Button>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <Card>
                    <CardContent class="p-4">
                        <div class="flex items-center gap-2">
                            <CreditCard class="h-5 w-5 text-blue-600" />
                            <div>
                                <p class="text-sm text-muted-foreground">Total Payments</p>
                                <p class="text-2xl font-bold">{{ props.payments.meta?.total || 0 }}</p>
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
                                <p class="text-2xl font-bold">{{ filteredPayments.filter(p => p.status === 'completed').length }}</p>
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
                                <p class="text-2xl font-bold">{{ filteredPayments.filter(p => p.status === 'pending').length }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent class="p-4">
                        <div class="flex items-center gap-2">
                            <XCircle class="h-5 w-5 text-red-600" />
                            <div>
                                <p class="text-sm text-muted-foreground">Failed</p>
                                <p class="text-2xl font-bold">{{ filteredPayments.filter(p => p.status === 'failed').length }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Filters -->
            <Card>
                <CardHeader>
                    <CardTitle class="text-lg flex items-center gap-2">
                        <Filter class="w-5 h-5" />
                        Filters
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="space-y-2">
                            <label class="text-sm font-medium">Search</label>
                            <div class="relative">
                                <Search class="absolute left-2 top-2.5 h-4 w-4 text-muted-foreground" />
                                <Input
                                    placeholder="Reference, client, transaction..."
                                    v-model="searchQuery"
                                    class="pl-8"
                                    @keyup.enter="applyFilters"
                                />
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-medium">Status</label>
                            <Select v-model="statusFilter">
                                <SelectTrigger>
                                    <SelectValue placeholder="All statuses" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">All Statuses</SelectItem>
                                    <SelectItem value="pending">Pending</SelectItem>
                                    <SelectItem value="processing">Processing</SelectItem>
                                    <SelectItem value="completed">Completed</SelectItem>
                                    <SelectItem value="failed">Failed</SelectItem>
                                    <SelectItem value="cancelled">Cancelled</SelectItem>
                                    <SelectItem value="refunded">Refunded</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-medium">Method</label>
                            <Select v-model="methodFilter">
                                <SelectTrigger>
                                    <SelectValue placeholder="All methods" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">All Methods</SelectItem>
                                    <SelectItem value="cash">Cash</SelectItem>
                                    <SelectItem value="mpesa">M-Pesa</SelectItem>
                                    <SelectItem value="card">Card</SelectItem>
                                    <SelectItem value="bank_transfer">Bank Transfer</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-medium">Date</label>
                            <Input
                                type="date"
                                v-model="dateFilter"
                            />
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-2 mt-4">
                        <Button @click="applyFilters">Apply Filters</Button>
                        <Button variant="outline" @click="clearFilters">Clear</Button>
                    </div>
                </CardContent>
            </Card>

            <!-- Payments List -->
            <Card>
                <CardHeader>
                    <CardTitle class="text-lg">
                        Payments ({{ filteredPayments.length }} found)
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <div v-if="filteredPayments.length === 0" class="text-center py-12 text-muted-foreground">
                        <CreditCard class="h-12 w-12 mx-auto mb-4 opacity-50" />
                        <h3 class="font-semibold mb-2">No payments found</h3>
                        <p>No payments match your current filters.</p>
                    </div>

                    <div v-else class="space-y-4">
                        <div 
                            v-for="payment in filteredPayments" 
                            :key="payment.id" 
                            class="border rounded-lg p-4 hover:bg-muted/50 transition-colors"
                        >
                            <div class="flex items-start justify-between">
                                <div class="flex items-start gap-4 flex-1">
                                    <component :is="getStatusIcon(payment.status)" class="h-5 w-5 mt-1 text-muted-foreground" />
                                    
                                    <div class="flex-1 min-w-0 space-y-2">
                                        <!-- Header Row -->
                                        <div class="flex items-center gap-4 flex-wrap">
                                            <div>
                                                <h3 class="font-semibold text-lg">{{ payment.payment_reference }}</h3>
                                                <p class="text-sm text-muted-foreground">{{ payment.client_name }}</p>
                                            </div>
                                            <Badge :class="payment.status_badge_class" class="text-xs">
                                                {{ payment.status }}
                                            </Badge>
                                            <div class="text-right">
                                                <p class="font-bold text-lg">{{ payment.formatted_amount }}</p>
                                                <div class="flex items-center gap-1 text-sm text-muted-foreground">
                                                    <component :is="getMethodIcon(payment.method)" class="h-3 w-3" />
                                                    {{ payment.method.replace('_', ' ').toUpperCase() }}
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Payment Details -->
                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                                            <div>
                                                <p class="font-medium text-muted-foreground">Booking Reference</p>
                                                <p>{{ payment.booking_reference }}</p>
                                            </div>
                                            <div v-if="payment.transaction_id">
                                                <p class="font-medium text-muted-foreground">Transaction ID</p>
                                                <p>{{ payment.transaction_id }}</p>
                                            </div>
                                            <div v-if="payment.mpesa_transaction_id">
                                                <p class="font-medium text-muted-foreground">M-Pesa Transaction</p>
                                                <p>{{ payment.mpesa_transaction_id }}</p>
                                            </div>
                                            <div>
                                                <p class="font-medium text-muted-foreground">Created</p>
                                                <p>{{ new Date(payment.created_at).toLocaleDateString() }}</p>
                                            </div>
                                            <div v-if="payment.processed_at">
                                                <p class="font-medium text-muted-foreground">Processed</p>
                                                <p>{{ new Date(payment.processed_at).toLocaleDateString() }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Actions -->
                                <div class="flex items-center gap-2 ml-4">
                                    <Button variant="outline" size="sm" as-child>
                                        <Link :href="`/dashboard/payments/${payment.id}`">
                                            <Eye class="w-4 h-4 mr-1" />
                                            View Details
                                        </Link>
                                    </Button>

                                    <div class="flex items-center gap-1">
                                        <Button 
                                            v-if="payment.status === 'pending'"
                                            @click="updatePaymentStatus(payment.id, 'completed')"
                                            size="sm"
                                            class="bg-green-600 hover:bg-green-700"
                                        >
                                            <CheckCircle class="w-4 h-4 mr-1" />
                                            Complete
                                        </Button>

                                        <Button 
                                            v-if="payment.status === 'pending'"
                                            @click="updatePaymentStatus(payment.id, 'failed')"
                                            size="sm"
                                            class="bg-red-600 hover:bg-red-700"
                                        >
                                            <XCircle class="w-4 h-4 mr-1" />
                                            Mark Failed
                                        </Button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div v-if="props.payments.links" class="mt-6 flex justify-center">
                        <div class="flex items-center gap-2">
                            <template v-for="link in props.payments.links" :key="link.label">
                                <Button
                                    v-if="link.url"
                                    :variant="link.active ? 'default' : 'outline'"
                                    size="sm"
                                    @click="router.get(link.url)"
                                    v-html="link.label"
                                />
                                <span v-else class="px-3 py-1 text-sm text-muted-foreground" v-html="link.label" />
                            </template>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>