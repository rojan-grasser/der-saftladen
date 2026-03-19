<script setup lang="ts">
import { computed } from 'vue';

import type { Appointment, CalendarDay } from '../types';

const props = defineProps<{
    dayLabels: string[];
    calendarDays: CalendarDay[];
    selectedDate: Date;
    getEventBgClass: (appointment: Appointment) => string;
}>();

const emit = defineEmits<{
    (e: 'select-day', date: Date): void;
    (e: 'open-details', appointment: Appointment): void;
}>();

const weeks = computed(() => {
    const result: CalendarDay[][] = [];
    for (let i = 0; i < props.calendarDays.length; i += 7) {
        result.push(props.calendarDays.slice(i, i + 7));
    }
    return result;
});

const getWeekNumber = (date: Date): number => {
    const d = new Date(date);
    d.setHours(0, 0, 0, 0);
    d.setDate(d.getDate() + 3 - ((d.getDay() + 6) % 7));
    const week1 = new Date(d.getFullYear(), 0, 4);
    return 1 + Math.round(((d.getTime() - week1.getTime()) / 86400000 - 3 + ((week1.getDay() + 6) % 7)) / 7);
};

const isSameDay = (d1: Date, d2: Date) =>
    d1.getDate() === d2.getDate() &&
    d1.getMonth() === d2.getMonth() &&
    d1.getFullYear() === d2.getFullYear();
</script>

<template>
    <div class="flex flex-col select-none">
        <!-- Day labels row -->
        <div class="grid grid-cols-[28px_repeat(7,1fr)] border-b bg-muted/20">
            <div class="py-1.5 text-center text-[10px] text-muted-foreground/40">KW</div>
            <div
                v-for="label in dayLabels"
                :key="label"
                class="py-1.5 text-center text-[11px] font-medium text-muted-foreground"
            >
                {{ label }}
            </div>
        </div>

        <!-- Week rows -->
        <div
            v-for="(week, wIdx) in weeks"
            :key="wIdx"
            class="grid grid-cols-[28px_repeat(7,1fr)] border-b last:border-b-0"
        >
            <!-- Week number -->
            <div class="flex items-start justify-center pt-2.5 text-[10px] font-medium text-muted-foreground/50">
                {{ getWeekNumber(week[0].date) }}
            </div>

            <!-- Day cells -->
            <button
                v-for="day in week"
                :key="day.key"
                type="button"
                class="flex flex-col items-center py-1.5 gap-0.5 transition-colors active:bg-muted/50 min-h-[52px]"
                :class="[!day.isCurrentMonth ? 'opacity-35' : '']"
                @click="emit('select-day', day.date)"
            >
                <span
                    class="flex h-7 w-7 items-center justify-center rounded-full text-sm font-medium leading-none"
                    :class="[
                        isSameDay(day.date, selectedDate)
                            ? 'bg-primary text-primary-foreground'
                            : day.isToday
                              ? 'bg-primary/15 text-primary font-bold'
                              : day.isCurrentMonth
                                ? 'text-foreground'
                                : 'text-muted-foreground',
                    ]"
                >
                    {{ day.date.getDate() }}
                </span>
                <!-- Up to 3 event color dots -->
                <div class="flex h-2.5 items-center justify-center gap-0.5">
                    <span
                        v-for="apt in day.appointments.slice(0, 3)"
                        :key="apt.id"
                        class="h-1.5 w-1.5 rounded-full"
                        :class="getEventBgClass(apt)"
                    />
                </div>
            </button>
        </div>
    </div>
</template>
