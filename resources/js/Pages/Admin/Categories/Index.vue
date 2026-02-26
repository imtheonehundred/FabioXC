<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import DataTable from '@/Components/DataTable.vue';
import type { Column } from '@/Components/DataTable.vue';
import Button from '@/Components/ui/Button.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import { Plus, Pencil, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';

defineProps<{
    categories: { data: any[]; current_page: number; last_page: number; per_page: number; total: number; from: number | null; to: number | null };
    filters: Record<string, any>;
}>();

const columns: Column[] = [
    { key: 'id', label: 'ID', sortable: true, class: 'w-16' },
    { key: 'category_name', label: 'Name', sortable: true },
    { key: 'category_type', label: 'Type', sortable: true, class: 'w-28' },
    { key: 'streams_count', label: 'Streams', sortable: true, class: 'w-24' },
    { key: 'cat_order', label: 'Order', sortable: true, class: 'w-20' },
];

const deleteDialog = ref(false);
const deleteId = ref<number | null>(null);

function confirmDelete(id: number) { deleteId.value = id; deleteDialog.value = true; }
function handleDelete() { if (deleteId.value) router.delete(`/admin/categories/${deleteId.value}`); deleteDialog.value = false; }
</script>

<template>
    <Head title="Categories" />
    <AdminLayout>
        <template #title>Categories</template>
        <DataTable :columns="columns" :rows="categories.data" :total="categories.total" :current-page="categories.current_page" :last-page="categories.last_page" :per-page="categories.per_page" :from="categories.from" :to="categories.to" route-name="admin.categories.index" :filters="filters" :selectable="false">
            <template #actions>
                <Link href="/admin/categories/create"><Button size="sm"><Plus class="mr-1 h-4 w-4" /> Add Category</Button></Link>
            </template>
            <template #cell-category_type="{ value }">
                <span class="capitalize text-xs font-medium px-2 py-0.5 rounded bg-muted">{{ value }}</span>
            </template>
            <template #row-actions="{ row }">
                <div class="flex items-center justify-end gap-1">
                    <Link :href="`/admin/categories/${row.id}/edit`"><Button variant="ghost" size="icon" class="h-8 w-8"><Pencil class="h-4 w-4" /></Button></Link>
                    <Button variant="ghost" size="icon" class="h-8 w-8 text-destructive" @click="confirmDelete(row.id)"><Trash2 class="h-4 w-4" /></Button>
                </div>
            </template>
        </DataTable>
        <ConfirmDialog v-model:open="deleteDialog" title="Delete Category" message="Delete this category? Streams will be uncategorized." @confirm="handleDelete" />
    </AdminLayout>
</template>
