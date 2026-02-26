<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import StatsCard from '@/Components/StatsCard.vue';
import Card from '@/Components/ui/Card.vue';
import { Wifi, MonitorPlay, Users, Radio } from 'lucide-vue-next';

interface Props {
    connections: Array<{
        id: number;
        username: string;
        active_connections: number;
        max_connections: number;
        exp_date: string | null;
    }>;
    activeStreams: Array<{
        id: number;
        stream_display_name: string;
        current_viewers: number;
        type: string;
        server_name: string;
    }>;
    totalConnections: number;
    totalViewers: number;
}

defineProps<Props>();
</script>
<template>
    <Head title="Live Connections" />
    <AdminLayout>
        <template #title>Live Connections</template>
        <div class="space-y-6">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <StatsCard title="Total Connections" :value="totalConnections" description="Active right now" :icon="Wifi" />
                <StatsCard title="Total Viewers" :value="totalViewers" description="Across all streams" :icon="Users" />
                <StatsCard title="Active Lines" :value="connections.length" description="Lines with connections" :icon="MonitorPlay" />
                <StatsCard title="Active Streams" :value="activeStreams.length" description="Streams being watched" :icon="Radio" />
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <Card class="p-6">
                    <h3 class="mb-4 text-sm font-semibold text-muted-foreground uppercase tracking-wider">Active Lines</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead><tr class="border-b text-left text-muted-foreground">
                                <th class="pb-2 font-medium">Username</th>
                                <th class="pb-2 font-medium w-28">Connections</th>
                                <th class="pb-2 font-medium w-32">Expires</th>
                            </tr></thead>
                            <tbody>
                                <tr v-for="line in connections" :key="line.id" class="border-b last:border-0">
                                    <td class="py-2 font-mono text-sm">{{ line.username }}</td>
                                    <td class="py-2"><span class="text-success font-semibold">{{ line.active_connections }}</span> / {{ line.max_connections }}</td>
                                    <td class="py-2 text-muted-foreground text-xs">{{ line.exp_date ? new Date(line.exp_date).toLocaleDateString() : 'Never' }}</td>
                                </tr>
                                <tr v-if="connections.length === 0"><td colspan="3" class="py-8 text-center text-muted-foreground">No active connections</td></tr>
                            </tbody>
                        </table>
                    </div>
                </Card>

                <Card class="p-6">
                    <h3 class="mb-4 text-sm font-semibold text-muted-foreground uppercase tracking-wider">Active Streams</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead><tr class="border-b text-left text-muted-foreground">
                                <th class="pb-2 font-medium">Stream</th>
                                <th class="pb-2 font-medium w-20">Viewers</th>
                                <th class="pb-2 font-medium w-20">Type</th>
                                <th class="pb-2 font-medium w-28">Server</th>
                            </tr></thead>
                            <tbody>
                                <tr v-for="stream in activeStreams" :key="stream.id" class="border-b last:border-0">
                                    <td class="py-2 font-medium">{{ stream.stream_display_name }}</td>
                                    <td class="py-2"><span class="text-success font-semibold">{{ stream.current_viewers }}</span></td>
                                    <td class="py-2"><span class="capitalize text-xs font-medium px-2 py-0.5 rounded bg-muted">{{ stream.type }}</span></td>
                                    <td class="py-2 text-muted-foreground text-xs">{{ stream.server_name }}</td>
                                </tr>
                                <tr v-if="activeStreams.length === 0"><td colspan="4" class="py-8 text-center text-muted-foreground">No active streams</td></tr>
                            </tbody>
                        </table>
                    </div>
                </Card>
            </div>
        </div>
    </AdminLayout>
</template>
