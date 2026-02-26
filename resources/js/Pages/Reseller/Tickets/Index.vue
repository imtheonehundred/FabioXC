<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import ResellerLayout from '@/Layouts/ResellerLayout.vue';
import DataTable from '@/Components/DataTable.vue';
import type { Column } from '@/Components/DataTable.vue';
import Button from '@/Components/ui/Button.vue';
import Badge from '@/Components/ui/Badge.vue';
import Select from '@/Components/ui/Select.vue';
import { Plus, Eye } from 'lucide-vue-next';

const props = defineProps<{
    tickets: any;
    filters: Record<string, any>;
}>();

const columns: Column[] = [
    { key: 'id', label: 'ID', sortable: true, class: 'w-16' },
    { key: 'subject', label: 'Subject', sortable: true },
    { key: 'status', label: 'Status', sortable: true, class: 'w-28' },
    { key: 'priority', label: 'Priority', sortable: true, class: 'w-24' },
    { key: 'created_at', label: 'Created', sortable: true, class: 'w-36' },
];

const statusVariants: Record<string, string> = {
    open: 'warning',
    in_progress: 'default',
    closed: 'secondary',
};

const priorityVariants: Record<string, string> = {
    low: 'secondary',
    normal: 'default',
    high: 'destructive',
};

function filterByStatus(status: string) {
    router.get('/reseller/tickets', {
        ...props.filters,
        status: status || undefined,
        page: 1,
    }, { preserveState: true });
}

function formatDate(date: string | null) {
    if (!date) return '—';
    return new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
}
</script>

<template>
    <Head title="Tickets" />
    <ResellerLayout>
        <template #title>Tickets</template>
        <DataTable
            :columns="columns"
            :rows="tickets.data"
            :total="tickets.total"
            :current-page="tickets.current_page"
            :last-page="tickets.last_page"
            :per-page="tickets.per_page"
            :from="tickets.from"
            :to="tickets.to"
            route-name="reseller.tickets.index"
            :filters="filters"
            :selectable="false"
        >
            <template #filters>
                <Select :model-value="filters.status || ''" class="w-36" @update:model-value="filterByStatus">
                    <option value="">All Status</option>
                    <option value="open">Open</option>
                    <option value="in_progress">In Progress</option>
                    <option value="closed">Closed</option>
                </Select>
            </template>
            <template #actions>
                <Link href="/reseller/tickets/create"><Button size="sm"><Plus class="mr-1 h-4 w-4" />New Ticket</Button></Link>
            </template>
            <template #cell-status="{ value }"><Badge :variant="(statusVariants[value] as any) || 'default'">{{ value?.replace('_', ' ') }}</Badge></template>
            <template #cell-priority="{ value }"><Badge :variant="(priorityVariants[value] as any) || 'default'">{{ value }}</Badge></template>
            <template #cell-created_at="{ value }">{{ formatDate(value) }}</template>
            <template #row-actions="{ row }">
                <div class="flex items-center justify-end gap-1">
                    <Link :href="`/reseller/tickets/${row.id}`"><Button variant="ghost" size="icon" class="h-8 w-8"><Eye class="h-4 w-4" /></Button></Link>
                </div>
            </template>
        </DataTable>
    </ResellerLayout>
</template>
