<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Switch } from '@/components/ui/switch';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { useToast } from '@/components/ui/toast';
import { ref, computed } from 'vue';
import { 
    Scissors,
    Plus,
    Edit,
    Trash2,
    Upload,
    CheckCircle,
    XCircle,
    CheckSquare,
    Square,
    Search,
    Star,
    Clock,
    DollarSign,
    Save,
    X
} from 'lucide-vue-next';

interface Service {
    id: number;
    name: string;
    description: string;
    price: number;
    duration_minutes: number;
    formatted_price: string;
    formatted_duration: string;
    status: string;
    is_popular: boolean;
    is_active: boolean;
    image_url?: string;
    created_at: string;
    updated_at: string;
}

interface Props {
    services: Service[];
}

// Mock data for demonstration - replace with actual props
const props = withDefaults(defineProps<Partial<Props>>(), {
    services: () => [
        {
            id: 1,
            name: 'Classic Manicure',
            description: 'Basic nail care with shaping, cuticle care, and polish application',
            price: 1500,
            duration_minutes: 45,
            formatted_price: 'KSh 1,500',
            formatted_duration: '45 min',
            status: 'active',
            is_popular: false,
            is_active: true,
            created_at: '2024-01-01',
            updated_at: '2024-01-01'
        },
        {
            id: 2,
            name: 'Gel Manicure',
            description: 'Long-lasting gel polish application with UV/LED curing',
            price: 2500,
            duration_minutes: 60,
            formatted_price: 'KSh 2,500',
            formatted_duration: '1h',
            status: 'active',
            is_popular: true,
            is_active: true,
            created_at: '2024-01-01',
            updated_at: '2024-01-01'
        },
        {
            id: 3,
            name: 'Acrylic Full Set',
            description: 'Complete acrylic nail extension with shaping and polish',
            price: 4000,
            duration_minutes: 120,
            formatted_price: 'KSh 4,000',
            formatted_duration: '2h',
            category: 'acrylics',
            is_popular: true,
            is_active: true,
            created_at: '2024-01-01',
            updated_at: '2024-01-01'
        },
        {
            id: 4,
            name: 'Basic Pedicure',
            description: 'Foot care with nail trimming, shaping, and polish',
            price: 1800,
            duration_minutes: 50,
            formatted_price: 'KSh 1,800',
            formatted_duration: '50 min',
            category: 'pedicure',
            is_popular: false,
            is_active: true,
            created_at: '2024-01-01',
            updated_at: '2024-01-01'
        }
    ]
});

const { success, error } = useToast();

const searchQuery = ref('');
const showCreateDialog = ref(false);
const showEditDialog = ref(false);
const editingService = ref<Service | null>(null);
const selectedServices = ref<number[]>([]);
const showImageUpload = ref(false);
const uploadingService = ref<Service | null>(null);

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Services', href: '/dashboard/services' },
];

const categories = [
    { value: 'all', label: 'All Categories' },
    { value: 'manicure', label: 'Manicure Services' },
    { value: 'pedicure', label: 'Pedicure Services' },
    { value: 'acrylics', label: 'Acrylic Services' },
    { value: 'enhancements', label: 'Nail Enhancements' },
    { value: 'removal', label: 'Removal Services' },
];

const createForm = useForm({
    name: '',
    description: '',
    price: 0,
    duration_minutes: 30,
    is_popular: false,
    image: null as File | null,
});

const editForm = useForm({
    name: '',
    description: '',
    price: 0,
    duration_minutes: 30,
    is_popular: false,
    image: null as File | null,
});

const filteredServices = computed(() => {
    let filtered = props.services || [];

    // Search filter
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        filtered = filtered.filter(service => 
            service.name.toLowerCase().includes(query) ||
            service.description.toLowerCase().includes(query) ||
            service.category.toLowerCase().includes(query)
        );
    }

    // Category filter

    return filtered;
});

const servicesByCategory = computed(() => {
    const grouped: Record<string, Service[]> = {};
    filteredServices.value.forEach(service => {
        if (!grouped[service.category]) {
            grouped[service.category] = [];
        }
        grouped[service.category].push(service);
    });
    return grouped;
});

const categoryDisplayNames: Record<string, string> = {
    manicure: 'Manicure Services',
    pedicure: 'Pedicure Services',
    acrylics: 'Acrylic Services',
    enhancements: 'Nail Enhancements & Art',
    removal: 'Removal Services'
};

