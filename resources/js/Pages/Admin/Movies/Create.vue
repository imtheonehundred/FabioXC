<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Button from '@/Components/ui/Button.vue';
import Input from '@/Components/ui/Input.vue';
import Select from '@/Components/ui/Select.vue';
import Card from '@/Components/ui/Card.vue';
import { ArrowLeft } from 'lucide-vue-next';

defineProps<{
    categories: Array<{ id: number; category_name: string }>;
    servers: Array<{ id: number; server_name: string }>;
}>();

const form = useForm({
    stream_display_name: '', stream_source: '', category_id: '', server_id: '',
    cover: '', plot: '', cast: '', director: '', genre: '', rating: '',
    release_date: '', duration: '', youtube_trailer: '', tmdb_id: '',
    container_extension: 'mp4', admin_enabled: true,
});
function submit() { form.post('/admin/movies'); }
</script>

<template>
    <Head title="Add Movie" />
    <AdminLayout>
        <template #title>Add Movie</template>
        <div class="mx-auto max-w-2xl">
            <div class="mb-6"><Link href="/admin/movies" class="inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground"><ArrowLeft class="h-4 w-4" /> Back</Link></div>
            <Card class="p-6">
                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="space-y-2 sm:col-span-2"><label class="text-sm font-medium">Title *</label><Input v-model="form.stream_display_name" /><p v-if="form.errors.stream_display_name" class="text-xs text-destructive">{{ form.errors.stream_display_name }}</p></div>
                        <div class="space-y-2 sm:col-span-2"><label class="text-sm font-medium">Source URL *</label><Input v-model="form.stream_source" /><p v-if="form.errors.stream_source" class="text-xs text-destructive">{{ form.errors.stream_source }}</p></div>
                        <div class="space-y-2"><label class="text-sm font-medium">Category</label><Select v-model="form.category_id"><option value="">None</option><option v-for="c in categories" :key="c.id" :value="c.id">{{ c.category_name }}</option></Select></div>
                        <div class="space-y-2"><label class="text-sm font-medium">Server</label><Select v-model="form.server_id"><option value="">None</option><option v-for="s in servers" :key="s.id" :value="s.id">{{ s.server_name }}</option></Select></div>
                        <div class="space-y-2"><label class="text-sm font-medium">Genre</label><Input v-model="form.genre" placeholder="Action" /></div>
                        <div class="space-y-2"><label class="text-sm font-medium">Rating</label><Input v-model="form.rating" placeholder="8.5" /></div>
                        <div class="space-y-2"><label class="text-sm font-medium">Director</label><Input v-model="form.director" /></div>
                        <div class="space-y-2"><label class="text-sm font-medium">Duration</label><Input v-model="form.duration" placeholder="120 min" /></div>
                        <div class="space-y-2"><label class="text-sm font-medium">Release Date</label><Input v-model="form.release_date" placeholder="2024-01-15" /></div>
                        <div class="space-y-2"><label class="text-sm font-medium">TMDB ID</label><Input v-model="form.tmdb_id" /></div>
                        <div class="space-y-2 sm:col-span-2"><label class="text-sm font-medium">Cover URL</label><Input v-model="form.cover" /></div>
                        <div class="space-y-2 sm:col-span-2"><label class="text-sm font-medium">Cast</label><Input v-model="form.cast" placeholder="Actor A, Actor B" /></div>
                        <div class="space-y-2 sm:col-span-2"><label class="text-sm font-medium">Plot</label><textarea v-model="form.plot" rows="3" class="flex w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring" /></div>
                        <div class="flex items-center gap-2"><input id="enabled" v-model="form.admin_enabled" type="checkbox" class="rounded border-input" /><label for="enabled" class="text-sm font-medium cursor-pointer">Enabled</label></div>
                    </div>
                    <div class="flex justify-end gap-3 pt-4 border-t"><Link href="/admin/movies"><Button variant="outline" type="button">Cancel</Button></Link><Button type="submit" :disabled="form.processing">Create Movie</Button></div>
                </form>
            </Card>
        </div>
    </AdminLayout>
</template>
