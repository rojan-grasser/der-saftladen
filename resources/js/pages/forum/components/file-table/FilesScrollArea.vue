<script setup lang="ts">
import { LucideDownload, LucideTrash2 } from 'lucide-vue-next';

import { Button } from '@/components/ui/button';
import { ScrollArea } from '@/components/ui/scroll-area';
import { Table, TableBody, TableCell, TableRow } from '@/components/ui/table';
import { FileWithPreview, formatBytes } from '@/composables/useFileUpload';
import { getFileIcon } from '@/pages/forum/components/file-table/getFileIcon';

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
                                <component
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
                        {{ formatBytes(file.file.size) }}
                    </TableCell>
                    <TableCell class="py-2 text-right whitespace-nowrap">
                        <Button
                            size="icon"
                            type="button"
                            variant="ghost"
                            class="size-8 text-muted-foreground/80 hover:bg-transparent hover:text-foreground"
                            aria-label="Download file"
                            @click="handleDownload(file.preview)"
                        >
                            <LucideDownload class="size-4" />
                        </Button>
                        <Button
                            size="icon"
                            variant="ghost"
                            type="button"
                            class="size-8 text-muted-foreground/80 hover:bg-transparent hover:text-foreground"
                            aria-label="Remove file"
                            @click="removeFile(file.id)"
                            v-if="!readonly"
                        >
                            <LucideTrash2 class="size-4" />
                        </Button>
                    </TableCell>
                </TableRow>
            </TableBody>
        </Table>
    </ScrollArea>
</template>
