<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Button from '@/Components/ui/Button.vue';
import Input from '@/Components/ui/Input.vue';
import Card from '@/Components/ui/Card.vue';
import { ArrowLeft } from 'lucide-vue-next';

const form = useForm({ isp_name: '', reason: '' });
function submit() { form.post('/admin/blocked-isps'); }
</script>
<template>
    <Head title="Block ISP" /><AdminLayout><template #title>Block ISP</template>
    <div class="mx-auto max-w-lg">
        <div class="mb-6"><Link href="/admin/blocked-isps" class="inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground"><ArrowLeft class="h-4 w-4" /> Back</Link></div>
        <Card class="p-6"><form @submit.prevent="submit" class="space-y-4">
            <div class="space-y-2"><label class="text-sm font-medium">ISP Name *</label><Input v-model="form.isp_name" /><p v-if="form.errors.isp_name" class="text-xs text-destructive">{{ form.errors.isp_name }}</p></div>
            <div class="space-y-2"><label class="text-sm font-medium">Reason</label><Input v-model="form.reason" placeholder="Reason for blocking" /><p v-if="form.errors.reason" class="text-xs text-destructive">{{ form.errors.reason }}</p></div>
            <div class="flex justify-end gap-3 pt-4 border-t"><Link href="/admin/blocked-isps"><Button variant="outline" type="button">Cancel</Button></Link><Button type="submit" :disabled="form.processing">Block ISP</Button></div>
        </form></Card>
    </div></AdminLayout>
</template>
