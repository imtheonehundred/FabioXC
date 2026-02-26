<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import DataTable from '@/Components/DataTable.vue';
import type { Column } from '@/Components/DataTable.vue';
import Button from '@/Components/ui/Button.vue';
import Select from '@/Components/ui/Select.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import { Plus, Pencil, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps<{
    devices: any;
    filters: Record<string, any>;
}>();

const columns: Column[] = [
    { key: 'id', label: 'ID', sortable: true, class: 'w-16' },
    { key: 'mac_address', label: 'MAC Address', sortable: true },
    { key: 'device_type', label: 'Type', sortable: true, class: 'w-24' },
    { key: 'line', label: 'Line', class: 'w-32' },
    { key: 'model', label: 'Model', class: 'w-32' },
    { key: 'status', label: 'Status', class: 'w-28' },
];

const deleteDialog = ref(false);
const deleteId = ref<number | null>(null);
function confirmDelete(id: number) { deleteId.value = id; deleteDialog.value = true; }
function handleDelete() { if (deleteId.value) router.delete(`/admin/devices/${deleteId.value}`); deleteDialog.value = false; }

function filterByType(type: string) {
    router.get('/admin/devices', {
        ...props.filters,
        device_type: type || undefined,
        page: 1,
    }, { preserveState: true });
}
</script>
<template>
    <Head title="Devices" />
    <AdminLayout>
        <template #title>Devices</template>
        <DataTable :columns="columns" :rows="devices.data" :total="devices.total" :current-page="devices.current_page" :last-page="devices.last_page" :per-page="devices.per_page" :from="devices.from" :to="devices.to" route-name="admin.devices.index" :filters="filters" :selectable="false">
            <template #filters>
                <Select :model-value="filters.device_type || ''" class="w-32" @update:model-value="filterByType">
                    <option value="">All Types</option>
                    <option value="mag">MAG</option>
                    <option value="enigma">Enigma</option>
                    <option value="stb">STB</option>
                </Select>
            </template>
            <template #actions><Link href="/admin/devices/create"><Button size="sm"><Plus class="mr-1 h-4 w-4" />Add Device</Button></Link></template>
            <template #cell-mac_address="{ value }"><span class="font-mono text-xs">{{ value }}</span></template>
            <template #cell-device_type="{ value }"><span class="capitalize text-xs font-medium px-2 py-0.5 rounded bg-muted">{{ value }}</span></template>
            <template #cell-line="{ row }">{{ row.line?.username || '—' }}</template>
            <template #cell-status="{ row }"><StatusBadge :status="row.admin_enabled ? 'active' : 'disabled'" type="device" /></template>
            <template #row-actions="{ row }"><div class="flex items-center justify-end gap-1"><Link :href="`/admin/devices/${row.id}/edit`"><Button variant="ghost" size="icon" class="h-8 w-8"><Pencil class="h-4 w-4" /></Button></Link><Button variant="ghost" size="icon" class="h-8 w-8 text-destructive" @click="confirmDelete(row.id)"><Trash2 class="h-4 w-4" /></Button></div></template>
        </DataTable>
        <ConfirmDialog v-model:open="deleteDialog" title="Delete Device" message="This will permanently delete this device." @confirm="handleDelete" />
    </AdminLayout>
</template>
