<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import DataTable from '@/Components/DataTable.vue';
import type { Column } from '@/Components/DataTable.vue';
import Button from '@/Components/ui/Button.vue';
import Select from '@/Components/ui/Select.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import Badge from '@/Components/ui/Badge.vue';
import { Plus, Pencil, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps<{
    movies: { data: any[]; current_page: number; last_page: number; per_page: number; total: number; from: number | null; to: number | null };
    categories: Array<{ id: number; category_name: string }>;
    filters: Record<string, any>;
}>();

const columns: Column[] = [
    { key: 'id', label: 'ID', sortable: true, class: 'w-16' },
    { key: 'stream_display_name', label: 'Title', sortable: true },
    { key: 'genre', label: 'Genre', class: 'w-28' },
    { key: 'rating', label: 'Rating', sortable: true, class: 'w-20' },
    { key: 'category', label: 'Category', class: 'w-32' },
    { key: 'release_date', label: 'Released', class: 'w-28' },
];

const deleteDialog = ref(false);
const deleteId = ref<number | null>(null);
function confirmDelete(id: number) { deleteId.value = id; deleteDialog.value = true; }
function handleDelete() { if (deleteId.value) router.delete(`/admin/movies/${deleteId.value}`); deleteDialog.value = false; }
function handleBulkAction(action: string, ids: number[]) { router.post('/admin/movies/mass-action', { action, ids }); }
</script>

<template>
    <Head title="Movies" />
    <AdminLayout>
        <template #title>Movies</template>
        <DataTable :columns="columns" :rows="movies.data" :total="movies.total" :current-page="movies.current_page" :last-page="movies.last_page" :per-page="movies.per_page" :from="movies.from" :to="movies.to" route-name="admin.movies.index" :filters="filters" @bulk-action="handleBulkAction">
            <template #filters>
                <Select :model-value="filters.category_id || ''" class="w-40" @update:model-value="v => router.get('/admin/movies', { ...filters, category_id: v || undefined, page: 1 }, { preserveState: true })">
                    <option value="">All Categories</option>
                    <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.category_name }}</option>
                </Select>
            </template>
            <template #actions="{ selected, bulkAction }">
                <Button v-if="selected.length > 0" variant="destructive" size="sm" @click="bulkAction('delete')"><Trash2 class="mr-1 h-4 w-4" />Delete</Button>
                <Link href="/admin/movies/create"><Button size="sm"><Plus class="mr-1 h-4 w-4" />Add Movie</Button></Link>
            </template>
            <template #cell-stream_display_name="{ row }">
                <div class="flex items-center gap-3">
                    <img v-if="row.cover" :src="row.cover" class="h-10 w-7 rounded object-cover shrink-0" />
                    <div>
                        <p class="font-medium">{{ row.stream_display_name }}</p>
                        <p v-if="row.director" class="text-xs text-muted-foreground">Dir: {{ row.director }}</p>
                    </div>
                </div>
            </template>
            <template #cell-genre="{ value }"><Badge v-if="value" variant="secondary">{{ value }}</Badge><span v-else class="text-muted-foreground">—</span></template>
            <template #cell-rating="{ value }">
                <span v-if="value" class="font-medium">{{ value }}<span class="text-muted-foreground text-xs">/10</span></span>
                <span v-else class="text-muted-foreground">—</span>
            </template>
            <template #cell-category="{ row }">{{ row.category?.category_name || '—' }}</template>
            <template #row-actions="{ row }">
                <div class="flex items-center justify-end gap-1">
                    <Link :href="`/admin/movies/${row.id}/edit`"><Button variant="ghost" size="icon" class="h-8 w-8"><Pencil class="h-4 w-4" /></Button></Link>
                    <Button variant="ghost" size="icon" class="h-8 w-8 text-destructive" @click="confirmDelete(row.id)"><Trash2 class="h-4 w-4" /></Button>
                </div>
            </template>
        </DataTable>
        <ConfirmDialog v-model:open="deleteDialog" title="Delete Movie" message="This will permanently delete this movie." @confirm="handleDelete" />
    </AdminLayout>
</template>
