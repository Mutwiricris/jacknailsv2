<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { ref, computed } from 'vue';
import { 
    Users,
    Search,
    Filter,
    Eye,
    Mail,
    Phone,
    Calendar,
    DollarSign,
    Star,
    UserCheck,
    Download
} from 'lucide-vue-next';

interface Client {
    id: number;
    name: string;
    email: string;
    phone: string;
    total_bookings: number;
    total_spent: string;
    last_visit: string;
    status: 'active' | 'inactive' | 'vip';
    created_at: string;
    favorite_services: string[];
    notes?: string;
}

interface Props {
    clients: Client[];
    stats: {
        total_clients: number;
        new_this_month: number;
        vip_clients: number;
        average_spent: string;
    };
}

// Mock data for demonstration - replace with actual props
const props = withDefaults(defineProps<Partial<Props>>(), {
    clients: () => [
        {
            id: 1,
            name: 'Jane Doe',
            email: 'jane@example.com',
            phone: '+254 701 234 567',
            total_bookings: 12,
            total_spent: 'KSh 28,500',
            last_visit: '2024-01-10',
            status: 'vip',
            created_at: '2023-08-15',
            favorite_services: ['Gel Manicure', 'Pedicure'],
            notes: 'Prefers pink colors and afternoon appointments'
        },
        {
            id: 2,
            name: 'Mary Smith',
            email: 'mary@example.com',
            phone: '+254 702 345 678',
            total_bookings: 5,
            total_spent: 'KSh 12,000',
            last_visit: '2024-01-08',
            status: 'active',
            created_at: '2023-11-20',
            favorite_services: ['Acrylic Full Set'],
        },
        {
            id: 3,
            name: 'Sarah Wilson',
            email: 'sarah@example.com',
            phone: '+254 703 456 789',
            total_bookings: 2,
            total_spent: 'KSh 4,500',
            last_visit: '2023-12-15',
            status: 'inactive',
            created_at: '2023-10-05',
            favorite_services: ['Classic Manicure'],
        }
    ],
    stats: () => ({
        total_clients: 89,
        new_this_month: 12,
        vip_clients: 8,
        average_spent: 'KSh 15,200'
    })
});

const searchQuery = ref('');
const statusFilter = ref('all');

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Clients', href: '/dashboard/clients' },
];

const filteredClients = computed(() => {
    let filtered = props.clients || [];

    // Search filter
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        filtered = filtered.filter(client => 
            client.name.toLowerCase().includes(query) ||
            client.email.toLowerCase().includes(query) ||
            client.phone.includes(query)
        );
    }

    // Status filter
    if (statusFilter.value && statusFilter.value !== 'all') {
        filtered = filtered.filter(client => client.status === statusFilter.value);
    }

    return filtered;
});

const getStatusColor = (status: string) => {
    switch (status) {
        case 'vip': return 'bg-yellow-100 text-yellow-800 border-yellow-200';
        case 'active': return 'bg-green-100 text-green-800 border-green-200';
        case 'inactive': return 'bg-gray-100 text-gray-800 border-gray-200';
        default: return 'bg-gray-100 text-gray-800 border-gray-200';
    }
};

const getStatusIcon = (status: string) => {
    switch (status) {
        case 'vip': return Star;
        case 'active': return UserCheck;
        case 'inactive': return Users;
        default: return Users;
    }
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};

const clearFilters = () => {
    searchQuery.value = '';
    statusFilter.value = 'all';
};
</script>

