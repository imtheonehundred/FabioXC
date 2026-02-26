<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import DataTable from '@/Components/DataTable.vue';
import type { Column } from '@/Components/DataTable.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import Button from '@/Components/ui/Button.vue';
import Select from '@/Components/ui/Select.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import { Plus, Pencil, Trash2, Eye } from 'lucide-vue-next';
import { ref } from 'vue';

interface Props {
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
}

const props = defineProps<Props>();

const columns: Column[] = [
    { key: 'id', label: 'ID', sortable: true, class: 'w-16' },
    { key: 'stream_display_name', label: 'Name', sortable: true },
    { key: 'type', label: 'Type', sortable: true, class: 'w-24' },
    { key: 'category', label: 'Category', class: 'w-40' },
    { key: 'server', label: 'Server', class: 'w-36' },
    { key: 'current_viewers', label: 'Viewers', sortable: true, class: 'w-24' },
    { key: 'status', label: 'Status', sortable: true, class: 'w-28' },
];

const deleteDialog = ref(false);
const deleteTarget = ref<number | null>(null);

function confirmDelete(id: number) {
    deleteTarget.value = id;
    deleteDialog.value = true;
}

function handleDelete() {
    if (deleteTarget.value) {
        router.delete(`/admin/streams/${deleteTarget.value}`);
    }
    deleteDialog.value = false;
}

function handleBulkAction(action: string, ids: number[]) {
    router.post('/admin/streams/mass-action', { action, ids });
}

function filterByCategory(catId: string) {
    router.get('/admin/streams', {
        ...props.filters,
        category_id: catId || undefined,
        page: 1,
    }, { preserveState: true });
}

function filterByStatus(status: string) {
    router.get('/admin/streams', {
        ...props.filters,
        status: status !== '' ? status : undefined,
        page: 1,
    }, { preserveState: true });
}
</script>

<template>
    <Head title="Streams" />
    <AdminLayout>
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
            route-name="admin.streams.index"
            :filters="filters"
            @bulk-action="handleBulkAction"
        >
            <template #filters>
                <Select
                    :model-value="filters.category_id || ''"
                    placeholder="All Categories"
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
                    placeholder="All Status"
                    class="w-32"
                    @update:model-value="filterByStatus"
                >
                    <option value="">All Status</option>
                    <option value="1">Online</option>
                    <option value="0">Offline</option>
                    <option value="3">Error</option>
                </Select>
            </template>

            <template #actions="{ selected, bulkAction }">
                <Button v-if="selected.length > 0" variant="outline" size="sm" @click="bulkAction('enable')">Enable</Button>
                <Button v-if="selected.length > 0" variant="outline" size="sm" @click="bulkAction('disable')">Disable</Button>
                <Button v-if="selected.length > 0" variant="destructive" size="sm" @click="bulkAction('delete')">
                    <Trash2 class="mr-1 h-4 w-4" /> Delete
                </Button>
                <Link href="/admin/streams/create">
                    <Button size="sm"><Plus class="mr-1 h-4 w-4" /> Add Stream</Button>
                </Link>
            </template>

            <template #cell-stream_display_name="{ row }">
                <div class="flex items-center gap-2">
                    <img v-if="row.stream_icon" :src="row.stream_icon" class="h-8 w-8 rounded object-cover" />
                    <div>
                        <p class="font-medium">{{ row.stream_display_name }}</p>
                        <p class="text-xs text-muted-foreground truncate max-w-[200px]">{{ row.stream_source }}</p>
                    </div>
                </div>
            </template>

            <template #cell-type="{ value }">
                <span class="capitalize text-xs font-medium px-2 py-0.5 rounded bg-muted">{{ value }}</span>
            </template>

            <template #cell-category="{ row }">
                {{ row.category?.category_name || '—' }}
            </template>

            <template #cell-server="{ row }">
                {{ row.server?.server_name || '—' }}
            </template>

            <template #cell-current_viewers="{ value }">
                <span :class="value > 0 ? 'text-success font-medium' : 'text-muted-foreground'">{{ value }}</span>
            </template>

            <template #cell-status="{ row }">
                <StatusBadge :status="row.status" type="stream" />
            </template>

            <template #row-actions="{ row }">
                <div class="flex items-center justify-end gap-1">
                    <Link :href="`/admin/streams/${row.id}`">
                        <Button variant="ghost" size="icon" class="h-8 w-8"><Eye class="h-4 w-4" /></Button>
                    </Link>
                    <Link :href="`/admin/streams/${row.id}/edit`">
                        <Button variant="ghost" size="icon" class="h-8 w-8"><Pencil class="h-4 w-4" /></Button>
                    </Link>
                    <Button variant="ghost" size="icon" class="h-8 w-8 text-destructive" @click="confirmDelete(row.id)">
                        <Trash2 class="h-4 w-4" />
                    </Button>
                </div>
            </template>
        </DataTable>

        <ConfirmDialog
            v-model:open="deleteDialog"
            title="Delete Stream"
            message="Are you sure you want to delete this stream? This action cannot be undone."
            confirm-label="Delete"
            @confirm="handleDelete"
        />
    </AdminLayout>
</template>
