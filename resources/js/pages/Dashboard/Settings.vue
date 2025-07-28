<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Switch } from '@/components/ui/switch';
import { useToast } from '@/components/ui/toast';
import { ref } from 'vue';
import { 
    Settings,
    Save,
    Bell,
    Mail,
    Phone,
    Clock,
    DollarSign,
    MapPin,
    Globe
} from 'lucide-vue-next';

interface BusinessSettings {
    business_name: string;
    business_email: string;
    business_phone: string;
    business_address: string;
    business_website: string;
    currency: string;
    timezone: string;
    booking_advance_days: number;
    booking_notice_hours: number;
    auto_confirm_bookings: boolean;
    send_email_confirmations: boolean;
    send_sms_reminders: boolean;
    reminder_hours_before: number;
}

interface Props {
    settings: BusinessSettings;
}

// Mock data for demonstration - replace with actual props
const props = withDefaults(defineProps<Partial<Props>>(), {
    settings: () => ({
        business_name: 'Jacknails Kenya',
        business_email: 'hello@jacknails.co.ke',
        business_phone: '+254 700 000 000',
        business_address: 'Nairobi, Kenya',
        business_website: 'https://jacknails.co.ke',
        currency: 'KSh',
        timezone: 'Africa/Nairobi',
        booking_advance_days: 30,
        booking_notice_hours: 4,
        auto_confirm_bookings: false,
        send_email_confirmations: true,
        send_sms_reminders: true,
        reminder_hours_before: 24
    })
});

const { success, error } = useToast();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Settings', href: '/dashboard/settings' },
];

const businessForm = useForm({
    business_name: props.settings?.business_name || '',
    business_email: props.settings?.business_email || '',
    business_phone: props.settings?.business_phone || '',
    business_address: props.settings?.business_address || '',
    business_website: props.settings?.business_website || '',
});

const bookingForm = useForm({
    booking_advance_days: props.settings?.booking_advance_days || 30,
    booking_notice_hours: props.settings?.booking_notice_hours || 4,
    auto_confirm_bookings: props.settings?.auto_confirm_bookings || false,
});

const notificationForm = useForm({
    send_email_confirmations: props.settings?.send_email_confirmations || true,
    send_sms_reminders: props.settings?.send_sms_reminders || true,
    reminder_hours_before: props.settings?.reminder_hours_before || 24,
});

const systemForm = useForm({
    currency: props.settings?.currency || 'KSh',
    timezone: props.settings?.timezone || 'Africa/Nairobi',
});

const saveBusinessSettings = () => {
    businessForm.put('/dashboard/settings/business', {
        onSuccess: () => {
            success('Business settings updated successfully', 'Settings Saved');
        },
        onError: () => {
            error('Failed to update business settings', 'Save Failed');
        }
    });
};

const saveBookingSettings = () => {
    bookingForm.put('/dashboard/settings/booking', {
        onSuccess: () => {
            success('Booking settings updated successfully', 'Settings Saved');
        },
        onError: () => {
            error('Failed to update booking settings', 'Save Failed');
        }
    });
};

const saveNotificationSettings = () => {
    notificationForm.put('/dashboard/settings/notifications', {
        onSuccess: () => {
            success('Notification settings updated successfully', 'Settings Saved');
        },
        onError: () => {
            error('Failed to update notification settings', 'Save Failed');
        }
    });
};

const saveSystemSettings = () => {
    systemForm.put('/dashboard/settings/system', {
        onSuccess: () => {
            success('System settings updated successfully', 'Settings Saved');
        },
        onError: () => {
            error('Failed to update system settings', 'Save Failed');
        }
    });
};
</script>

