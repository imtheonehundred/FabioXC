<script setup lang="ts">
import { cn } from '@/lib/utils';

interface Props {
    modelValue?: string | number;
    placeholder?: string;
    disabled?: boolean;
    class?: string;
}

defineProps<Props>();
const emit = defineEmits<{ 'update:modelValue': [value: string] }>();
</script>

<template>
    <select
        :value="modelValue"
        :disabled="disabled"
        :class="cn(
            'flex h-9 w-full items-center justify-between rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm',
            'ring-offset-background placeholder:text-muted-foreground',
            'focus:outline-none focus:ring-1 focus:ring-ring',
            'disabled:cursor-not-allowed disabled:opacity-50',
            $props.class,
        )"
        @change="emit('update:modelValue', ($event.target as HTMLSelectElement).value)"
    >
        <option v-if="placeholder" value="" disabled>{{ placeholder }}</option>
        <slot />
    </select>
</template>
