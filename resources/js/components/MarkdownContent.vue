<script setup lang="ts">
import { marked } from 'marked';
import { computed } from 'vue';

const props = defineProps<{
    content: string;
    class?: string;
}>();

// Configure marked for safe rendering
marked.setOptions({
    breaks: true,
    gfm: true,
});

const renderedContent = computed(() => {
    if (!props.content) return '';
    return marked.parse(props.content) as string;
});
</script>

<template>
    <div
        class="prose prose-sm dark:prose-invert max-w-none"
        :class="props.class"
        v-html="renderedContent"
    />
</template>

<style>
@reference "../../css/app.css";

.prose h1 {
    @apply text-xl font-bold mb-2 mt-4;
}
.prose h2 {
    @apply text-lg font-semibold mb-2 mt-3;
}
.prose h3 {
    @apply text-base font-semibold mb-1 mt-2;
}
.prose p {
    @apply mb-2;
}
.prose ul, .prose ol {
    @apply pl-5 mb-2;
}
.prose ul {
    @apply list-disc;
}
.prose ol {
    @apply list-decimal;
}
.prose li {
    @apply mb-1;
}
.prose code {
    @apply bg-muted px-1 py-0.5 rounded text-sm;
}
.prose pre {
    @apply bg-muted p-3 rounded-lg overflow-x-auto mb-2;
}
.prose pre code {
    @apply bg-transparent p-0;
}
.prose blockquote {
    @apply border-l-4 border-muted-foreground/30 pl-4 italic text-muted-foreground;
}
.prose a {
    @apply text-primary underline;
}
.prose strong {
    @apply font-semibold;
}
.prose hr {
    @apply my-4 border-border;
}
</style>
