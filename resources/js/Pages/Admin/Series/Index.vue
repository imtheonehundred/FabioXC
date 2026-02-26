<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import DataTable from '@/Components/DataTable.vue';
import type { Column } from '@/Components/DataTable.vue';
import Button from '@/Components/ui/Button.vue';
import Badge from '@/Components/ui/Badge.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import { Plus, Pencil, Trash2, Eye } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps<{
    series: { data: any[]; current_page: number; last_page: number; per_page: number; total: number; from: number | null; to: number | null };
    categories: Array<{ id: number; category_name: string }>;
    filters: Record<string, any>;
}>();

const columns: Column[] = [
    { key: 'id', label: 'ID', sortable: true, class: 'w-16' },
    { key: 'title', label: 'Title', sortable: true },
    { key: 'genre', label: 'Genre', class: 'w-28' },
    { key: 'episodes_count', label: 'Episodes', sortable: true, class: 'w-24' },
    { key: 'category', label: 'Category', class: 'w-32' },
    { key: 'rating', label: 'Rating', class: 'w-20' },
];

const deleteDialog = ref(false);
const deleteId = ref<number | null>(null);
function confirmDelete(id: number) { deleteId.value = id; deleteDialog.value = true; }
function handleDelete() { if (deleteId.value) router.delete(`/admin/series/${deleteId.value}`); deleteDialog.value = false; }
function handleBulkAction(action: string, ids: number[]) { router.post('/admin/series/mass-action', { action, ids }); }
</script>

<template>
    <Head title="Series" />
    <AdminLayout>
        <template #title>Series</template>
        <DataTable :columns="columns" :rows="series.data" :total="series.total" :current-page="series.current_page" :last-page="series.last_page" :per-page="series.per_page" :from="series.from" :to="series.to" route-name="admin.series.index" :filters="filters" @bulk-action="handleBulkAction">
            <template #actions="{ selected, bulkAction }">
                <Button v-if="selected.length > 0" variant="destructive" size="sm" @click="bulkAction('delete')"><Trash2 class="mr-1 h-4 w-4" />Delete</Button>
                <Link href="/admin/series/create"><Button size="sm"><Plus class="mr-1 h-4 w-4" />Add Series</Button></Link>
            </template>
            <template #cell-title="{ row }">
                <div class="flex items-center gap-3">
                    <img v-if="row.cover" :src="row.cover" class="h-10 w-7 rounded object-cover shrink-0" />
                    <span class="font-medium">{{ row.title }}</span>
                </div>
            </template>
            <template #cell-genre="{ value }"><Badge v-if="value" variant="secondary">{{ value }}</Badge><span v-else class="text-muted-foreground">—</span></template>
            <template #cell-episodes_count="{ value }"><span class="font-medium">{{ value }}</span></template>
            <template #cell-category="{ row }">{{ row.category?.category_name || '—' }}</template>
            <template #cell-rating="{ value }"><span v-if="value">{{ value }}</span><span v-else class="text-muted-foreground">—</span></template>
            <template #row-actions="{ row }">
                <div class="flex items-center justify-end gap-1">
                    <Link :href="`/admin/series/${row.id}`"><Button variant="ghost" size="icon" class="h-8 w-8"><Eye class="h-4 w-4" /></Button></Link>
                    <Link :href="`/admin/series/${row.id}/edit`"><Button variant="ghost" size="icon" class="h-8 w-8"><Pencil class="h-4 w-4" /></Button></Link>
                    <Button variant="ghost" size="icon" class="h-8 w-8 text-destructive" @click="confirmDelete(row.id)"><Trash2 class="h-4 w-4" /></Button>
                </div>
            </template>
        </DataTable>
        <ConfirmDialog v-model:open="deleteDialog" title="Delete Series" message="This will delete the series and all its episodes." @confirm="handleDelete" />
    </AdminLayout>
</template>
