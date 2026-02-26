<script setup lang="ts">
import { ref, watch, onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { X, CheckCircle, AlertCircle } from 'lucide-vue-next';

const page = usePage();
const toasts = ref<Array<{ id: number; type: 'success' | 'error'; message: string }>>([]);
let counter = 0;

function addToast(type: 'success' | 'error', message: string) {
    const id = ++counter;
    toasts.value.push({ id, type, message });
    setTimeout(() => removeToast(id), 5000);
}

function removeToast(id: number) {
    toasts.value = toasts.value.filter((t) => t.id !== id);
}

watch(
    () => page.props.flash,
    (flash: any) => {
        if (flash?.success) addToast('success', flash.success);
        if (flash?.error) addToast('error', flash.error);
    },
    { immediate: true },
);
</script>

<template>
    <Teleport to="body">
        <div class="fixed bottom-4 right-4 z-[100] flex flex-col gap-2">
            <TransitionGroup name="toast">
                <div
                    v-for="toast in toasts"
                    :key="toast.id"
                    :class="[
                        'flex items-center gap-3 rounded-lg border px-4 py-3 shadow-lg min-w-[320px] max-w-[420px]',
                        toast.type === 'success' ? 'bg-card border-success/30' : 'bg-card border-destructive/30',
                    ]"
                >
                    <CheckCircle v-if="toast.type === 'success'" class="h-5 w-5 shrink-0 text-success" />
                    <AlertCircle v-else class="h-5 w-5 shrink-0 text-destructive" />
                    <p class="text-sm flex-1">{{ toast.message }}</p>
                    <button class="shrink-0 text-muted-foreground hover:text-foreground cursor-pointer" @click="removeToast(toast.id)">
                        <X class="h-4 w-4" />
                    </button>
                </div>
            </TransitionGroup>
        </div>
    </Teleport>
</template>

<style scoped>
.toast-enter-active { transition: all 0.3s ease; }
.toast-leave-active { transition: all 0.2s ease; }
.toast-enter-from { opacity: 0; transform: translateX(100%); }
.toast-leave-to { opacity: 0; transform: translateX(100%); }
</style>
