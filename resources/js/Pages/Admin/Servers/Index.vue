<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import DataTable from '@/Components/DataTable.vue';
import type { Column } from '@/Components/DataTable.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import Button from '@/Components/ui/Button.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import { Plus, Pencil, Trash2, Eye } from 'lucide-vue-next';
import { ref } from 'vue';

defineProps<{
    servers: { data: any[]; current_page: number; last_page: number; per_page: number; total: number; from: number | null; to: number | null };
    filters: Record<string, any>;
}>();

const columns: Column[] = [
    { key: 'id', label: 'ID', sortable: true, class: 'w-16' },
    { key: 'server_name', label: 'Name', sortable: true },
    { key: 'server_ip', label: 'IP Address', class: 'w-36' },
    { key: 'total_clients', label: 'Clients', sortable: true, class: 'w-24' },
    { key: 'cpu_usage', label: 'CPU', sortable: true, class: 'w-20' },
    { key: 'mem_usage', label: 'Memory', sortable: true, class: 'w-20' },
    { key: 'streams_count', label: 'Streams', class: 'w-24' },
    { key: 'status', label: 'Status', sortable: true, class: 'w-28' },
];

const deleteDialog = ref(false);
const deleteId = ref<number | null>(null);
function confirmDelete(id: number) { deleteId.value = id; deleteDialog.value = true; }
function handleDelete() { if (deleteId.value) router.delete(`/admin/servers/${deleteId.value}`); deleteDialog.value = false; }
</script>

<template>
    <Head title="Servers" />
    <AdminLayout>
        <template #title>Servers</template>
        <DataTable :columns="columns" :rows="servers.data" :total="servers.total" :current-page="servers.current_page" :last-page="servers.last_page" :per-page="servers.per_page" :from="servers.from" :to="servers.to" route-name="admin.servers.index" :filters="filters" :selectable="false">
            <template #actions><Link href="/admin/servers/create"><Button size="sm"><Plus class="mr-1 h-4 w-4" />Add Server</Button></Link></template>
            <template #cell-server_name="{ row }">
                <div>
                    <p class="font-medium">{{ row.server_name }}</p>
                    <p v-if="row.domain_name" class="text-xs text-muted-foreground">{{ row.domain_name }}</p>
                </div>
            </template>
            <template #cell-server_ip="{ value }"><code class="text-xs bg-muted px-1.5 py-0.5 rounded font-mono">{{ value }}</code></template>
            <template #cell-cpu_usage="{ value }">
                <div v-if="value != null" class="flex items-center gap-2">
                    <div class="h-1.5 w-16 rounded-full bg-muted"><div class="h-1.5 rounded-full" :class="value > 80 ? 'bg-destructive' : value > 50 ? 'bg-warning' : 'bg-success'" :style="{ width: `${Math.min(value, 100)}%` }" /></div>
                    <span class="text-xs">{{ value }}%</span>
                </div>
                <span v-else class="text-muted-foreground">—</span>
            </template>
            <template #cell-mem_usage="{ value }">
                <div v-if="value != null" class="flex items-center gap-2">
                    <div class="h-1.5 w-16 rounded-full bg-muted"><div class="h-1.5 rounded-full" :class="value > 80 ? 'bg-destructive' : value > 50 ? 'bg-warning' : 'bg-success'" :style="{ width: `${Math.min(value, 100)}%` }" /></div>
                    <span class="text-xs">{{ value }}%</span>
                </div>
                <span v-else class="text-muted-foreground">—</span>
            </template>
            <template #cell-status="{ row }"><StatusBadge :status="row.status" type="server" /></template>
            <template #row-actions="{ row }">
                <div class="flex items-center justify-end gap-1">
                    <Link :href="`/admin/servers/${row.id}`"><Button variant="ghost" size="icon" class="h-8 w-8"><Eye class="h-4 w-4" /></Button></Link>
                    <Link :href="`/admin/servers/${row.id}/edit`"><Button variant="ghost" size="icon" class="h-8 w-8"><Pencil class="h-4 w-4" /></Button></Link>
                    <Button variant="ghost" size="icon" class="h-8 w-8 text-destructive" @click="confirmDelete(row.id)"><Trash2 class="h-4 w-4" /></Button>
                </div>
            </template>
        </DataTable>
        <ConfirmDialog v-model:open="deleteDialog" title="Delete Server" message="This will remove the server. Streams assigned to it will be unassigned." @confirm="handleDelete" />
    </AdminLayout>
</template>
