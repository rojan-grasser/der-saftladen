<script setup lang="ts">
import { computed, reactive } from 'vue';

import type { Appointment } from '../types';
import { parseDate } from '../utils/date';

const props = defineProps<{
    selectedDate: Date;
    selectedAppointments: Appointment[];
    formatTime: (value: string | number) => string;
    formatFullDate: (value: Date) => string;
    getEventBgClass: (appointment: Appointment) => string;
    canEditAppointment: (appointment: Appointment) => boolean;
}>();

const emit = defineEmits<{
    (e: 'open-details', appointment: Appointment): void;
    (e: 'move-appointment', payload: { appointment: Appointment; newStart: Date; newEnd: Date }): void;
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
});

const getAppointmentPosition = (appointment: Appointment) => {
    const startDate = parseDate(appointment.start_time);
    const endDate = parseDate(appointment.end_time);
    if (!startDate || !endDate) return { top: '0px', height: '24px', left: '2px', width: 'calc(100% - 4px)', right: 'auto', justifyContent: 'flex-start', alignItems: 'flex-start' };
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
        justifyContent: 'flex-start',
        alignItems: 'flex-start',
    };
};

// ── Drag & Drop (nur Uhrzeit, gleicher Tag) ───────────────────────────────────
const drag = reactive({
    appointment: null as Appointment | null,
    durationMs: 0,
    grabOffsetMinutes: 0,
    isDragOver: false,
    previewStartMinutes: null as number | null,
});

const startDrag = (e: DragEvent, appointment: Appointment) => {
    const start = parseDate(appointment.start_time);
    const end = parseDate(appointment.end_time);
    if (!start || !end) return;

    drag.appointment = appointment;
    drag.durationMs = end.getTime() - start.getTime();
    drag.grabOffsetMinutes = Math.round((e.offsetY / 48) * 60);
    e.dataTransfer!.effectAllowed = 'move';
};

const onGridDragOver = (e: DragEvent) => {
    if (!drag.appointment) return;
    e.preventDefault();
    e.dataTransfer!.dropEffect = 'move';
    drag.isDragOver = true;

    const el = e.currentTarget as HTMLElement;
    const rect = el.getBoundingClientRect();
    const relativeY = e.clientY - rect.top;

    const rawMinutes = Math.round((relativeY / 48) * 60) - drag.grabOffsetMinutes;
    const snapped = Math.round(rawMinutes / 15) * 15;
    const maxStartMinutes = 24 * 60 - Math.ceil(drag.durationMs / 60000);
    drag.previewStartMinutes = Math.max(0, Math.min(snapped, maxStartMinutes));
};

const onGridDragLeave = (e: DragEvent) => {
    const el = e.currentTarget as HTMLElement;
    const related = e.relatedTarget as HTMLElement | null;
    if (related && el.contains(related)) return;
    drag.isDragOver = false;
    drag.previewStartMinutes = null;
};

const onGridDrop = (e: DragEvent) => {
    e.preventDefault();
    if (!drag.appointment || drag.previewStartMinutes === null) return;

    const newStart = new Date(props.selectedDate);
    newStart.setHours(0, drag.previewStartMinutes, 0, 0);
    const newEnd = new Date(newStart.getTime() + drag.durationMs);

    emit('move-appointment', { appointment: drag.appointment, newStart, newEnd });
    resetDrag();
};

const resetDrag = () => {
    drag.appointment = null;
    drag.durationMs = 0;
    drag.grabOffsetMinutes = 0;
    drag.isDragOver = false;
    drag.previewStartMinutes = null;
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

                <div
                    class="relative overflow-hidden transition-colors"
                    :class="drag.appointment && drag.isDragOver ? 'bg-primary/5' : ''"
                    @dragover="onGridDragOver"
                    @dragleave="onGridDragLeave"
                    @drop="onGridDrop"
                >
                    <div
                        v-for="hour in hours"
                        :key="`grid-${hour.value}`"
                        class="h-12 border-b"
                    />

                    <div class="absolute inset-0">
                        <!-- Drop-Vorschau -->
                        <div
                            v-if="drag.appointment && drag.isDragOver && drag.previewStartMinutes !== null"
                            class="pointer-events-none absolute left-1 right-1 rounded-lg border-2 border-dashed border-primary bg-primary/15"
                            :style="{
                                top: `${(drag.previewStartMinutes / 60) * 48}px`,
                                height: `${Math.max((drag.durationMs / 3600000) * 48, 24)}px`,
                            }"
                        />

                        <button
                            v-for="appointment in selectedAppointments"
                            :key="appointment.id"
                            type="button"
                            :draggable="canEditAppointment(appointment)"
                            class="absolute flex flex-col gap-0.5 overflow-hidden rounded-lg px-3 py-2 text-left text-white shadow-sm transition-opacity"
                            :class="[
                                getEventBgClass(appointment),
                                drag.appointment?.id === appointment.id
                                    ? 'opacity-40'
                                    : 'hover:opacity-90',
                                canEditAppointment(appointment) ? 'cursor-grab active:cursor-grabbing' : '',
                            ]"
                            :style="getAppointmentPosition(appointment)"
                            @click="emit('open-details', appointment)"
                            @dragstart="(e) => startDrag(e, appointment)"
                            @dragend="resetDrag"
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
