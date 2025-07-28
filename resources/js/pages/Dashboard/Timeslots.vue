<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { useToast } from '@/components/ui/toast';
import { ref, computed } from 'vue';
import { 
    Clock,
    Plus,
    Edit,
    Trash2,
    Calendar,
    Settings,
    Save,
    X,
    CheckCircle,
    XCircle,
    Users,
    AlertTriangle
} from 'lucide-vue-next';

interface BusinessHours {
    day: string;
    is_open: boolean;
    open_time: string;
    close_time: string;
    break_start?: string;
    break_end?: string;
}

interface TimeSlotTemplate {
    id: number;
    name: string;
    duration_minutes: number;
    buffer_minutes: number;
    max_bookings: number;
    is_active: boolean;
    created_at: string;
}

interface Props {
    businessHours: BusinessHours[];
    timeSlotTemplates: TimeSlotTemplate[];
    settings: {
        default_slot_duration: number;
        default_buffer_time: number;
        advance_booking_days: number;
        min_booking_notice_hours: number;
    };
}

// Mock data for demonstration - replace with actual props
const props = withDefaults(defineProps<Partial<Props>>(), {
    businessHours: () => [
        { day: 'monday', is_open: true, open_time: '09:00', close_time: '18:00' },
        { day: 'tuesday', is_open: true, open_time: '09:00', close_time: '18:00' },
        { day: 'wednesday', is_open: true, open_time: '09:00', close_time: '18:00' },
        { day: 'thursday', is_open: true, open_time: '09:00', close_time: '18:00' },
        { day: 'friday', is_open: true, open_time: '09:00', close_time: '20:00' },
        { day: 'saturday', is_open: true, open_time: '10:00', close_time: '19:00' },
        { day: 'sunday', is_open: false, open_time: '10:00', close_time: '16:00' }
    ],
    timeSlotTemplates: () => [
        {
            id: 1,
            name: 'Standard Appointment',
            duration_minutes: 60,
            buffer_minutes: 15,
            max_bookings: 1,
            is_active: true,
            created_at: '2024-01-01'
        },
        {
            id: 2,
            name: 'Quick Service',
            duration_minutes: 30,
            buffer_minutes: 10,
            max_bookings: 1,
            is_active: true,
            created_at: '2024-01-01'
        },
        {
            id: 3,
            name: 'Extended Session',
            duration_minutes: 120,
            buffer_minutes: 20,
            max_bookings: 1,
            is_active: true,
            created_at: '2024-01-01'
        }
    ],
    settings: () => ({
        default_slot_duration: 60,
        default_buffer_time: 15,
        advance_booking_days: 30,
        min_booking_notice_hours: 4
    })
});

const { success, error } = useToast();

const showBusinessHoursDialog = ref(false);
const showTemplateDialog = ref(false);
const showSettingsDialog = ref(false);
const editingTemplate = ref<TimeSlotTemplate | null>(null);

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Time Slots', href: '/dashboard/timeslots' },
];

const dayNames: Record<string, string> = {
    monday: 'Monday',
    tuesday: 'Tuesday',
    wednesday: 'Wednesday',
    thursday: 'Thursday',
    friday: 'Friday',
    saturday: 'Saturday',
    sunday: 'Sunday'
};

const businessHoursForm = useForm({
    hours: props.businessHours?.map(hour => ({ ...hour })) || []
});

const templateForm = useForm({
    name: '',
    duration_minutes: 60,
    buffer_minutes: 15,
    max_bookings: 1,
    is_active: true
});

const settingsForm = useForm({
    default_slot_duration: props.settings?.default_slot_duration || 60,
    default_buffer_time: props.settings?.default_buffer_time || 15,
    advance_booking_days: props.settings?.advance_booking_days || 30,
    min_booking_notice_hours: props.settings?.min_booking_notice_hours || 4
});

const openBusinessHours = computed(() => {
    return businessHoursForm.hours.filter(hour => hour.is_open);
});

const updateBusinessHours = () => {
    businessHoursForm.put('/dashboard/timeslots/business-hours', {
        onSuccess: () => {
            success('Business hours updated successfully', 'Hours Updated');
            showBusinessHoursDialog.value = false;
        },
        onError: () => {
            error('Failed to update business hours', 'Update Failed');
        }
    });
};

const createTemplate = () => {
    templateForm.post('/dashboard/timeslots/templates', {
        onSuccess: () => {
            success('Time slot template created successfully', 'Template Created');
            showTemplateDialog.value = false;
            templateForm.reset();
        },
        onError: () => {
            error('Failed to create template', 'Creation Failed');
        }
    });
};

