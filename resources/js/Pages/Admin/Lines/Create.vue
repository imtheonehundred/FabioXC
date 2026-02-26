<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Button from '@/Components/ui/Button.vue';
import Input from '@/Components/ui/Input.vue';
import Card from '@/Components/ui/Card.vue';
import { ArrowLeft, RefreshCw } from 'lucide-vue-next';

const props = defineProps<{
    packages: Array<{ id: number; package_name: string; is_trial: boolean }>;
    bouquets: Array<{ id: number; bouquet_name: string }>;
}>();

const form = useForm({
    username: '', password: '', exp_date: '', max_connections: 1,
    is_trial: false, admin_enabled: true, is_restreamer: false,
    bouquet: [] as number[], notes: '', package_ids: [] as number[],
});

function generateRandom() {
    const chars = 'abcdefghijklmnopqrstuvwxyz0123456789';
    const gen = (len: number) => Array.from({ length: len }, () => chars[Math.floor(Math.random() * chars.length)]).join('');
    form.username = gen(8);
    form.password = gen(10);
}

function submit() { form.post('/admin/lines'); }
</script>

<template>
    <Head title="Add Line" />
    <AdminLayout>
        <template #title>Add Line</template>
        <div class="mx-auto max-w-2xl">
            <div class="mb-6"><Link href="/admin/lines" class="inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground"><ArrowLeft class="h-4 w-4" /> Back</Link></div>
            <Card class="p-6">
                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="space-y-2">
                            <div class="flex items-center justify-between"><label class="text-sm font-medium">Username *</label><button type="button" class="text-xs text-primary hover:underline flex items-center gap-1 cursor-pointer" @click="generateRandom"><RefreshCw class="h-3 w-3" /> Generate</button></div>
                            <Input v-model="form.username" /><p v-if="form.errors.username" class="text-xs text-destructive">{{ form.errors.username }}</p>
                        </div>
                        <div class="space-y-2"><label class="text-sm font-medium">Password *</label><Input v-model="form.password" /><p v-if="form.errors.password" class="text-xs text-destructive">{{ form.errors.password }}</p></div>
                        <div class="space-y-2"><label class="text-sm font-medium">Max Connections</label><Input v-model="form.max_connections" type="number" /></div>
                        <div class="space-y-2"><label class="text-sm font-medium">Expiry Date</label><Input v-model="form.exp_date" type="date" /></div>
                        <div class="space-y-2 sm:col-span-2">
                            <label class="text-sm font-medium">Packages</label>
                            <div class="flex flex-wrap gap-2">
                                <label v-for="pkg in packages" :key="pkg.id" class="flex items-center gap-2 rounded-lg border px-3 py-2 cursor-pointer hover:bg-muted/50 transition-colors" :class="{ 'border-primary bg-primary/5': form.package_ids.includes(pkg.id) }">
                                    <input type="checkbox" :value="pkg.id" v-model="form.package_ids" class="rounded border-input" />
                                    <span class="text-sm">{{ pkg.package_name }}</span>
                                </label>
                            </div>
                        </div>
                        <div class="space-y-2 sm:col-span-2">
                            <label class="text-sm font-medium">Bouquets</label>
                            <div class="flex flex-wrap gap-2">
                                <label v-for="b in bouquets" :key="b.id" class="flex items-center gap-2 rounded-lg border px-3 py-2 cursor-pointer hover:bg-muted/50 transition-colors" :class="{ 'border-primary bg-primary/5': form.bouquet.includes(b.id) }">
                                    <input type="checkbox" :value="b.id" v-model="form.bouquet" class="rounded border-input" />
                                    <span class="text-sm">{{ b.bouquet_name }}</span>
                                </label>
                            </div>
                        </div>
                        <div class="space-y-2 sm:col-span-2"><label class="text-sm font-medium">Notes</label><textarea v-model="form.notes" rows="2" class="flex w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring" /></div>
                        <div class="flex items-center gap-4">
                            <label class="flex items-center gap-2 cursor-pointer"><input v-model="form.admin_enabled" type="checkbox" class="rounded border-input" /><span class="text-sm font-medium">Enabled</span></label>
                            <label class="flex items-center gap-2 cursor-pointer"><input v-model="form.is_trial" type="checkbox" class="rounded border-input" /><span class="text-sm font-medium">Trial</span></label>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 pt-4 border-t"><Link href="/admin/lines"><Button variant="outline" type="button">Cancel</Button></Link><Button type="submit" :disabled="form.processing">Create Line</Button></div>
                </form>
            </Card>
        </div>
    </AdminLayout>
</template>
