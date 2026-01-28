<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { computed, ref } from 'vue';
import { ChevronDown, Clock } from 'lucide-vue-next';

import { Button } from '@/components/ui/button';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { cn } from '@/lib/utils';

const props = withDefaults(
    defineProps<{
        modelValue?: string | null;
        placeholder?: string;
        disabled?: boolean;
        id?: string;
        class?: HTMLAttributes['class'];
        stepMinutes?: number;
    }>(),
    {
        stepMinutes: 15,
    },
);

const emit = defineEmits<{
    (e: 'update:modelValue', value: string): void;
}>();

const timePattern = /^([01]\d|2[0-3]):[0-5]\d(?::[0-5]\d)?$/;

const normalizeTime = (value: string | null | undefined) => {
    if (!value) {
        return '';
    }

    const trimmed = value.trim();
    if (!trimmed) {
        return '';
    }

    if (timePattern.test(trimmed)) {
        return trimmed.slice(0, 5);
    }

    if (trimmed.includes('T')) {
        const timePart = trimmed.split('T')[1]?.slice(0, 5) ?? '';
        return timePattern.test(timePart) ? timePart : '';
    }

    return '';
};

const selectedValue = computed(() => normalizeTime(props.modelValue));
const isEmpty = computed(() => !selectedValue.value);
const open = ref(false);

const baseOptions = computed(() => {
    const step = Math.max(1, Math.min(60, Math.round(props.stepMinutes)));
    const options: string[] = [];

    for (let hour = 0; hour < 24; hour += 1) {
        for (let minute = 0; minute < 60; minute += step) {
            options.push(
                `${String(hour).padStart(2, '0')}:${String(minute).padStart(
                    2,
                    '0',
                )}`,
            );
        }
    }

    return options;
});

const customValue = computed(() => {
    const value = selectedValue.value;
    if (!value) {
        return '';
    }
    return baseOptions.value.includes(value) ? '' : value;
});

const timeOptions = computed(() => {
    return customValue.value
        ? [customValue.value, ...baseOptions.value]
        : baseOptions.value;
});

const optionLabel = (value: string) => {
    if (customValue.value && value === customValue.value) {
        return `${value} (aktuell)`;
    }
    return value;
};

const displayLabel = computed(() => {
    if (!selectedValue.value) {
        return props.placeholder ?? 'Zeit auswählen';
    }
    return selectedValue.value;
});

const handleSelect = (value: string) => {
    emit('update:modelValue', value);
    open.value = false;
};
</script>

<template>
    <Popover v-model:open="open">
        <PopoverTrigger as-child>
            <Button
                :id="id"
                type="button"
                variant="outline"
                :disabled="disabled"
                :class="cn(
                    'w-full justify-between text-left font-normal',
                    isEmpty ? 'text-muted-foreground' : '',
                    props.class,
                )"
            >
                <span class="flex items-center gap-2 truncate">
                    <Clock class="size-4" />
                    <span class="truncate">{{ displayLabel }}</span>
                </span>
                <ChevronDown class="size-4 opacity-50" />
            </Button>
        </PopoverTrigger>
        <PopoverContent
            class="w-[--radix-popover-trigger-width] p-1"
            align="start"
        >
            <div class="max-h-64 overflow-y-auto py-1">
                <button
                    v-for="option in timeOptions"
                    :key="option"
                    type="button"
                    class="flex w-full items-center justify-between rounded-md px-2 py-1.5 text-sm transition hover:bg-accent hover:text-accent-foreground"
                    :class="option === selectedValue ? 'bg-accent text-accent-foreground' : ''"
                    @click="handleSelect(option)"
                >
                    <span>{{ optionLabel(option) }}</span>
                    <span v-if="option === selectedValue" class="text-xs">✓</span>
                </button>
            </div>
        </PopoverContent>
    </Popover>
</template>
