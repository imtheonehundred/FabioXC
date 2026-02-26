<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import DataTable from '@/Components/DataTable.vue';
import type { Column } from '@/Components/DataTable.vue';
import Button from '@/Components/ui/Button.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import { Plus, Pencil, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';

defineProps<{ epgs: any; filters: Record<string, any> }>();
const columns: Column[] = [
    { key: 'id', label: 'ID', sortable: true, class: 'w-16' },
    { key: 'epg_name', label: 'Name', sortable: true },
    { key: 'epg_url', label: 'URL', sortable: true },
    { key: 'last_updated_at', label: 'Last Updated', sortable: true, class: 'w-40' },
];
const deleteDialog = ref(false);
const deleteId = ref<number | null>(null);
function confirmDelete(id: number) { deleteId.value = id; deleteDialog.value = true; }
function handleDelete() { if (deleteId.value) router.delete(`/admin/epgs/${deleteId.value}`); deleteDialog.value = false; }
function formatDate(date: string | null) {
    if (!date) return 'Never';
    return new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
}
</script>
<template>
    <Head title="EPG Sources" /><AdminLayout><template #title>EPG Sources</template>
    <DataTable :columns="columns" :rows="epgs.data" :total="epgs.total" :current-page="epgs.current_page" :last-page="epgs.last_page" :per-page="epgs.per_page" :from="epgs.from" :to="epgs.to" route-name="admin.epgs.index" :filters="filters" :selectable="false">
        <template #actions><Link href="/admin/epgs/create"><Button size="sm"><Plus class="mr-1 h-4 w-4" />Add EPG Source</Button></Link></template>
        <template #cell-epg_url="{ value }"><span class="text-xs font-mono truncate max-w-[300px] block">{{ value }}</span></template>
        <template #cell-last_updated_at="{ value }">{{ formatDate(value) }}</template>
        <template #row-actions="{ row }"><div class="flex items-center justify-end gap-1"><Link :href="`/admin/epgs/${row.id}/edit`"><Button variant="ghost" size="icon" class="h-8 w-8"><Pencil class="h-4 w-4" /></Button></Link><Button variant="ghost" size="icon" class="h-8 w-8 text-destructive" @click="confirmDelete(row.id)"><Trash2 class="h-4 w-4" /></Button></div></template>
    </DataTable>
    <ConfirmDialog v-model:open="deleteDialog" title="Delete EPG Source" message="This will remove the EPG source." @confirm="handleDelete" />
    </AdminLayout>
</template>
