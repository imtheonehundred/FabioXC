<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Button from '@/Components/ui/Button.vue';
import Input from '@/Components/ui/Input.vue';
import Select from '@/Components/ui/Select.vue';
import Card from '@/Components/ui/Card.vue';
import { ArrowLeft } from 'lucide-vue-next';

defineProps<{ parents: Array<{ id: number; category_name: string }> }>();
const form = useForm({ category_name: '', category_type: 'live', parent_id: '', cat_order: 0 });
function submit() { form.post('/admin/categories'); }
</script>

<template>
    <Head title="Add Category" />
    <AdminLayout>
        <template #title>Add Category</template>
        <div class="mx-auto max-w-lg">
            <div class="mb-6"><Link href="/admin/categories" class="inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground"><ArrowLeft class="h-4 w-4" /> Back</Link></div>
            <Card class="p-6">
                <form @submit.prevent="submit" class="space-y-4">
                    <div class="space-y-2"><label class="text-sm font-medium">Name *</label><Input v-model="form.category_name" /><p v-if="form.errors.category_name" class="text-xs text-destructive">{{ form.errors.category_name }}</p></div>
                    <div class="space-y-2"><label class="text-sm font-medium">Type</label><Select v-model="form.category_type"><option value="live">Live</option><option value="movie">Movie</option><option value="series">Series</option><option value="radio">Radio</option></Select></div>
                    <div class="space-y-2"><label class="text-sm font-medium">Parent</label><Select v-model="form.parent_id"><option value="">None</option><option v-for="p in parents" :key="p.id" :value="p.id">{{ p.category_name }}</option></Select></div>
                    <div class="space-y-2"><label class="text-sm font-medium">Order</label><Input v-model="form.cat_order" type="number" /></div>
                    <div class="flex justify-end gap-3 pt-4 border-t"><Link href="/admin/categories"><Button variant="outline" type="button">Cancel</Button></Link><Button type="submit" :disabled="form.processing">Create</Button></div>
                </form>
            </Card>
        </div>
    </AdminLayout>
</template>
