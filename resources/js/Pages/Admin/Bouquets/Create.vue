<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Button from '@/Components/ui/Button.vue';
import Input from '@/Components/ui/Input.vue';
import Card from '@/Components/ui/Card.vue';
import { ArrowLeft } from 'lucide-vue-next';

defineProps<{ streams: any[]; movies: any[]; series: any[] }>();
const form = useForm({ bouquet_name: '', bouquet_channels: [] as number[], bouquet_movies: [] as number[], bouquet_series: [] as number[], bouquet_radios: [] as number[], bouquet_order: 0 });
function submit() { form.post('/admin/bouquets'); }
</script>
<template>
    <Head title="Add Bouquet" /><AdminLayout><template #title>Add Bouquet</template>
    <div class="mx-auto max-w-2xl">
        <div class="mb-6"><Link href="/admin/bouquets" class="inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground"><ArrowLeft class="h-4 w-4" /> Back</Link></div>
        <Card class="p-6"><form @submit.prevent="submit" class="space-y-4">
            <div class="space-y-2"><label class="text-sm font-medium">Bouquet Name *</label><Input v-model="form.bouquet_name" /><p v-if="form.errors.bouquet_name" class="text-xs text-destructive">{{ form.errors.bouquet_name }}</p></div>
            <div class="space-y-2"><label class="text-sm font-medium">Order</label><Input v-model="form.bouquet_order" type="number" /></div>
            <div class="space-y-2"><label class="text-sm font-medium">Channels</label><div class="max-h-48 overflow-y-auto rounded border p-2 space-y-1"><label v-for="s in streams.filter(s => s.type === 'live')" :key="s.id" class="flex items-center gap-2 text-sm cursor-pointer"><input type="checkbox" :value="s.id" v-model="form.bouquet_channels" class="rounded border-input" />{{ s.stream_display_name }}</label></div></div>
            <div class="space-y-2"><label class="text-sm font-medium">Movies</label><div class="max-h-48 overflow-y-auto rounded border p-2 space-y-1"><label v-for="m in movies" :key="m.id" class="flex items-center gap-2 text-sm cursor-pointer"><input type="checkbox" :value="m.id" v-model="form.bouquet_movies" class="rounded border-input" />{{ m.stream_display_name }}</label></div></div>
            <div class="space-y-2"><label class="text-sm font-medium">Series</label><div class="max-h-48 overflow-y-auto rounded border p-2 space-y-1"><label v-for="s in series" :key="s.id" class="flex items-center gap-2 text-sm cursor-pointer"><input type="checkbox" :value="s.id" v-model="form.bouquet_series" class="rounded border-input" />{{ s.title }}</label></div></div>
            <div class="flex justify-end gap-3 pt-4 border-t"><Link href="/admin/bouquets"><Button variant="outline" type="button">Cancel</Button></Link><Button type="submit" :disabled="form.processing">Create Bouquet</Button></div>
        </form></Card>
    </div></AdminLayout>
</template>
