<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { 
    Calendar,
    Users,
    Scissors,
    TrendingUp,
    Clock,
    CheckCircle,
    AlertCircle,
    XCircle,
    Plus,
    Eye,
    DollarSign
} from 'lucide-vue-next';

interface DashboardStats {
    totalBookings: number;
    todayBookings: number;
    totalClients: number;
    totalRevenue: string;
    upcomingBookings: number;
    completedBookings: number;
    cancelledBookings: number;
    pendingBookings: number;
}

interface RecentBooking {
    id: number;
    booking_reference: string;
    client_name: string;
    service_name: string;
    appointment_date: string;
    start_time: string;
    status: string;
    total_amount: string;
}

interface Props {
    stats: DashboardStats;
    recentBookings: RecentBooking[];
    todaysBookings: RecentBooking[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
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
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-6 overflow-x-auto">
            <!-- Welcome Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Dashboard Overview</h1>
                    <p class="text-muted-foreground">Welcome back! Here's what's happening at Jacknails today.</p>
                </div>
                <div class="flex gap-2">
                    <Button as-child>
                        <Link href="/booking/stepper/start">
                            <Plus class="w-4 h-4 mr-2" />
                            New Booking
                        </Link>
                    </Button>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Bookings</CardTitle>
                        <Calendar class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.totalBookings }}</div>
                        <p class="text-xs text-muted-foreground">
                            {{ stats.todayBookings }} scheduled today
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Clients</CardTitle>
                        <Users class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.totalClients }}</div>
                        <p class="text-xs text-muted-foreground">
                            Active client base
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Revenue</CardTitle>
                        <DollarSign class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.totalRevenue }}</div>
                        <p class="text-xs text-muted-foreground">
                            <TrendingUp class="inline w-3 h-3 mr-1" />
                            +12% from last month
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Upcoming</CardTitle>
                        <Clock class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.upcomingBookings }}</div>
                        <p class="text-xs text-muted-foreground">
                            Next 7 days
                        </p>
                    </CardContent>
                </Card>
            </div>

            <!-- Status Overview -->
            <div class="grid gap-4 md:grid-cols-4">
                <Card class="border-green-200 bg-green-50/50">
                    <CardContent class="p-4">
                        <div class="flex items-center gap-2">
                            <CheckCircle class="h-5 w-5 text-green-600" />
                            <div>
                                <p class="text-sm font-medium text-green-900">Completed</p>
                                <p class="text-2xl font-bold text-green-900">{{ stats.completedBookings }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card class="border-blue-200 bg-blue-50/50">
                    <CardContent class="p-4">
                        <div class="flex items-center gap-2">
                            <CheckCircle class="h-5 w-5 text-blue-600" />
                            <div>
                                <p class="text-sm font-medium text-blue-900">Confirmed</p>
                                <p class="text-2xl font-bold text-blue-900">{{ stats.upcomingBookings }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card class="border-yellow-200 bg-yellow-50/50">
                    <CardContent class="p-4">
                        <div class="flex items-center gap-2">
                            <Clock class="h-5 w-5 text-yellow-600" />
                            <div>
                                <p class="text-sm font-medium text-yellow-900">Pending</p>
                                <p class="text-2xl font-bold text-yellow-900">{{ stats.pendingBookings }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card class="border-red-200 bg-red-50/50">
                    <CardContent class="p-4">
                        <div class="flex items-center gap-2">
                            <XCircle class="h-5 w-5 text-red-600" />
                            <div>
                                <p class="text-sm font-medium text-red-900">Cancelled</p>
                                <p class="text-2xl font-bold text-red-900">{{ stats.cancelledBookings }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Main Content Grid -->
            <div class="grid gap-4 md:grid-cols-2">
                <!-- Today's Appointments -->
                <Card>
                    <CardHeader>
                        <div class="flex items-center justify-between">
                            <div>
                                <CardTitle>Today's Appointments</CardTitle>
                                <CardDescription>{{ todaysBookings.length }} appointments scheduled</CardDescription>
                            </div>
                            <Button variant="outline" size="sm" as-child>
                                <Link href="/dashboard/bookings">
                                    <Eye class="w-4 h-4 mr-2" />
                                    View All
                                </Link>
                            </Button>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <div v-if="todaysBookings.length === 0" class="text-center py-6 text-muted-foreground">
                                <Calendar class="h-12 w-12 mx-auto mb-2 opacity-50" />
                                <p>No appointments scheduled for today</p>
                            </div>
                            <div v-else v-for="booking in todaysBookings" :key="booking.id" class="flex items-center justify-between p-3 rounded-lg border bg-muted/50">
                                <div class="flex items-center gap-3">
                                    <component :is="getStatusIcon(booking.status)" class="h-5 w-5 text-muted-foreground" />
                                    <div>
                                        <p class="font-medium">{{ booking.client_name }}</p>
                                        <p class="text-sm text-muted-foreground">{{ booking.service_name }}</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="font-medium">{{ booking.start_time }}</p>
                                    <Badge :class="getStatusColor(booking.status)" class="text-xs">
                                        {{ booking.status }}
                                    </Badge>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Recent Bookings -->
                <Card>
                    <CardHeader>
                        <div class="flex items-center justify-between">
                            <div>
                                <CardTitle>Recent Bookings</CardTitle>
                                <CardDescription>Latest booking activity</CardDescription>
                            </div>
                            <Button variant="outline" size="sm" as-child>
                                <Link href="/dashboard/bookings">
                                    <Eye class="w-4 h-4 mr-2" />
                                    View All
                                </Link>
                            </Button>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <div v-for="booking in recentBookings.slice(0, 5)" :key="booking.id" class="flex items-center justify-between p-3 rounded-lg border bg-muted/50">
                                <div class="flex items-center gap-3">
                                    <component :is="getStatusIcon(booking.status)" class="h-5 w-5 text-muted-foreground" />
                                    <div>
                                        <p class="font-medium">{{ booking.client_name }}</p>
                                        <p class="text-sm text-muted-foreground">{{ booking.service_name }}</p>
                                        <p class="text-xs text-muted-foreground">{{ booking.booking_reference }}</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="font-medium">{{ booking.total_amount }}</p>
                                    <Badge :class="getStatusColor(booking.status)" class="text-xs">
                                        {{ booking.status }}
                                    </Badge>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Quick Actions -->
            <Card>
                <CardHeader>
                    <CardTitle>Quick Actions</CardTitle>
                    <CardDescription>Common management tasks</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="grid gap-4 md:grid-cols-3 lg:grid-cols-6">
                        <Button variant="outline" class="h-20 flex-col gap-2" as-child>
                            <Link href="/booking/stepper/start">
                                <Plus class="h-5 w-5" />
                                <span class="text-xs">New Booking</span>
                            </Link>
                        </Button>
                        <Button variant="outline" class="h-20 flex-col gap-2" as-child>
                            <Link href="/dashboard/bookings">
                                <Calendar class="h-5 w-5" />
                                <span class="text-xs">Manage Bookings</span>
                            </Link>
                        </Button>
                        <Button variant="outline" class="h-20 flex-col gap-2" as-child>
                            <Link href="/dashboard/services">
                                <Scissors class="h-5 w-5" />
                                <span class="text-xs">Manage Services</span>
                            </Link>
                        </Button>
                        <Button variant="outline" class="h-20 flex-col gap-2" as-child>
                            <Link href="/dashboard/clients">
                                <Users class="h-5 w-5" />
                                <span class="text-xs">View Clients</span>
                            </Link>
                        </Button>
                        <Button variant="outline" class="h-20 flex-col gap-2" as-child>
                            <Link href="/dashboard/timeslots">
                                <Clock class="h-5 w-5" />
                                <span class="text-xs">Time Slots</span>
                            </Link>
                        </Button>
                        <Button variant="outline" class="h-20 flex-col gap-2" as-child>
                            <Link href="/dashboard/analytics">
                                <TrendingUp class="h-5 w-5" />
                                <span class="text-xs">Analytics</span>
                            </Link>
                        </Button>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
