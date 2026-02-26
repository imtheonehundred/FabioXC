<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import StatsCard from '@/Components/StatsCard.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import Card from '@/Components/ui/Card.vue';
import {
    Radio, MonitorPlay, Film, Tv, Users, Server, Wifi, Activity
} from 'lucide-vue-next';
import { ref, onMounted } from 'vue';

interface Props {
    stats: {
        total_streams: number;
        active_streams: number;
        total_connections: number;
        total_lines: number;
        active_lines: number;
        total_movies: number;
        total_series: number;
        total_users: number;
        servers: Array<{
            id: number;
            name: string;
            status: number;
            cpu: number;
            memory: number;
            disk: number;
            clients: number;
        }>;
        stream_activity: Array<{ label: string; value: number }>;
        connection_history: Array<{ label: string; value: number }>;
    };
    recentActivity: Array<{
        id: number;
        action: string;
        description: string;
        username: string;
        ip_address: string;
        created_at: string;
    }>;
}

const props = defineProps<Props>();

const streamChart = ref<any>(null);
const connectionChart = ref<any>(null);
const ApexCharts = ref<any>(null);

onMounted(async () => {
    const mod = await import('vue3-apexcharts');
    ApexCharts.value = mod.default;
});

const streamChartOptions = {
    chart: { type: 'area', height: 250, toolbar: { show: false }, background: 'transparent' },
    theme: { mode: 'dark' },
    colors: ['#6366f1'],
    stroke: { curve: 'smooth', width: 2 },
    fill: { type: 'gradient', gradient: { opacityFrom: 0.4, opacityTo: 0.05 } },
    grid: { borderColor: 'rgba(255,255,255,0.06)', strokeDashArray: 4 },
    xaxis: { categories: props.stats.stream_activity.map((p) => p.label), labels: { style: { colors: '#94a3b8' } } },
    yaxis: { labels: { style: { colors: '#94a3b8' } } },
    dataLabels: { enabled: false },
    tooltip: { theme: 'dark' },
};

const connectionChartOptions = {
    chart: { type: 'bar', height: 250, toolbar: { show: false }, background: 'transparent' },
    theme: { mode: 'dark' },
    colors: ['#22c55e'],
    grid: { borderColor: 'rgba(255,255,255,0.06)', strokeDashArray: 4 },
    xaxis: { categories: props.stats.connection_history.map((p) => p.label), labels: { style: { colors: '#94a3b8' } } },
    yaxis: { labels: { style: { colors: '#94a3b8' } } },
    plotOptions: { bar: { borderRadius: 4, columnWidth: '60%' } },
    dataLabels: { enabled: false },
    tooltip: { theme: 'dark' },
};

function timeAgo(dateStr: string) {
    const diff = Date.now() - new Date(dateStr).getTime();
    const mins = Math.floor(diff / 60000);
    if (mins < 1) return 'just now';
    if (mins < 60) return `${mins}m ago`;
    const hrs = Math.floor(mins / 60);
    if (hrs < 24) return `${hrs}h ago`;
    return `${Math.floor(hrs / 24)}d ago`;
}

function actionIcon(action: string) {
    if (action.includes('login')) return '🔑';
    if (action.includes('stream')) return '📡';
    if (action.includes('line')) return '👤';
    if (action.includes('server')) return '🖥️';
    if (action.includes('setting')) return '⚙️';
    return '📋';
}
</script>

