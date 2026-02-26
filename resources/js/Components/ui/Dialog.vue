<script setup lang="ts">
import { watch } from 'vue';

interface Props {
    open: boolean;
}

const props = defineProps<Props>();
const emit = defineEmits<{ 'update:open': [value: boolean] }>();

function close() {
    emit('update:open', false);
}

watch(() => props.open, (val) => {
    if (val) {
        document.body.style.overflow = 'hidden';
    } else {
        document.body.style.overflow = '';
    }
});
</script>

<template>
    <Teleport to="body">
        <Transition name="dialog">
            <div v-if="open" class="fixed inset-0 z-50 flex items-center justify-center">
                <div class="fixed inset-0 bg-black/80" @click="close" />
                <div class="relative z-50 w-full max-w-lg rounded-lg border bg-card p-6 shadow-lg">
                    <slot />
                    <button
                        class="absolute right-4 top-4 rounded-sm opacity-70 transition-opacity hover:opacity-100 cursor-pointer"
                        @click="close"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                    </button>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
.dialog-enter-active, .dialog-leave-active { transition: opacity 0.15s ease; }
.dialog-enter-from, .dialog-leave-to { opacity: 0; }
</style>
