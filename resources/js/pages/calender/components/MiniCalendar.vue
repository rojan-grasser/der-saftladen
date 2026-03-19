<script setup lang="ts">
import { ChevronLeft, ChevronRight } from 'lucide-vue-next';
import { computed } from 'vue';

import { Button } from '@/components/ui/button';

import type { CalendarDay } from '../types';

const props = defineProps<{
    currentMonth: Date;
    selectedDate: Date;
    calendarDays: CalendarDay[];
}>();

const emit = defineEmits<{
    (e: 'select-day', date: Date): void;
    (e: 'prev-month'): void;
    (e: 'next-month'): void;
}>();

const monthLabel = computed(() => {
    return new Intl.DateTimeFormat('de-DE', {
        month: 'long',
        year: 'numeric',
    }).format(props.currentMonth);
});

const weekDayLabels = ['Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa', 'So'];

const isSameDay = (date1: Date, date2: Date) => {
    return (
        date1.getDate() === date2.getDate() &&
        date1.getMonth() === date2.getMonth() &&
        date1.getFullYear() === date2.getFullYear()
    );
};
</script>

<template>
    <div class="rounded-lg border bg-card p-3">
        <div class="mb-2 flex items-center justify-between">
            <span class="text-sm font-medium">{{ monthLabel }}</span>
            <div class="flex items-center gap-1">
                <Button
                    variant="ghost"
                    size="icon"
                    class="h-6 w-6"
                    @click="emit('prev-month')"
                >
                    <ChevronLeft class="h-3 w-3" />
                </Button>
                <Button
                    variant="ghost"
                    size="icon"
                    class="h-6 w-6"
                    @click="emit('next-month')"
                >
                    <ChevronRight class="h-3 w-3" />
                </Button>
            </div>
        </div>

        <div class="grid grid-cols-7 gap-0.5 text-center text-xs">
            <div
                v-for="label in weekDayLabels"
                :key="label"
                class="py-1 font-medium text-muted-foreground"
            >
                {{ label }}
            </div>

            <button
                v-for="day in calendarDays"
                :key="day.key"
                type="button"
                class="relative flex h-7 w-7 items-center justify-center rounded-full text-xs transition-colors"
                :class="[
                    isSameDay(day.date, selectedDate)
                        ? 'bg-primary text-primary-foreground'
                        : day.isToday
                          ? 'bg-primary/10 text-primary'
                          : day.isCurrentMonth
                            ? 'hover:bg-muted'
                            : 'text-muted-foreground/50 hover:bg-muted/50',
                ]"
                @click="emit('select-day', day.date)"
            >
                {{ day.date.getDate() }}
                <span
                    v-if="day.appointments.length > 0 && !isSameDay(day.date, selectedDate)"
                    class="absolute bottom-0.5 left-1/2 h-1 w-1 -translate-x-1/2 rounded-full bg-primary"
                />
            </button>
        </div>
    </div>
</template>