const updateTemplate = () => {
    if (!editingTemplate.value) return;
    
    templateForm.put(`/dashboard/timeslots/templates/${editingTemplate.value.id}`, {
        onSuccess: () => {
            success('Template updated successfully', 'Template Updated');
            showTemplateDialog.value = false;
            editingTemplate.value = null;
            templateForm.reset();
        },
        onError: () => {
            error('Failed to update template', 'Update Failed');
        }
    });
};

const openTemplateDialog = (template?: TimeSlotTemplate) => {
    if (template) {
        editingTemplate.value = template;
        templateForm.name = template.name;
        templateForm.duration_minutes = template.duration_minutes;
        templateForm.buffer_minutes = template.buffer_minutes;
        templateForm.max_bookings = template.max_bookings;
        templateForm.is_active = template.is_active;
    } else {
        editingTemplate.value = null;
        templateForm.reset();
    }
    showTemplateDialog.value = true;
};

const deleteTemplate = (template: TimeSlotTemplate) => {
    if (confirm(`Are you sure you want to delete "${template.name}"? This action cannot be undone.`)) {
        router.delete(`/dashboard/timeslots/templates/${template.id}`, {
            onSuccess: () => {
                success('Template deleted successfully', 'Template Deleted');
            },
            onError: () => {
                error('Failed to delete template', 'Delete Failed');
            }
        });
    }
};

const updateSettings = () => {
    settingsForm.put('/dashboard/timeslots/settings', {
        onSuccess: () => {
            success('Time slot settings updated successfully', 'Settings Updated');
            showSettingsDialog.value = false;
        },
        onError: () => {
            error('Failed to update settings', 'Update Failed');
        }
    });
};

const formatTime = (time: string) => {
    const [hours, minutes] = time.split(':');
    const date = new Date();
    date.setHours(parseInt(hours), parseInt(minutes));
    return date.toLocaleTimeString('en-US', { 
        hour: 'numeric', 
        minute: '2-digit', 
        hour12: true 
    });
};

const formatDuration = (minutes: number) => {
    const hours = Math.floor(minutes / 60);
    const mins = minutes % 60;
    
    if (hours > 0 && mins > 0) {
        return `${hours}h ${mins}min`;
    } else if (hours > 0) {
        return `${hours}h`;
    } else {
        return `${mins}min`;
    }
};

const generateTimeSlots = () => {
    if (confirm('This will regenerate all available time slots based on current settings. Continue?')) {
        router.post('/dashboard/timeslots/generate', {}, {
            onSuccess: () => {
                success('Time slots generated successfully', 'Slots Generated');
            },
            onError: () => {
                error('Failed to generate time slots', 'Generation Failed');
            }
        });
    }
};
</script>

