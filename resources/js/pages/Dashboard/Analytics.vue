<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { ref } from 'vue';
import { 
    BarChart3,
    TrendingUp,
    TrendingDown,
    DollarSign,
    Users,
    Calendar,
    Clock,
    Star,
    Download,
    Filter
} from 'lucide-vue-next';

interface AnalyticsData {
    revenue: {
        total: string;
        growth: number;
        monthly: Array<{ month: string; amount: number }>;
    };
    bookings: {
        total: number;
        growth: number;
        by_status: Array<{ status: string; count: number; percentage: number }>;
        by_service: Array<{ service: string; count: number; revenue: string }>;
    };
    clients: {
        total: number;
        new: number;
        retention_rate: number;
        top_clients: Array<{ name: string; bookings: number; spent: string }>;
    };
    performance: {
        avg_appointment_value: string;
        peak_hours: Array<{ hour: string; bookings: number }>;
        popular_services: Array<{ service: string; bookings: number; revenue: string }>;
    };
}

interface Props {
    analytics: AnalyticsData;
    period: string;
}

// Mock data for demonstration - replace with actual props
const props = withDefaults(defineProps<Partial<Props>>(), {
    analytics: () => ({
        revenue: {
            total: 'KSh 245,600',
            growth: 12.5,
            monthly: [
                { month: 'Jan', amount: 18500 },
                { month: 'Feb', amount: 22300 },
                { month: 'Mar', amount: 19800 },
                { month: 'Apr', amount: 25100 },
                { month: 'May', amount: 28900 },
                { month: 'Jun', amount: 31200 }
            ]
        },
        bookings: {
            total: 156,
            growth: 8.3,
            by_status: [
                { status: 'completed', count: 134, percentage: 86 },
                { status: 'confirmed', count: 12, percentage: 8 },
                { status: 'pending', count: 5, percentage: 3 },
                { status: 'cancelled', count: 5, percentage: 3 }
            ],
            by_service: [
                { service: 'Gel Manicure', count: 45, revenue: 'KSh 112,500' },
                { service: 'Acrylic Full Set', count: 28, revenue: 'KSh 112,000' },
                { service: 'Pedicure', count: 35, revenue: 'KSh 63,000' },
                { service: 'Classic Manicure', count: 25, revenue: 'KSh 37,500' }
            ]
        },
        clients: {
            total: 89,
            new: 12,
            retention_rate: 78,
            top_clients: [
                { name: 'Jane Doe', bookings: 12, spent: 'KSh 28,500' },
                { name: 'Mary Smith', bookings: 8, spent: 'KSh 22,000' },
                { name: 'Sarah Wilson', bookings: 6, spent: 'KSh 18,500' }
            ]
        },
        performance: {
            avg_appointment_value: 'KSh 1,575',
            peak_hours: [
                { hour: '2:00 PM', bookings: 18 },
                { hour: '3:00 PM', bookings: 16 },
                { hour: '4:00 PM', bookings: 15 }
            ],
            popular_services: [
                { service: 'Gel Manicure', bookings: 45, revenue: 'KSh 112,500' },
                { service: 'Acrylic Full Set', bookings: 28, revenue: 'KSh 112,000' },
                { service: 'Pedicure', bookings: 35, revenue: 'KSh 63,000' }
            ]
        }
    }),
    period: () => 'last_month'
});

const selectedPeriod = ref(props.period || 'last_month');

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Analytics', href: '/dashboard/analytics' },
];

const getStatusColor = (status: string) => {
    switch (status) {
        case 'completed': return 'bg-green-100 text-green-800 border-green-200';
        case 'confirmed': return 'bg-blue-100 text-blue-800 border-blue-200';
        case 'pending': return 'bg-yellow-100 text-yellow-800 border-yellow-200';
        case 'cancelled': return 'bg-red-100 text-red-800 border-red-200';
        default: return 'bg-gray-100 text-gray-800 border-gray-200';
    }
};

const formatCurrency = (amount: number) => {
    return 'KSh ' + new Intl.NumberFormat().format(amount);
};

