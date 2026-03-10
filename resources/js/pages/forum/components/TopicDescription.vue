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

<style scoped>
.prose :deep(h1),
.prose :deep(h2),
.prose :deep(h3),
.prose :deep(h4),
.prose :deep(h5),
.prose :deep(h6) {
    font-weight: 600;
    margin-top: 1em;
    margin-bottom: 0.5em;
}
.prose :deep(h1) { font-size: 1.5em; }
.prose :deep(h2) { font-size: 1.25em; }
.prose :deep(h3) { font-size: 1.1em; }

.prose :deep(p) {
    margin-top: 0.5em;
    margin-bottom: 0.5em;
}

.prose :deep(ul),
.prose :deep(ol) {
    padding-left: 1.5em;
    margin-top: 0.5em;
    margin-bottom: 0.5em;
}
.prose :deep(ul) { list-style-type: disc; }
.prose :deep(ol) { list-style-type: decimal; }

.prose :deep(li) {
    margin-top: 0.25em;
    margin-bottom: 0.25em;
}

.prose :deep(a) {
    color: hsl(var(--primary));
    text-decoration: underline;
}

.prose :deep(code) {
    background: hsl(var(--muted));
    padding: 0.1em 0.3em;
    border-radius: 0.25em;
    font-size: 0.9em;
}

.prose :deep(pre) {
    background: hsl(var(--muted));
    padding: 0.75em 1em;
    border-radius: 0.375em;
    overflow-x: auto;
    margin: 0.75em 0;
}

.prose :deep(pre code) {
    background: none;
    padding: 0;
}

.prose :deep(blockquote) {
    border-left: 3px solid hsl(var(--border));
    padding-left: 1em;
    color: hsl(var(--muted-foreground));
    margin: 0.5em 0;
}

.prose :deep(hr) {
    border-color: hsl(var(--border));
    margin: 1em 0;
}

.prose :deep(strong) { font-weight: 700; }
.prose :deep(em) { font-style: italic; }
</style>
