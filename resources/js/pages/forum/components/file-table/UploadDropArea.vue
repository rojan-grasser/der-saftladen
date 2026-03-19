<script setup lang="ts">
import { useTemplateRef } from 'vue';
import { LucideFile, LucideFileUp } from 'lucide-vue-next';

import { Button } from '@/components/ui/button';
import { formatBytes } from '@/composables/useFileUpload';

defineProps<{
    readonly: boolean;
    files: boolean;
    maxSize: number;
    openFileDialog: () => void;
}>();

const dropzoneRef = useTemplateRef<HTMLElement>('dropzone');
const inputRef = useTemplateRef<HTMLInputElement>('input');

defineExpose({ dropzoneRef, inputRef });
</script>

<template>
    <div
        ref="dropzone"
        v-if="!readonly"
        :data-files="files || undefined"
        class="flex min-h-56 flex-col items-center rounded-xl border-2 border-dashed border-input p-4 transition-colors not-data-[files]:justify-center has-[input:focus]:border-ring has-[input:focus]:ring-[3px] has-[input:focus]:ring-ring/50 data-[dragging=true]:bg-accent/50 data-[files]:hidden"
    >
        <input ref="input" aria-label="Upload files" />
        <div class="flex flex-col items-center justify-center text-center">
            <div
                class="mb-2 flex size-11 shrink-0 items-center justify-center rounded-full border bg-background"
                aria-hidden="true"
            >
                <LucideFile class="size-4 opacity-60" />
            </div>
            <p class="mb-1.5 text-sm font-medium">Dateien Hochladen</p>
            <p class="text-xs text-muted-foreground">
                Dateien bis {{ formatBytes(maxSize) }} hochladen
            </p>
            <Button
                size="sm"
                variant="outline"
                type="button"
                @click="openFileDialog"
                class="mt-4"
            >
                <LucideFileUp class="-ms-1 opacity-60" aria-hidden="true" />
                Dateien auswählen
            </Button>
        </div>
    </div>
</template>