const getMaxAmount = () => {
    return Math.max(...(props.analytics?.revenue.monthly.map(m => m.amount) || [0]));
};
</script>

<template>
    <Head title="Analytics Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-6 overflow-x-auto">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Analytics Dashboard</h1>
                    <p class="text-muted-foreground">Track your salon's performance and trends</p>
                </div>
                <div class="flex gap-2">
                    <Select v-model="selectedPeriod">
                        <SelectTrigger class="w-48">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="last_week">Last Week</SelectItem>
                            <SelectItem value="last_month">Last Month</SelectItem>
                            <SelectItem value="last_quarter">Last Quarter</SelectItem>
                            <SelectItem value="last_year">Last Year</SelectItem>
                        </SelectContent>
                    </Select>
                    <Button variant="outline">
                        <Download class="w-4 h-4 mr-2" />
                        Export
                    </Button>
                </div>
            </div>

            <!-- Key Metrics -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <Card>
                    <CardContent class="p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-muted-foreground">Total Revenue</p>
                                <p class="text-2xl font-bold">{{ analytics?.revenue.total }}</p>
                                <div class="flex items-center gap-1 text-sm">
                                    <TrendingUp v-if="analytics?.revenue.growth > 0" class="w-3 h-3 text-green-600" />
                                    <TrendingDown v-else class="w-3 h-3 text-red-600" />
                                    <span :class="analytics?.revenue.growth > 0 ? 'text-green-600' : 'text-red-600'">
                                        {{ Math.abs(analytics?.revenue.growth || 0) }}%
                                    </span>
                                    <span class="text-muted-foreground">vs last period</span>
                                </div>
                            </div>
                            <DollarSign class="h-8 w-8 text-green-600" />
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent class="p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-muted-foreground">Total Bookings</p>
                                <p class="text-2xl font-bold">{{ analytics?.bookings.total }}</p>
                                <div class="flex items-center gap-1 text-sm">
                                    <TrendingUp v-if="analytics?.bookings.growth > 0" class="w-3 h-3 text-green-600" />
                                    <TrendingDown v-else class="w-3 h-3 text-red-600" />
                                    <span :class="analytics?.bookings.growth > 0 ? 'text-green-600' : 'text-red-600'">
                                        {{ Math.abs(analytics?.bookings.growth || 0) }}%
                                    </span>
                                    <span class="text-muted-foreground">vs last period</span>
                                </div>
                            </div>
                            <Calendar class="h-8 w-8 text-blue-600" />
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent class="p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-muted-foreground">Total Clients</p>
                                <p class="text-2xl font-bold">{{ analytics?.clients.total }}</p>
                                <div class="flex items-center gap-1 text-sm">
                                    <span class="text-green-600">+{{ analytics?.clients.new }}</span>
                                    <span class="text-muted-foreground">new this period</span>
                                </div>
                            </div>
                            <Users class="h-8 w-8 text-purple-600" />
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent class="p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-muted-foreground">Avg. Appointment</p>
                                <p class="text-2xl font-bold">{{ analytics?.performance.avg_appointment_value }}</p>
                                <div class="flex items-center gap-1 text-sm">
                                    <span class="text-blue-600">{{ analytics?.clients.retention_rate }}%</span>
                                    <span class="text-muted-foreground">retention rate</span>
                                </div>
                            </div>
                            <BarChart3 class="h-8 w-8 text-orange-600" />
                        </div>
                    </CardContent>
                </Card>
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <!-- Revenue Chart -->
                <Card>
                    <CardHeader>
                        <CardTitle>Revenue Trend</CardTitle>
                        <CardDescription>Monthly revenue over time</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <div 
                                v-for="month in analytics?.revenue.monthly" 
                                :key="month.month"
                                class="flex items-center justify-between"
                            >
                                <div class="flex items-center gap-4 flex-1">
                                    <span class="w-8 text-sm font-medium">{{ month.month }}</span>
                                    <div class="flex-1 bg-muted rounded-full h-2 overflow-hidden">
                                        <div 
                                            class="bg-gradient-to-r from-green-500 to-blue-500 h-full rounded-full transition-all duration-500"
                                            :style="{ width: `${(month.amount / getMaxAmount()) * 100}%` }"
                                        ></div>
                                    </div>
                                </div>
                                <span class="text-sm font-medium ml-4">{{ formatCurrency(month.amount) }}</span>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Booking Status -->
                <Card>
                    <CardHeader>
                        <CardTitle>Booking Status</CardTitle>
                        <CardDescription>Distribution of booking statuses</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <div 
                                v-for="status in analytics?.bookings.by_status" 
                                :key="status.status"
                                class="flex items-center justify-between"
                            >
                                <div class="flex items-center gap-3">
                                    <Badge :class="getStatusColor(status.status)" class="capitalize">
                                        {{ status.status }}
                                    </Badge>
                                    <div class="flex-1 bg-muted rounded-full h-2 w-32 overflow-hidden">
                                        <div 
                                            class="bg-current h-full rounded-full transition-all duration-500"
                                            :style="{ width: `${status.percentage}%` }"
                                        ></div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span class="text-sm font-medium">{{ status.count }}</span>
                                    <span class="text-xs text-muted-foreground ml-1">({{ status.percentage }}%)</span>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <!-- Popular Services -->
                <Card>
                    <CardHeader>
                        <CardTitle>Popular Services</CardTitle>
                        <CardDescription>Top performing services by revenue</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <div 
                                v-for="(service, index) in analytics?.performance.popular_services" 
                                :key="service.service"
                                class="flex items-center justify-between p-3 rounded-lg border bg-muted/50"
                            >
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 bg-gradient-to-r from-pink-500 to-purple-500 rounded-full flex items-center justify-center text-white font-bold text-sm">
                                        {{ index + 1 }}
                                    </div>
                                    <div>
                                        <h4 class="font-medium">{{ service.service }}</h4>
                                        <p class="text-sm text-muted-foreground">{{ service.bookings }} bookings</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="font-bold text-green-600">{{ service.revenue }}</p>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Top Clients -->
                <Card>
                    <CardHeader>
                        <CardTitle>Top Clients</CardTitle>
                        <CardDescription>Highest value clients by spending</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <div 
                                v-for="(client, index) in analytics?.clients.top_clients" 
                                :key="client.name"
                                class="flex items-center justify-between p-3 rounded-lg border bg-muted/50"
                            >
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 bg-gradient-to-r from-blue-500 to-purple-500 rounded-full flex items-center justify-center text-white font-bold text-sm">
                                        {{ client.name.charAt(0) }}
                                    </div>
                                    <div>
                                        <h4 class="font-medium">{{ client.name }}</h4>
                                        <p class="text-sm text-muted-foreground">{{ client.bookings }} appointments</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="font-bold text-green-600">{{ client.spent }}</p>
                                    <Badge v-if="index === 0" class="bg-yellow-100 text-yellow-800 text-xs">
                                        <Star class="w-3 h-3 mr-1" />
                                        VIP
                                    </Badge>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Peak Hours -->
            <Card>
                <CardHeader>
                    <CardTitle>Peak Hours Analysis</CardTitle>
                    <CardDescription>Busiest appointment times</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="grid gap-4 md:grid-cols-3">
                        <div 
                            v-for="(peak, index) in analytics?.performance.peak_hours" 
                            :key="peak.hour"
                            class="text-center p-4 rounded-lg border bg-muted/50"
                        >
                            <div class="flex items-center justify-center gap-2 mb-2">
                                <Clock class="w-5 h-5 text-blue-600" />
                                <h3 class="font-semibold">{{ peak.hour }}</h3>
                            </div>
                            <p class="text-2xl font-bold text-blue-600">{{ peak.bookings }}</p>
                            <p class="text-sm text-muted-foreground">appointments</p>
                            <Badge v-if="index === 0" class="mt-2 bg-blue-100 text-blue-800">
                                Peak Time
                            </Badge>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>