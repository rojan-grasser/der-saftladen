<script setup lang="ts">
import { ref, computed, nextTick } from 'vue';
import {
    Bold,
    Code2,
    Heading2,
    Italic,
    List,
    ListOrdered,
    Quote,
    Strikethrough,
} from 'lucide-vue-next';

import MarkdownContent from './MarkdownContent.vue';

const props = defineProps<{
    modelValue: string;
    placeholder?: string;
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: string): void;
}>();

const mode = ref<'write' | 'preview'>('write');
const textareaRef = ref<HTMLTextAreaElement | null>(null);
const preRef = ref<HTMLPreElement | null>(null);

// ── Syntax highlighting ──────────────────────────────────────────────────────

function escapeHtml(text: string): string {
    return text
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;');
}

const highlightedHtml = computed(() => {
    const raw = props.modelValue || '';
    const text = escapeHtml(raw);

    const lines = text.split('\n').map((line) => {
        line = line.replace(/^(#{1,6} )/, '<s>$1</s>');
        line = line.replace(/^(&gt; )/, '<s>$1</s>');
        line = line.replace(/^([-*+] )/, '<s>$1</s>');
        line = line.replace(/^(\d+\. )/, '<s>$1</s>');
        line = line.replace(/^(-{3,})$/, '<s>$1</s>');
        return line;
    });

    let result = lines.join('\n');

    // Bold **...**  (before italic to avoid partial matches)
    result = result.replace(/(\*\*)(.+?)(\*\*)/gs, '<s>$1</s>$2<s>$3</s>');
    // Italic *...* (not **)
    result = result.replace(/(?<!\*)\*(?!\*)(.+?)(?<!\*)\*(?!\*)/gs, '<s>*</s>$1<s>*</s>');
    // Strikethrough ~~...~~
    result = result.replace(/(~~)(.+?)(~~)/gs, '<s>$1</s>$2<s>$3</s>');
    // Inline code `...`
    result = result.replace(/(`(?!`))(.+?)(`(?!`))/gs, '<s>$1</s>$2<s>$3</s>');

    // Trailing newline prevents last-line scroll glitch
    return result + '\n';
});

// ── Scroll sync ──────────────────────────────────────────────────────────────

function syncScroll() {
    if (textareaRef.value && preRef.value) {
        preRef.value.scrollTop = textareaRef.value.scrollTop;
        preRef.value.scrollLeft = textareaRef.value.scrollLeft;
    }
}

// ── Format helpers ───────────────────────────────────────────────────────────

function applyInlineFormat(before: string, after: string = before) {
    const ta = textareaRef.value;
    if (!ta) return;
    const { selectionStart: s, selectionEnd: e, value } = ta;
    const selected = value.substring(s, e);
    const newValue = value.substring(0, s) + before + selected + after + value.substring(e);
    emit('update:modelValue', newValue);
    nextTick(() => {
        ta.focus();
        ta.selectionStart = s + before.length;
        ta.selectionEnd = e + before.length;
    });
}

function applyLineFormat(prefix: string) {
    const ta = textareaRef.value;
    if (!ta) return;
    const { selectionStart: s, value } = ta;
    const lineStart = value.lastIndexOf('\n', s - 1) + 1;
    const lineEnd = value.indexOf('\n', s);
    const lineText = value.substring(lineStart, lineEnd === -1 ? value.length : lineEnd);
    const hasPrefix = lineText.startsWith(prefix);
    const newLine = hasPrefix ? lineText.substring(prefix.length) : prefix + lineText;
    const newValue = value.substring(0, lineStart) + newLine + value.substring(lineStart + lineText.length);
    const offset = hasPrefix ? -prefix.length : prefix.length;
    emit('update:modelValue', newValue);
    nextTick(() => {
        ta.focus();
        const pos = Math.max(lineStart, s + offset);
        ta.selectionStart = pos;
        ta.selectionEnd = pos;
    });
}

function handleKeydown(e: KeyboardEvent) {
    if (e.ctrlKey || e.metaKey) {
        if (e.key === 'b') { e.preventDefault(); applyInlineFormat('**'); }
        if (e.key === 'i') { e.preventDefault(); applyInlineFormat('*'); }
    }
    // Tab → insert 2 spaces
    if (e.key === 'Tab') {
        e.preventDefault();
        applyInlineFormat('  ', '');
    }
}

// ── Toolbar definition ───────────────────────────────────────────────────────

