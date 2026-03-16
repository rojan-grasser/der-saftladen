<script setup lang="ts">
import { computed, reactive } from 'vue';

import type { Appointment, WeekDay } from '../types';
import { parseDate } from '../utils/date';

const props = defineProps<{
    weekDays: WeekDay[];
    dayLabels: string[];
    formatTime: (value: string | number) => string;
    getEventBgClass: (appointment: Appointment) => string;
    canEditAppointment: (appointment: Appointment) => boolean;
}>();

const emit = defineEmits<{
    (e: 'select-day', date: Date): void;
    (e: 'open-details', appointment: Appointment): void;
    (e: 'move-appointment', payload: { appointment: Appointment; newStart: Date; newEnd: Date }): void;
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

    // Compute totalCols via connected components (BFS):
    // Direkt und transitiv überlappende Termine teilen dasselbe totalCols,
    // damit kein Termin zu breit dargestellt wird (z. B. 50 % statt 33 %).
    const layout = new Map<number, { col: number; totalCols: number }>();
    const visitedIds = new Set<number>();
    for (const item of withTimes) {
        if (visitedIds.has(item.appt.id)) continue;
        const component: typeof withTimes = [];
        const queue: typeof withTimes = [item];
        visitedIds.add(item.appt.id);
        while (queue.length > 0) {
            const current = queue.shift()!;
            component.push(current);
            for (const other of withTimes) {
                if (!visitedIds.has(other.appt.id) && other.start < current.end && other.end > current.start) {
                    visitedIds.add(other.appt.id);
                    queue.push(other);
                }
            }
        }
        const maxCol = Math.max(...component.map((c) => colAssign.get(c.appt.id) ?? 0));
        const totalCols = maxCol + 1;
        for (const c of component) {
            layout.set(c.appt.id, { col: colAssign.get(c.appt.id) ?? 0, totalCols });
        }
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
    if (!startDate || !endDate) return { top: '0px', height: '24px', left: '2px', width: 'calc(100% - 4px)', right: 'auto', display: 'flex', flexDirection: 'column', justifyContent: 'flex-start', alignItems: 'flex-start' };
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

// ── Drag & Drop ───────────────────────────────────────────────────────────────
const drag = reactive({
    appointment: null as Appointment | null,
    durationMs: 0,
    grabOffsetMinutes: 0,
    overDayKey: null as string | null,
    previewStartMinutes: null as number | null,
});

const startDrag = (e: DragEvent, appointment: Appointment) => {
    const start = parseDate(appointment.start_time);
    const end = parseDate(appointment.end_time);
    if (!start || !end) return;

    drag.appointment = appointment;
    drag.durationMs = end.getTime() - start.getTime();
    // Grab-Offset: wie viele Minuten vom Terminkopf hat der User geklickt?
    drag.grabOffsetMinutes = Math.round((e.offsetY / 48) * 60);
    e.dataTransfer!.effectAllowed = 'move';
};

const onColumnDragOver = (e: DragEvent, day: WeekDay) => {
    if (!drag.appointment) return;
    e.preventDefault();
    e.dataTransfer!.dropEffect = 'move';
    drag.overDayKey = day.key;

    // Position relativ zum absoluten Beginn der Spalte berechnen
    // (getBoundingClientRect liefert viewport-relative Koordinaten, die bereits
    //  den Scroll berücksichtigen, da die Spalte overflow-hidden hat)
    const el = e.currentTarget as HTMLElement;
    const rect = el.getBoundingClientRect();
    const relativeY = e.clientY - rect.top;

    const rawMinutes = Math.round((relativeY / 48) * 60) - drag.grabOffsetMinutes;
    const snapped = Math.round(rawMinutes / 15) * 15;
    const maxStartMinutes = 24 * 60 - Math.ceil(drag.durationMs / 60000);
    drag.previewStartMinutes = Math.max(0, Math.min(snapped, maxStartMinutes));
};

const onColumnDragLeave = (e: DragEvent) => {
    // Nicht zurücksetzen wenn der Mauszeiger noch innerhalb der Spalte ist
    const el = e.currentTarget as HTMLElement;
    const related = e.relatedTarget as HTMLElement | null;
    if (related && el.contains(related)) return;
    drag.overDayKey = null;
    drag.previewStartMinutes = null;
};

const onColumnDrop = (e: DragEvent, day: WeekDay) => {
    e.preventDefault();
    if (!drag.appointment || drag.previewStartMinutes === null) return;

    const newStart = new Date(day.date);
    newStart.setHours(0, drag.previewStartMinutes, 0, 0);
    const newEnd = new Date(newStart.getTime() + drag.durationMs);

    emit('move-appointment', { appointment: drag.appointment, newStart, newEnd });
    resetDrag();
};

const resetDrag = () => {
    drag.appointment = null;
    drag.durationMs = 0;
    drag.grabOffsetMinutes = 0;
    drag.overDayKey = null;
    drag.previewStartMinutes = null;
};

const now = new Date();
const isPastAppointment = (appointment: Appointment): boolean => {
    const end = parseDate(appointment.end_time);
    return end !== null && end < now;
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
                    class="relative overflow-hidden border-r transition-colors last:border-r-0"
                    :class="[
                        day.isSelected ? 'bg-primary/5' : '',
                        drag.appointment && drag.overDayKey === day.key ? 'bg-primary/5' : '',
                    ]"
                    @dragover="(e) => onColumnDragOver(e, day)"
                    @dragleave="onColumnDragLeave"
                    @drop="(e) => onColumnDrop(e, day)"
                >
                    <div
                        v-for="hour in hours"
                        :key="`${day.key}-${hour}`"
                        class="h-12 border-b"
                    />

                    <div class="absolute inset-0">
                        <!-- Drop-Vorschau: zeigt wo der Termin landen würde -->
                        <div
                            v-if="drag.appointment && drag.overDayKey === day.key && drag.previewStartMinutes !== null"
                            class="pointer-events-none absolute left-0.5 right-0.5 rounded border-2 border-dashed border-primary bg-primary/15"
                            :style="{
                                top: `${(drag.previewStartMinutes / 60) * 48}px`,
                                height: `${Math.max((drag.durationMs / 3600000) * 48, 24)}px`,
                            }"
                        />

                        <button
                            v-for="appointment in day.appointments"
                            :key="appointment.id"
                            type="button"
                            :draggable="canEditAppointment(appointment)"
                            class="absolute overflow-hidden rounded px-1 py-0.5 text-left text-[10px] text-white transition-opacity"
                            :class="[
                                getEventBgClass(appointment),
                                drag.appointment?.id === appointment.id
                                    ? 'opacity-40'
                                    : isPastAppointment(appointment)
                                      ? 'opacity-50 hover:opacity-70'
                                      : 'hover:opacity-80',
                                canEditAppointment(appointment) ? 'cursor-grab active:cursor-grabbing' : '',
                            ]"
                            :style="getAppointmentPosition(appointment, day.key)"
                            @click="emit('open-details', appointment)"
                            @dragstart="(e) => startDrag(e, appointment)"
                            @dragend="resetDrag"
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
