<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Button from '@/Components/ui/Button.vue';
import Input from '@/Components/ui/Input.vue';
import Select from '@/Components/ui/Select.vue';
import Card from '@/Components/ui/Card.vue';
import { ArrowLeft } from 'lucide-vue-next';

interface Props {
    stream: any;
    categories: Array<{ id: number; category_name: string }>;
    servers: Array<{ id: number; server_name: string }>;
    epgs: Array<{ id: number; epg_name: string }>;
}

const props = defineProps<Props>();

const form = useForm({
    stream_display_name: props.stream.stream_display_name,
    stream_source: props.stream.stream_source,
    type: props.stream.type,
    category_id: String(props.stream.category_id || ''),
    server_id: String(props.stream.server_id || ''),
    epg_id: String(props.stream.epg_id || ''),
    stream_icon: props.stream.stream_icon || '',
    notes: props.stream.notes || '',
    admin_enabled: Boolean(props.stream.admin_enabled),
});

function submit() {
    form.put(`/admin/streams/${props.stream.id}`);
}
</script>

<template>
    <Head :title="`Edit: ${stream.stream_display_name}`" />
    <AdminLayout>
        <template #title>Edit Stream</template>

        <div class="mx-auto max-w-2xl">
            <div class="mb-6">
                <Link href="/admin/streams" class="inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground">
                    <ArrowLeft class="h-4 w-4" /> Back to Streams
                </Link>
            </div>

            <Card class="p-6">
                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div class="space-y-2 sm:col-span-2">
                            <label class="text-sm font-medium">Stream Name *</label>
                            <Input v-model="form.stream_display_name" />
                            <p v-if="form.errors.stream_display_name" class="text-xs text-destructive">{{ form.errors.stream_display_name }}</p>
                        </div>

                        <div class="space-y-2 sm:col-span-2">
                            <label class="text-sm font-medium">Stream Source *</label>
                            <Input v-model="form.stream_source" />
                            <p v-if="form.errors.stream_source" class="text-xs text-destructive">{{ form.errors.stream_source }}</p>
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-medium">Type</label>
                            <Select v-model="form.type">
                                <option value="live">Live</option>
                                <option value="created">Created</option>
                                <option value="radio">Radio</option>
                            </Select>
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-medium">Category</label>
                            <Select v-model="form.category_id">
                                <option value="">None</option>
                                <option v-for="cat in categories" :key="cat.id" :value="String(cat.id)">{{ cat.category_name }}</option>
                            </Select>
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-medium">Server</label>
                            <Select v-model="form.server_id">
                                <option value="">None</option>
                                <option v-for="s in servers" :key="s.id" :value="String(s.id)">{{ s.server_name }}</option>
                            </Select>
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-medium">EPG</label>
                            <Select v-model="form.epg_id">
                                <option value="">None</option>
                                <option v-for="e in epgs" :key="e.id" :value="String(e.id)">{{ e.epg_name }}</option>
                            </Select>
                        </div>

                        <div class="space-y-2 sm:col-span-2">
                            <label class="text-sm font-medium">Stream Icon URL</label>
                            <Input v-model="form.stream_icon" />
                        </div>

                        <div class="space-y-2 sm:col-span-2">
                            <label class="text-sm font-medium">Notes</label>
                            <textarea
                                v-model="form.notes"
                                rows="3"
                                class="flex w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                            />
                        </div>

                        <div class="flex items-center gap-2">
                            <input id="enabled" v-model="form.admin_enabled" type="checkbox" class="rounded border-input" />
                            <label for="enabled" class="text-sm font-medium cursor-pointer">Enabled</label>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t">
                        <Link href="/admin/streams">
                            <Button variant="outline" type="button">Cancel</Button>
                        </Link>
                        <Button type="submit" :disabled="form.processing">
                            {{ form.processing ? 'Saving...' : 'Save Changes' }}
                        </Button>
                    </div>
                </form>
            </Card>
        </div>
    </AdminLayout>
</template>
