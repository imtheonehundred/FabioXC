<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Card from '@/Components/ui/Card.vue';
import Button from '@/Components/ui/Button.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import { ArrowLeft, Pencil, Play, Square, Radio, Server, Tv } from 'lucide-vue-next';

const props = defineProps<{ stream: any }>();

const status = Number(props.stream.status);
const isLive = props.stream.type === 'live' || props.stream.type === 'created';
const canStart = isLive && (status === 0 || status === 3);
const canStop = isLive && (status === 1 || status === 2);

function startStream() {
    router.post(`/admin/streams/${props.stream.id}/start`, {}, { preserveScroll: true });
}

function stopStream() {
    router.post(`/admin/streams/${props.stream.id}/stop`, {}, { preserveScroll: true });
}
</script>

<template>
    <Head :title="stream.stream_display_name" />
    <AdminLayout>
        <template #title>{{ stream.stream_display_name }}</template>

        <div class="mx-auto max-w-3xl space-y-6">
            <div class="flex items-center justify-between">
                <Link href="/admin/streams" class="inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground">
                    <ArrowLeft class="h-4 w-4" /> Back to Streams
                </Link>
                <div class="flex items-center gap-2">
                    <Button
                        v-if="canStart"
                        size="sm"
                        variant="default"
                        :disabled="stream.status === 2 || Number(stream.status) === 2"
                        @click="startStream"
                    >
                        <Play class="mr-1 h-4 w-4" /> Start Stream
                    </Button>
                    <Button
                        v-if="canStop"
                        size="sm"
                        variant="destructive"
                        @click="stopStream"
                    >
                        <Square class="mr-1 h-4 w-4" /> Stop Stream
                    </Button>
                    <Link :href="`/admin/streams/${stream.id}/edit`">
                        <Button size="sm"><Pencil class="mr-1 h-4 w-4" /> Edit</Button>
                    </Link>
                </div>
            </div>

            <Card class="p-6">
                <div class="flex items-start gap-4">
                    <div v-if="stream.stream_icon" class="shrink-0">
                        <img :src="stream.stream_icon" class="h-20 w-20 rounded-lg object-cover" />
                    </div>
                    <div v-else class="flex h-20 w-20 shrink-0 items-center justify-center rounded-lg bg-primary/10">
                        <Radio class="h-8 w-8 text-primary" />
                    </div>
                    <div class="flex-1 space-y-2">
                        <div class="flex items-center gap-3">
                            <h2 class="text-xl font-bold">{{ stream.stream_display_name }}</h2>
                            <StatusBadge :status="stream.status" type="stream" />
                        </div>
                        <p class="text-sm text-muted-foreground break-all">{{ stream.stream_source }}</p>
                    </div>
                </div>
            </Card>

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <Card class="p-6 space-y-4">
                    <h3 class="text-sm font-semibold text-muted-foreground uppercase tracking-wider">Details</h3>
                    <dl class="space-y-3">
                        <div class="flex justify-between text-sm">
                            <dt class="text-muted-foreground">Type</dt>
                            <dd class="capitalize font-medium">{{ stream.type }}</dd>
                        </div>
                        <div class="flex justify-between text-sm">
                            <dt class="text-muted-foreground">Category</dt>
                            <dd class="font-medium">{{ stream.category?.category_name || '—' }}</dd>
                        </div>
                        <div class="flex justify-between text-sm">
                            <dt class="text-muted-foreground">Resolution</dt>
                            <dd class="font-medium">{{ stream.resolution || '—' }}</dd>
                        </div>
                        <div class="flex justify-between text-sm">
                            <dt class="text-muted-foreground">Bitrate</dt>
                            <dd class="font-medium">{{ stream.bitrate ? `${stream.bitrate} kbps` : '—' }}</dd>
                        </div>
                        <div class="flex justify-between text-sm">
                            <dt class="text-muted-foreground">Viewers</dt>
                            <dd class="font-medium">{{ stream.current_viewers }}</dd>
                        </div>
                        <div class="flex justify-between text-sm">
                            <dt class="text-muted-foreground">Enabled</dt>
                            <dd class="font-medium">{{ stream.admin_enabled ? 'Yes' : 'No' }}</dd>
                        </div>
                    </dl>
                </Card>

                <Card class="p-6 space-y-4">
                    <h3 class="text-sm font-semibold text-muted-foreground uppercase tracking-wider">Server Info</h3>
                    <dl class="space-y-3">
                        <div class="flex justify-between text-sm">
                            <dt class="text-muted-foreground">Server</dt>
                            <dd class="font-medium">{{ stream.server?.server_name || '—' }}</dd>
                        </div>
                        <div class="flex justify-between text-sm">
                            <dt class="text-muted-foreground">EPG</dt>
                            <dd class="font-medium">{{ stream.epg?.epg_name || '—' }}</dd>
                        </div>
                        <div class="flex justify-between text-sm">
                            <dt class="text-muted-foreground">PID</dt>
                            <dd class="font-medium font-mono">{{ stream.pid || '—' }}</dd>
                        </div>
                        <div class="flex justify-between text-sm">
                            <dt class="text-muted-foreground">Added</dt>
                            <dd class="font-medium">{{ stream.added || '—' }}</dd>
                        </div>
                    </dl>
                </Card>
            </div>

            <Card v-if="stream.notes" class="p-6">
                <h3 class="mb-2 text-sm font-semibold text-muted-foreground uppercase tracking-wider">Notes</h3>
                <p class="text-sm whitespace-pre-wrap">{{ stream.notes }}</p>
            </Card>
        </div>
    </AdminLayout>
</template>
