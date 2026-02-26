<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import DataTable from '@/Components/DataTable.vue';
import type { Column } from '@/Components/DataTable.vue';
import Button from '@/Components/ui/Button.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import { Plus, Pencil, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';

defineProps<{ bouquets: any; filters: Record<string, any> }>();
const columns: Column[] = [
    { key: 'id', label: 'ID', sortable: true, class: 'w-16' },
    { key: 'bouquet_name', label: 'Name', sortable: true },
    { key: 'channels', label: 'Channels', class: 'w-24' },
    { key: 'movies', label: 'Movies', class: 'w-24' },
    { key: 'series', label: 'Series', class: 'w-24' },
    { key: 'bouquet_order', label: 'Order', sortable: true, class: 'w-20' },
];
const deleteDialog = ref(false);
const deleteId = ref<number | null>(null);
function confirmDelete(id: number) { deleteId.value = id; deleteDialog.value = true; }
function handleDelete() { if (deleteId.value) router.delete(`/admin/bouquets/${deleteId.value}`); deleteDialog.value = false; }
</script>
<template>
    <Head title="Bouquets" /><AdminLayout><template #title>Bouquets</template>
    <DataTable :columns="columns" :rows="bouquets.data" :total="bouquets.total" :current-page="bouquets.current_page" :last-page="bouquets.last_page" :per-page="bouquets.per_page" :from="bouquets.from" :to="bouquets.to" route-name="admin.bouquets.index" :filters="filters" :selectable="false">
        <template #actions><Link href="/admin/bouquets/create"><Button size="sm"><Plus class="mr-1 h-4 w-4" />Add Bouquet</Button></Link></template>
        <template #cell-channels="{ row }">{{ (row.bouquet_channels || []).length }}</template>
        <template #cell-movies="{ row }">{{ (row.bouquet_movies || []).length }}</template>
        <template #cell-series="{ row }">{{ (row.bouquet_series || []).length }}</template>
        <template #row-actions="{ row }"><div class="flex items-center justify-end gap-1"><Link :href="`/admin/bouquets/${row.id}/edit`"><Button variant="ghost" size="icon" class="h-8 w-8"><Pencil class="h-4 w-4" /></Button></Link><Button variant="ghost" size="icon" class="h-8 w-8 text-destructive" @click="confirmDelete(row.id)"><Trash2 class="h-4 w-4" /></Button></div></template>
    </DataTable>
    <ConfirmDialog v-model:open="deleteDialog" title="Delete Bouquet" message="This will remove the bouquet." @confirm="handleDelete" />
    </AdminLayout>
</template>
