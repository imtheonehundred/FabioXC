<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Button from '@/Components/ui/Button.vue';
import Input from '@/Components/ui/Input.vue';
import Select from '@/Components/ui/Select.vue';
import Card from '@/Components/ui/Card.vue';
import { ArrowLeft } from 'lucide-vue-next';

const props = defineProps<{ series: any; servers: Array<{ id: number; server_name: string }> }>();
const form = useForm({ season_number: 1, episode_number: 1, title: '', stream_source: '', server_id: '', cover: '', plot: '', duration: '', container_extension: 'mp4', admin_enabled: true });
function submit() { form.post(`/admin/series/${props.series.id}/episodes`); }
</script>

<template>
    <Head :title="`Add Episode - ${series.title}`" />
    <AdminLayout>
        <template #title>Add Episode to {{ series.title }}</template>
        <div class="mx-auto max-w-lg">
            <div class="mb-6"><Link :href="`/admin/series/${series.id}`" class="inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground"><ArrowLeft class="h-4 w-4" /> Back to {{ series.title }}</Link></div>
            <Card class="p-6">
                <form @submit.prevent="submit" class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2"><label class="text-sm font-medium">Season *</label><Input v-model="form.season_number" type="number" /></div>
                        <div class="space-y-2"><label class="text-sm font-medium">Episode *</label><Input v-model="form.episode_number" type="number" /></div>
                    </div>
                    <div class="space-y-2"><label class="text-sm font-medium">Title</label><Input v-model="form.title" /></div>
                    <div class="space-y-2"><label class="text-sm font-medium">Source URL *</label><Input v-model="form.stream_source" /><p v-if="form.errors.stream_source" class="text-xs text-destructive">{{ form.errors.stream_source }}</p></div>
                    <div class="space-y-2"><label class="text-sm font-medium">Server</label><Select v-model="form.server_id"><option value="">None</option><option v-for="s in servers" :key="s.id" :value="s.id">{{ s.server_name }}</option></Select></div>
                    <div class="space-y-2"><label class="text-sm font-medium">Duration</label><Input v-model="form.duration" placeholder="45 min" /></div>
                    <div class="flex justify-end gap-3 pt-4 border-t"><Link :href="`/admin/series/${series.id}`"><Button variant="outline" type="button">Cancel</Button></Link><Button type="submit" :disabled="form.processing">Add Episode</Button></div>
                </form>
            </Card>
        </div>
    </AdminLayout>
</template>
