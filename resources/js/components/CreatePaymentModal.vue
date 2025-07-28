<script setup lang="ts">
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { useToast } from '@/components/ui/toast';
import { 
    CreditCard,
    X,
    DollarSign,
    Smartphone,
    CreditCard as CardIcon,
    Building
} from 'lucide-vue-next';

interface Booking {
    id: number;
    booking_reference: string;
    client_name: string;
    total_amount: number;
    formatted_amount: string;
    payment_method: string;
}

interface Props {
    booking: Booking;
    show: boolean;
}

interface Emits {
    (e: 'close'): void;
    (e: 'created'): void;
}

const props = defineProps<Props>();
const emit = defineEmits<Emits>();

const { success, error } = useToast();

const form = ref({
    amount: props.booking.total_amount,
    method: props.booking.payment_method,
    transaction_id: '',
    mpesa_transaction_id: '',
    provider: '',
    notes: ''
});

const isSubmitting = ref(false);
const errors = ref<Record<string, string>>({});

const paymentMethods = [
    { value: 'cash', label: 'Cash', icon: DollarSign },
    { value: 'mpesa', label: 'M-Pesa', icon: Smartphone },
    { value: 'card', label: 'Card', icon: CardIcon },
    { value: 'bank_transfer', label: 'Bank Transfer', icon: Building }
];

const selectedMethodIcon = computed(() => {
    const method = paymentMethods.find(m => m.value === form.value.method);
    return method?.icon || DollarSign;
});

const isFullPayment = computed(() => {
    return form.value.amount === props.booking.total_amount;
});

const createPayment = async () => {
    if (isSubmitting.value) return;

    isSubmitting.value = true;
    errors.value = {};

    // Basic validation
    if (!form.value.amount || form.value.amount <= 0) {
        errors.value.amount = 'Amount is required and must be greater than 0';
        isSubmitting.value = false;
        return;
    }

    if (form.value.amount > props.booking.total_amount) {
        errors.value.amount = 'Amount cannot exceed booking total';
        isSubmitting.value = false;
        return;
    }

    if (!form.value.method) {
        errors.value.method = 'Payment method is required';
        isSubmitting.value = false;
        return;
    }

    try {
        await router.post(`/dashboard/bookings/${props.booking.id}/payments`, form.value, {
            onSuccess: () => {
                success('Payment record created successfully', 'Payment Created');
                emit('created');
                resetForm();
            },
            onError: (errorResponse) => {
                errors.value = errorResponse;
                error('Failed to create payment record', 'Creation Failed');
            },
            onFinish: () => {
                isSubmitting.value = false;
            }
        });
    } catch (e) {
        error('An unexpected error occurred', 'Error');
        isSubmitting.value = false;
    }
};

const resetForm = () => {
    form.value = {
        amount: props.booking.total_amount,
        method: props.booking.payment_method,
        transaction_id: '',
        mpesa_transaction_id: '',
        provider: '',
        notes: ''
    };
    errors.value = {};
};

const closeModal = () => {
    resetForm();
    emit('close');
};

// Auto-populate provider based on method
const updateProvider = (method: string) => {
    form.value.method = method;
    switch (method) {
        case 'mpesa':
            form.value.provider = 'safaricom';
            break;
        case 'card':
            form.value.provider = 'stripe';
            break;
        default:
            form.value.provider = '';
    }
};
</script>

