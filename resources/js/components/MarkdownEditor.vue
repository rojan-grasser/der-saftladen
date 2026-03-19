<script setup lang="ts">
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
import { nextTick, ref } from 'vue';

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

// ── Format helpers ───────────────────────────────────────────────────────────

function applyInlineFormat(before: string, after: string = before) {
    const ta = textareaRef.value;
    if (!ta) return;
    const { selectionStart: s, selectionEnd: e } = ta;
    const value = props.modelValue ?? '';
    const selected = value.slice(s, e);

    // Toggle: if already wrapped, unwrap
    if (selected.startsWith(before) && selected.endsWith(after)) {
        const unwrapped = selected.slice(before.length, selected.length - after.length);
        emit('update:modelValue', value.slice(0, s) + unwrapped + value.slice(e));
        nextTick(() => {
            ta.focus();
            ta.selectionStart = s;
            ta.selectionEnd = s + unwrapped.length;
        });
        return;
    }

    emit('update:modelValue', value.slice(0, s) + before + selected + after + value.slice(e));
    nextTick(() => {
        ta.focus();
        ta.selectionStart = selected ? s + before.length : s + before.length;
        ta.selectionEnd = selected ? e + before.length : s + before.length;
    });
}

function applyLineFormat(prefix: string) {
    const ta = textareaRef.value;
    if (!ta) return;
    const s = ta.selectionStart;
    const value = props.modelValue ?? '';
    const lineStart = value.lastIndexOf('\n', s - 1) + 1;
    const lineEnd = value.indexOf('\n', s);
    const lineText = value.slice(lineStart, lineEnd === -1 ? undefined : lineEnd);
    const hasPrefix = lineText.startsWith(prefix);
    const newLine = hasPrefix ? lineText.slice(prefix.length) : prefix + lineText;
    const offset = hasPrefix ? -prefix.length : prefix.length;
    emit('update:modelValue', value.slice(0, lineStart) + newLine + value.slice(lineStart + lineText.length));
    nextTick(() => {
        ta.focus();
        const pos = Math.max(lineStart, s + offset);
        ta.selectionStart = pos;
        ta.selectionEnd = pos;
    });
}

function handleKeydown(e: KeyboardEvent) {
    const mod = e.ctrlKey || e.metaKey;
    if (mod && e.key === 'b') { e.preventDefault(); applyInlineFormat('**'); }
    if (mod && e.key === 'i') { e.preventDefault(); applyInlineFormat('*'); }
    if (e.key === 'Tab') { e.preventDefault(); applyInlineFormat('  ', ''); }
}

// ── Toolbar ──────────────────────────────────────────────────────────────────

const inlineTools = [
    { icon: Bold,          title: 'Fett (Strg+B)',      action: () => applyInlineFormat('**') },
    { icon: Italic,        title: 'Kursiv (Strg+I)',    action: () => applyInlineFormat('*') },
    { icon: Strikethrough, title: 'Durchgestrichen',    action: () => applyInlineFormat('~~') },
    { icon: Code2,         title: 'Inline-Code',        action: () => applyInlineFormat('`') },
];

const blockTools = [
    { icon: Heading2,     title: 'Überschrift',         action: () => applyLineFormat('## ') },
    { icon: List,         title: 'Aufzählung',          action: () => applyLineFormat('- ') },
    { icon: ListOrdered,  title: 'Nummerierte Liste',   action: () => applyLineFormat('1. ') },
    { icon: Quote,        title: 'Zitat',               action: () => applyLineFormat('> ') },
];
</script>

<template>
    <div class="md-editor overflow-hidden rounded-md border border-input bg-background focus-within:ring-1 focus-within:ring-ring">

        <div class="flex items-center gap-1 border-b border-border bg-muted/30 px-2 py-1.5">

            <!-- Write / Preview tabs -->
            <div class="flex rounded-md bg-muted p-0.5">
                <button
                    type="button"
                    class="rounded px-2.5 py-0.5 text-xs font-medium transition-all"
                    :class="mode === 'write'
                        ? 'bg-background text-foreground shadow-sm'
                        : 'text-muted-foreground hover:text-foreground'"
                    @click="mode = 'write'"
                >
                    Schreiben
                </button>
                <button
                    type="button"
                    class="rounded px-2.5 py-0.5 text-xs font-medium transition-all"
                    :class="mode === 'preview'
                        ? 'bg-background text-foreground shadow-sm'
                        : 'text-muted-foreground hover:text-foreground'"
                    @click="mode = 'preview'"
                >
                    Vorschau
                </button>
            </div>

            <!-- Divider + format buttons (write only) -->
            <template v-if="mode === 'write'">
                <div class="h-4 w-px bg-border" />

                <button
                    v-for="tool in inlineTools"
                    :key="tool.title"
                    type="button"
                    :title="tool.title"
                    class="rounded p-1.5 text-muted-foreground transition-colors hover:bg-accent hover:text-foreground"
                    @click="tool.action"
                >
                    <component :is="tool.icon" class="h-3.5 w-3.5" />
                </button>

                <div class="h-4 w-px bg-border" />

                <button
                    v-for="tool in blockTools"
                    :key="tool.title"
                    type="button"
                    :title="tool.title"
                    class="rounded p-1.5 text-muted-foreground transition-colors hover:bg-accent hover:text-foreground"
                    @click="tool.action"
                >
                    <component :is="tool.icon" class="h-3.5 w-3.5" />
                </button>
            </template>
        </div>

        <!-- ── Write mode ─────────────────────────────────────────── -->
        <textarea
            v-if="mode === 'write'"
            ref="textareaRef"
            :value="modelValue"
            :placeholder="placeholder"
            class="md-textarea w-full resize-none bg-transparent px-3 py-2.5 text-sm leading-relaxed outline-none placeholder:text-muted-foreground"
            @input="emit('update:modelValue', ($event.target as HTMLTextAreaElement).value)"
            @keydown="handleKeydown"
        />

        <!-- ── Preview mode ───────────────────────────────────────── -->
        <div v-else class="min-h-[120px] px-3 py-2.5">
            <MarkdownContent v-if="modelValue?.trim()" :content="modelValue" />
            <p v-else class="text-sm italic text-muted-foreground">Keine Beschreibung vorhanden.</p>
        </div>

    </div>
</template>

<style scoped>
.md-textarea {
    min-height: 120px;
    field-sizing: content;
}
</style>
