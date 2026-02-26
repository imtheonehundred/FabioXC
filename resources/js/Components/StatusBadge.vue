<script setup lang="ts">
import Badge from '@/Components/ui/Badge.vue';
import { computed } from 'vue';

interface Props {
    status: number | string | boolean;
    type?: 'stream' | 'server' | 'line' | 'generic';
}

const props = withDefaults(defineProps<Props>(), {
    type: 'generic',
});

const config = computed(() => {
    if (props.type === 'stream') {
        return match(Number(props.status), {
            0: { label: 'Offline', variant: 'secondary' as const },
            1: { label: 'Online', variant: 'success' as const },
            2: { label: 'Starting', variant: 'warning' as const },
            3: { label: 'Error', variant: 'destructive' as const },
            4: { label: 'Stopping', variant: 'warning' as const },
        });
    }
    if (props.type === 'server') {
        return match(Number(props.status), {
            0: { label: 'Offline', variant: 'destructive' as const },
            1: { label: 'Online', variant: 'success' as const },
            2: { label: 'Maintenance', variant: 'warning' as const },
        });
    }
    if (props.type === 'line') {
        if (typeof props.status === 'string') {
            const map: Record<string, any> = {
                Online: { label: 'Online', variant: 'success' as const },
                Offline: { label: 'Offline', variant: 'secondary' as const },
                Expired: { label: 'Expired', variant: 'destructive' as const },
                Disabled: { label: 'Disabled', variant: 'warning' as const },
            };
            return map[props.status] || { label: props.status, variant: 'secondary' as const };
        }
    }
    if (typeof props.status === 'boolean') {
        return props.status
            ? { label: 'Active', variant: 'success' as const }
            : { label: 'Inactive', variant: 'secondary' as const };
    }
    return { label: String(props.status), variant: 'secondary' as const };
});

function match(val: number, map: Record<number, any>) {
    return map[val] || { label: 'Unknown', variant: 'secondary' as const };
}
</script>

<template>
    <Badge :variant="config.variant">
        <span class="flex items-center gap-1.5">
            <span class="h-1.5 w-1.5 rounded-full" :class="{
                'bg-success-foreground': config.variant === 'success',
                'bg-destructive-foreground': config.variant === 'destructive',
                'bg-warning-foreground': config.variant === 'warning',
                'bg-secondary-foreground': config.variant === 'secondary',
            }" />
            {{ config.label }}
        </span>
    </Badge>
</template>