<template>
    <Head title="Settings" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-6 overflow-x-auto">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Settings</h1>
                    <p class="text-muted-foreground">Manage your salon settings and preferences</p>
                </div>
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <!-- Business Information -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Globe class="h-5 w-5" />
                            Business Information
                        </CardTitle>
                        <CardDescription>Update your business details</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <form @submit.prevent="saveBusinessSettings" class="space-y-4">
                            <div class="space-y-2">
                                <Label for="business_name">Business Name</Label>
                                <Input 
                                    id="business_name"
                                    v-model="businessForm.business_name" 
                                    placeholder="Your salon name"
                                    required 
                                />
                            </div>

                            <div class="space-y-2">
                                <Label for="business_email">Business Email</Label>
                                <Input 
                                    id="business_email"
                                    type="email"
                                    v-model="businessForm.business_email" 
                                    placeholder="hello@yoursalon.com"
                                    required 
                                />
                            </div>

                            <div class="space-y-2">
                                <Label for="business_phone">Business Phone</Label>
                                <Input 
                                    id="business_phone"
                                    type="tel"
                                    v-model="businessForm.business_phone" 
                                    placeholder="+254 700 000 000"
                                    required 
                                />
                            </div>

                            <div class="space-y-2">
                                <Label for="business_address">Business Address</Label>
                                <Textarea 
                                    id="business_address"
                                    v-model="businessForm.business_address" 
                                    placeholder="Your salon address"
                                    rows="2"
                                />
                            </div>

                            <div class="space-y-2">
                                <Label for="business_website">Website</Label>
                                <Input 
                                    id="business_website"
                                    type="url"
                                    v-model="businessForm.business_website" 
                                    placeholder="https://yoursalon.com"
                                />
                            </div>

                            <Button type="submit" :disabled="businessForm.processing" class="w-full">
                                <Save class="w-4 h-4 mr-2" />
                                Save Business Settings
                            </Button>
                        </form>
                    </CardContent>
                </Card>

                <!-- Booking Settings -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Clock class="h-5 w-5" />
                            Booking Settings
                        </CardTitle>
                        <CardDescription>Configure booking rules and preferences</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <form @submit.prevent="saveBookingSettings" class="space-y-4">
                            <div class="space-y-2">
                                <Label for="booking_advance_days">Advance Booking (Days)</Label>
                                <Input 
                                    id="booking_advance_days"
                                    type="number"
                                    v-model="bookingForm.booking_advance_days" 
                                    min="1"
                                    max="365"
                                    required 
                                />
                                <p class="text-xs text-muted-foreground">How far in advance clients can book</p>
                            </div>

                            <div class="space-y-2">
                                <Label for="booking_notice_hours">Minimum Notice (Hours)</Label>
                                <Input 
                                    id="booking_notice_hours"
                                    type="number"
                                    v-model="bookingForm.booking_notice_hours" 
                                    min="0"
                                    max="72"
                                    required 
                                />
                                <p class="text-xs text-muted-foreground">Minimum time before appointment</p>
                            </div>

                            <div class="flex items-center space-x-2">
                                <Switch 
                                    id="auto_confirm"
                                    v-model="bookingForm.auto_confirm_bookings" 
                                />
                                <Label for="auto_confirm">Auto-confirm bookings</Label>
                            </div>
                            <p class="text-xs text-muted-foreground">Automatically confirm new bookings instead of keeping them pending</p>

                            <Button type="submit" :disabled="bookingForm.processing" class="w-full">
                                <Save class="w-4 h-4 mr-2" />
                                Save Booking Settings
                            </Button>
                        </form>
                    </CardContent>
                </Card>

                <!-- Notification Settings -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Bell class="h-5 w-5" />
                            Notifications
                        </CardTitle>
                        <CardDescription>Manage email and SMS notifications</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <form @submit.prevent="saveNotificationSettings" class="space-y-4">
                            <div class="space-y-4">
                                <div class="flex items-center space-x-2">
                                    <Switch 
                                        id="email_confirmations"
                                        v-model="notificationForm.send_email_confirmations" 
                                    />
                                    <Label for="email_confirmations" class="flex items-center gap-2">
                                        <Mail class="h-4 w-4" />
                                        Send email confirmations
                                    </Label>
                                </div>

                                <div class="flex items-center space-x-2">
                                    <Switch 
                                        id="sms_reminders"
                                        v-model="notificationForm.send_sms_reminders" 
                                    />
                                    <Label for="sms_reminders" class="flex items-center gap-2">
                                        <Phone class="h-4 w-4" />
                                        Send SMS reminders
                                    </Label>
                                </div>

                                <div class="space-y-2">
                                    <Label for="reminder_hours">Reminder Time (Hours Before)</Label>
                                    <Input 
                                        id="reminder_hours"
                                        type="number"
                                        v-model="notificationForm.reminder_hours_before" 
                                        min="1"
                                        max="168"
                                        required 
                                    />
                                    <p class="text-xs text-muted-foreground">When to send appointment reminders</p>
                                </div>
                            </div>

                            <Button type="submit" :disabled="notificationForm.processing" class="w-full">
                                <Save class="w-4 h-4 mr-2" />
                                Save Notification Settings
                            </Button>
                        </form>
                    </CardContent>
                </Card>

                <!-- System Settings -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Settings class="h-5 w-5" />
                            System Settings
                        </CardTitle>
                        <CardDescription>Configure system preferences</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <form @submit.prevent="saveSystemSettings" class="space-y-4">
                            <div class="space-y-2">
                                <Label for="currency">Currency</Label>
                                <Input 
                                    id="currency"
                                    v-model="systemForm.currency" 
                                    placeholder="KSh"
                                    required 
                                />
                                <p class="text-xs text-muted-foreground">Currency symbol for pricing</p>
                            </div>

                            <div class="space-y-2">
                                <Label for="timezone">Timezone</Label>
                                <Input 
                                    id="timezone"
                                    v-model="systemForm.timezone" 
                                    placeholder="Africa/Nairobi"
                                    required 
                                />
                                <p class="text-xs text-muted-foreground">System timezone for appointments</p>
                            </div>

                            <Button type="submit" :disabled="systemForm.processing" class="w-full">
                                <Save class="w-4 h-4 mr-2" />
                                Save System Settings
                            </Button>
                        </form>
                    </CardContent>
                </Card>
            </div>

            <!-- System Information -->
            <Card>
                <CardHeader>
                    <CardTitle>System Information</CardTitle>
                    <CardDescription>Application details and status</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="grid gap-4 md:grid-cols-3">
                        <div class="text-center p-4 border rounded-lg">
                            <h4 class="font-semibold">Application Version</h4>
                            <p class="text-sm text-muted-foreground">v1.0.0</p>
                        </div>
                        
                        <div class="text-center p-4 border rounded-lg">
                            <h4 class="font-semibold">Database Status</h4>
                            <p class="text-sm text-green-600">Connected</p>
                        </div>
                        
                        <div class="text-center p-4 border rounded-lg">
                            <h4 class="font-semibold">Last Backup</h4>
                            <p class="text-sm text-muted-foreground">2 hours ago</p>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>