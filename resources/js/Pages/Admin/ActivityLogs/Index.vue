<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import DataTable from '@/Components/DataTable.vue';
import type { Column } from '@/Components/DataTable.vue';
import Select from '@/Components/ui/Select.vue';

const props = defineProps<{
    logs: any;
    actions: string[];
    filters: Record<string, any>;
}>();

const columns: Column[] = [
    { key: 'id', label: 'ID', sortable: true, class: 'w-16' },
    { key: 'action', label: 'Action', sortable: true, class: 'w-32' },
    { key: 'description', label: 'Description' },
    { key: 'username', label: 'User', sortable: true, class: 'w-32' },
    { key: 'ip_address', label: 'IP Address', class: 'w-36' },
    { key: 'created_at', label: 'Date', sortable: true, class: 'w-40' },
];

function filterByAction(action: string) {
    router.get('/admin/activity-logs', {
        ...props.filters,
        action: action || undefined,
        page: 1,
    }, { preserveState: true });
}

function formatDate(date: string | null) {
    if (!date) return '—';
    return new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
}
</script>
<template>
    <Head title="Activity Logs" />
    <AdminLayout>
        <template #title>Activity Logs</template>
        <DataTable :columns="columns" :rows="logs.data" :total="logs.total" :current-page="logs.current_page" :last-page="logs.last_page" :per-page="logs.per_page" :from="logs.from" :to="logs.to" route-name="admin.activity-logs.index" :filters="filters" :selectable="false">
            <template #filters>
                <Select :model-value="filters.action || ''" class="w-40" @update:model-value="filterByAction">
                    <option value="">All Actions</option>
                    <option v-for="action in actions" :key="action" :value="action">{{ action }}</option>
                </Select>
            </template>
            <template #cell-action="{ value }"><span class="capitalize text-xs font-medium px-2 py-0.5 rounded bg-muted">{{ value }}</span></template>
            <template #cell-ip_address="{ value }"><span class="font-mono text-xs">{{ value }}</span></template>
            <template #cell-created_at="{ value }">{{ formatDate(value) }}</template>
        </DataTable>
    </AdminLayout>
</template>
