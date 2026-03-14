<script setup lang="ts">
import { computed } from 'vue';

import type { Appointment } from '../types';
import { parseDate } from '../utils/date';

const props = defineProps<{
    selectedDate: Date;
    selectedAppointments: Appointment[];
    formatTime: (value: string | number) => string;
    formatFullDate: (value: Date) => string;
    getEventBgClass: (appointment: Appointment) => string;
}>();

const emit = defineEmits<{
    (e: 'open-details', appointment: Appointment): void;
}>();

const hours = computed(() => {
    return Array.from({ length: 24 }, (_, i) => {
        const hour = i.toString().padStart(2, '0');
        return { value: i, label: `${hour}:00` };
    });
});

const overlapLayout = computed(() => {
    const appointments = props.selectedAppointments;
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

    // Compute totalCols: for each appointment, find max col index among all overlapping appointments
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
});

const getAppointmentPosition = (appointment: Appointment) => {
    const startDate = parseDate(appointment.start_time);
    const endDate = parseDate(appointment.end_time);
    if (!startDate || !endDate) return { top: '0px', height: '24px', left: '2px', width: 'calc(100% - 4px)', right: 'auto' };
    const startHour = startDate.getHours() + startDate.getMinutes() / 60;
    const endHour = endDate.getHours() + endDate.getMinutes() / 60;
    const duration = Math.max(endHour - startHour, 0.5);

    const { col, totalCols } = overlapLayout.value.get(appointment.id) ?? { col: 0, totalCols: 1 };
    const gapPx = 2;
    const marginPx = 2;
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
    };
};
</script>

<template>
    <div class="flex h-full flex-col">
        <div class="border-b px-4 py-4">
            <h2 class="text-xl font-semibold">
                {{ formatFullDate(selectedDate) }}
            </h2>
            <p class="text-sm text-muted-foreground">
                {{ selectedAppointments.length }} Termin(e)
            </p>
        </div>

        <div class="flex-1 overflow-auto">
            <div class="relative grid grid-cols-[60px_1fr]">
                <div class="sticky left-0 bg-background">
                    <div
                        v-for="hour in hours"
                        :key="hour.value"
                        class="relative h-12 border-b border-r"
                    >
                        <span
                            v-if="hour.value > 0"
                            class="absolute -top-2 right-2 text-[10px] text-muted-foreground"
                        >
                            {{ hour.label }}
                        </span>
                    </div>
                </div>

                <div class="relative overflow-hidden">
                    <div
                        v-for="hour in hours"
                        :key="`grid-${hour.value}`"
                        class="h-12 border-b"
                    />

                    <div class="absolute inset-0">
                        <button
                            v-for="appointment in selectedAppointments"
                            :key="appointment.id"
                            type="button"
                            class="absolute flex flex-col justify-start gap-0.5 overflow-hidden rounded-lg px-3 py-2 text-left text-white shadow-sm transition-opacity hover:opacity-90"
                            :class="getEventBgClass(appointment)"
                            :style="getAppointmentPosition(appointment)"
                            @click="emit('open-details', appointment)"
                        >
                            <span class="text-sm font-semibold">
                                {{ appointment.title }}
                            </span>
                            <span class="text-xs opacity-90">
                                {{ formatTime(appointment.start_time) }} - {{ formatTime(appointment.end_time) }}
                            </span>
                            <span
                                v-if="appointment.location"
                                class="truncate text-xs opacity-75"
                            >
                                {{ appointment.location }}
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
