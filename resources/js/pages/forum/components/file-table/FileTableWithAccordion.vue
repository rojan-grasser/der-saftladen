<script setup lang="ts">
import {
    Accordion,
    AccordionContent,
    AccordionItem,
    AccordionTrigger,
} from '@/components/ui/accordion';
import FileTable from '@/pages/forum/components/file-table/FileTable.vue';
import { FileUpload } from '@/pages/forum/types';

defineProps<{
    initialFiles: Array<FileUpload>;
    readonly?: boolean;
}>();
</script>

<template>
    <Accordion type="single" collapsible class="rounded">
        <AccordionItem value="files">
            <AccordionTrigger class="cursor-pointer px-2">
                Dateien
            </AccordionTrigger>

            <AccordionContent>
                <FileTable :initial-files="initialFiles" :readonly="readonly" />
            </AccordionContent>
        </AccordionItem>
    </Accordion>
</template>
