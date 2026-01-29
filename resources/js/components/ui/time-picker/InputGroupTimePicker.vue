<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { computed } from 'vue';
import { Clock } from 'lucide-vue-next';

import { cn } from '@/lib/utils';

const props = withDefaults(
    defineProps<{
        modelValue?: string | null;
        placeholder?: string;
        disabled?: boolean;
        id?: string;
        step?: string | number;
        class?: HTMLAttributes['class'];
    }>(),
    {
        step: '60',
    },
);

const emit = defineEmits<{
    (e: 'update:modelValue', value: string): void;
}>();

const value = computed({
    get: () => props.modelValue ?? '',
    set: (val) => emit('update:modelValue', val),
});

const stepValue = computed(() => props.step ?? '60');
const isDisabled = computed(() => Boolean(props.disabled));
</script>

<template>
    <div
        :class="cn(
            'flex w-full items-center rounded-md border border-input bg-transparent shadow-xs transition-[color,box-shadow] focus-within:border-ring focus-within:ring-ring/50 focus-within:ring-[3px]',
            isDisabled ? 'cursor-not-allowed opacity-50' : '',
            props.class,
        )"
    >
        <input
            :id="id"
            v-model="value"
            type="time"
            :disabled="disabled"
            :placeholder="placeholder"
            :step="stepValue"
            class="flex h-10 flex-1 rounded-l-md bg-transparent px-3 py-2 text-sm outline-none appearance-none [&::-webkit-calendar-picker-indicator]:hidden [&::-webkit-calendar-picker-indicator]:appearance-none disabled:cursor-not-allowed disabled:opacity-50"
        />
        <div class="flex h-10 items-center justify-center rounded-r-md border-l border-input bg-muted px-3">
            <Clock class="h-4 w-4 text-muted-foreground" />
        </div>
    </div>
</template>
