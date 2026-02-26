<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import ResellerLayout from '@/Layouts/ResellerLayout.vue';
import DataTable from '@/Components/DataTable.vue';
import type { Column } from '@/Components/DataTable.vue';

const props = defineProps<{
    users: {
        data: any[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        from: number | null;
        to: number | null;
    };
    filters: Record<string, any>;
}>();

const columns: Column[] = [
    { key: 'id', label: 'ID', sortable: true, class: 'w-16' },
    { key: 'username', label: 'Username', sortable: true },
    { key: 'active_connections', label: 'Active Conn.', sortable: true, class: 'w-32' },
    { key: 'exp_date', label: 'Expires', sortable: true, class: 'w-36' },
];

function formatDate(date: string | null) {
    if (!date) return 'Never';
    return new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
}
</script>

<template>
    <Head title="Users" />
    <ResellerLayout>
        <template #title>Users</template>

        <DataTable
            :columns="columns"
            :rows="users.data"
            :total="users.total"
            :current-page="users.current_page"
            :last-page="users.last_page"
            :per-page="users.per_page"
            :from="users.from"
            :to="users.to"
            route-name="reseller.users.index"
            :filters="filters"
            :selectable="false"
        >
            <template #cell-username="{ row }">
                <p class="font-medium font-mono">{{ row.username }}</p>
            </template>

            <template #cell-active_connections="{ row }">
                <span :class="row.active_connections > 0 ? 'text-success font-semibold' : 'text-muted-foreground'">
                    {{ row.active_connections }}
                </span>
            </template>

            <template #cell-exp_date="{ row }">{{ formatDate(row.exp_date) }}</template>
        </DataTable>
    </ResellerLayout>
</template>
