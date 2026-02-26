<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import DataTable from '@/Components/DataTable.vue';
import type { Column } from '@/Components/DataTable.vue';
import Button from '@/Components/ui/Button.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import { Plus, Pencil, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';

defineProps<{ profiles: any }>();
const columns: Column[] = [
    { key: 'id', label: 'ID', sortable: true, class: 'w-16' },
    { key: 'name', label: 'Name', sortable: true },
    { key: 'video_codec', label: 'Video Codec', class: 'w-28' },
    { key: 'audio_codec', label: 'Audio Codec', class: 'w-28' },
    { key: 'resolution', label: 'Resolution', class: 'w-28' },
    { key: 'video_bitrate', label: 'Video Bitrate', class: 'w-28' },
];
const deleteDialog = ref(false);
const deleteId = ref<number | null>(null);
function confirmDelete(id: number) { deleteId.value = id; deleteDialog.value = true; }
function handleDelete() { if (deleteId.value) router.delete(`/admin/transcode-profiles/${deleteId.value}`); deleteDialog.value = false; }
</script>
<template>
    <Head title="Transcode Profiles" /><AdminLayout><template #title>Transcode Profiles</template>
    <DataTable :columns="columns" :rows="profiles.data" :total="profiles.total" :current-page="profiles.current_page" :last-page="profiles.last_page" :per-page="profiles.per_page" :from="profiles.from" :to="profiles.to" route-name="admin.transcode-profiles.index" :selectable="false">
        <template #actions><Link href="/admin/transcode-profiles/create"><Button size="sm"><Plus class="mr-1 h-4 w-4" />Add Profile</Button></Link></template>
        <template #cell-video_codec="{ value }"><span class="font-mono text-xs">{{ value || '—' }}</span></template>
        <template #cell-audio_codec="{ value }"><span class="font-mono text-xs">{{ value || '—' }}</span></template>
        <template #cell-resolution="{ value }">{{ value || '—' }}</template>
        <template #cell-video_bitrate="{ value }">{{ value ? `${value} kbps` : '—' }}</template>
        <template #row-actions="{ row }"><div class="flex items-center justify-end gap-1"><Link :href="`/admin/transcode-profiles/${row.id}/edit`"><Button variant="ghost" size="icon" class="h-8 w-8"><Pencil class="h-4 w-4" /></Button></Link><Button variant="ghost" size="icon" class="h-8 w-8 text-destructive" @click="confirmDelete(row.id)"><Trash2 class="h-4 w-4" /></Button></div></template>
    </DataTable>
    <ConfirmDialog v-model:open="deleteDialog" title="Delete Profile" message="This will permanently delete this transcode profile." @confirm="handleDelete" />
    </AdminLayout>
</template>
