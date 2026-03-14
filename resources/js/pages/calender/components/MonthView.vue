<script setup lang="ts">
import { computed, reactive } from 'vue';

import type { Appointment, CalendarDay } from '../types';
import { parseDate, toDayStart } from '../utils/date';

const props = defineProps<{
    dayLabels: string[];
    calendarDays: CalendarDay[];
    formatTime: (value: string | number) => string;
    getEventBgClass: (appointment: Appointment) => string;
    canEditAppointment: (appointment: Appointment) => boolean;
}>();

const emit = defineEmits<{
    (e: 'select-day', date: Date): void;
    (e: 'open-details', appointment: Appointment): void;
    (e: 'move-appointment', payload: { appointment: Appointment; newStart: Date; newEnd: Date }): void;
}>();

interface EventSegment {
    appointment: Appointment;
    startCol: number;
    span: number;
    isStart: boolean;
    isEnd: boolean;
    lane: number;
}

interface WeekRow {
    days: CalendarDay[];
    eventSegments: EventSegment[];
}

const weeks = computed<WeekRow[]>(() => {
    const result: WeekRow[] = [];

    for (let i = 0; i < props.calendarDays.length; i += 7) {
        const weekDays = props.calendarDays.slice(i, i + 7);
        const weekStart = toDayStart(weekDays[0].date);
        const weekEnd = toDayStart(weekDays[6].date);

        // Collect all unique appointments that appear in this week
        const appointmentsInWeek = new Map<number, Appointment>();
        for (const day of weekDays) {
            for (const apt of day.appointments) {
                if (!appointmentsInWeek.has(apt.id)) {
                    appointmentsInWeek.set(apt.id, apt);
                }
            }
        }

        // Calculate segments for each appointment
        const segments: EventSegment[] = [];
        // Sortierung: Längste Überschneidung mit dieser Wochenzeile zuerst (→ niedrigste Lane),
        // dann nach Gesamtdauer, dann nach Startzeit.
        // Dadurch bekommen Mehrtagestermine immer Vorrang vor Eintagesterminen,
        // auch wenn sie in der vorherigen Woche gestartet sind.
        const weekStartMs = weekStart.getTime();
        const weekEndMs = weekEnd.getTime() + 86400000; // exklusives Ende (Montag der nächsten Woche)
        const sortedAppointments = Array.from(appointmentsInWeek.values()).sort((a, b) => {
            const aStart = parseDate(a.start_time)?.getTime() ?? 0;
            const bStart = parseDate(b.start_time)?.getTime() ?? 0;
            const aEnd = parseDate(a.end_time)?.getTime() ?? aStart;
            const bEnd = parseDate(b.end_time)?.getTime() ?? bStart;
            // 1. Überschneidung mit dieser Woche (mehr Tage = höhere Priorität)
            const aWeekSpan = Math.max(0, Math.min(aEnd, weekEndMs) - Math.max(aStart, weekStartMs));
            const bWeekSpan = Math.max(0, Math.min(bEnd, weekEndMs) - Math.max(bStart, weekStartMs));
            if (bWeekSpan !== aWeekSpan) return bWeekSpan - aWeekSpan;
            // 2. Gesamtdauer (länger = höhere Priorität)
            if (bEnd - bStart !== aEnd - aStart) return (bEnd - bStart) - (aEnd - aStart);
            // 3. Früherer Starttermin zuerst
            return aStart - bStart;
        });

        // Track lane usage per day (0-6)
        const lanesPerDay: number[][] = [[], [], [], [], [], [], []];

        for (const apt of sortedAppointments) {
            const aptStart = parseDate(apt.start_time);
            const aptEnd = parseDate(apt.end_time);
            if (!aptStart || !aptEnd) continue;

            const aptStartDay = toDayStart(aptStart);
            const aptEndDay = toDayStart(aptEnd);

            // Calculate which columns this event spans in this week
            let startCol = 0;
            let endCol = 6;

            for (let col = 0; col < 7; col++) {
                const dayDate = toDayStart(weekDays[col].date);
                if (
                    dayDate.getTime() === aptStartDay.getTime() ||
                    (aptStartDay < weekStart && col === 0)
                ) {
                    startCol = col;
                    if (aptStartDay >= weekStart) break;
                }
            }

            for (let col = 6; col >= 0; col--) {
                const dayDate = toDayStart(weekDays[col].date);
                if (
                    dayDate.getTime() === aptEndDay.getTime() ||
                    (aptEndDay > weekEnd && col === 6)
                ) {
                    endCol = col;
                    if (aptEndDay <= weekEnd) break;
                }
            }

            // Clamp to week bounds
            if (aptStartDay < weekStart) startCol = 0;
            if (aptEndDay > weekEnd) endCol = 6;

            const span = endCol - startCol + 1;
            const isStart = aptStartDay >= weekStart && aptStartDay <= weekEnd;
            const isEnd = aptEndDay >= weekStart && aptEndDay <= weekEnd;

            // Find available lane
            let lane = 0;
            let foundLane = false;
            while (!foundLane) {
                foundLane = true;
                for (let col = startCol; col <= endCol; col++) {
                    if (lanesPerDay[col].includes(lane)) {
                        foundLane = false;
                        lane++;
                        break;
                    }
                }
            }

            // Mark lane as used for these days
            for (let col = startCol; col <= endCol; col++) {
                lanesPerDay[col].push(lane);
            }

            segments.push({
                appointment: apt,
                startCol,
                span,
                isStart,
                isEnd,
                lane,
            });
        }

        result.push({
            days: weekDays,
            eventSegments: segments,
        });
    }

    return result;
});

