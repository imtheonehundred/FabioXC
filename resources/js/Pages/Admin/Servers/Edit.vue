<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Button from '@/Components/ui/Button.vue';
import Input from '@/Components/ui/Input.vue';
import Card from '@/Components/ui/Card.vue';
import { ArrowLeft } from 'lucide-vue-next';

const props = defineProps<{ server: any }>();
const form = useForm({ server_name: props.server.server_name, server_ip: props.server.server_ip, domain_name: props.server.domain_name || '', http_port: props.server.http_port, rtmp_port: props.server.rtmp_port });
function submit() { form.put(`/admin/servers/${props.server.id}`); }
</script>

<template>
    <Head :title="`Edit: ${server.server_name}`" />
    <AdminLayout>
        <template #title>Edit Server</template>
        <div class="mx-auto max-w-lg">
            <div class="mb-6"><Link href="/admin/servers" class="inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground"><ArrowLeft class="h-4 w-4" /> Back</Link></div>
            <Card class="p-6">
                <form @submit.prevent="submit" class="space-y-4">
                    <div class="space-y-2"><label class="text-sm font-medium">Server Name *</label><Input v-model="form.server_name" /></div>
                    <div class="space-y-2"><label class="text-sm font-medium">IP Address *</label><Input v-model="form.server_ip" /></div>
                    <div class="space-y-2"><label class="text-sm font-medium">Domain Name</label><Input v-model="form.domain_name" /></div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2"><label class="text-sm font-medium">HTTP Port</label><Input v-model="form.http_port" type="number" /></div>
                        <div class="space-y-2"><label class="text-sm font-medium">RTMP Port</label><Input v-model="form.rtmp_port" type="number" /></div>
                    </div>
                    <div class="flex justify-end gap-3 pt-4 border-t"><Link href="/admin/servers"><Button variant="outline" type="button">Cancel</Button></Link><Button type="submit" :disabled="form.processing">Save Changes</Button></div>
                </form>
            </Card>
        </div>
    </AdminLayout>
</template>