<template>
    <Head title="Dashboard" />
    <AdminLayout>
        <template #title>Dashboard</template>

        <div class="space-y-6">
            <!-- Stats Grid -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <StatsCard
                    title="Active Streams"
                    :value="`${stats.active_streams} / ${stats.total_streams}`"
                    description="Currently running"
                    :icon="Radio"
                    trend="up"
                    trend-value="+3 today"
                />
                <StatsCard
                    title="Connections"
                    :value="stats.total_connections"
                    description="Active viewers"
                    :icon="Wifi"
                    trend="up"
                    trend-value="+12%"
                />
                <StatsCard
                    title="Lines"
                    :value="`${stats.active_lines} / ${stats.total_lines}`"
                    description="Active subscriptions"
                    :icon="MonitorPlay"
                />
                <StatsCard
                    title="VOD Library"
                    :value="`${stats.total_movies + stats.total_series}`"
                    :description="`${stats.total_movies} movies, ${stats.total_series} series`"
                    :icon="Film"
                />
            </div>

            <!-- Charts -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <Card class="p-6">
                    <h3 class="mb-4 text-sm font-semibold text-muted-foreground uppercase tracking-wider">Stream Activity</h3>
                    <component
                        v-if="ApexCharts"
                        :is="ApexCharts"
                        :options="streamChartOptions"
                        :series="[{ name: 'Active Streams', data: stats.stream_activity.map(p => p.value) }]"
                        height="250"
                    />
                    <div v-else class="h-[250px] flex items-center justify-center text-muted-foreground text-sm">
                        Loading chart...
                    </div>
                </Card>

                <Card class="p-6">
                    <h3 class="mb-4 text-sm font-semibold text-muted-foreground uppercase tracking-wider">Connections</h3>
                    <component
                        v-if="ApexCharts"
                        :is="ApexCharts"
                        :options="connectionChartOptions"
                        :series="[{ name: 'Connections', data: stats.connection_history.map(p => p.value) }]"
                        height="250"
                    />
                    <div v-else class="h-[250px] flex items-center justify-center text-muted-foreground text-sm">
                        Loading chart...
                    </div>
                </Card>
            </div>

            <!-- Servers + Activity -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <!-- Server Health -->
                <Card class="p-6">
                    <h3 class="mb-4 text-sm font-semibold text-muted-foreground uppercase tracking-wider">Server Health</h3>
                    <div class="space-y-4">
                        <div
                            v-for="server in stats.servers"
                            :key="server.id"
                            class="flex items-center justify-between rounded-lg border p-4"
                        >
                            <div class="flex items-center gap-3">
                                <Server class="h-5 w-5 text-muted-foreground" />
                                <div>
                                    <p class="text-sm font-medium">{{ server.name }}</p>
                                    <p class="text-xs text-muted-foreground">{{ server.clients }} clients</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-4">
                                <div class="hidden sm:flex items-center gap-3 text-xs text-muted-foreground">
                                    <div class="text-center">
                                        <p class="font-medium text-foreground">{{ server.cpu }}%</p>
                                        <p>CPU</p>
                                    </div>
                                    <div class="text-center">
                                        <p class="font-medium text-foreground">{{ server.memory }}%</p>
                                        <p>RAM</p>
                                    </div>
                                    <div class="text-center">
                                        <p class="font-medium text-foreground">{{ server.disk }}%</p>
                                        <p>Disk</p>
                                    </div>
                                </div>
                                <StatusBadge :status="server.status" type="server" />
                            </div>
                        </div>
                    </div>
                </Card>

                <!-- Recent Activity -->
                <Card class="p-6">
                    <h3 class="mb-4 text-sm font-semibold text-muted-foreground uppercase tracking-wider">Recent Activity</h3>
                    <div class="space-y-3">
                        <div
                            v-for="activity in recentActivity"
                            :key="activity.id"
                            class="flex items-start gap-3 rounded-lg p-3 hover:bg-muted/50 transition-colors"
                        >
                            <span class="text-lg mt-0.5">{{ actionIcon(activity.action) }}</span>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm">{{ activity.description }}</p>
                                <p class="text-xs text-muted-foreground">
                                    {{ activity.username || 'System' }} &middot; {{ timeAgo(activity.created_at) }}
                                </p>
                            </div>
                        </div>
                        <p v-if="recentActivity.length === 0" class="text-sm text-muted-foreground text-center py-4">
                            No recent activity
                        </p>
                    </div>
                </Card>
            </div>
        </div>
    </AdminLayout>
</template>
