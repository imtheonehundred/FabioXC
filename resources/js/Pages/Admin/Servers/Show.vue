<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Card from '@/Components/ui/Card.vue';
import Button from '@/Components/ui/Button.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import { ArrowLeft, Pencil, Server } from 'lucide-vue-next';

defineProps<{ server: any }>();
</script>

<template>
    <Head :title="server.server_name" />
    <AdminLayout>
        <template #title>{{ server.server_name }}</template>
        <div class="mx-auto max-w-3xl space-y-6">
            <div class="flex items-center justify-between">
                <Link href="/admin/servers" class="inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground"><ArrowLeft class="h-4 w-4" /> Back</Link>
                <Link :href="`/admin/servers/${server.id}/edit`"><Button size="sm"><Pencil class="mr-1 h-4 w-4" /> Edit</Button></Link>
            </div>

            <Card class="p-6">
                <div class="flex items-center gap-4 mb-6">
                    <div class="flex h-14 w-14 items-center justify-center rounded-xl bg-primary/10"><Server class="h-7 w-7 text-primary" /></div>
                    <div>
                        <div class="flex items-center gap-3"><h2 class="text-xl font-bold">{{ server.server_name }}</h2><StatusBadge :status="server.status" type="server" /></div>
                        <p class="text-sm text-muted-foreground"><code>{{ server.server_ip }}</code><span v-if="server.domain_name"> &middot; {{ server.domain_name }}</span></p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-6 sm:grid-cols-4">
                    <div class="space-y-1 text-center p-4 rounded-lg bg-muted/50">
                        <p class="text-2xl font-bold">{{ server.total_clients }}</p>
                        <p class="text-xs text-muted-foreground">Clients</p>
                    </div>
                    <div class="space-y-1 text-center p-4 rounded-lg bg-muted/50">
                        <p class="text-2xl font-bold">{{ server.cpu_usage ?? 0 }}%</p>
                        <p class="text-xs text-muted-foreground">CPU Usage</p>
                    </div>
                    <div class="space-y-1 text-center p-4 rounded-lg bg-muted/50">
                        <p class="text-2xl font-bold">{{ server.mem_usage ?? 0 }}%</p>
                        <p class="text-xs text-muted-foreground">Memory</p>
                    </div>
                    <div class="space-y-1 text-center p-4 rounded-lg bg-muted/50">
                        <p class="text-2xl font-bold">{{ server.disk_usage ?? 0 }}%</p>
                        <p class="text-xs text-muted-foreground">Disk</p>
                    </div>
                </div>
            </Card>

            <Card class="p-6">
                <h3 class="mb-4 text-sm font-semibold text-muted-foreground uppercase tracking-wider">Connection Details</h3>
                <dl class="grid grid-cols-2 gap-4 text-sm">
                    <div><dt class="text-muted-foreground">HTTP Port</dt><dd class="font-medium font-mono">{{ server.http_port }}</dd></div>
                    <div><dt class="text-muted-foreground">RTMP Port</dt><dd class="font-medium font-mono">{{ server.rtmp_port }}</dd></div>
                    <div><dt class="text-muted-foreground">Uptime</dt><dd class="font-medium">{{ server.uptime || '—' }}</dd></div>
                    <div><dt class="text-muted-foreground">Streams</dt><dd class="font-medium">{{ server.streams_count }}</dd></div>
                </dl>
            </Card>
        </div>
    </AdminLayout>
</template>