const maxVisibleLanes = 3;

const getWeekMinHeight = (week: WeekRow) => {
    const laneCount = Math.max(
        ...week.eventSegments.map((segment) => segment.lane + 1),
        0,
    );
    return Math.max(100, 28 + Math.min(maxVisibleLanes, laneCount) * 22 + 16);
};

const getVisibleSegments = (segments: EventSegment[]) => {
    return segments.filter((s) => s.lane < maxVisibleLanes);
};

const getHiddenCount = (segments: EventSegment[], dayIndex: number) => {
    const hidden = segments.filter((s) => {
        if (s.lane < maxVisibleLanes) return false;
        return dayIndex >= s.startCol && dayIndex < s.startCol + s.span;
    });
    return hidden.length;
};

// ── Drag & Drop (Termin auf anderen Tag verschieben, Uhrzeit bleibt) ──────────
const drag = reactive({
    appointment: null as Appointment | null,
    durationMs: 0,
    overDayKey: null as string | null,
});

const startSegmentDrag = (e: DragEvent, appointment: Appointment) => {
    const start = parseDate(appointment.start_time);
    const end = parseDate(appointment.end_time);
    if (!start || !end) return;

    drag.appointment = appointment;
    drag.durationMs = end.getTime() - start.getTime();
    e.dataTransfer!.effectAllowed = 'move';
};

const onDayCellDragOver = (e: DragEvent, day: CalendarDay) => {
    if (!drag.appointment) return;
    e.preventDefault();
    e.dataTransfer!.dropEffect = 'move';
    drag.overDayKey = day.key;
};

const onDayCellDragLeave = (e: DragEvent) => {
    const el = e.currentTarget as HTMLElement;
    const related = e.relatedTarget as HTMLElement | null;
    if (related && el.contains(related)) return;
    drag.overDayKey = null;
};

const onDayCellDrop = (e: DragEvent, day: CalendarDay) => {
    e.preventDefault();
    if (!drag.appointment) return;

    const origStart = parseDate(drag.appointment.start_time);
    if (!origStart) return;

    // Uhrzeit des Originals beibehalten, nur das Datum ändern
    const newStart = new Date(day.date);
    newStart.setHours(origStart.getHours(), origStart.getMinutes(), 0, 0);
    const newEnd = new Date(newStart.getTime() + drag.durationMs);

    emit('move-appointment', { appointment: drag.appointment, newStart, newEnd });
    drag.appointment = null;
    drag.overDayKey = null;
};

const resetMonthDrag = () => {
    drag.appointment = null;
    drag.durationMs = 0;
    drag.overDayKey = null;
};
</script>

