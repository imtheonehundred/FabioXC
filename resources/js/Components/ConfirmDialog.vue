<script setup lang="ts">
import Dialog from '@/Components/ui/Dialog.vue';
import Button from '@/Components/ui/Button.vue';

interface Props {
    open: boolean;
    title?: string;
    message?: string;
    confirmLabel?: string;
    confirmVariant?: 'default' | 'destructive';
}

withDefaults(defineProps<Props>(), {
    title: 'Are you sure?',
    message: 'This action cannot be undone.',
    confirmLabel: 'Confirm',
    confirmVariant: 'destructive',
});

const emit = defineEmits<{
    'update:open': [value: boolean];
    confirm: [];
}>();
</script>

<template>
    <Dialog :open="open" @update:open="emit('update:open', $event)">
        <div class="space-y-4">
            <div>
                <h3 class="text-lg font-semibold">{{ title }}</h3>
                <p class="mt-1 text-sm text-muted-foreground">{{ message }}</p>
            </div>
            <div class="flex justify-end gap-2">
                <Button variant="outline" @click="emit('update:open', false)">Cancel</Button>
                <Button :variant="confirmVariant" @click="emit('confirm')">{{ confirmLabel }}</Button>
            </div>
        </div>
    </Dialog>
</template>
