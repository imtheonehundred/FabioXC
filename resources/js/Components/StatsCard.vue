<script setup lang="ts">
import Card from '@/Components/ui/Card.vue';
import { cn } from '@/lib/utils';

interface Props {
    title: string;
    value: string | number;
    description?: string;
    trend?: 'up' | 'down' | 'neutral';
    trendValue?: string;
    icon?: any;
    class?: string;
}

defineProps<Props>();
</script>

<template>
    <Card :class="cn('p-6', $props.class)">
        <div class="flex items-center justify-between">
            <div class="space-y-1">
                <p class="text-sm font-medium text-muted-foreground">{{ title }}</p>
                <p class="text-2xl font-bold tracking-tight">{{ value }}</p>
                <p v-if="description" class="text-xs text-muted-foreground">{{ description }}</p>
                <div v-if="trendValue" class="flex items-center gap-1 text-xs">
                    <span
                        :class="{
                            'text-success': trend === 'up',
                            'text-destructive': trend === 'down',
                            'text-muted-foreground': trend === 'neutral',
                        }"
                    >
                        <span v-if="trend === 'up'">↑</span>
                        <span v-else-if="trend === 'down'">↓</span>
                        {{ trendValue }}
                    </span>
                </div>
            </div>
            <div v-if="icon" class="rounded-lg bg-primary/10 p-3 text-primary">
                <component :is="icon" class="h-5 w-5" />
            </div>
        </div>
    </Card>
</template>
