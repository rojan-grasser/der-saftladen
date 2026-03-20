<script setup lang="ts">
import { LucideTrash2 } from 'lucide-vue-next';
import { ref } from 'vue';

import { Button } from '@/components/ui/button';
import { FileWithPreview } from '@/composables/useFileUpload';
import { uploadProgress } from '@/pages/forum/components/file-table/onFileChange';

const { file, removeFile } = defineProps<{
    readonly: boolean;
    file: FileWithPreview;
    removeFile: (id: string | undefined) => void;
}>();

const clicked = ref(false);

const onClick = () => {
    if (!clicked.value) removeFile(file.id);

    clicked.value = true;
};
</script>

<template>
    <Button
        v-if="!readonly"
        size="icon"
        variant="ghost"
        type="button"
        class="size-8 text-muted-foreground/80 hover:bg-transparent hover:text-foreground"
        aria-label="Remove file"
        :disabled="file.id in uploadProgress || clicked"
        @click="onClick"
    >
        <LucideTrash2 class="size-4" />
    </Button>
</template>
