<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Button from '@/Components/ui/Button.vue';
import Input from '@/Components/ui/Input.vue';
import Select from '@/Components/ui/Select.vue';
import Card from '@/Components/ui/Card.vue';
import { ArrowLeft } from 'lucide-vue-next';

const props = defineProps<{ episode: any; servers: Array<{ id: number; server_name: string }> }>();
const form = useForm({
    season_number: props.episode.season_number, episode_number: props.episode.episode_number,
    title: props.episode.title || '', stream_source: props.episode.stream_source,
    server_id: String(props.episode.server_id || ''), cover: props.episode.cover || '',
    plot: props.episode.plot || '', duration: props.episode.duration || '',
    container_extension: props.episode.container_extension || 'mp4', admin_enabled: Boolean(props.episode.admin_enabled),
});
function submit() { form.put(`/admin/episodes/${props.episode.id}`); }
</script>

<template>
    <Head title="Edit Episode" />
    <AdminLayout>
        <template #title>Edit Episode</template>
        <div class="mx-auto max-w-lg">
            <div class="mb-6"><Link :href="`/admin/series/${episode.series_id}`" class="inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground"><ArrowLeft class="h-4 w-4" /> Back to Series</Link></div>
            <Card class="p-6">
                <form @submit.prevent="submit" class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2"><label class="text-sm font-medium">Season</label><Input v-model="form.season_number" type="number" /></div>
                        <div class="space-y-2"><label class="text-sm font-medium">Episode</label><Input v-model="form.episode_number" type="number" /></div>
                    </div>
                    <div class="space-y-2"><label class="text-sm font-medium">Title</label><Input v-model="form.title" /></div>
                    <div class="space-y-2"><label class="text-sm font-medium">Source URL *</label><Input v-model="form.stream_source" /></div>
                    <div class="space-y-2"><label class="text-sm font-medium">Server</label><Select v-model="form.server_id"><option value="">None</option><option v-for="s in servers" :key="s.id" :value="String(s.id)">{{ s.server_name }}</option></Select></div>
                    <div class="space-y-2"><label class="text-sm font-medium">Duration</label><Input v-model="form.duration" /></div>
                    <div class="flex justify-end gap-3 pt-4 border-t"><Link :href="`/admin/series/${episode.series_id}`"><Button variant="outline" type="button">Cancel</Button></Link><Button type="submit" :disabled="form.processing">Save Changes</Button></div>
                </form>
            </Card>
        </div>
    </AdminLayout>
</template>
