<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import ResellerLayout from '@/Layouts/ResellerLayout.vue';
import Button from '@/Components/ui/Button.vue';
import Input from '@/Components/ui/Input.vue';
import Select from '@/Components/ui/Select.vue';
import Card from '@/Components/ui/Card.vue';
import { ArrowLeft } from 'lucide-vue-next';

const form = useForm({
    subject: '',
    message: '',
    priority: 'normal',
});

function submit() { form.post('/reseller/tickets'); }
</script>

<template>
    <Head title="New Ticket" />
    <ResellerLayout>
        <template #title>New Ticket</template>
        <div class="mx-auto max-w-2xl">
            <div class="mb-6"><Link href="/reseller/tickets" class="inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground"><ArrowLeft class="h-4 w-4" /> Back</Link></div>
            <Card class="p-6">
                <form @submit.prevent="submit" class="space-y-6">
                    <div class="space-y-4">
                        <div class="space-y-2">
                            <label class="text-sm font-medium">Subject *</label>
                            <Input v-model="form.subject" placeholder="Brief description of the issue" />
                            <p v-if="form.errors.subject" class="text-xs text-destructive">{{ form.errors.subject }}</p>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium">Priority</label>
                            <Select v-model="form.priority" class="w-full">
                                <option value="low">Low</option>
                                <option value="normal">Normal</option>
                                <option value="high">High</option>
                            </Select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium">Message *</label>
                            <textarea
                                v-model="form.message"
                                rows="6"
                                placeholder="Describe your issue in detail..."
                                class="flex w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                            />
                            <p v-if="form.errors.message" class="text-xs text-destructive">{{ form.errors.message }}</p>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 pt-4 border-t">
                        <Link href="/reseller/tickets"><Button variant="outline" type="button">Cancel</Button></Link>
                        <Button type="submit" :disabled="form.processing">Submit Ticket</Button>
                    </div>
                </form>
            </Card>
        </div>
    </ResellerLayout>
</template>
