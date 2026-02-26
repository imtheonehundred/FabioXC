<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Button from '@/Components/ui/Button.vue';
import Input from '@/Components/ui/Input.vue';
import Card from '@/Components/ui/Card.vue';
import { ArrowLeft } from 'lucide-vue-next';

const form = useForm({ server_name: '', server_ip: '', domain_name: '', http_port: 80, rtmp_port: 1935 });
function submit() { form.post('/admin/servers'); }
</script>

<template>
    <Head title="Add Server" />
    <AdminLayout>
        <template #title>Add Server</template>
        <div class="mx-auto max-w-lg">
            <div class="mb-6"><Link href="/admin/servers" class="inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground"><ArrowLeft class="h-4 w-4" /> Back</Link></div>
            <Card class="p-6">
                <form @submit.prevent="submit" class="space-y-4">
                    <div class="space-y-2"><label class="text-sm font-medium">Server Name *</label><Input v-model="form.server_name" /><p v-if="form.errors.server_name" class="text-xs text-destructive">{{ form.errors.server_name }}</p></div>
                    <div class="space-y-2"><label class="text-sm font-medium">IP Address *</label><Input v-model="form.server_ip" placeholder="192.168.1.100" /><p v-if="form.errors.server_ip" class="text-xs text-destructive">{{ form.errors.server_ip }}</p></div>
                    <div class="space-y-2"><label class="text-sm font-medium">Domain Name</label><Input v-model="form.domain_name" placeholder="server.example.com" /></div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2"><label class="text-sm font-medium">HTTP Port</label><Input v-model="form.http_port" type="number" /></div>
                        <div class="space-y-2"><label class="text-sm font-medium">RTMP Port</label><Input v-model="form.rtmp_port" type="number" /></div>
                    </div>
                    <div class="flex justify-end gap-3 pt-4 border-t"><Link href="/admin/servers"><Button variant="outline" type="button">Cancel</Button></Link><Button type="submit" :disabled="form.processing">Create Server</Button></div>
                </form>
            </Card>
        </div>
    </AdminLayout>
</template>
