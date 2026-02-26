<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import DataTable from '@/Components/DataTable.vue';
import type { Column } from '@/Components/DataTable.vue';
import Button from '@/Components/ui/Button.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import { Plus, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';

defineProps<{ blockedUas: any }>();
const columns: Column[] = [
    { key: 'id', label: 'ID', sortable: true, class: 'w-16' },
    { key: 'user_agent', label: 'User Agent', sortable: true },
    { key: 'reason', label: 'Reason' },
    { key: 'created_at', label: 'Blocked At', sortable: true, class: 'w-40' },
];
const deleteDialog = ref(false);
const deleteId = ref<number | null>(null);
function confirmDelete(id: number) { deleteId.value = id; deleteDialog.value = true; }
function handleDelete() { if (deleteId.value) router.delete(`/admin/blocked-uas/${deleteId.value}`); deleteDialog.value = false; }
function formatDate(date: string | null) {
    if (!date) return '—';
    return new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
}
</script>
<template>
    <Head title="Blocked User Agents" /><AdminLayout><template #title>Blocked User Agents</template>
    <DataTable :columns="columns" :rows="blockedUas.data" :total="blockedUas.total" :current-page="blockedUas.current_page" :last-page="blockedUas.last_page" :per-page="blockedUas.per_page" :from="blockedUas.from" :to="blockedUas.to" route-name="admin.blocked-uas.index" :selectable="false">
        <template #actions><Link href="/admin/blocked-uas/create"><Button size="sm"><Plus class="mr-1 h-4 w-4" />Block User Agent</Button></Link></template>
        <template #cell-user_agent="{ value }"><span class="text-xs font-mono truncate max-w-[300px] block">{{ value }}</span></template>
        <template #cell-created_at="{ value }">{{ formatDate(value) }}</template>
        <template #row-actions="{ row }"><div class="flex items-center justify-end gap-1"><Button variant="ghost" size="icon" class="h-8 w-8 text-destructive" @click="confirmDelete(row.id)"><Trash2 class="h-4 w-4" /></Button></div></template>
    </DataTable>
    <ConfirmDialog v-model:open="deleteDialog" title="Unblock User Agent" message="This will remove the user agent from the blocked list." @confirm="handleDelete" />
    </AdminLayout>
</template>
