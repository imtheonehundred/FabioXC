<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Button from '@/Components/ui/Button.vue';
import Input from '@/Components/ui/Input.vue';
import Select from '@/Components/ui/Select.vue';
import Card from '@/Components/ui/Card.vue';
import { ArrowLeft } from 'lucide-vue-next';

const props = defineProps<{ user: any; groups: Array<{ id: number; group_name: string }> }>();
const form = useForm({ username: props.user.username, password: '', email: props.user.email || '', member_group_id: String(props.user.member_group_id || '') });
function submit() { form.put(`/admin/users/${props.user.id}`); }
</script>

<template>
    <Head :title="`Edit: ${user.username}`" />
    <AdminLayout>
        <template #title>Edit User</template>
        <div class="mx-auto max-w-lg">
            <div class="mb-6"><Link href="/admin/users" class="inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground"><ArrowLeft class="h-4 w-4" /> Back</Link></div>
            <Card class="p-6">
                <form @submit.prevent="submit" class="space-y-4">
                    <div class="space-y-2"><label class="text-sm font-medium">Username *</label><Input v-model="form.username" /></div>
                    <div class="space-y-2"><label class="text-sm font-medium">Password</label><Input v-model="form.password" type="password" placeholder="Leave blank to keep current" /></div>
                    <div class="space-y-2"><label class="text-sm font-medium">Email</label><Input v-model="form.email" type="email" /></div>
                    <div class="space-y-2"><label class="text-sm font-medium">Group</label><Select v-model="form.member_group_id"><option value="">None</option><option v-for="g in groups" :key="g.id" :value="String(g.id)">{{ g.group_name }}</option></Select></div>
                    <div class="flex justify-end gap-3 pt-4 border-t"><Link href="/admin/users"><Button variant="outline" type="button">Cancel</Button></Link><Button type="submit" :disabled="form.processing">Save Changes</Button></div>
                </form>
            </Card>
        </div>
    </AdminLayout>
</template>
