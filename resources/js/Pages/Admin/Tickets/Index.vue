<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import DataTable from '@/Components/DataTable.vue';
import type { Column } from '@/Components/DataTable.vue';
import Button from '@/Components/ui/Button.vue';
import Badge from '@/Components/ui/Badge.vue';
import Select from '@/Components/ui/Select.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import { Eye, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps<{
    tickets: any;
    filters: Record<string, any>;
}>();

const columns: Column[] = [
    { key: 'id', label: 'ID', sortable: true, class: 'w-16' },
    { key: 'subject', label: 'Subject', sortable: true },
    { key: 'user', label: 'User', class: 'w-32' },
    { key: 'status', label: 'Status', sortable: true, class: 'w-28' },
    { key: 'priority', label: 'Priority', sortable: true, class: 'w-24' },
    { key: 'created_at', label: 'Created', sortable: true, class: 'w-36' },
];

const statusVariants: Record<string, string> = {
    open: 'warning',
    in_progress: 'default',
    closed: 'secondary',
};

const deleteDialog = ref(false);
const deleteId = ref<number | null>(null);
function confirmDelete(id: number) { deleteId.value = id; deleteDialog.value = true; }
function handleDelete() { if (deleteId.value) router.delete(`/admin/tickets/${deleteId.value}`); deleteDialog.value = false; }

function filterByStatus(status: string) {
    router.get('/admin/tickets', {
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
    <AdminLayout>
        <template #title>Tickets</template>
        <DataTable :columns="columns" :rows="tickets.data" :total="tickets.total" :current-page="tickets.current_page" :last-page="tickets.last_page" :per-page="tickets.per_page" :from="tickets.from" :to="tickets.to" route-name="admin.tickets.index" :filters="filters" :selectable="false">
            <template #filters>
                <Select :model-value="filters.status || ''" class="w-36" @update:model-value="filterByStatus">
                    <option value="">All Status</option>
                    <option value="open">Open</option>
                    <option value="in_progress">In Progress</option>
                    <option value="closed">Closed</option>
                </Select>
            </template>
            <template #cell-user="{ row }">{{ row.user?.username || '—' }}</template>
            <template #cell-status="{ value }"><Badge :variant="(statusVariants[value] as any) || 'default'">{{ value?.replace('_', ' ') }}</Badge></template>
            <template #cell-created_at="{ value }">{{ formatDate(value) }}</template>
            <template #row-actions="{ row }">
                <div class="flex items-center justify-end gap-1">
                    <Link :href="`/admin/tickets/${row.id}`"><Button variant="ghost" size="icon" class="h-8 w-8"><Eye class="h-4 w-4" /></Button></Link>
                    <Button variant="ghost" size="icon" class="h-8 w-8 text-destructive" @click="confirmDelete(row.id)"><Trash2 class="h-4 w-4" /></Button>
                </div>
            </template>
        </DataTable>
        <ConfirmDialog v-model:open="deleteDialog" title="Delete Ticket" message="This will permanently delete this ticket." @confirm="handleDelete" />
    </AdminLayout>
</template>
