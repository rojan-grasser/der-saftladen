<script setup lang="ts">
import { LucideDownload, LucideLoader, LucideTrash2 } from 'lucide-vue-next';

import { Button } from '@/components/ui/button';
import { Progress } from '@/components/ui/progress';
import { ScrollArea } from '@/components/ui/scroll-area';
import { Table, TableBody, TableCell, TableRow } from '@/components/ui/table';
import { FileWithPreview, formatBytes } from '@/composables/useFileUpload';
import { getFileIcon } from '@/pages/forum/components/file-table/getFileIcon';
import { uploadProgress } from '@/pages/forum/components/file-table/onFileChange';

defineProps<{
    files: Array<FileWithPreview>;
    removeFile: (id: string | undefined) => void;
    readonly: boolean;
}>();

const handleDownload = (url: string | undefined) => {
    if (url) {
        window.open(url, '_blank');
    }
};
</script>

<template>
    <ScrollArea
        class="max-h-[30vh] [&>[data-slot=scroll-area-viewport]]:max-h-[30vh]"
        hide-scroll-bar
    >
        <Table class="w-full table-fixed">
            <colgroup>
                <col class="w-1/4" />
                <col class="w-[15%]" />
                <col class="w-[20%]" />
                <col class="w-[15%]" />
            </colgroup>
            <TableBody class="text-[13px]">
                <TableRow v-for="file in files" :key="file.id">
                    <TableCell class="max-w-48 py-2 font-medium">
                        <span class="flex items-center gap-2">
                            <span class="shrink-0">
                                <LucideLoader
                                    v-if="file.id in uploadProgress"
                                    class="size-4 animate-spin opacity-60"
                                />
                                <component
                                    v-else
                                    :is="getFileIcon(file)"
                                    class="size-4 opacity-60"
                                />
                            </span>
                            <span class="truncate">{{ file.file.name }}</span>
                        </span>
                    </TableCell>
                    <TableCell class="py-2 text-left text-muted-foreground">
                        {{
                            (() => {
                                const splitName = file.file.name.split('.');
                                return splitName[splitName.length - 1];
                            })().toUpperCase()
                        }}
                    </TableCell>
                    <TableCell class="py-2 text-left text-muted-foreground">
                        <template v-if="file.id in uploadProgress">
                            <div class="flex items-center gap-2">
                                <Progress
                                    :model-value="uploadProgress[file.id]"
                                    class="h-1.5"
                                />
                                <span class="text-xs tabular-nums">
                                    {{ uploadProgress[file.id] }}%
                                </span>
                            </div>
                        </template>
                        <template v-else>
                            {{ formatBytes(file.file.size) }}
                        </template>
                    </TableCell>
                    <TableCell class="py-2 text-right whitespace-nowrap">
                        <Button
                            size="icon"
                            type="button"
                            variant="ghost"
                            class="size-8 text-muted-foreground/80 hover:bg-transparent hover:text-foreground"
                            aria-label="Download file"
                            :disabled="file.id in uploadProgress"
                            @click="handleDownload(file.preview)"
                        >
                            <LucideDownload class="size-4" />
                        </Button>
                        <Button
                            v-if="!readonly"
                            size="icon"
                            variant="ghost"
                            type="button"
                            class="size-8 text-muted-foreground/80 hover:bg-transparent hover:text-foreground"
                            aria-label="Remove file"
                            :disabled="file.id in uploadProgress"
                            @click="removeFile(file.id)"
                        >
                            <LucideTrash2 class="size-4" />
                        </Button>
                    </TableCell>
                </TableRow>
            </TableBody>
        </Table>
    </ScrollArea>
</template>