<template>
    <div v-if="show" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" @click.self="closeModal">
        <Card class="w-full max-w-2xl">
            <CardHeader>
                <div class="flex items-center justify-between">
                    <div>
                        <CardTitle class="flex items-center gap-2">
                            <CreditCard class="w-5 h-5" />
                            Create Payment Record
                        </CardTitle>
                        <CardDescription class="mt-1">
                            Record a new payment for booking {{ booking.booking_reference }}
                        </CardDescription>
                    </div>
                    <Button variant="ghost" size="sm" @click="closeModal">
                        <X class="w-4 h-4" />
                    </Button>
                </div>
            </CardHeader>
            
            <CardContent class="space-y-6">
                <!-- Booking Summary -->
                <div class="bg-muted/50 p-4 rounded-lg">
                    <h4 class="font-medium mb-2">Booking Details</h4>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-muted-foreground">Reference:</span>
                            <span class="ml-1 font-mono">{{ booking.booking_reference }}</span>
                        </div>
                        <div>
                            <span class="text-muted-foreground">Client:</span>
                            <span class="ml-1">{{ booking.client_name }}</span>
                        </div>
                        <div>
                            <span class="text-muted-foreground">Total Amount:</span>
                            <span class="ml-1 font-semibold">{{ booking.formatted_amount }}</span>
                        </div>
                        <div>
                            <span class="text-muted-foreground">Method:</span>
                            <span class="ml-1 capitalize">{{ booking.payment_method.replace('_', ' ') }}</span>
                        </div>
                    </div>
                </div>

                <form @submit.prevent="createPayment" class="space-y-4">
                    <!-- Amount and Method Row -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <Label for="amount">Payment Amount *</Label>
                            <div class="relative">
                                <DollarSign class="absolute left-3 top-3 h-4 w-4 text-muted-foreground" />
                                <Input
                                    id="amount"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    :max="booking.total_amount"
                                    v-model.number="form.amount"
                                    class="pl-10"
                                    :class="errors.amount ? 'border-red-500' : ''"
                                    placeholder="0.00"
                                />
                            </div>
                            <p v-if="errors.amount" class="text-sm text-red-500">{{ errors.amount }}</p>
                            <p v-if="!isFullPayment" class="text-sm text-amber-600">
                                ⚠️ Partial payment ({{ ((form.amount / booking.total_amount) * 100).toFixed(1) }}% of total)
                            </p>
                        </div>

                        <div class="space-y-2">
                            <Label for="method">Payment Method *</Label>
                            <Select v-model="form.method" @update:model-value="updateProvider">
                                <SelectTrigger :class="errors.method ? 'border-red-500' : ''">
                                    <div class="flex items-center gap-2">
                                        <component :is="selectedMethodIcon" class="h-4 w-4" />
                                        <SelectValue placeholder="Select payment method" />
                                    </div>
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem 
                                        v-for="method in paymentMethods" 
                                        :key="method.value" 
                                        :value="method.value"
                                    >
                                        <div class="flex items-center gap-2">
                                            <component :is="method.icon" class="h-4 w-4" />
                                            {{ method.label }}
                                        </div>
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <p v-if="errors.method" class="text-sm text-red-500">{{ errors.method }}</p>
                        </div>
                    </div>

                    <!-- Transaction IDs Row -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <Label for="transaction_id">Transaction ID</Label>
                            <Input
                                id="transaction_id"
                                v-model="form.transaction_id"
                                placeholder="External transaction ID"
                                :class="errors.transaction_id ? 'border-red-500' : ''"
                            />
                            <p v-if="errors.transaction_id" class="text-sm text-red-500">{{ errors.transaction_id }}</p>
                        </div>

                        <div class="space-y-2" v-if="form.method === 'mpesa'">
                            <Label for="mpesa_transaction_id">M-Pesa Transaction ID</Label>
                            <Input
                                id="mpesa_transaction_id"
                                v-model="form.mpesa_transaction_id"
                                placeholder="M-Pesa confirmation code"
                                :class="errors.mpesa_transaction_id ? 'border-red-500' : ''"
                            />
                            <p v-if="errors.mpesa_transaction_id" class="text-sm text-red-500">{{ errors.mpesa_transaction_id }}</p>
                        </div>

                        <div class="space-y-2" v-if="form.method !== 'cash'">
                            <Label for="provider">Payment Provider</Label>
                            <Input
                                id="provider"
                                v-model="form.provider"
                                placeholder="e.g., Stripe, Safaricom, Equity Bank"
                                :class="errors.provider ? 'border-red-500' : ''"
                            />
                            <p v-if="errors.provider" class="text-sm text-red-500">{{ errors.provider }}</p>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="space-y-2">
                        <Label for="notes">Notes</Label>
                        <Textarea
                            id="notes"
                            v-model="form.notes"
                            placeholder="Additional notes about this payment..."
                            rows="3"
                            :class="errors.notes ? 'border-red-500' : ''"
                        />
                        <p v-if="errors.notes" class="text-sm text-red-500">{{ errors.notes }}</p>
                    </div>

                    <!-- Auto-completion notice for cash -->
                    <div v-if="form.method === 'cash' && isFullPayment" class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                        <p class="text-sm text-blue-800">
                            💡 <strong>Note:</strong> Cash payments for the full amount will be automatically marked as completed.
                        </p>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex justify-end gap-2 pt-4 border-t">
                        <Button type="button" variant="outline" @click="closeModal" :disabled="isSubmitting">
                            Cancel
                        </Button>
                        <Button type="submit" :disabled="isSubmitting">
                            <CreditCard class="w-4 h-4 mr-2" />
                            {{ isSubmitting ? 'Creating...' : 'Create Payment Record' }}
                        </Button>
                    </div>
                </form>
            </CardContent>
        </Card>
    </div>
</template>