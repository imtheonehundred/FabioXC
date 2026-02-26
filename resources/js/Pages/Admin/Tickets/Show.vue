<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Button from '@/Components/ui/Button.vue';
import Select from '@/Components/ui/Select.vue';
import Badge from '@/Components/ui/Badge.vue';
import Card from '@/Components/ui/Card.vue';
import { ArrowLeft } from 'lucide-vue-next';

const props = defineProps<{ ticket: any }>();

const statusVariants: Record<string, string> = {
    open: 'warning',
    in_progress: 'default',
    closed: 'secondary',
};

const form = useForm({
    status: props.ticket.status,
    admin_reply: '',
});

function submit() { form.put(`/admin/tickets/${props.ticket.id}`); }

function formatDate(date: string | null) {
    if (!date) return '—';
    return new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
}
</script>
<template>
    <Head :title="`Ticket #${ticket.id}`" />
    <AdminLayout>
        <template #title>Ticket #{{ ticket.id }}</template>
        <div class="mx-auto max-w-2xl space-y-6">
            <div class="mb-6"><Link href="/admin/tickets" class="inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground"><ArrowLeft class="h-4 w-4" /> Back</Link></div>

            <Card class="p-6">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <h2 class="text-lg font-bold">{{ ticket.subject }}</h2>
                        <p class="text-sm text-muted-foreground">
                            By {{ ticket.user?.username || 'Unknown' }} &middot; {{ formatDate(ticket.created_at) }}
                        </p>
                    </div>
                    <Badge :variant="(statusVariants[ticket.status] as any) || 'default'">{{ ticket.status?.replace('_', ' ') }}</Badge>
                </div>
                <div class="rounded-lg bg-muted/50 p-4 text-sm whitespace-pre-wrap">{{ ticket.message }}</div>
            </Card>

            <Card class="p-6">
                <h3 class="mb-4 text-sm font-semibold text-muted-foreground uppercase tracking-wider">Admin Response</h3>
                <form @submit.prevent="submit" class="space-y-4">
                    <div class="space-y-2"><label class="text-sm font-medium">Status</label>
                        <Select v-model="form.status">
                            <option value="open">Open</option>
                            <option value="in_progress">In Progress</option>
                            <option value="closed">Closed</option>
                        </Select>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium">Reply</label>
                        <textarea v-model="form.admin_reply" rows="4" class="flex w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring" placeholder="Type your reply..."></textarea>
                        <p v-if="form.errors.admin_reply" class="text-xs text-destructive">{{ form.errors.admin_reply }}</p>
                    </div>
                    <div class="flex justify-end gap-3 pt-4 border-t"><Link href="/admin/tickets"><Button variant="outline" type="button">Cancel</Button></Link><Button type="submit" :disabled="form.processing">Update Ticket</Button></div>
                </form>
            </Card>
        </div>
    </AdminLayout>
</template>
