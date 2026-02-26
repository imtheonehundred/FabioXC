<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Button from '@/Components/ui/Button.vue';
import Input from '@/Components/ui/Input.vue';
import Card from '@/Components/ui/Card.vue';
import { ArrowLeft } from 'lucide-vue-next';

const props = defineProps<{ epg: any }>();
const form = useForm({ epg_name: props.epg.epg_name, epg_url: props.epg.epg_url });
function submit() { form.put(`/admin/epgs/${props.epg.id}`); }
</script>
<template>
    <Head title="Edit EPG Source" /><AdminLayout><template #title>Edit EPG Source</template>
    <div class="mx-auto max-w-lg">
        <div class="mb-6"><Link href="/admin/epgs" class="inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground"><ArrowLeft class="h-4 w-4" /> Back</Link></div>
        <Card class="p-6"><form @submit.prevent="submit" class="space-y-4">
            <div class="space-y-2"><label class="text-sm font-medium">EPG Name *</label><Input v-model="form.epg_name" /><p v-if="form.errors.epg_name" class="text-xs text-destructive">{{ form.errors.epg_name }}</p></div>
            <div class="space-y-2"><label class="text-sm font-medium">EPG URL *</label><Input v-model="form.epg_url" /><p v-if="form.errors.epg_url" class="text-xs text-destructive">{{ form.errors.epg_url }}</p></div>
            <div class="flex justify-end gap-3 pt-4 border-t"><Link href="/admin/epgs"><Button variant="outline" type="button">Cancel</Button></Link><Button type="submit" :disabled="form.processing">Save Changes</Button></div>
        </form></Card>
    </div></AdminLayout>
</template>