const createService = () => {
    createForm.post('/dashboard/services', {
        onSuccess: () => {
            success('Service created successfully', 'Service Created');
            showCreateDialog.value = false;
            createForm.reset();
        },
        onError: () => {
            error('Failed to create service', 'Creation Failed');
        }
    });
};

const editService = () => {
    if (!editingService.value) return;
    
    editForm.put(`/dashboard/services/${editingService.value.id}`, {
        onSuccess: () => {
            success('Service updated successfully', 'Service Updated');
            showEditDialog.value = false;
            editingService.value = null;
            editForm.reset();
        },
        onError: () => {
            error('Failed to update service', 'Update Failed');
        }
    });
};

const openEditDialog = (service: Service) => {
    editingService.value = service;
    editForm.name = service.name;
    editForm.description = service.description;
    editForm.price = service.price;
    editForm.duration_minutes = service.duration_minutes;
    editForm.is_popular = service.is_popular;
    editForm.image = null; // Reset image field
    showEditDialog.value = true;
};

const deleteService = (service: Service) => {
    if (confirm(`Are you sure you want to delete "${service.name}"? This action cannot be undone.`)) {
        router.delete(`/dashboard/services/${service.id}`, {
            onSuccess: () => {
                success('Service deleted successfully', 'Service Deleted');
            },
            onError: () => {
                error('Failed to delete service', 'Delete Failed');
            }
        });
    }
};

const toggleServiceStatus = (service: Service) => {
    router.post(`/dashboard/services/${service.id}/toggle-status`, {}, {
        onSuccess: () => {
            success(`Service ${!service.is_active ? 'activated' : 'deactivated'}`, 'Status Updated');
        },
        onError: () => {
            error('Failed to update service status', 'Update Failed');
        }
    });
};

const bulkActivate = () => {
    if (selectedServices.value.length === 0) {
        error('Please select services to activate', 'No Services Selected');
        return;
    }
    
    router.post('/dashboard/services/bulk-activate', {
        service_ids: selectedServices.value
    }, {
        onSuccess: () => {
            success(`${selectedServices.value.length} services activated successfully`, 'Bulk Activation');
            selectedServices.value = [];
        },
        onError: () => {
            error('Failed to activate services', 'Bulk Activation Failed');
        }
    });
};

const bulkDeactivate = () => {
    if (selectedServices.value.length === 0) {
        error('Please select services to deactivate', 'No Services Selected');
        return;
    }
    
    router.post('/dashboard/services/bulk-deactivate', {
        service_ids: selectedServices.value
    }, {
        onSuccess: () => {
            success(`${selectedServices.value.length} services deactivated successfully`, 'Bulk Deactivation');
            selectedServices.value = [];
        },
        onError: () => {
            error('Failed to deactivate services', 'Bulk Deactivation Failed');
        }
    });
};

const selectAllServices = () => {
    selectedServices.value = filteredServices.value.map(service => service.id);
};

const clearSelection = () => {
    selectedServices.value = [];
};

const toggleServiceSelection = (serviceId: number) => {
    const index = selectedServices.value.indexOf(serviceId);
    if (index > -1) {
        selectedServices.value.splice(index, 1);
    } else {
        selectedServices.value.push(serviceId);
    }
};

const openImageUpload = (service: Service) => {
    uploadingService.value = service;
    showImageUpload.value = true;
};

const uploadImage = (event: Event) => {
    const file = (event.target as HTMLInputElement).files?.[0];
    if (!file || !uploadingService.value) return;
    
    const formData = new FormData();
    formData.append('image', file);
    
    router.post(`/dashboard/services/${uploadingService.value.id}/upload-image`, formData, {
        onSuccess: () => {
            success('Service image uploaded successfully', 'Image Upload');
            showImageUpload.value = false;
            uploadingService.value = null;
        },
        onError: () => {
            error('Failed to upload image', 'Upload Failed');
        }
    });
};

const handleCreateImageUpload = (event: Event) => {
    const file = (event.target as HTMLInputElement).files?.[0];
    if (file) {
        createForm.image = file;
    }
};

const handleEditImageUpload = (event: Event) => {
    const file = (event.target as HTMLInputElement).files?.[0];
    if (file) {
        editForm.image = file;
    }
};

