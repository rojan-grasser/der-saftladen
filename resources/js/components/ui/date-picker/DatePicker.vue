<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { computed } from 'vue';
import { Calendar, ChevronLeft, ChevronRight } from 'lucide-vue-next';
import {
    DatePickerCalendar,
    DatePickerCell,
    DatePickerCellTrigger,
    DatePickerContent,
    DatePickerGrid,
    DatePickerGridBody,
    DatePickerGridHead,
    DatePickerGridRow,
    DatePickerHeadCell,
    DatePickerHeader,
    DatePickerHeading,
    DatePickerNext,
    DatePickerPrev,
    DatePickerRoot,
    DatePickerTrigger,
    type DateValue,
} from 'reka-ui';
import { getLocalTimeZone, parseDate } from '@internationalized/date';

import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';

const props = defineProps<{
    modelValue?: string | number | null;
    placeholder?: string;
    disabled?: boolean;
    id?: string;
    class?: HTMLAttributes['class'];
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: string): void;
}>();

const toDateValue = (value: string | number | null | undefined) => {
    if (value === null || value === undefined || value === '') {
        return undefined;
    }

    if (typeof value === 'number') {
        const milliseconds = value > 9999999999 ? value : value * 1000;
        const date = new Date(milliseconds);
        if (Number.isNaN(date.getTime())) {
            return undefined;
        }
        return parseDate(
            `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(
                2,
                '0',
            )}-${String(date.getDate()).padStart(2, '0')}`,
        );
    }

    const normalized = value.includes('T') ? value.split('T')[0] : value;
    try {
        return parseDate(normalized);
    } catch {
        return undefined;
    }
};

const selectedValue = computed(() => toDateValue(props.modelValue));

const displayLabel = computed(() => {
    if (!selectedValue.value) {
        return props.placeholder ?? 'Datum auswählen';
    }
    const date = selectedValue.value.toDate(getLocalTimeZone());
    return new Intl.DateTimeFormat('de-DE', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
    }).format(date);
});

const handleUpdate = (value: DateValue | undefined) => {
    emit('update:modelValue', value ? value.toString() : '');
};
</script>

<template>
    <DatePickerRoot
        :model-value="selectedValue"
        :disabled="disabled"
        :close-on-select="true"
        locale="de-DE"
        weekday-format="short"
        :week-starts-on="1"
        @update:model-value="handleUpdate"
    >
        <DatePickerTrigger as-child>
            <Button
                :id="id"
                variant="outline"
                class="w-full justify-start text-left font-normal"
                :class="cn(!selectedValue ? 'text-muted-foreground' : '', props.class)"
                :disabled="disabled"
            >
                <Calendar class="mr-2 h-4 w-4" />
                <span class="truncate">{{ displayLabel }}</span>
            </Button>
        </DatePickerTrigger>

        <DatePickerContent
            class="z-50 rounded-md border bg-popover p-2 text-popover-foreground shadow-md"
            align="start"
            :side-offset="6"
        >
            <DatePickerCalendar v-slot="{ weekDays, grid }">
                <DatePickerHeader class="flex items-center justify-between px-1 pb-2">
                    <DatePickerPrev
                        class="inline-flex size-8 items-center justify-center rounded-md text-muted-foreground transition hover:bg-accent hover:text-foreground data-[disabled]:pointer-events-none data-[disabled]:opacity-50"
                    >
                        <ChevronLeft class="size-4" />
                    </DatePickerPrev>
                    <DatePickerHeading class="text-sm font-medium" />
                    <DatePickerNext
                        class="inline-flex size-8 items-center justify-center rounded-md text-muted-foreground transition hover:bg-accent hover:text-foreground data-[disabled]:pointer-events-none data-[disabled]:opacity-50"
                    >
                        <ChevronRight class="size-4" />
                    </DatePickerNext>
                </DatePickerHeader>

                <div class="space-y-2">
                    <DatePickerGrid
                        v-for="month in grid"
                        :key="month.value.toString()"
                        class="w-full border-collapse"
                    >
                        <DatePickerGridHead>
                            <DatePickerGridRow class="grid grid-cols-7">
                                <DatePickerHeadCell
                                    v-for="day in weekDays"
                                    :key="day"
                                    class="py-1 text-center text-xs font-medium text-muted-foreground"
                                >
                                    {{ day }}
                                </DatePickerHeadCell>
                            </DatePickerGridRow>
                        </DatePickerGridHead>
                        <DatePickerGridBody>
                            <DatePickerGridRow
                                v-for="(week, index) in month.rows"
                                :key="`${month.value.toString()}-${index}`"
                                class="grid grid-cols-7"
                            >
                                <DatePickerCell
                                    v-for="day in week"
                                    :key="day.toString()"
                                    :date="day"
                                    class="p-0 text-center"
                                >
                                    <DatePickerCellTrigger
                                        :day="day"
                                        :month="month.value"
                                        class="flex size-9 items-center justify-center rounded-md text-sm font-medium transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring/50 data-[selected]:bg-primary data-[selected]:text-primary-foreground data-[disabled]:pointer-events-none data-[disabled]:opacity-50 data-[outside-view]:text-muted-foreground/50 data-[outside-view]:opacity-40 data-[today]:border data-[today]:border-primary/40 data-[today]:font-semibold"
                                    >
                                        {{ day.day }}
                                    </DatePickerCellTrigger>
                                </DatePickerCell>
                            </DatePickerGridRow>
                        </DatePickerGridBody>
                    </DatePickerGrid>
                </div>
            </DatePickerCalendar>
        </DatePickerContent>
    </DatePickerRoot>
</template>