<template>
    <div class="flex h-full flex-col">
        <div class="grid grid-cols-7 border-b bg-muted/30">
            <div
                v-for="label in dayLabels"
                :key="label"
                class="border-r px-2 py-2 text-center text-xs font-medium text-muted-foreground last:border-r-0"
            >
                {{ label }}
            </div>
        </div>

        <div class="grid min-h-0 flex-1 grid-rows-6">
            <div
                v-for="(week, weekIndex) in weeks"
                :key="weekIndex"
                class="relative grid h-full grid-cols-7"
                :style="{ minHeight: `${getWeekMinHeight(week)}px` }"
            >
                <!-- Day cells -->
                <div
                    v-for="(day, dayIndex) in week.days"
                    :key="day.key"
                    class="group relative h-full min-h-[100px] cursor-pointer border-r border-b p-1 transition-colors last:border-r-0 hover:bg-muted/30"
                    :class="[
                        !day.isCurrentMonth ? 'bg-muted/20' : '',
                        day.isSelected ? 'bg-primary/5' : '',
                        drag.appointment && drag.overDayKey === day.key ? 'bg-primary/10 ring-2 ring-inset ring-primary/40' : '',
                    ]"
                    @click="emit('select-day', day.date)"
                    @dragover="(e) => onDayCellDragOver(e, day)"
                    @dragleave="onDayCellDragLeave"
                    @drop="(e) => onDayCellDrop(e, day)"
                >
                    <div class="flex items-center justify-center">
                        <span
                            class="flex h-6 w-6 items-center justify-center rounded-full text-xs font-medium"
                            :class="[
                                day.isToday
                                    ? 'bg-primary text-primary-foreground'
                                    : day.isCurrentMonth
                                      ? 'text-foreground'
                                      : 'text-muted-foreground',
                            ]"
                        >
                            {{ day.date.getDate() }}
                        </span>
                    </div>

                    <!-- Hidden events indicator -->
                    <button
                        v-if="getHiddenCount(week.eventSegments, dayIndex) > 0"
                        type="button"
                        class="absolute right-1 bottom-1 left-1 rounded px-1 py-0.5 text-center text-[10px] font-medium text-muted-foreground hover:bg-muted"
                        @click.stop="emit('select-day', day.date)"
                    >
                        +{{
                            getHiddenCount(week.eventSegments, dayIndex)
                        }}
                        weitere
                    </button>
                </div>

                <!-- Event segments (absolute positioned) -->
                <button
                    v-for="segment in getVisibleSegments(week.eventSegments)"
                    :key="`${segment.appointment.id}-${segment.startCol}`"
                    type="button"
                    :draggable="canEditAppointment(segment.appointment)"
                    class="absolute z-10 flex items-center overflow-hidden text-[11px] text-white transition-opacity hover:opacity-90"
                    :class="[
                        getEventBgClass(segment.appointment),
                        segment.isStart ? 'rounded-l' : '',
                        segment.isEnd ? 'rounded-r' : '',
                        drag.appointment?.id === segment.appointment.id ? 'opacity-40' : '',
                        canEditAppointment(segment.appointment) ? 'cursor-grab active:cursor-grabbing' : '',
                    ]"
                    :style="{
                        left: `calc(${(segment.startCol / 7) * 100}% + ${segment.isStart ? '4px' : '0px'})`,
                        width: `calc(${(segment.span / 7) * 100}% - ${(segment.isStart ? 4 : 0) + (segment.isEnd ? 4 : 0)}px)`,
                        top: `${32 + segment.lane * 22}px`,
                        height: '20px',
                    }"
                    @click.stop="emit('open-details', segment.appointment)"
                    @dragstart="(e) => startSegmentDrag(e, segment.appointment)"
                    @dragend="resetMonthDrag"
                >
                    <span
                        v-if="segment.isStart"
                        class="truncate px-1.5 font-medium"
                    >
                        {{ segment.appointment.title }}
                    </span>
                    <span v-else class="truncate px-1.5 font-medium opacity-0">
                        {{ segment.appointment.title }}
                    </span>
                </button>
            </div>
        </div>
    </div>
</template>
