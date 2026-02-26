<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import DataTable from '@/Components/DataTable.vue';
import type { Column } from '@/Components/DataTable.vue';
import Button from '@/Components/ui/Button.vue';
import Badge from '@/Components/ui/Badge.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import { Plus, Pencil, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';

defineProps<{ codes: any }>();

const typeLabels: Record<number, string> = {
    0: 'Admin',
    1: 'Reseller',
    2: 'Ministra',
    3: 'Admin API',
    4: 'Reseller API',
    5: 'Ministra New',
    6: 'Player',
};

const typeVariants: Record<number, string> = {
    0: 'default',
    1: 'secondary',
    2: 'outline',
    3: 'warning',
    4: 'secondary',
    5: 'outline',
    6: 'success',
};

const columns: Column[] = [
    { key: 'id', label: 'ID', sortable: true, class: 'w-16' },
    { key: 'code', label: 'Code', sortable: true },
    { key: 'type', label: 'Type', sortable: true, class: 'w-36' },
];
const deleteDialog = ref(false);
const deleteId = ref<number | null>(null);
function confirmDelete(id: number) { deleteId.value = id; deleteDialog.value = true; }
function handleDelete() { if (deleteId.value) router.delete(`/admin/access-codes/${deleteId.value}`); deleteDialog.value = false; }
</script>
<template>
    <Head title="Access Codes" /><AdminLayout><template #title>Access Codes</template>
    <DataTable :columns="columns" :rows="codes.data" :total="codes.total" :current-page="codes.current_page" :last-page="codes.last_page" :per-page="codes.per_page" :from="codes.from" :to="codes.to" route-name="admin.access-codes.index" :selectable="false">
        <template #actions><Link href="/admin/access-codes/create"><Button size="sm"><Plus class="mr-1 h-4 w-4" />Add Access Code</Button></Link></template>
        <template #cell-code="{ value }"><span class="font-mono">{{ value }}</span></template>
        <template #cell-type="{ value }"><Badge :variant="(typeVariants[value] as any) || 'default'">{{ typeLabels[value] || 'Unknown' }}</Badge></template>
        <template #row-actions="{ row }"><div class="flex items-center justify-end gap-1"><Link :href="`/admin/access-codes/${row.id}/edit`"><Button variant="ghost" size="icon" class="h-8 w-8"><Pencil class="h-4 w-4" /></Button></Link><Button variant="ghost" size="icon" class="h-8 w-8 text-destructive" @click="confirmDelete(row.id)"><Trash2 class="h-4 w-4" /></Button></div></template>
    </DataTable>
    <ConfirmDialog v-model:open="deleteDialog" title="Delete Access Code" message="This will permanently delete this access code." @confirm="handleDelete" />
    </AdminLayout>
</template>
