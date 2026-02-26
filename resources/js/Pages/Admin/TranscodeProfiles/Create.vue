<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Button from '@/Components/ui/Button.vue';
import Input from '@/Components/ui/Input.vue';
import Select from '@/Components/ui/Select.vue';
import Card from '@/Components/ui/Card.vue';
import { ArrowLeft } from 'lucide-vue-next';

const form = useForm({
    name: '',
    video_codec: 'copy',
    audio_codec: 'copy',
    video_bitrate: '',
    audio_bitrate: '',
    resolution: '',
    fps: '',
    preset: 'medium',
});
function submit() { form.post('/admin/transcode-profiles'); }
</script>
<template>
    <Head title="Add Transcode Profile" /><AdminLayout><template #title>Add Transcode Profile</template>
    <div class="mx-auto max-w-lg">
        <div class="mb-6"><Link href="/admin/transcode-profiles" class="inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground"><ArrowLeft class="h-4 w-4" /> Back</Link></div>
        <Card class="p-6"><form @submit.prevent="submit" class="space-y-4">
            <div class="space-y-2"><label class="text-sm font-medium">Profile Name *</label><Input v-model="form.name" /><p v-if="form.errors.name" class="text-xs text-destructive">{{ form.errors.name }}</p></div>
            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-2"><label class="text-sm font-medium">Video Codec</label>
                    <Select v-model="form.video_codec">
                        <option value="copy">copy</option>
                        <option value="libx264">libx264</option>
                        <option value="libx265">libx265</option>
                    </Select>
                </div>
                <div class="space-y-2"><label class="text-sm font-medium">Audio Codec</label>
                    <Select v-model="form.audio_codec">
                        <option value="copy">copy</option>
                        <option value="aac">aac</option>
                        <option value="mp3">mp3</option>
                    </Select>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-2"><label class="text-sm font-medium">Video Bitrate (kbps)</label><Input v-model="form.video_bitrate" type="number" placeholder="e.g. 4000" /></div>
                <div class="space-y-2"><label class="text-sm font-medium">Audio Bitrate (kbps)</label><Input v-model="form.audio_bitrate" type="number" placeholder="e.g. 128" /></div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-2"><label class="text-sm font-medium">Resolution</label><Input v-model="form.resolution" placeholder="e.g. 1920x1080" /></div>
                <div class="space-y-2"><label class="text-sm font-medium">FPS</label><Input v-model="form.fps" type="number" placeholder="e.g. 30" /></div>
            </div>
            <div class="space-y-2"><label class="text-sm font-medium">Preset</label>
                <Select v-model="form.preset">
                    <option value="ultrafast">ultrafast</option>
                    <option value="veryfast">veryfast</option>
                    <option value="fast">fast</option>
                    <option value="medium">medium</option>
                </Select>
            </div>
            <div class="flex justify-end gap-3 pt-4 border-t"><Link href="/admin/transcode-profiles"><Button variant="outline" type="button">Cancel</Button></Link><Button type="submit" :disabled="form.processing">Create Profile</Button></div>
        </form></Card>
    </div></AdminLayout>
</template>
