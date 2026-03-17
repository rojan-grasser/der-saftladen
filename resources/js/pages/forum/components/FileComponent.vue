<script setup lang="ts">
import { Topic } from '@/pages/forum/types';
import { Icon } from '@iconify/vue';

import {
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger,
} from '@/components/ui/tooltip';

defineProps<{ fileUpload: Topic['files'][0] }>();

const getIcon = (type: string) => {
    const map: Record<string, string> = {
        pdf: 'mdi:file-pdf-box',
        png: 'mdi:file-image',
        jpg: 'mdi:file-image',
        jpeg: 'mdi:file-image',
        gif: 'mdi:file-image',
        svg: 'mdi:file-image',
        mp4: 'mdi:file-video',
        mp3: 'mdi:file-music',
        zip: 'mdi:folder-zip',
        yml: 'mdi:file-code',
        yaml: 'mdi:file-code',
        sh: 'mdi:bash',
        js: 'mdi:language-javascript',
        ts: 'mdi:language-typescript',
        php: 'mdi:language-php',
        json: 'mdi:code-json',
        html: 'mdi:language-html5',
        css: 'mdi:language-css3',
        md: 'mdi:language-markdown',
    };

    return map[type] ?? 'mdi:file-outline';
};
</script>

<template>
    <TooltipProvider>
        <Tooltip>
            <TooltipTrigger as-child>
                <a
                    :href="fileUpload.downloadUrl"
                    download
                    class="group relative flex h-18 w-18 flex-col items-center justify-center rounded-xl border border-gray-200 bg-white p-3 shadow-sm transition-all hover:-translate-y-1 hover:shadow-lg focus:ring-2 focus:ring-yellow-400"
                >
                    <div class="flex w-full flex-col items-center justify-center overflow-hidden">
                        <Icon
                            :icon="getIcon(fileUpload.type)"
                            class="h-6 w-6 text-gray-400 transition-colors group-hover:text-yellow-500"
                        />
                    </div>
                </a>
            </TooltipTrigger>
            <TooltipContent>
                <p>{{ fileUpload.name }}</p>
            </TooltipContent>
        </Tooltip>
    </TooltipProvider>
</template>
