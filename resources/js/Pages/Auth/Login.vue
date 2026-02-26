<script setup lang="ts">
import { useForm, Head } from '@inertiajs/vue3';
import Button from '@/Components/ui/Button.vue';
import Input from '@/Components/ui/Input.vue';
import Card from '@/Components/ui/Card.vue';
import { Eye, EyeOff } from 'lucide-vue-next';
import { ref } from 'vue';

const form = useForm({
    username: '',
    password: '',
    remember: false,
});

const showPassword = ref(false);

function submit() {
    form.post('/login', {
        onFinish: () => form.reset('password'),
    });
}
</script>

<template>
    <Head title="Login" />
    <div class="flex min-h-screen items-center justify-center bg-background p-4">
        <div class="w-full max-w-sm space-y-6">
            <div class="text-center space-y-2">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-primary text-primary-foreground font-bold text-xl">
                    XC
                </div>
                <h1 class="text-2xl font-bold">Welcome back</h1>
                <p class="text-sm text-muted-foreground">Sign in to your XC_VM panel</p>
            </div>

            <Card class="p-6">
                <form @submit.prevent="submit" class="space-y-4">
                    <div class="space-y-2">
                        <label class="text-sm font-medium" for="username">Username</label>
                        <Input
                            id="username"
                            v-model="form.username"
                            placeholder="Enter your username"
                            autocomplete="username"
                        />
                        <p v-if="form.errors.username" class="text-xs text-destructive">
                            {{ form.errors.username }}
                        </p>
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-medium" for="password">Password</label>
                        <div class="relative">
                            <Input
                                id="password"
                                v-model="form.password"
                                :type="showPassword ? 'text' : 'password'"
                                placeholder="Enter your password"
                                autocomplete="current-password"
                            />
                            <button
                                type="button"
                                class="absolute right-2.5 top-2.5 text-muted-foreground hover:text-foreground cursor-pointer"
                                @click="showPassword = !showPassword"
                            >
                                <EyeOff v-if="showPassword" class="h-4 w-4" />
                                <Eye v-else class="h-4 w-4" />
                            </button>
                        </div>
                        <p v-if="form.errors.password" class="text-xs text-destructive">
                            {{ form.errors.password }}
                        </p>
                    </div>

                    <div class="flex items-center gap-2">
                        <input
                            id="remember"
                            v-model="form.remember"
                            type="checkbox"
                            class="rounded border-input"
                        />
                        <label for="remember" class="text-sm text-muted-foreground cursor-pointer">Remember me</label>
                    </div>

                    <Button type="submit" class="w-full" :disabled="form.processing">
                        <span v-if="form.processing">Signing in...</span>
                        <span v-else>Sign in</span>
                    </Button>
                </form>
            </Card>

            <p class="text-center text-xs text-muted-foreground">
                XC_VM Panel &copy; {{ new Date().getFullYear() }}
            </p>
        </div>
    </div>
</template>
