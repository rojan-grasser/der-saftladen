<script setup lang="ts">
import { LucideAlertCircle } from 'lucide-vue-next';

import { FileWithPreview, useFileUpload } from '@/composables/useFileUpload';
import ButtonHeader from '@/pages/forum/components/file-table/ButtonHeader.vue';
import FilesScrollArea from '@/pages/forum/components/file-table/FilesScrollArea.vue';
import FileTableHeader from '@/pages/forum/components/file-table/FileTableHeader.vue';
import UploadDropArea from '@/pages/forum/components/file-table/UploadDropArea.vue';
import { FileUpload } from '@/pages/forum/types';

const { initialFiles, onFilesAdded } = defineProps<{
    initialFiles: Array<FileUpload>;
    readonly?: boolean;
    onFilesAdded?: (files: FileWithPreview[]) => void;
}>();

const maxSize = 512 * 1024 * 1024; // 512 MB

const {
    files,
    errors,
    dropzoneRef,
    openFileDialog,
    removeFile,
    clearFiles,
    inputRef,
} = useFileUpload({
    multiple: true,
    maxSize,
    initialFiles,
    onFilesAdded,
});
</script>

<template>
    <div class="flex flex-col gap-2">
        <UploadDropArea
            :readonly="!!readonly"
            :files="files.length > 0"
            :max-size="maxSize"
            :open-file-dialog="openFileDialog"
            :dropzone-ref="dropzoneRef"
            :input-ref="inputRef"
        />

        <div v-if="files.length > 0" class="flex flex-col gap-2">
            <ButtonHeader
                :readonly="!!readonly"
                :clear-files="clearFiles"
                :open-file-dialog="openFileDialog"
            />

            <div class="overflow-hidden rounded-md border bg-background">
                <FileTableHeader />

                <FilesScrollArea
                    :files="files"
                    :readonly="!!readonly"
                    :remove-file="removeFile"
                />
            </div>
        </div>

        <div
            v-if="errors.length > 0"
            class="flex items-center gap-1 text-xs text-destructive"
            role="alert"
        >
            <LucideAlertCircle class="size-3 shrink-0" />
            <span>{{ errors[0] }}</span>
        </div>
    </div>
</template>
