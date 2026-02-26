<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import ResellerLayout from '@/Layouts/ResellerLayout.vue';
import DataTable from '@/Components/DataTable.vue';
import type { Column } from '@/Components/DataTable.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import Select from '@/Components/ui/Select.vue';

const props = defineProps<{
    streams: {
        data: any[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        from: number | null;
        to: number | null;
    };
    categories: Array<{ id: number; category_name: string }>;
    filters: Record<string, any>;
}>();

const columns: Column[] = [
    { key: 'id', label: 'ID', sortable: true, class: 'w-16' },
    { key: 'stream_display_name', label: 'Name', sortable: true },
    { key: 'type', label: 'Type', sortable: true, class: 'w-24' },
    { key: 'category', label: 'Category', class: 'w-40' },
    { key: 'status', label: 'Status', sortable: true, class: 'w-28' },
];

function filterByCategory(catId: string) {
    router.get('/reseller/streams', {
        ...props.filters,
        category_id: catId || undefined,
        page: 1,
    }, { preserveState: true });
}

function filterByStatus(status: string) {
    router.get('/reseller/streams', {
        ...props.filters,
        status: status !== '' ? status : undefined,
        page: 1,
    }, { preserveState: true });
}
</script>

<template>
    <Head title="Streams" />
    <ResellerLayout>
        <template #title>Streams</template>

        <DataTable
            :columns="columns"
            :rows="streams.data"
            :total="streams.total"
            :current-page="streams.current_page"
            :last-page="streams.last_page"
            :per-page="streams.per_page"
            :from="streams.from"
            :to="streams.to"
            route-name="reseller.streams.index"
            :filters="filters"
            :selectable="false"
        >
            <template #filters>
                <Select
                    :model-value="filters.category_id || ''"
                    class="w-40"
                    @update:model-value="filterByCategory"
                >
                    <option value="">All Categories</option>
                    <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                        {{ cat.category_name }}
                    </option>
                </Select>
                <Select
                    :model-value="filters.status ?? ''"
                    class="w-32"
                    @update:model-value="filterByStatus"
                >
                    <option value="">All Status</option>
                    <option value="1">Online</option>
                    <option value="0">Offline</option>
                </Select>
            </template>

            <template #cell-stream_display_name="{ row }">
                <p class="font-medium">{{ row.stream_display_name }}</p>
            </template>

            <template #cell-type="{ value }">
                <span class="capitalize text-xs font-medium px-2 py-0.5 rounded bg-muted">{{ value }}</span>
            </template>

            <template #cell-category="{ row }">
                {{ row.category?.category_name || '—' }}
            </template>

            <template #cell-status="{ row }">
                <StatusBadge :status="row.status" type="stream" />
            </template>
        </DataTable>
    </ResellerLayout>
</template>
