<script setup lang="ts">
import DOMPurify from 'dompurify';
import { marked } from 'marked';
import { computed, ref, toRef, watch } from 'vue';

import { Button } from '@/components/ui/button';

const props = defineProps<{ description: string }>();

const description = toRef(props, 'description');

const size = 1000;

const splitDesc = computed<string[]>(() => {
    const parts: string[] = [];
    for (let i = 0; i < description.value.length; i += size) {
        parts.push(description.value.slice(i, i + size));
    }
    return parts;
});

const currentShowAllIdx = ref<number>(1);

watch(splitDesc, () => {
    currentShowAllIdx.value = 1;
});

function renderMd(text: string): string {
    return DOMPurify.sanitize(marked.parse(text) as string);
}

const visibleHtml = computed<string>(() => {
    const text = Array.from({ length: currentShowAllIdx.value })
        .map((_, index) => splitDesc.value[index])
        .join('');
    return renderMd(text);
});
</script>

<template>
    <div>
        <div class="prose prose-sm dark:prose-invert max-w-none" v-html="visibleHtml" />

        <span v-if="splitDesc.length > 1 && currentShowAllIdx !== splitDesc.length" class="text-foreground/50">...</span>

        <Button
            v-if="splitDesc.length > 1 && currentShowAllIdx !== splitDesc.length"
            variant="link"
            class="p-0 text-foreground/70 italic"
            @click="() => (currentShowAllIdx += 1)"
        >
            Mehr anzeigen
        </Button>
    </div>
</template>
