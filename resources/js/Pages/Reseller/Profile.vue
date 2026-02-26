<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import ResellerLayout from '@/Layouts/ResellerLayout.vue';
import Button from '@/Components/ui/Button.vue';
import Input from '@/Components/ui/Input.vue';
import Card from '@/Components/ui/Card.vue';

const props = defineProps<{
    user: { id: number; username: string; email: string };
}>();

const form = useForm({
    email: props.user.email || '',
    password: '',
    password_confirmation: '',
});

function submit() { form.put('/reseller/profile'); }
</script>

<template>
    <Head title="Profile" />
    <ResellerLayout>
        <template #title>Profile</template>
        <div class="mx-auto max-w-2xl">
            <Card class="p-6">
                <div class="mb-6">
                    <div class="flex items-center gap-4">
                        <div class="h-16 w-16 rounded-full bg-primary/20 flex items-center justify-center text-primary font-bold text-2xl">
                            {{ user.username?.charAt(0).toUpperCase() || 'R' }}
                        </div>
                        <div>
                            <h2 class="text-lg font-semibold">{{ user.username }}</h2>
                            <p class="text-sm text-muted-foreground">Reseller Account</p>
                        </div>
                    </div>
                </div>
                <form @submit.prevent="submit" class="space-y-6">
                    <div class="space-y-4">
                        <div class="space-y-2">
                            <label class="text-sm font-medium">Email</label>
                            <Input v-model="form.email" type="email" />
                            <p v-if="form.errors.email" class="text-xs text-destructive">{{ form.errors.email }}</p>
                        </div>
                        <div class="border-t pt-4">
                            <h3 class="text-sm font-semibold mb-4">Change Password</h3>
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div class="space-y-2">
                                    <label class="text-sm font-medium">New Password</label>
                                    <Input v-model="form.password" type="password" placeholder="Leave blank to keep current" />
                                    <p v-if="form.errors.password" class="text-xs text-destructive">{{ form.errors.password }}</p>
                                </div>
                                <div class="space-y-2">
                                    <label class="text-sm font-medium">Confirm Password</label>
                                    <Input v-model="form.password_confirmation" type="password" />
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-end pt-4 border-t">
                        <Button type="submit" :disabled="form.processing">Save Changes</Button>
                    </div>
                </form>
            </Card>
        </div>
    </ResellerLayout>
</template>
