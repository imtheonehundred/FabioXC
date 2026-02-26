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

defineProps<{
    packages: { data: any[]; current_page: number; last_page: number; per_page: number; total: number; from: number | null; to: number | null };
    filters: Record<string, any>;
}>();

const columns: Column[] = [
    { key: 'id', label: 'ID', sortable: true, class: 'w-16' },
    { key: 'package_name', label: 'Name', sortable: true },
    { key: 'lines_count', label: 'Lines', sortable: true, class: 'w-20' },
    { key: 'flags', label: 'Flags', class: 'w-40' },
];

const deleteDialog = ref(false);
const deleteId = ref<number | null>(null);
function confirmDelete(id: number) { deleteId.value = id; deleteDialog.value = true; }
function handleDelete() { if (deleteId.value) router.delete(`/admin/packages/${deleteId.value}`); deleteDialog.value = false; }
</script>

<template>
    <Head title="Packages" />
    <AdminLayout>
        <template #title>Packages</template>
        <DataTable :columns="columns" :rows="packages.data" :total="packages.total" :current-page="packages.current_page" :last-page="packages.last_page" :per-page="packages.per_page" :from="packages.from" :to="packages.to" route-name="admin.packages.index" :filters="filters" :selectable="false">
            <template #actions><Link href="/admin/packages/create"><Button size="sm"><Plus class="mr-1 h-4 w-4" />Add Package</Button></Link></template>
            <template #cell-flags="{ row }">
                <div class="flex gap-1">
                    <Badge v-if="row.is_trial" variant="warning">Trial</Badge>
                    <Badge v-if="row.is_official" variant="success">Official</Badge>
                    <Badge v-if="row.is_addon" variant="secondary">Add-on</Badge>
                </div>
            </template>
            <template #row-actions="{ row }">
                <div class="flex items-center justify-end gap-1">
                    <Link :href="`/admin/packages/${row.id}/edit`"><Button variant="ghost" size="icon" class="h-8 w-8"><Pencil class="h-4 w-4" /></Button></Link>
                    <Button variant="ghost" size="icon" class="h-8 w-8 text-destructive" @click="confirmDelete(row.id)"><Trash2 class="h-4 w-4" /></Button>
                </div>
            </template>
        </DataTable>
        <ConfirmDialog v-model:open="deleteDialog" title="Delete Package" message="This will remove the package from all lines." @confirm="handleDelete" />
    </AdminLayout>
</template>
