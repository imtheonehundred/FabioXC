<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Button from '@/Components/ui/Button.vue';
import Input from '@/Components/ui/Input.vue';
import Select from '@/Components/ui/Select.vue';
import Card from '@/Components/ui/Card.vue';
import { ArrowLeft } from 'lucide-vue-next';

const form = useForm({ code: '', type: 0 });
function submit() { form.post('/admin/access-codes'); }
</script>
<template>
    <Head title="Add Access Code" /><AdminLayout><template #title>Add Access Code</template>
    <div class="mx-auto max-w-lg">
        <div class="mb-6"><Link href="/admin/access-codes" class="inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground"><ArrowLeft class="h-4 w-4" /> Back</Link></div>
        <Card class="p-6"><form @submit.prevent="submit" class="space-y-4">
            <div class="space-y-2"><label class="text-sm font-medium">Code</label><Input v-model="form.code" placeholder="Leave blank to auto-generate" /><p v-if="form.errors.code" class="text-xs text-destructive">{{ form.errors.code }}</p></div>
            <div class="space-y-2"><label class="text-sm font-medium">Type *</label>
                <Select v-model="form.type">
                    <option :value="0">Admin</option>
                    <option :value="1">Reseller</option>
                    <option :value="2">Ministra</option>
                    <option :value="3">Admin API</option>
                    <option :value="4">Reseller API</option>
                    <option :value="5">Ministra New</option>
                    <option :value="6">Player</option>
                </Select>
                <p v-if="form.errors.type" class="text-xs text-destructive">{{ form.errors.type }}</p>
            </div>
            <div class="flex justify-end gap-3 pt-4 border-t"><Link href="/admin/access-codes"><Button variant="outline" type="button">Cancel</Button></Link><Button type="submit" :disabled="form.processing">Create Access Code</Button></div>
        </form></Card>
    </div></AdminLayout>
</template>
