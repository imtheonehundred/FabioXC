<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Button from '@/Components/ui/Button.vue';
import Input from '@/Components/ui/Input.vue';
import Select from '@/Components/ui/Select.vue';
import Card from '@/Components/ui/Card.vue';
import { ArrowLeft } from 'lucide-vue-next';

defineProps<{ lines: Array<{ id: number; username: string }> }>();
const form = useForm({
    mac_address: '',
    device_type: 'mag',
    line_id: '' as string | number,
    model: '',
    notes: '',
    admin_enabled: true,
});
function submit() { form.post('/admin/devices'); }
</script>
<template>
    <Head title="Add Device" /><AdminLayout><template #title>Add Device</template>
    <div class="mx-auto max-w-lg">
        <div class="mb-6"><Link href="/admin/devices" class="inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground"><ArrowLeft class="h-4 w-4" /> Back</Link></div>
        <Card class="p-6"><form @submit.prevent="submit" class="space-y-4">
            <div class="space-y-2"><label class="text-sm font-medium">MAC Address *</label><Input v-model="form.mac_address" placeholder="00:1A:79:XX:XX:XX" /><p v-if="form.errors.mac_address" class="text-xs text-destructive">{{ form.errors.mac_address }}</p></div>
            <div class="space-y-2"><label class="text-sm font-medium">Device Type *</label>
                <Select v-model="form.device_type">
                    <option value="mag">MAG</option>
                    <option value="enigma">Enigma</option>
                    <option value="stb">STB</option>
                </Select>
                <p v-if="form.errors.device_type" class="text-xs text-destructive">{{ form.errors.device_type }}</p>
            </div>
            <div class="space-y-2"><label class="text-sm font-medium">Line</label>
                <Select v-model="form.line_id">
                    <option value="">Select a line</option>
                    <option v-for="line in lines" :key="line.id" :value="line.id">{{ line.username }}</option>
                </Select>
            </div>
            <div class="space-y-2"><label class="text-sm font-medium">Model</label><Input v-model="form.model" placeholder="Device model" /></div>
            <div class="space-y-2"><label class="text-sm font-medium">Notes</label><Input v-model="form.notes" /></div>
            <div class="space-y-2"><label class="flex items-center gap-2 text-sm font-medium cursor-pointer"><input type="checkbox" v-model="form.admin_enabled" class="rounded border-input" /> Enabled</label></div>
            <div class="flex justify-end gap-3 pt-4 border-t"><Link href="/admin/devices"><Button variant="outline" type="button">Cancel</Button></Link><Button type="submit" :disabled="form.processing">Create Device</Button></div>
        </form></Card>
    </div></AdminLayout>
</template>
