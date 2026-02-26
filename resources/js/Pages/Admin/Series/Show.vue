<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Card from '@/Components/ui/Card.vue';
import Button from '@/Components/ui/Button.vue';
import Badge from '@/Components/ui/Badge.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import { ArrowLeft, Pencil, Plus, Trash2, Tv } from 'lucide-vue-next';
import { ref, computed } from 'vue';

const props = defineProps<{ series: any }>();

const seasons = computed(() => {
    const map = new Map<number, any[]>();
    for (const ep of (props.series.episodes || [])) {
        if (!map.has(ep.season_number)) map.set(ep.season_number, []);
        map.get(ep.season_number)!.push(ep);
    }
    return Array.from(map.entries()).sort(([a], [b]) => a - b);
});

const deleteDialog = ref(false);
const deleteEpId = ref<number | null>(null);
function confirmDeleteEp(id: number) { deleteEpId.value = id; deleteDialog.value = true; }
function handleDeleteEp() { if (deleteEpId.value) router.delete(`/admin/episodes/${deleteEpId.value}`); deleteDialog.value = false; }
</script>

<template>
    <Head :title="series.title" />
    <AdminLayout>
        <template #title>{{ series.title }}</template>
        <div class="mx-auto max-w-4xl space-y-6">
            <div class="flex items-center justify-between">
                <Link href="/admin/series" class="inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground"><ArrowLeft class="h-4 w-4" /> Back</Link>
                <div class="flex gap-2">
                    <Link :href="`/admin/series/${series.id}/edit`"><Button variant="outline" size="sm"><Pencil class="mr-1 h-4 w-4" /> Edit</Button></Link>
                    <Link :href="`/admin/series/${series.id}/episodes/create`"><Button size="sm"><Plus class="mr-1 h-4 w-4" /> Add Episode</Button></Link>
                </div>
            </div>

            <Card class="p-6">
                <div class="flex items-start gap-6">
                    <img v-if="series.cover" :src="series.cover" class="h-44 w-32 rounded-lg object-cover shrink-0" />
                    <div v-else class="flex h-44 w-32 shrink-0 items-center justify-center rounded-lg bg-primary/10"><Tv class="h-10 w-10 text-primary" /></div>
                    <div class="flex-1 space-y-3">
                        <h2 class="text-2xl font-bold">{{ series.title }}</h2>
                        <div class="flex flex-wrap gap-2">
                            <Badge v-if="series.genre" variant="secondary">{{ series.genre }}</Badge>
                            <Badge v-if="series.rating" variant="outline">Rating: {{ series.rating }}</Badge>
                            <Badge variant="outline">{{ series.episodes?.length || 0 }} episodes</Badge>
                        </div>
                        <p v-if="series.plot" class="text-sm text-muted-foreground">{{ series.plot }}</p>
                        <div class="flex gap-4 text-sm text-muted-foreground">
                            <span v-if="series.cast">Cast: {{ series.cast }}</span>
                            <span v-if="series.release_date">Released: {{ series.release_date }}</span>
                        </div>
                    </div>
                </div>
            </Card>

            <div v-for="[seasonNum, episodes] in seasons" :key="seasonNum" class="space-y-3">
                <h3 class="text-lg font-semibold">Season {{ seasonNum }}</h3>
                <div class="rounded-md border">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b bg-muted/50">
                                <th class="w-16 px-4 py-2 text-left text-muted-foreground">#</th>
                                <th class="px-4 py-2 text-left text-muted-foreground">Title</th>
                                <th class="w-24 px-4 py-2 text-left text-muted-foreground">Duration</th>
                                <th class="w-20 px-4 py-2 text-right text-muted-foreground">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="ep in episodes" :key="ep.id" class="border-b hover:bg-muted/50">
                                <td class="px-4 py-2 font-mono text-muted-foreground">E{{ String(ep.episode_number).padStart(2, '0') }}</td>
                                <td class="px-4 py-2 font-medium">{{ ep.title || `Episode ${ep.episode_number}` }}</td>
                                <td class="px-4 py-2 text-muted-foreground">{{ ep.duration || '—' }}</td>
                                <td class="px-4 py-2 text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <Link :href="`/admin/episodes/${ep.id}/edit`"><Button variant="ghost" size="icon" class="h-7 w-7"><Pencil class="h-3.5 w-3.5" /></Button></Link>
                                        <Button variant="ghost" size="icon" class="h-7 w-7 text-destructive" @click="confirmDeleteEp(ep.id)"><Trash2 class="h-3.5 w-3.5" /></Button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <p v-if="seasons.length === 0" class="text-center text-muted-foreground py-8">No episodes yet. Add the first episode to get started.</p>
        </div>
        <ConfirmDialog v-model:open="deleteDialog" title="Delete Episode" message="This will permanently delete this episode." @confirm="handleDeleteEp" />
    </AdminLayout>
</template>
