<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import ResellerLayout from '@/Layouts/ResellerLayout.vue';
import DataTable from '@/Components/DataTable.vue';
import type { Column } from '@/Components/DataTable.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import Button from '@/Components/ui/Button.vue';
import Select from '@/Components/ui/Select.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import { Plus, Pencil, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps<{
    lines: { data: any[]; current_page: number; last_page: number; per_page: number; total: number; from: number | null; to: number | null };
    filters: Record<string, any>;
}>();

const columns: Column[] = [
    { key: 'id', label: 'ID', sortable: true, class: 'w-16' },
    { key: 'username', label: 'Username', sortable: true },
    { key: 'password', label: 'Password', class: 'w-32' },
    { key: 'max_connections', label: 'Max Conn.', sortable: true, class: 'w-24' },
    { key: 'active_connections', label: 'Active', sortable: true, class: 'w-20' },
    { key: 'exp_date', label: 'Expires', sortable: true, class: 'w-32' },
    { key: 'status', label: 'Status', class: 'w-28' },
];

const deleteDialog = ref(false);
const deleteId = ref<number | null>(null);
function confirmDelete(id: number) { deleteId.value = id; deleteDialog.value = true; }
function handleDelete() { if (deleteId.value) router.delete(`/reseller/lines/${deleteId.value}`); deleteDialog.value = false; }

function getLineStatus(line: any): string {
    if (!line.admin_enabled) return 'Disabled';
    if (line.exp_date && new Date(line.exp_date) < new Date()) return 'Expired';
    if (line.active_connections > 0) return 'Online';
    return 'Offline';
}

function formatDate(date: string | null) {
    if (!date) return 'Never';
    return new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
}
</script>

<template>
    <Head title="Lines" />
    <ResellerLayout>
        <template #title>Lines</template>
        <DataTable
            :columns="columns"
            :rows="lines.data"
            :total="lines.total"
            :current-page="lines.current_page"
            :last-page="lines.last_page"
            :per-page="lines.per_page"
            :from="lines.from"
            :to="lines.to"
            route-name="reseller.lines.index"
            :filters="filters"
        >
            <template #filters>
                <Select :model-value="filters.status || ''" class="w-32" @update:model-value="v => router.get('/reseller/lines', { ...filters, status: v || undefined, page: 1 }, { preserveState: true })">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="expired">Expired</option>
                    <option value="disabled">Disabled</option>
                </Select>
            </template>
            <template #actions>
                <Link href="/reseller/lines/create"><Button size="sm"><Plus class="mr-1 h-4 w-4" />Add Line</Button></Link>
            </template>
            <template #cell-username="{ row }">
                <p class="font-medium font-mono">{{ row.username }}</p>
            </template>
            <template #cell-password="{ row }">
                <p class="text-xs text-muted-foreground font-mono">{{ row.password }}</p>
            </template>
            <template #cell-active_connections="{ row }">
                <span :class="row.active_connections > 0 ? 'text-success font-semibold' : 'text-muted-foreground'">
                    {{ row.active_connections }} / {{ row.max_connections }}
                </span>
            </template>
            <template #cell-exp_date="{ row }">{{ formatDate(row.exp_date) }}</template>
            <template #cell-status="{ row }"><StatusBadge :status="getLineStatus(row)" type="line" /></template>
            <template #row-actions="{ row }">
                <div class="flex items-center justify-end gap-1">
                    <Link :href="`/reseller/lines/${row.id}/edit`"><Button variant="ghost" size="icon" class="h-8 w-8"><Pencil class="h-4 w-4" /></Button></Link>
                    <Button variant="ghost" size="icon" class="h-8 w-8 text-destructive" @click="confirmDelete(row.id)"><Trash2 class="h-4 w-4" /></Button>
                </div>
            </template>
        </DataTable>
        <ConfirmDialog v-model:open="deleteDialog" title="Delete Line" message="This will permanently delete this line." @confirm="handleDelete" />
    </ResellerLayout>
</template>
