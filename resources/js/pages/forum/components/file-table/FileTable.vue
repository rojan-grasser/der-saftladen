<script setup lang="ts">
import {
    LucideAlertCircle,
    LucideDownload,
    LucideFile,
    LucideFileArchive,
    LucideFileSpreadsheet,
    LucideFileText,
    LucideFileUp,
    LucideHeadphones,
    LucideImage,
    LucideTrash2,
    LucideVideo,
} from 'lucide-vue-next';

import { Button } from '@/components/ui/button';
import { ScrollArea } from '@/components/ui/scroll-area';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import {
    FileWithPreview,
    formatBytes,
    useFileUpload,
} from '@/composables/useFileUpload';
import { FileUpload } from '@/pages/forum/types';

const { initialFiles, onFilesAdded } = defineProps<{
    initialFiles: Array<FileUpload>;
    readonly?: boolean;
    onFilesAdded?: (files: FileWithPreview[]) => void;
}>();

const getFileIcon = (file: { file: File | { type: string; name: string } }) => {
    const fileType =
        file.file instanceof File ? file.file.type : file.file.type;
    const fileName =
        file.file instanceof File ? file.file.name : file.file.name;

    if (
        fileType.includes('pdf') ||
        fileName.endsWith('.pdf') ||
        fileType.includes('word') ||
        fileName.endsWith('.doc') ||
        fileName.endsWith('.docx')
    ) {
        return LucideFileText;
    } else if (
        fileType.includes('zip') ||
        fileType.includes('archive') ||
        fileName.endsWith('.zip') ||
        fileName.endsWith('.rar')
    ) {
        return LucideFileArchive;
    } else if (
        fileType.includes('excel') ||
        fileName.endsWith('.xls') ||
        fileName.endsWith('.xlsx')
    ) {
        return LucideFileSpreadsheet;
    } else if (fileType.includes('video/')) {
        return LucideVideo;
    } else if (fileType.includes('audio/')) {
        return LucideHeadphones;
    } else if (fileType.startsWith('image/')) {
        return LucideImage;
    }
    return LucideFile;
};

const maxSize = 512 * 1024 * 1024; // 10MB default

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

const handleDownload = (url: string | undefined) => {
    if (url) {
        window.open(url, '_blank');
    }
};
</script>

<template>
    <div class="flex flex-col gap-2">
        <!-- Drop area -->
        <div
            ref="dropzoneRef"
            v-if="!readonly"
            :data-files="files.length > 0 || undefined"
            class="flex min-h-56 flex-col items-center rounded-xl border-2 border-dashed border-input p-4 transition-colors not-data-[files]:justify-center has-[input:focus]:border-ring has-[input:focus]:ring-[3px] has-[input:focus]:ring-ring/50 data-[dragging=true]:bg-accent/50 data-[files]:hidden"
        >
            <input ref="inputRef" aria-label="Upload files" />
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

        <div v-if="files.length > 0" class="flex flex-col gap-2">
            <!-- Table header with actions -->
            <div class="flex items-center justify-between gap-2">
                <div class="flex gap-2" v-if="!readonly">
                    <Button
                        size="sm"
                        variant="outline"
                        @click="openFileDialog"
                        type="button"
                    >
                        <LucideFileUp
                            class="-ms-0.5 size-3.5 opacity-60"
                            aria-hidden="true"
                        />
                        Add files
                    </Button>
                    <Button
                        size="sm"
                        variant="outline"
                        @click="clearFiles"
                        type="button"
                    >
                        <LucideTrash2
                            class="-ms-0.5 size-3.5 opacity-60"
                            aria-hidden="true"
                        />
                        Remove all
                    </Button>
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-hidden rounded-md border bg-background">
                <!-- Sticky header outside scroll area -->
                <Table class="w-full table-fixed">
                    <colgroup>
                        <col class="w-1/4" />
                        <col class="w-[15%]" />
                        <col class="w-[20%]" />
                        <col class="w-[15%]" />
                    </colgroup>
                    <TableHeader class="bg-background text-xs">
                        <TableRow class="bg-muted/50">
                            <TableHead class="h-9 py-2 text-left">Name</TableHead>
                            <TableHead class="h-9 py-2 text-left">Typ</TableHead>
                            <TableHead class="h-9 py-2 text-left">Große</TableHead>
                            <TableHead class="h-9 py-2 text-right">Aktionen</TableHead>
                        </TableRow>
                    </TableHeader>
                </Table>
                <!-- Scrollable body -->
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
                                        <span class="truncate">{{
                                            file.file.name
                                        }}</span>
                                    </span>
                                </TableCell>
                                <TableCell
                                    class="py-2 text-left text-muted-foreground"
                                >
                                    {{
                                        (() => {
                                            const splitName =
                                                file.file.name.split('.');
                                            return splitName[
                                                splitName.length - 1
                                            ];
                                        })().toUpperCase()
                                    }}
                                </TableCell>
                                <TableCell
                                    class="py-2 text-left text-muted-foreground"
                                >
                                    {{ formatBytes(file.file.size) }}
                                </TableCell>
                                <TableCell
                                    class="py-2 text-right whitespace-nowrap"
                                >
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