<template>
    <Head title="Manage Clients" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-6 overflow-x-auto">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Client Management</h1>
                    <p class="text-muted-foreground">View and manage your client database</p>
                </div>
                <div class="flex gap-2">
                    <Button variant="outline">
                        <Download class="w-4 h-4 mr-2" />
                        Export
                    </Button>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid gap-4 md:grid-cols-4">
                <Card>
                    <CardContent class="p-4">
                        <div class="flex items-center gap-2">
                            <Users class="h-5 w-5 text-blue-600" />
                            <div>
                                <p class="text-sm text-muted-foreground">Total Clients</p>
                                <p class="text-2xl font-bold">{{ props.stats?.total_clients || 0 }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent class="p-4">
                        <div class="flex items-center gap-2">
                            <UserCheck class="h-5 w-5 text-green-600" />
                            <div>
                                <p class="text-sm text-muted-foreground">New This Month</p>
                                <p class="text-2xl font-bold">{{ props.stats?.new_this_month || 0 }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent class="p-4">
                        <div class="flex items-center gap-2">
                            <Star class="h-5 w-5 text-yellow-600" />
                            <div>
                                <p class="text-sm text-muted-foreground">VIP Clients</p>
                                <p class="text-2xl font-bold">{{ props.stats?.vip_clients || 0 }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent class="p-4">
                        <div class="flex items-center gap-2">
                            <DollarSign class="h-5 w-5 text-green-600" />
                            <div>
                                <p class="text-sm text-muted-foreground">Avg. Spent</p>
                                <p class="text-2xl font-bold">{{ props.stats?.average_spent || 'KSh 0' }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Filters -->
            <Card>
                <CardContent class="p-4">
                    <div class="flex gap-4 items-end">
                        <div class="flex-1 space-y-2">
                            <label class="text-sm font-medium">Search Clients</label>
                            <div class="relative">
                                <Search class="absolute left-3 top-3 h-4 w-4 text-muted-foreground" />
                                <Input
                                    v-model="searchQuery"
                                    placeholder="Search by name, email, or phone..."
                                    class="pl-9"
                                />
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-medium">Status</label>
                            <Select v-model="statusFilter">
                                <SelectTrigger class="w-48">
                                    <SelectValue placeholder="All Statuses" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">All Statuses</SelectItem>
                                    <SelectItem value="vip">VIP Clients</SelectItem>
                                    <SelectItem value="active">Active</SelectItem>
                                    <SelectItem value="inactive">Inactive</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <Button @click="clearFilters" variant="outline">
                            Clear
                        </Button>
                    </div>
                </CardContent>
            </Card>

            <!-- Clients List -->
            <Card>
                <CardHeader>
                    <CardTitle>Clients ({{ filteredClients.length }})</CardTitle>
                    <CardDescription>Manage your client relationships</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="space-y-4">
                        <div v-if="filteredClients.length === 0" class="text-center py-12 text-muted-foreground">
                            <Users class="h-12 w-12 mx-auto mb-4 opacity-50" />
                            <h3 class="font-semibold mb-2">No clients found</h3>
                            <p>No clients match your current filters.</p>
                        </div>

                        <div v-else class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                            <div 
                                v-for="client in filteredClients" 
                                :key="client.id"
                                class="border rounded-lg p-4 space-y-4 hover:bg-muted/50 transition-colors"
                            >
                                <!-- Client Header -->
                                <div class="flex items-start justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 bg-gradient-to-r from-pink-500 to-purple-500 rounded-full flex items-center justify-center text-white font-bold">
                                            {{ client.name.charAt(0).toUpperCase() }}
                                        </div>
                                        <div>
                                            <h3 class="font-semibold">{{ client.name }}</h3>
                                            <Badge :class="getStatusColor(client.status)" class="text-xs">
                                                <component :is="getStatusIcon(client.status)" class="w-3 h-3 mr-1" />
                                                {{ client.status.toUpperCase() }}
                                            </Badge>
                                        </div>
                                    </div>
                                </div>

                                <!-- Contact Info -->
                                <div class="space-y-2 text-sm">
                                    <div class="flex items-center gap-2">
                                        <Mail class="w-4 h-4 text-muted-foreground" />
                                        <span class="truncate">{{ client.email }}</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <Phone class="w-4 h-4 text-muted-foreground" />
                                        <span>{{ client.phone }}</span>
                                    </div>
                                </div>

                                <!-- Stats -->
                                <div class="grid grid-cols-2 gap-4 text-sm">
                                    <div>
                                        <span class="text-muted-foreground">Total Bookings:</span>
                                        <p class="font-bold">{{ client.total_bookings }}</p>
                                    </div>
                                    <div>
                                        <span class="text-muted-foreground">Total Spent:</span>
                                        <p class="font-bold text-green-600">{{ client.total_spent }}</p>
                                    </div>
                                </div>

                                <!-- Last Visit -->
                                <div class="text-sm">
                                    <span class="text-muted-foreground">Last Visit:</span>
                                    <p class="font-medium">{{ formatDate(client.last_visit) }}</p>
                                </div>

                                <!-- Favorite Services -->
                                <div v-if="client.favorite_services?.length" class="space-y-2">
                                    <span class="text-sm text-muted-foreground">Favorite Services:</span>
                                    <div class="flex flex-wrap gap-1">
                                        <Badge 
                                            v-for="service in client.favorite_services" 
                                            :key="service"
                                            variant="outline"
                                            class="text-xs"
                                        >
                                            {{ service }}
                                        </Badge>
                                    </div>
                                </div>

                                <!-- Notes -->
                                <div v-if="client.notes" class="text-sm">
                                    <span class="text-muted-foreground">Notes:</span>
                                    <p class="text-sm bg-muted p-2 rounded mt-1">{{ client.notes }}</p>
                                </div>

                                <!-- Actions -->
                                <div class="flex items-center gap-2 pt-2 border-t">
                                    <Button size="sm" variant="outline" class="flex-1">
                                        <Eye class="w-3 h-3 mr-1" />
                                        View
                                    </Button>
                                    <Button size="sm" variant="outline" class="flex-1">
                                        <Calendar class="w-3 h-3 mr-1" />
                                        Book
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>