<template>
    <Head title="Manage Time Slots" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-6 overflow-x-auto">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Time Slot Management</h1>
                    <p class="text-muted-foreground">Configure business hours, time slots, and availability</p>
                </div>
                <div class="flex gap-2">
                    <Button @click="generateTimeSlots" variant="outline">
                        <Calendar class="w-4 h-4 mr-2" />
                        Generate Slots
                    </Button>
                    <Dialog v-model:open="showSettingsDialog">
                        <DialogTrigger as-child>
                            <Button variant="outline">
                                <Settings class="w-4 h-4 mr-2" />
                                Settings
                            </Button>
                        </DialogTrigger>
                    </Dialog>
                </div>
            </div>

            <!-- Overview Cards -->
            <div class="grid gap-4 md:grid-cols-4">
                <Card>
                    <CardContent class="p-4">
                        <div class="flex items-center gap-2">
                            <Calendar class="h-5 w-5 text-blue-600" />
                            <div>
                                <p class="text-sm text-muted-foreground">Open Days</p>
                                <p class="text-2xl font-bold">{{ openBusinessHours.length }}/7</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent class="p-4">
                        <div class="flex items-center gap-2">
                            <Clock class="h-5 w-5 text-green-600" />
                            <div>
                                <p class="text-sm text-muted-foreground">Templates</p>
                                <p class="text-2xl font-bold">{{ props.timeSlotTemplates?.length || 0 }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent class="p-4">
                        <div class="flex items-center gap-2">
                            <Settings class="h-5 w-5 text-purple-600" />
                            <div>
                                <p class="text-sm text-muted-foreground">Default Duration</p>
                                <p class="text-2xl font-bold">{{ formatDuration(props.settings?.default_slot_duration || 60) }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent class="p-4">
                        <div class="flex items-center gap-2">
                            <AlertTriangle class="h-5 w-5 text-orange-600" />
                            <div>
                                <p class="text-sm text-muted-foreground">Advance Booking</p>
                                <p class="text-2xl font-bold">{{ props.settings?.advance_booking_days || 30 }} days</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Business Hours -->
            <Card>
                <CardHeader>
                    <div class="flex items-center justify-between">
                        <div>
                            <CardTitle>Business Hours</CardTitle>
                            <CardDescription>Configure when your salon is open for appointments</CardDescription>
                        </div>
                        <Dialog v-model:open="showBusinessHoursDialog">
                            <DialogTrigger as-child>
                                <Button variant="outline">
                                    <Edit class="w-4 h-4 mr-2" />
                                    Edit Hours
                                </Button>
                            </DialogTrigger>
                        </Dialog>
                    </div>
                </CardHeader>
                <CardContent>
                    <div class="grid gap-3 md:grid-cols-2 lg:grid-cols-4">
                        <div 
                            v-for="hour in businessHoursForm.hours" 
                            :key="hour.day"
                            class="border rounded-lg p-4"
                            :class="hour.is_open ? 'bg-green-50 border-green-200' : 'bg-gray-50 border-gray-200'"
                        >
                            <div class="flex items-center justify-between mb-2">
                                <h3 class="font-semibold">{{ dayNames[hour.day] }}</h3>
                                <Badge :class="hour.is_open ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'">
                                    {{ hour.is_open ? 'Open' : 'Closed' }}
                                </Badge>
                            </div>
                            <div v-if="hour.is_open" class="text-sm text-muted-foreground">
                                <p>{{ formatTime(hour.open_time) }} - {{ formatTime(hour.close_time) }}</p>
                                <div v-if="hour.break_start && hour.break_end" class="mt-1">
                                    <p class="text-xs">Break: {{ formatTime(hour.break_start) }} - {{ formatTime(hour.break_end) }}</p>
                                </div>
                            </div>
                            <div v-else class="text-sm text-muted-foreground">
                                <p>Closed all day</p>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Time Slot Templates -->
            <Card>
                <CardHeader>
                    <div class="flex items-center justify-between">
                        <div>
                            <CardTitle>Time Slot Templates</CardTitle>
                            <CardDescription>Define different types of appointment slots</CardDescription>
                        </div>
                        <Dialog v-model:open="showTemplateDialog">
                            <DialogTrigger as-child>
                                <Button @click="openTemplateDialog()">
                                    <Plus class="w-4 h-4 mr-2" />
                                    Add Template
                                </Button>
                            </DialogTrigger>
                        </Dialog>
                    </div>
                </CardHeader>
                <CardContent>
                    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                        <div 
                            v-for="template in props.timeSlotTemplates" 
                            :key="template.id"
                            class="border rounded-lg p-4 space-y-3"
                            :class="template.is_active ? 'bg-white' : 'bg-gray-50 opacity-60'"
                        >
                            <div class="flex items-start justify-between">
                                <div>
                                    <h3 class="font-semibold">{{ template.name }}</h3>
                                    <Badge :class="template.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'" class="text-xs mt-1">
                                        {{ template.is_active ? 'Active' : 'Inactive' }}
                                    </Badge>
                                </div>
                            </div>

                            <div class="space-y-2 text-sm">
                                <div class="flex items-center justify-between">
                                    <span class="text-muted-foreground">Duration:</span>
                                    <span class="font-medium">{{ formatDuration(template.duration_minutes) }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-muted-foreground">Buffer:</span>
                                    <span class="font-medium">{{ template.buffer_minutes }}min</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-muted-foreground">Max Bookings:</span>
                                    <span class="font-medium">{{ template.max_bookings }}</span>
                                </div>
                            </div>

                            <div class="flex items-center gap-2 pt-2 border-t">
                                <Button
                                    @click="openTemplateDialog(template)"
                                    size="sm"
                                    variant="outline"
                                    class="flex-1"
                                >
                                    <Edit class="w-3 h-3 mr-1" />
                                    Edit
                                </Button>
                                <Button
                                    @click="deleteTemplate(template)"
                                    size="sm"
                                    variant="destructive"
                                >
                                    <Trash2 class="w-3 h-3" />
                                </Button>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Business Hours Dialog -->
            <Dialog v-model:open="showBusinessHoursDialog">
                <DialogContent class="max-w-2xl">
                    <DialogHeader>
                        <DialogTitle>Edit Business Hours</DialogTitle>
                        <DialogDescription>Configure your salon's opening hours for each day</DialogDescription>
                    </DialogHeader>
                    <form @submit.prevent="updateBusinessHours" class="space-y-4">
                        <div class="space-y-4">
                            <div 
                                v-for="(hour, index) in businessHoursForm.hours" 
                                :key="hour.day"
                                class="border rounded-lg p-4 space-y-3"
                            >
                                <div class="flex items-center justify-between">
                                    <h3 class="font-semibold">{{ dayNames[hour.day] }}</h3>
                                    <div class="flex items-center space-x-2">
                                        <Switch 
                                            :id="`is-open-${hour.day}`" 
                                            v-model="businessHoursForm.hours[index].is_open" 
                                        />
                                        <Label :for="`is-open-${hour.day}`">Open</Label>
                                    </div>
                                </div>

                                <div v-if="hour.is_open" class="grid grid-cols-2 gap-4">
                                    <div class="space-y-2">
                                        <Label>Opening Time</Label>
                                        <Input 
                                            type="time" 
                                            v-model="businessHoursForm.hours[index].open_time"
                                        />
                                    </div>
                                    <div class="space-y-2">
                                        <Label>Closing Time</Label>
                                        <Input 
                                            type="time" 
                                            v-model="businessHoursForm.hours[index].close_time"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <DialogFooter>
                            <Button type="button" variant="outline" @click="showBusinessHoursDialog = false">
                                Cancel
                            </Button>
                            <Button type="submit" :disabled="businessHoursForm.processing">
                                <Save class="w-4 h-4 mr-2" />
                                Save Hours
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            <!-- Template Dialog -->
            <Dialog v-model:open="showTemplateDialog">
                <DialogContent class="max-w-md">
                    <DialogHeader>
                        <DialogTitle>{{ editingTemplate ? 'Edit' : 'Create' }} Time Slot Template</DialogTitle>
                        <DialogDescription>Configure appointment slot parameters</DialogDescription>
                    </DialogHeader>
                    <form @submit.prevent="editingTemplate ? updateTemplate() : createTemplate()" class="space-y-4">
                        <div class="space-y-2">
                            <Label for="template-name">Template Name</Label>
                            <Input 
                                id="template-name"
                                v-model="templateForm.name" 
                                placeholder="e.g., Standard Appointment"
                                required 
                            />
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label for="template-duration">Duration (minutes)</Label>
                                <Input 
                                    id="template-duration"
                                    type="number" 
                                    v-model="templateForm.duration_minutes" 
                                    min="15"
                                    step="15"
                                    required 
                                />
                            </div>

                            <div class="space-y-2">
                                <Label for="template-buffer">Buffer (minutes)</Label>
                                <Input 
                                    id="template-buffer"
                                    type="number" 
                                    v-model="templateForm.buffer_minutes" 
                                    min="0"
                                    step="5"
                                    required 
                                />
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="template-max">Max Concurrent Bookings</Label>
                            <Input 
                                id="template-max"
                                type="number" 
                                v-model="templateForm.max_bookings" 
                                min="1"
                                max="10"
                                required 
                            />
                        </div>

                        <div class="flex items-center space-x-2">
                            <Switch id="template-active" v-model="templateForm.is_active" />
                            <Label for="template-active">Active Template</Label>
                        </div>

                        <DialogFooter>
                            <Button type="button" variant="outline" @click="showTemplateDialog = false">
                                Cancel
                            </Button>
                            <Button type="submit" :disabled="templateForm.processing">
                                <Save class="w-4 h-4 mr-2" />
                                {{ editingTemplate ? 'Update' : 'Create' }} Template
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            <!-- Settings Dialog -->
            <Dialog v-model:open="showSettingsDialog">
                <DialogContent class="max-w-md">
                    <DialogHeader>
                        <DialogTitle>Time Slot Settings</DialogTitle>
                        <DialogDescription>Configure global time slot parameters</DialogDescription>
                    </DialogHeader>
                    <form @submit.prevent="updateSettings" class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label for="settings-duration">Default Duration (min)</Label>
                                <Input 
                                    id="settings-duration"
                                    type="number" 
                                    v-model="settingsForm.default_slot_duration" 
                                    min="15"
                                    step="15"
                                    required 
                                />
                            </div>

                            <div class="space-y-2">
                                <Label for="settings-buffer">Default Buffer (min)</Label>
                                <Input 
                                    id="settings-buffer"
                                    type="number" 
                                    v-model="settingsForm.default_buffer_time" 
                                    min="0"
                                    step="5"
                                    required 
                                />
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="settings-advance">Advance Booking (days)</Label>
                            <Input 
                                id="settings-advance"
                                type="number" 
                                v-model="settingsForm.advance_booking_days" 
                                min="1"
                                max="365"
                                required 
                            />
                        </div>

                        <div class="space-y-2">
                            <Label for="settings-notice">Min Notice (hours)</Label>
                            <Input 
                                id="settings-notice"
                                type="number" 
                                v-model="settingsForm.min_booking_notice_hours" 
                                min="0"
                                max="72"
                                required 
                            />
                        </div>

                        <DialogFooter>
                            <Button type="button" variant="outline" @click="showSettingsDialog = false">
                                Cancel
                            </Button>
                            <Button type="submit" :disabled="settingsForm.processing">
                                <Save class="w-4 h-4 mr-2" />
                                Save Settings
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>
        </div>
    </AppLayout>
</template>