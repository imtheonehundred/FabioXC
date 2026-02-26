<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import DataTable from '@/Components/DataTable.vue';
import type { Column } from '@/Components/DataTable.vue';
import Badge from '@/Components/ui/Badge.vue';
import Button from '@/Components/ui/Button.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import { Plus, Pencil, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';

defineProps<{
    users: { data: any[]; current_page: number; last_page: number; per_page: number; total: number; from: number | null; to: number | null };
    filters: Record<string, any>;
}>();

const columns: Column[] = [
    { key: 'id', label: 'ID', sortable: true, class: 'w-16' },
    { key: 'username', label: 'Username', sortable: true },
    { key: 'email', label: 'Email', sortable: true },
    { key: 'group', label: 'Group', class: 'w-36' },
    { key: 'created_at', label: 'Created', sortable: true, class: 'w-32' },
];

const deleteDialog = ref(false);
const deleteId = ref<number | null>(null);
function confirmDelete(id: number) { deleteId.value = id; deleteDialog.value = true; }
function handleDelete() { if (deleteId.value) router.delete(`/admin/users/${deleteId.value}`); deleteDialog.value = false; }
</script>

<template>
    <Head title="Users" />
    <AdminLayout>
        <template #title>Users</template>
        <DataTable :columns="columns" :rows="users.data" :total="users.total" :current-page="users.current_page" :last-page="users.last_page" :per-page="users.per_page" :from="users.from" :to="users.to" route-name="admin.users.index" :filters="filters" :selectable="false">
            <template #actions><Link href="/admin/users/create"><Button size="sm"><Plus class="mr-1 h-4 w-4" />Add User</Button></Link></template>
            <template #cell-username="{ value }"><span class="font-medium">{{ value }}</span></template>
            <template #cell-email="{ value }">{{ value || '—' }}</template>
            <template #cell-group="{ row }"><Badge v-if="row.group" variant="secondary">{{ row.group.group_name }}</Badge><span v-else class="text-muted-foreground">—</span></template>
            <template #cell-created_at="{ value }">{{ value ? new Date(value).toLocaleDateString() : '—' }}</template>
            <template #row-actions="{ row }">
                <div class="flex items-center justify-end gap-1">
                    <Link :href="`/admin/users/${row.id}/edit`"><Button variant="ghost" size="icon" class="h-8 w-8"><Pencil class="h-4 w-4" /></Button></Link>
                    <Button variant="ghost" size="icon" class="h-8 w-8 text-destructive" @click="confirmDelete(row.id)"><Trash2 class="h-4 w-4" /></Button>
                </div>
            </template>
        </DataTable>
        <ConfirmDialog v-model:open="deleteDialog" title="Delete User" message="This will permanently delete this user." @confirm="handleDelete" />
    </AdminLayout>
</template>
