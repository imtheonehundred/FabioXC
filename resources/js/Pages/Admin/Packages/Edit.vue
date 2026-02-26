<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Button from '@/Components/ui/Button.vue';
import Input from '@/Components/ui/Input.vue';
import Card from '@/Components/ui/Card.vue';
import { ArrowLeft } from 'lucide-vue-next';

const props = defineProps<{ package: any }>();
const form = useForm({ package_name: props.package.package_name, is_trial: Boolean(props.package.is_trial), is_official: Boolean(props.package.is_official), is_addon: Boolean(props.package.is_addon) });
function submit() { form.put(`/admin/packages/${props.package.id}`); }
</script>

<template>
    <Head title="Edit Package" />
    <AdminLayout>
        <template #title>Edit Package</template>
        <div class="mx-auto max-w-lg">
            <div class="mb-6"><Link href="/admin/packages" class="inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground"><ArrowLeft class="h-4 w-4" /> Back</Link></div>
            <Card class="p-6">
                <form @submit.prevent="submit" class="space-y-4">
                    <div class="space-y-2"><label class="text-sm font-medium">Name *</label><Input v-model="form.package_name" /></div>
                    <div class="flex items-center gap-4">
                        <label class="flex items-center gap-2 cursor-pointer"><input v-model="form.is_official" type="checkbox" class="rounded border-input" /><span class="text-sm font-medium">Official</span></label>
                        <label class="flex items-center gap-2 cursor-pointer"><input v-model="form.is_trial" type="checkbox" class="rounded border-input" /><span class="text-sm font-medium">Trial</span></label>
                        <label class="flex items-center gap-2 cursor-pointer"><input v-model="form.is_addon" type="checkbox" class="rounded border-input" /><span class="text-sm font-medium">Add-on</span></label>
                    </div>
                    <div class="flex justify-end gap-3 pt-4 border-t"><Link href="/admin/packages"><Button variant="outline" type="button">Cancel</Button></Link><Button type="submit" :disabled="form.processing">Save</Button></div>
                </form>
            </Card>
        </div>
    </AdminLayout>
</template>
