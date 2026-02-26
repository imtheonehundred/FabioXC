<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import DataTable from '@/Components/DataTable.vue';
import type { Column } from '@/Components/DataTable.vue';
import Button from '@/Components/ui/Button.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import { Plus, Pencil, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';

defineProps<{ groups: any }>();
const columns: Column[] = [
    { key: 'id', label: 'ID', sortable: true, class: 'w-16' },
    { key: 'group_name', label: 'Group Name', sortable: true },
    { key: 'users_count', label: 'Users', sortable: true, class: 'w-24' },
];
const deleteDialog = ref(false);
const deleteId = ref<number | null>(null);
function confirmDelete(id: number) { deleteId.value = id; deleteDialog.value = true; }
function handleDelete() { if (deleteId.value) router.delete(`/admin/groups/${deleteId.value}`); deleteDialog.value = false; }
</script>
<template>
    <Head title="Groups" /><AdminLayout><template #title>Groups</template>
    <DataTable :columns="columns" :rows="groups.data" :total="groups.total" :current-page="groups.current_page" :last-page="groups.last_page" :per-page="groups.per_page" :from="groups.from" :to="groups.to" route-name="admin.groups.index" :selectable="false">
        <template #actions><Link href="/admin/groups/create"><Button size="sm"><Plus class="mr-1 h-4 w-4" />Add Group</Button></Link></template>
        <template #row-actions="{ row }"><div class="flex items-center justify-end gap-1"><Link :href="`/admin/groups/${row.id}/edit`"><Button variant="ghost" size="icon" class="h-8 w-8"><Pencil class="h-4 w-4" /></Button></Link><Button variant="ghost" size="icon" class="h-8 w-8 text-destructive" @click="confirmDelete(row.id)"><Trash2 class="h-4 w-4" /></Button></div></template>
    </DataTable>
    <ConfirmDialog v-model:open="deleteDialog" title="Delete Group" message="This will remove the group." @confirm="handleDelete" />
    </AdminLayout>
</template>
