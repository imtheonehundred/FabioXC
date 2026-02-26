<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import ResellerLayout from '@/Layouts/ResellerLayout.vue';
import StatsCard from '@/Components/StatsCard.vue';
import { MonitorPlay, Wifi, Radio, Activity, Users } from 'lucide-vue-next';

interface Props {
    stats: {
        total_lines: number;
        active_lines: number;
        total_connections: number;
        active_streams: number;
        total_streams: number;
    };
}

defineProps<Props>();
</script>

<template>
    <Head title="Reseller Dashboard" />
    <ResellerLayout>
        <template #title>Dashboard</template>

        <div class="space-y-6">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
                <StatsCard
                    title="Total Lines"
                    :value="stats.total_lines"
                    description="All created lines"
                    :icon="MonitorPlay"
                />
                <StatsCard
                    title="Active Lines"
                    :value="stats.active_lines"
                    description="Currently active"
                    :icon="Activity"
                />
                <StatsCard
                    title="Connections"
                    :value="stats.total_connections"
                    description="Active connections"
                    :icon="Wifi"
                />
                <StatsCard
                    title="Active Streams"
                    :value="stats.active_streams"
                    description="Currently running"
                    :icon="Radio"
                />
                <StatsCard
                    title="Total Streams"
                    :value="stats.total_streams"
                    description="Available streams"
                    :icon="Users"
                />
            </div>
        </div>
    </ResellerLayout>
</template>
