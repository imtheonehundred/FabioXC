<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Button from '@/Components/ui/Button.vue';
import Input from '@/Components/ui/Input.vue';
import Card from '@/Components/ui/Card.vue';
import { ArrowLeft } from 'lucide-vue-next';

const form = useForm({ group_name: '' });
function submit() { form.post('/admin/groups'); }
</script>
<template>
    <Head title="Add Group" /><AdminLayout><template #title>Add Group</template>
    <div class="mx-auto max-w-lg">
        <div class="mb-6"><Link href="/admin/groups" class="inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground"><ArrowLeft class="h-4 w-4" /> Back</Link></div>
        <Card class="p-6"><form @submit.prevent="submit" class="space-y-4">
            <div class="space-y-2"><label class="text-sm font-medium">Group Name *</label><Input v-model="form.group_name" /><p v-if="form.errors.group_name" class="text-xs text-destructive">{{ form.errors.group_name }}</p></div>
            <div class="flex justify-end gap-3 pt-4 border-t"><Link href="/admin/groups"><Button variant="outline" type="button">Cancel</Button></Link><Button type="submit" :disabled="form.processing">Create Group</Button></div>
        </form></Card>
    </div></AdminLayout>
</template>
