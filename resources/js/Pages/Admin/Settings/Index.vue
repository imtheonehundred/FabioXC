<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Button from '@/Components/ui/Button.vue';
import Input from '@/Components/ui/Input.vue';
import Select from '@/Components/ui/Select.vue';
import Card from '@/Components/ui/Card.vue';
import { Save } from 'lucide-vue-next';

const props = defineProps<{ settings: Record<string, string> }>();

const form = useForm({
    settings: {
        server_name: props.settings.server_name || 'XC_VM Panel',
        timezone: props.settings.timezone || 'UTC',
        default_language: props.settings.default_language || 'en',
        max_connections_per_line: props.settings.max_connections_per_line || '1',
        trial_duration_hours: props.settings.trial_duration_hours || '24',
        auto_restart_streams: props.settings.auto_restart_streams || '1',
        epg_update_interval: props.settings.epg_update_interval || '24',
    },
});

function submit() {
    form.post('/admin/settings');
}

const settingGroups = [
    {
        title: 'General',
        fields: [
            { key: 'server_name', label: 'Panel Name', type: 'text', placeholder: 'XC_VM Panel' },
            { key: 'timezone', label: 'Timezone', type: 'select', options: ['UTC', 'America/New_York', 'America/Chicago', 'America/Los_Angeles', 'Europe/London', 'Europe/Paris', 'Europe/Berlin', 'Asia/Tokyo', 'Asia/Shanghai'] },
            { key: 'default_language', label: 'Default Language', type: 'select', options: ['en', 'es', 'fr', 'de', 'ru', 'pt', 'bg'] },
        ],
    },
    {
        title: 'Subscriptions',
        fields: [
            { key: 'max_connections_per_line', label: 'Default Max Connections', type: 'number' },
            { key: 'trial_duration_hours', label: 'Trial Duration (hours)', type: 'number' },
        ],
    },
    {
        title: 'Streaming',
        fields: [
            { key: 'auto_restart_streams', label: 'Auto-restart Streams', type: 'select', options: ['1', '0'], optionLabels: ['Enabled', 'Disabled'] },
            { key: 'epg_update_interval', label: 'EPG Update Interval (hours)', type: 'number' },
        ],
    },
];
</script>

<template>
    <Head title="Settings" />
    <AdminLayout>
        <template #title>Settings</template>

        <div class="mx-auto max-w-2xl">
            <form @submit.prevent="submit" class="space-y-6">
                <Card v-for="group in settingGroups" :key="group.title" class="p-6">
                    <h3 class="mb-4 text-sm font-semibold text-muted-foreground uppercase tracking-wider">{{ group.title }}</h3>
                    <div class="space-y-4">
                        <div v-for="field in group.fields" :key="field.key" class="grid grid-cols-1 gap-2 sm:grid-cols-3 sm:items-center">
                            <label class="text-sm font-medium">{{ field.label }}</label>
                            <div class="sm:col-span-2">
                                <Input
                                    v-if="field.type === 'text' || field.type === 'number'"
                                    v-model="(form.settings as any)[field.key]"
                                    :type="field.type"
                                    :placeholder="field.placeholder"
                                />
                                <Select
                                    v-else-if="field.type === 'select'"
                                    v-model="(form.settings as any)[field.key]"
                                >
                                    <option
                                        v-for="(opt, i) in field.options"
                                        :key="opt"
                                        :value="opt"
                                    >
                                        {{ (field as any).optionLabels ? (field as any).optionLabels[i] : opt }}
                                    </option>
                                </Select>
                            </div>
                        </div>
                    </div>
                </Card>

                <div class="flex justify-end">
                    <Button type="submit" :disabled="form.processing">
                        <Save class="mr-1 h-4 w-4" />
                        {{ form.processing ? 'Saving...' : 'Save Settings' }}
                    </Button>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
