<script setup lang="ts">
import { computed } from 'vue';

import type { Appointment, WeekDay } from '../types';
import { parseDate } from '../utils/date';

const props = defineProps<{
    weekDays: WeekDay[];
    dayLabels: string[];
    formatTime: (value: string | number) => string;
    getEventBgClass: (appointment: Appointment) => string;
}>();

const emit = defineEmits<{
    (e: 'select-day', date: Date): void;
    (e: 'open-details', appointment: Appointment): void;
}>();

const hours = computed(() => {
    return Array.from({ length: 24 }, (_, i) => {
        const hour = i.toString().padStart(2, '0');
        return `${hour}:00`;
    });
});

const formatDayHeader = (date: Date) => {
    return new Intl.DateTimeFormat('de-DE', {
        weekday: 'short',
    }).format(date);
};

const computeDayOverlapLayout = (appointments: Appointment[]) => {
    if (appointments.length === 0) return new Map<number, { col: number; totalCols: number }>();

    const withTimes = appointments.map((appt) => ({
        appt,
        start: parseDate(appt.start_time)?.getTime() ?? 0,
        end: parseDate(appt.end_time)?.getTime() ?? 0,
    }));

    const sorted = [...withTimes].sort((a, b) => a.start - b.start);

    // Greedy column assignment
    const colEndTimes: number[] = [];
    const colAssign = new Map<number, number>();
    for (const item of sorted) {
        let col = colEndTimes.findIndex((endTime) => endTime <= item.start);
        if (col === -1) {
            col = colEndTimes.length;
            colEndTimes.push(item.end);
        } else {
            colEndTimes[col] = item.end;
        }
        colAssign.set(item.appt.id, col);
    }

    // Compute totalCols: for each appointment, find max col among all overlapping appointments
    const layout = new Map<number, { col: number; totalCols: number }>();
    for (const item of withTimes) {
        const col = colAssign.get(item.appt.id) ?? 0;
        let maxCol = col;
        for (const other of withTimes) {
            if (other.appt.id !== item.appt.id && other.start < item.end && other.end > item.start) {
                maxCol = Math.max(maxCol, colAssign.get(other.appt.id) ?? 0);
            }
        }
        layout.set(item.appt.id, { col, totalCols: maxCol + 1 });
    }

    return layout;
};

const overlapLayouts = computed(() => {
    const result = new Map<string, Map<number, { col: number; totalCols: number }>>();
    for (const day of props.weekDays) {
        result.set(day.key, computeDayOverlapLayout(day.appointments));
    }
    return result;
});

const getAppointmentPosition = (appointment: Appointment, dayKey: string) => {
    const startDate = parseDate(appointment.start_time);
    const endDate = parseDate(appointment.end_time);
    if (!startDate || !endDate) return { top: '0px', height: '24px', left: '2px', width: 'calc(100% - 4px)', right: 'auto' };
    const startHour = startDate.getHours() + startDate.getMinutes() / 60;
    const endHour = endDate.getHours() + endDate.getMinutes() / 60;
    const duration = Math.max(endHour - startHour, 0.5);

    const dayLayout = overlapLayouts.value.get(dayKey);
    const { col, totalCols } = dayLayout?.get(appointment.id) ?? { col: 0, totalCols: 1 };
    const gapPx = 2;
    const marginPx = 1;
    const totalSpacePx = 2 * marginPx + (totalCols - 1) * gapPx;
    const colWidth = `calc((100% - ${totalSpacePx}px) / ${totalCols})`;
    const colLeft =
        col === 0 ? `${marginPx}px` : `calc(${marginPx}px + ${col} * (${colWidth} + ${gapPx}px))`;

    return {
        top: `${startHour * 48}px`,
        height: `${duration * 48}px`,
        left: colLeft,
        width: colWidth,
        right: 'auto',
        display: 'flex',
        flexDirection: 'column',
        justifyContent: 'flex-start',
        alignItems: 'flex-start',
    };
};
</script>

<template>
    <div class="flex h-full flex-col">
        <div class="grid grid-cols-[60px_repeat(7,1fr)] border-b">
            <div class="border-r" />
            <div
                v-for="day in weekDays"
                :key="day.key"
                class="border-r px-2 py-3 text-center last:border-r-0"
                :class="day.isSelected ? 'bg-primary/5' : ''"
            >
                <div class="text-xs font-medium text-muted-foreground">
                    {{ formatDayHeader(day.date) }}
                </div>
                <button
                    class="mt-1 flex h-10 w-10 items-center justify-center rounded-full text-xl font-semibold transition-colors hover:bg-muted"
                    :class="day.isToday ? 'bg-primary text-primary-foreground hover:bg-primary/90' : ''"
                    @click="emit('select-day', day.date)"
                >
                    {{ day.date.getDate() }}
                </button>
            </div>
        </div>

        <div class="flex-1 overflow-auto">
            <div class="grid grid-cols-[60px_repeat(7,1fr)]">
                <div class="sticky left-0 bg-background">
                    <div
                        v-for="(hour, index) in hours"
                        :key="hour"
                        class="relative h-12 border-b border-r"
                    >
                        <span
                            v-if="index > 0"
                            class="absolute -top-2 right-2 text-[10px] text-muted-foreground"
                        >
                            {{ hour }}
                        </span>
                    </div>
                </div>

                <div
                    v-for="day in weekDays"
                    :key="`grid-${day.key}`"
                    class="relative overflow-hidden border-r last:border-r-0"
                    :class="day.isSelected ? 'bg-primary/5' : ''"
                >
                    <div
                        v-for="hour in hours"
                        :key="`${day.key}-${hour}`"
                        class="h-12 border-b"
                    />

                    <div class="absolute inset-0">
                        <button
                            v-for="appointment in day.appointments"
                            :key="appointment.id"
                            type="button"
                            class="absolute overflow-hidden rounded px-1 py-0.5 text-left text-[10px] text-white transition-opacity hover:opacity-80"
                            :class="getEventBgClass(appointment)"
                            :style="getAppointmentPosition(appointment, day.key)"
                            @click="emit('open-details', appointment)"
                        >
                            <span class="block font-medium leading-tight">
                                {{ formatTime(appointment.start_time) }}
                            </span>
                            <span class="block truncate leading-tight">
                                {{ appointment.title }}
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