const formatPrice = (price: number) => {
    return 'KSh ' + new Intl.NumberFormat().format(price);
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

const handleImageError = (event: Event) => {
    const target = event.target as HTMLImageElement;
    target.src = `/placeholder.svg?height=128&width=200&text=${encodeURIComponent(target.alt)}`;
};
</script>

<template>
    <Head title="Manage Services" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-6 overflow-x-auto">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Manage Services</h1>
                    <p class="text-muted-foreground">Create and manage your nail art services</p>
                </div>
                <Dialog v-model:open="showCreateDialog">
                    <DialogTrigger as-child>
                        <Button>
                            <Plus class="w-4 h-4 mr-2" />
                            Add Service
                        </Button>
                    </DialogTrigger>
                    <DialogContent class="max-w-md">
                        <DialogHeader>
                            <DialogTitle>Create New Service</DialogTitle>
                            <DialogDescription>Add a new service to your offerings</DialogDescription>
                        </DialogHeader>
                        <form @submit.prevent="createService" class="space-y-4">
                            <div class="space-y-2">
                                <Label for="create-name">Service Name</Label>
                                <Input 
                                    id="create-name"
                                    v-model="createForm.name" 
                                    placeholder="e.g., Gel Manicure"
                                    required 
                                />
                            </div>

                            <div class="space-y-2">
                                <Label for="create-description">Description</Label>
                                <Textarea 
                                    id="create-description"
                                    v-model="createForm.description" 
                                    placeholder="Brief description of the service"
                                    rows="3"
                                />
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <Label for="create-price">Price (KSh)</Label>
                                    <Input 
                                        id="create-price"
                                        type="number" 
                                        v-model="createForm.price" 
                                        min="0"
                                        step="50"
                                        required 
                                    />
                                </div>

                                <div class="space-y-2">
                                    <Label for="create-duration">Duration (minutes)</Label>
                                    <Input 
                                        id="create-duration"
                                        type="number" 
                                        v-model="createForm.duration_minutes" 
                                        min="15"
                                        step="15"
                                        required 
                                    />
                                </div>
                            </div>

                            <div class="space-y-2">
                                <Label for="create-image">Service Image</Label>
                                <Input 
                                    id="create-image" 
                                    type="file" 
                                    accept="image/*"
                                    @change="handleCreateImageUpload"
                                    class="cursor-pointer"
                                />
                                <p class="text-xs text-muted-foreground">
                                    Supported formats: JPG, PNG, GIF. Max size: 2MB
                                </p>
                            </div>

                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-2">
                                    <Switch id="create-popular" v-model="createForm.is_popular" />
                                    <Label for="create-popular">Popular Service</Label>
                                </div>

                                <div class="flex items-center space-x-2">
                                    <Switch id="create-active" v-model="createForm.is_active" />
                                    <Label for="create-active">Active</Label>
                                </div>
                            </div>

                            <DialogFooter>
                                <Button type="button" variant="outline" @click="showCreateDialog = false">
                                    Cancel
                                </Button>
                                <Button type="submit" :disabled="createForm.processing">
                                    <Save class="w-4 h-4 mr-2" />
                                    Create Service
                                </Button>
                            </DialogFooter>
                        </form>
                    </DialogContent>
                </Dialog>
            </div>

            <!-- Filters -->
            <Card>
                <CardContent class="p-4">
                    <div class="flex gap-4 items-end">
                        <div class="flex-1 space-y-2">
                            <Label>Search Services</Label>
                            <div class="relative">
                                <Search class="absolute left-3 top-3 h-4 w-4 text-muted-foreground" />
                                <Input
                                    v-model="searchQuery"
                                    placeholder="Search services..."
                                    class="pl-9"
                                />
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label>Category</Label>
                            <Select disabled>
                                <SelectTrigger class="w-48">
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="category in categories" :key="category.value" :value="category.value">
                                        {{ category.label }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Stats -->
            <div class="grid gap-4 md:grid-cols-4">
                <Card>
                    <CardContent class="p-4">
                        <div class="flex items-center gap-2">
                            <Scissors class="h-5 w-5 text-blue-600" />
                            <div>
                                <p class="text-sm text-muted-foreground">Total Services</p>
                                <p class="text-2xl font-bold">{{ filteredServices.length }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent class="p-4">
                        <div class="flex items-center gap-2">
                            <Star class="h-5 w-5 text-yellow-600" />
                            <div>
                                <p class="text-sm text-muted-foreground">Popular Services</p>
                                <p class="text-2xl font-bold">{{ filteredServices.filter(s => s.is_popular).length }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent class="p-4">
                        <div class="flex items-center gap-2">
                            <DollarSign class="h-5 w-5 text-green-600" />
                            <div>
                                <p class="text-sm text-muted-foreground">Avg. Price</p>
                                <p class="text-2xl font-bold">
                                    {{ formatPrice(filteredServices.reduce((sum, s) => sum + s.price, 0) / filteredServices.length || 0) }}
                                </p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent class="p-4">
                        <div class="flex items-center gap-2">
                            <Clock class="h-5 w-5 text-purple-600" />
                            <div>
                                <p class="text-sm text-muted-foreground">Avg. Duration</p>
                                <p class="text-2xl font-bold">
                                    {{ formatDuration(filteredServices.reduce((sum, s) => sum + s.duration_minutes, 0) / filteredServices.length || 0) }}
                                </p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Bulk Actions Toolbar -->
            <Card v-if="selectedServices.length > 0" class="border-blue-200 bg-blue-50">
                <CardContent class="p-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <span class="font-medium">
                                {{ selectedServices.length }} service{{ selectedServices.length !== 1 ? 's' : '' }} selected
                            </span>
                        </div>
                        <div class="flex items-center gap-2">
                            <Button variant="outline" size="sm" @click="bulkActivate">
                                <CheckCircle class="h-4 w-4 mr-2" />
                                Activate Selected
                            </Button>
                            <Button variant="outline" size="sm" @click="bulkDeactivate">
                                <XCircle class="h-4 w-4 mr-2" />
                                Deactivate Selected
                            </Button>
                            <Button variant="outline" size="sm" @click="clearSelection">
                                Clear Selection
                            </Button>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Selection Controls -->
            <Card>
                <CardContent class="p-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <Button variant="outline" size="sm" @click="selectAllServices">
                                <CheckSquare class="h-4 w-4 mr-2" />
                                Select All
                            </Button>
                            <Button variant="outline" size="sm" @click="clearSelection" v-if="selectedServices.length > 0">
                                <Square class="h-4 w-4 mr-2" />
                                Clear All
                            </Button>
                        </div>
                        <div class="text-sm text-muted-foreground">
                            Total services: {{ filteredServices.length }}
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Services by Category -->
            <div class="space-y-6">
                <div v-for="(services, category) in servicesByCategory" :key="category">
                    <Card>
                        <CardHeader>
                            <div class="flex items-center justify-between">
                                <div>
                                    <CardTitle class="flex items-center gap-2">
                                        <Scissors class="h-5 w-5" />
                                        {{ categoryDisplayNames[category] || category }}
                                    </CardTitle>
                                    <CardDescription>{{ services.length }} services in this category</CardDescription>
                                </div>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                                <div 
                                    v-for="service in services" 
                                    :key="service.id"
                                    class="border rounded-lg p-4 space-y-3 hover:bg-muted/50 transition-colors"
                                    :class="{ 'opacity-60': !service.is_active }"
                                >
                                    <!-- Service Header -->
                                    <div class="flex items-start justify-between">
                                        <div class="flex items-center gap-2">
                                            <input 
                                                type="checkbox" 
                                                :checked="selectedServices.includes(service.id)"
                                                @change="toggleServiceSelection(service.id)"
                                                class="rounded border-gray-300"
                                            />
                                        </div>
                                        <div class="flex-1">
                                            <div class="flex items-center gap-2 mb-1">
                                                <h3 class="font-semibold">{{ service.name }}</h3>
                                                <Badge v-if="service.is_popular" class="bg-yellow-100 text-yellow-800 border-yellow-200">
                                                    <Star class="w-3 h-3 mr-1" />
                                                    Popular
                                                </Badge>
                                                <Badge v-if="!service.is_active" variant="secondary">
                                                    Inactive
                                                </Badge>
                                            </div>
                                            <p class="text-sm text-muted-foreground mb-2">{{ service.description }}</p>
                                        </div>
                                    </div>

                                    <!-- Service Image -->
                                    <div class="w-full h-32 bg-gray-100 rounded-lg overflow-hidden mb-3">
                                        <img 
                                            :src="service.image_url" 
                                            :alt="service.name"
                                            class="w-full h-full object-cover"
                                            @error="handleImageError"
                                        />
                                    </div>

                                    <!-- Service Details -->
                                    <div class="grid grid-cols-2 gap-2 text-sm">
                                        <div>
                                            <span class="text-muted-foreground">Price:</span>
                                            <p class="font-bold text-green-600">{{ service.formatted_price }}</p>
                                        </div>
                                        <div>
                                            <span class="text-muted-foreground">Duration:</span>
                                            <p class="font-medium">{{ service.formatted_duration }}</p>
                                        </div>
                                    </div>

                                    <!-- Actions -->
                                    <div class="flex items-center justify-between pt-2 border-t">
                                        <div class="flex items-center gap-1">
                                            <Button
                                                @click="openEditDialog(service)"
                                                size="sm"
                                                variant="outline"
                                            >
                                                <Edit class="w-3 h-3 mr-1" />
                                                Edit
                                            </Button>

                                            <Button
                                                @click="toggleServiceStatus(service)"
                                                size="sm"
                                                :variant="service.is_active ? 'outline' : 'default'"
                                            >
                                                {{ service.is_active ? 'Deactivate' : 'Activate' }}
                                            </Button>

                                            <Button
                                                @click="openImageUpload(service)"
                                                size="sm"
                                                variant="outline"
                                            >
                                                <Upload class="w-3 h-3 mr-1" />
                                                Image
                                            </Button>
                                        </div>

                                        <Button
                                            @click="deleteService(service)"
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
                </div>
            </div>

            <!-- Edit Service Dialog -->
            <Dialog v-model:open="showEditDialog">
                <DialogContent class="max-w-md">
                    <DialogHeader>
                        <DialogTitle>Edit Service</DialogTitle>
                        <DialogDescription>Update service details</DialogDescription>
                    </DialogHeader>
                    <form @submit.prevent="editService" class="space-y-4">
                        <div class="space-y-2">
                            <Label for="edit-name">Service Name</Label>
                            <Input 
                                id="edit-name"
                                v-model="editForm.name" 
                                required 
                            />
                        </div>

                        <div class="space-y-2">
                            <Label for="edit-description">Description</Label>
                            <Textarea 
                                id="edit-description"
                                v-model="editForm.description" 
                                rows="3"
                            />
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label for="edit-price">Price (KSh)</Label>
                                <Input 
                                    id="edit-price"
                                    type="number" 
                                    v-model="editForm.price" 
                                    min="0"
                                    step="50"
                                    required 
                                />
                            </div>

                            <div class="space-y-2">
                                <Label for="edit-duration">Duration (minutes)</Label>
                                <Input 
                                    id="edit-duration"
                                    type="number" 
                                    v-model="editForm.duration_minutes" 
                                    min="15"
                                    step="15"
                                    required 
                                />
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="edit-image">Service Image</Label>
                            <Input 
                                id="edit-image" 
                                type="file" 
                                accept="image/*"
                                @change="handleEditImageUpload"
                                class="cursor-pointer"
                            />
                            <p class="text-xs text-muted-foreground">
                                Leave empty to keep current image. Supported formats: JPG, PNG, GIF. Max size: 2MB
                            </p>
                        </div>

                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-2">
                                <Switch id="edit-popular" v-model="editForm.is_popular" />
                                <Label for="edit-popular">Popular Service</Label>
                            </div>

                            <div class="flex items-center space-x-2">
                                <Switch id="edit-active" v-model="editForm.is_active" />
                                <Label for="edit-active">Active</Label>
                            </div>
                        </div>

                        <DialogFooter>
                            <Button type="button" variant="outline" @click="showEditDialog = false">
                                Cancel
                            </Button>
                            <Button type="submit" :disabled="editForm.processing">
                                <Save class="w-4 h-4 mr-2" />
                                Update Service
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            <!-- Image Upload Modal -->
            <Dialog v-model:open="showImageUpload">
                <DialogContent class="sm:max-w-md">
                    <DialogHeader>
                        <DialogTitle>Upload Service Image</DialogTitle>
                        <DialogDescription>
                            Upload an image for {{ uploadingService?.name }}
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
        </div>
    </AppLayout>
</template>