const inlineTools = [
    { icon: Bold,          title: 'Fett (Strg+B)',        action: () => applyInlineFormat('**') },
    { icon: Italic,        title: 'Kursiv (Strg+I)',       action: () => applyInlineFormat('*') },
    { icon: Strikethrough, title: 'Durchgestrichen',       action: () => applyInlineFormat('~~') },
    { icon: Code2,         title: 'Inline-Code',           action: () => applyInlineFormat('`') },
];

const blockTools = [
    { icon: Heading2,     title: 'Überschrift',           action: () => applyLineFormat('## ') },
    { icon: List,         title: 'Aufzählung',            action: () => applyLineFormat('- ') },
    { icon: ListOrdered,  title: 'Nummerierte Liste',     action: () => applyLineFormat('1. ') },
    { icon: Quote,        title: 'Zitat',                 action: () => applyLineFormat('> ') },
];
</script>

<template>
    <div
        class="overflow-hidden rounded-md border border-input bg-background focus-within:outline-none focus-within:ring-1 focus-within:ring-ring"
    >
        <!-- ── Toolbar ───────────────────────────────────────────── -->
        <div class="flex items-center gap-0.5 border-b border-border bg-muted/40 px-2 py-1">
            <!-- Write / Preview toggle -->
            <button
                type="button"
                class="rounded px-2.5 py-1 text-xs font-medium transition-colors"
                :class="
                    mode === 'write'
                        ? 'bg-background text-foreground shadow-sm'
                        : 'text-muted-foreground hover:text-foreground'
                "
                @click="mode = 'write'"
            >
                Schreiben
            </button>
            <button
                type="button"
                class="rounded px-2.5 py-1 text-xs font-medium transition-colors"
                :class="
                    mode === 'preview'
                        ? 'bg-background text-foreground shadow-sm'
                        : 'text-muted-foreground hover:text-foreground'
                "
                @click="mode = 'preview'"
            >
                Vorschau
            </button>

            <div class="mx-1.5 h-4 w-px bg-border" />

            <!-- Format buttons (write mode only) -->
            <template v-if="mode === 'write'">
                <button
                    v-for="tool in inlineTools"
                    :key="tool.title"
                    type="button"
                    :title="tool.title"
                    class="rounded p-1.5 text-muted-foreground transition-colors hover:bg-accent hover:text-accent-foreground"
                    @click="tool.action"
                >
                    <component :is="tool.icon" class="h-3.5 w-3.5" />
                </button>

                <div class="mx-1 h-4 w-px bg-border" />

                <button
                    v-for="tool in blockTools"
                    :key="tool.title"
                    type="button"
                    :title="tool.title"
                    class="rounded p-1.5 text-muted-foreground transition-colors hover:bg-accent hover:text-accent-foreground"
                    @click="tool.action"
                >
                    <component :is="tool.icon" class="h-3.5 w-3.5" />
                </button>
            </template>
        </div>

        <!-- ── Write mode: overlay + textarea ───────────────────── -->
        <div v-if="mode === 'write'" class="relative min-h-[100px]">
            <!--
                Syntax-highlight layer (behind textarea).
                Identical font / padding / sizing to the textarea.
                <s> tags are repurposed for syntax markers and styled via CSS.
            -->
            <pre
                ref="preRef"
                aria-hidden="true"
                class="pointer-events-none absolute inset-0 overflow-hidden whitespace-pre-wrap break-words px-3 py-2 text-sm leading-relaxed"
                v-html="highlightedHtml"
            />

            <!-- Transparent textarea on top; only the caret is visible -->
            <textarea
                ref="textareaRef"
                :value="modelValue"
                :placeholder="placeholder"
                class="relative min-h-[100px] w-full resize-none bg-transparent px-3 py-2 text-sm leading-relaxed text-transparent outline-none placeholder:text-muted-foreground"
                style="caret-color: hsl(var(--foreground))"
                @input="emit('update:modelValue', ($event.target as HTMLTextAreaElement).value)"
                @scroll="syncScroll"
                @keydown="handleKeydown"
            />
        </div>

        <!-- ── Preview mode ──────────────────────────────────────── -->
        <div v-else class="min-h-[100px] px-3 py-2">
            <MarkdownContent v-if="modelValue?.trim()" :content="modelValue" />
            <p v-else class="text-sm text-muted-foreground italic">Keine Beschreibung vorhanden.</p>
        </div>
    </div>
</template>

<style scoped>
/*
 * The highlight layer uses <s> (re-purposed) for syntax markers.
 * We give them a muted colour so the actual content stands out.
 */
pre :deep(s) {
    text-decoration: none;
    color: hsl(var(--muted-foreground) / 0.5);
}
</style>
