<script setup lang="ts">
import { Separator } from '@/components/ui/separator';

import type { Appointment, CalendarDay, ViewMode, WeekDay } from '../types';

defineProps<{
    viewMode: ViewMode;
    dayLabels: string[];
    calendarDays: CalendarDay[];
    weekDays: WeekDay[];
    selectedDate: Date;
    selectedAppointments: Appointment[];
    formatDate: (value: Date) => string;
    formatTime: (value: string | number) => string;
    getEventClass: (appointment: Appointment) => string;
}>();

const emit = defineEmits<{
    (e: 'select-day', date: Date): void;
    (e: 'open-details', appointment: Appointment): void;
}>();
</script>

<template>
    <div class="overflow-hidden rounded-3xl border bg-card shadow-sm">
        <div
            v-if="viewMode !== 'day'"
            class="grid grid-cols-7 border-b bg-muted/20 text-xs text-muted-foreground"
        >
            <div
                v-for="label in dayLabels"
                :key="label"
                class="px-2 py-3 text-center font-medium"
            >
                {{ label }}
            </div>
        </div>

        <div
            v-if="viewMode === 'month'"
            class="grid grid-cols-7 gap-px bg-border/70"
        >
            <div
                v-for="day in calendarDays"
                :key="day.key"
                class="bg-card p-2"
            >
                <div
                    class="flex min-h-[132px] flex-col gap-2 rounded-2xl border p-2 transition hover:border-primary/40"
                    :class="[
                        day.isSelected
                            ? 'border-primary/60 ring-1 ring-primary/20'
                            : 'border-border/60',
                        !day.isCurrentMonth ? 'bg-muted/40' : '',
                    ]"
                    @click="emit('select-day', day.date)"
                >
                    <div class="flex items-center justify-between">
                        <span
                            class="text-xs font-semibold"
                            :class="
                                day.isToday
                                    ? 'text-primary'
                                    : day.isCurrentMonth
                                      ? 'text-foreground'
                                      : 'text-muted-foreground'
                            "
                        >
                            {{ day.date.getDate() }}
                        </span>
                        <span
                            v-if="day.isToday"
                            class="rounded-full bg-primary/10 px-2 py-0.5 text-[10px] font-medium text-primary"
                        >
                            Heute
                        </span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <button
                            v-for="appointment in day.appointments.slice(0, 2)"
                            :key="appointment.id"
                            type="button"
                            class="group flex items-center justify-between gap-2 rounded-xl border px-2 py-1 text-left text-[11px] font-medium"
                            :class="getEventClass(appointment)"
                            @click.stop="emit('open-details', appointment)"
                        >
                            <span class="truncate">
                                {{ appointment.title }}
                            </span>
                            <span class="text-[10px] font-normal opacity-70">
                                {{ formatTime(appointment.start_time) }}
                            </span>
                        </button>
                        <span
                            v-if="day.appointments.length > 2"
                            class="text-[11px] text-muted-foreground"
                        >
                            +{{ day.appointments.length - 2 }} weitere
                        </span>
                        <span
                            v-if="day.appointments.length === 0"
                            class="text-[11px] text-muted-foreground"
                        >
                            Keine Termine
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div
            v-else-if="viewMode === 'week'"
            class="grid grid-cols-7 gap-px bg-border/70"
        >
            <div
                v-for="day in weekDays"
                :key="`week-${day.key}`"
                class="bg-card p-3"
            >
                <div
                    class="flex h-full flex-col gap-2 rounded-2xl border p-3"
                    :class="
                        day.isSelected
                            ? 'border-primary/60 ring-1 ring-primary/20'
                            : 'border-border/60'
                    "
                    @click="emit('select-day', day.date)"
                >
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold">
                            {{ formatDate(day.date) }}
                        </span>
                        <span
                            v-if="day.isToday"
                            class="rounded-full bg-primary/10 px-2 py-0.5 text-[10px] font-medium text-primary"
                        >
                            Heute
                        </span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <button
                            v-for="appointment in day.appointments"
                            :key="appointment.id"
                            type="button"
                            class="flex items-center justify-between gap-2 rounded-xl border px-2 py-1 text-left text-[11px] font-medium"
                            :class="getEventClass(appointment)"
                            @click.stop="emit('open-details', appointment)"
                        >
                            <span class="truncate">
                                {{ appointment.title }}
                            </span>
                            <span class="text-[10px] font-normal opacity-70">
                                {{ formatTime(appointment.start_time) }}
                            </span>
                        </button>
                        <span
                            v-if="day.appointments.length === 0"
                            class="text-[11px] text-muted-foreground"
                        >
                            Keine Termine
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div v-else class="p-6">
            <div class="space-y-1">
                <div class="text-sm text-muted-foreground">Tagesansicht</div>
                <div class="text-lg font-semibold">
                    {{ formatDate(selectedDate) }}
                </div>
            </div>

            <Separator class="my-4" />

            <div class="space-y-3">
                <button
                    v-for="appointment in selectedAppointments"
                    :key="`day-${appointment.id}`"
                    type="button"
                    class="flex w-full items-start justify-between gap-4 rounded-2xl border p-3 text-left"
                    :class="getEventClass(appointment)"
                    @click="emit('open-details', appointment)"
                >
                    <div>
                        <div class="text-sm font-semibold">
                            {{ appointment.title }}
                        </div>
                        <div class="text-xs text-muted-foreground">
                            {{ appointment.description || 'Keine Beschreibung' }}
                        </div>
                    </div>
                    <div class="text-xs text-muted-foreground">
                        {{ formatTime(appointment.start_time) }} -
                        {{ formatTime(appointment.end_time) }}
                    </div>
                </button>

                <div
                    v-if="selectedAppointments.length === 0"
                    class="rounded-2xl border border-dashed p-6 text-sm text-muted-foreground"
                >
                    Keine Termine f&uuml;r diesen Tag.
                </div>
            </div>
        </div>
    </div>
</template>
