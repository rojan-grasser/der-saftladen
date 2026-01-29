<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

import { DatePicker } from '@/components/ui/date-picker';
import { TimePicker } from '@/components/ui/time-picker';
import { computed, ref, watch } from 'vue';

import { Label } from '@/components/ui/label';
import { cn } from '@/lib/utils';

const props = withDefaults(
    defineProps<{
        date?: string;
        time?: string;
        dateLabel?: string;
        timeLabel?: string;
        datePlaceholder?: string;
        timePlaceholder?: string;
        dateId?: string;
        timeId?: string;
        class?: HTMLAttributes['class'];
        timeStepMinutes?: number;
    }>(),
    {
        dateLabel: 'Date',
        timeLabel: 'Time',
        datePlaceholder: 'Select date',
        timePlaceholder: 'Select time',
        dateId: 'date-picker-optional',
        timeId: 'time-picker-optional',
        timeStepMinutes: 15,
    },
);

const emit = defineEmits<{
    (e: 'update:date', value: string): void;
    (e: 'update:time', value: string): void;
}>();

const localDate = ref(props.date ?? '');
const localTime = ref(props.time ?? '');

watch(
    () => props.date,
    (value) => {
        if (value !== undefined) {
            localDate.value = value;
        }
    },
);

watch(
    () => props.time,
    (value) => {
        if (value !== undefined) {
            localTime.value = value;
        }
    },
);

const dateValue = computed(() =>
    props.date === undefined ? localDate.value : props.date,
);
const timeValue = computed(() =>
    props.time === undefined ? localTime.value : props.time,
);

const handleDateUpdate = (value: string) => {
    if (props.date === undefined) {
        localDate.value = value;
    }
    emit('update:date', value);
};

const handleTimeUpdate = (value: string) => {
    if (props.time === undefined) {
        localTime.value = value;
    }
    emit('update:time', value);
};
</script>

<template>
    <div
        :class="cn('mx-auto flex max-w-xs flex-row gap-3', props.class)"
    >
        <div class="flex flex-1 flex-col gap-2">
            <Label :for="dateId">{{ dateLabel }}</Label>
            <DatePicker
                :id="dateId"
                :model-value="dateValue"
                :placeholder="datePlaceholder"
                class="w-32 justify-between"
                @update:model-value="handleDateUpdate"
            />
        </div>
        <div class="flex w-32 flex-col gap-2">
            <Label :for="timeId">{{ timeLabel }}</Label>
            <TimePicker
                :id="timeId"
                :model-value="timeValue"
                :placeholder="timePlaceholder"
                :step-minutes="timeStepMinutes"
                class="w-32"
                @update:model-value="handleTimeUpdate"
            />
        </div>
    </div>
</template>
