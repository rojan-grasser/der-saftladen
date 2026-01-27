<script setup lang="ts">
import { Plus } from 'lucide-vue-next';

import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';

import type { AgendaGroup, Appointment, CalendarDay, ViewMode, WeekDay } from '../types';

defineProps<{
    viewMode: ViewMode;
    dayLabels: string[];
    calendarDays: CalendarDay[];
    weekDays: WeekDay[];
    selectedDate: Date;
    selectedAppointments: Appointment[];
    agendaGroups: AgendaGroup[];
    sortedAppointmentsCount: number;
    formatDate: (value: Date) => string;
    formatTime: (value: string | number) => string;
    getEventClass: (appointment: Appointment) => string;
}>();

const emit = defineEmits<{
    (e: 'select-day', date: Date): void;
    (e: 'open-details', appointment: Appointment): void;
    (e: 'open-create'): void;
}>();
</script>

<template>
    <div class="rounded-lg border bg-card">
        <div
            v-if="viewMode !== 'agenda' && viewMode !== 'day'"
            class="grid grid-cols-7 border-b text-xs text-muted-foreground"
        >
            <div
                v-for="label in dayLabels"
                :key="label"
                class="px-2 py-3 text-center font-medium"
            >
                {{ label }}
            </div>
        </div>

        <div v-if="viewMode === 'month'" class="grid grid-cols-7 gap-px bg-border">
            <div
                v-for="day in calendarDays"
                :key="day.key"
                class="bg-card p-2"
            >
                <div
                    class="flex min-h-[120px] flex-col gap-2 rounded-md border p-2 transition hover:border-primary/40"
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
                            class="group flex items-center justify-between gap-2 rounded-md border px-2 py-1 text-left text-[11px] font-medium"
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

        <div v-else-if="viewMode === 'week'" class="grid grid-cols-7 gap-px bg-border">
            <div
                v-for="day in weekDays"
                :key="`week-${day.key}`"
                class="bg-card p-3"
            >
                <div
                    class="flex flex-col gap-2 rounded-md border p-2"
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
                            class="flex items-center justify-between gap-2 rounded-md border px-2 py-1 text-left text-[11px] font-medium"
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

        <div v-else-if="viewMode === 'day'" class="p-6">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm text-muted-foreground">Tagesansicht</div>
                    <div class="text-lg font-semibold">
                        {{ formatDate(selectedDate) }}
                    </div>
                </div>
                <Button variant="outline" size="sm" @click="emit('open-create')">
                    <Plus class="h-4 w-4" />
                    Hinzufügen
                </Button>
            </div>
            <Separator class="my-4" />
            <div class="space-y-3">
                <div
                    v-for="appointment in selectedAppointments"
                    :key="`day-${appointment.id}`"
                    class="flex items-start justify-between gap-4 rounded-md border p-3"
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
                </div>
                <div
                    v-if="selectedAppointments.length === 0"
                    class="rounded-md border border-dashed p-6 text-sm text-muted-foreground"
                >
                    Keine Termine für diesen Tag.
                </div>
            </div>
        </div>

        <div v-else class="p-6">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm text-muted-foreground">Agenda</div>
                    <div class="text-lg font-semibold">Kommende Termine</div>
                </div>
                <Badge variant="outline">
                    {{ sortedAppointmentsCount }} gesamt
                </Badge>
            </div>
            <Separator class="my-4" />
            <div
                v-if="agendaGroups.length === 0"
                class="rounded-md border border-dashed p-6 text-sm text-muted-foreground"
            >
                Keine Termine gefunden.
            </div>
            <div v-else class="space-y-4">
                <div
                    v-for="group in agendaGroups"
                    :key="`agenda-${group.key}`"
                    class="space-y-2"
                >
                    <div
                        class="text-xs font-semibold tracking-wide text-muted-foreground uppercase"
                    >
                        {{ formatDate(group.date) }}
                    </div>
                    <div class="grid gap-2">
                        <div
                            v-for="appointment in group.items"
                            :key="`agenda-item-${appointment.id}`"
                            class="flex flex-wrap items-center justify-between gap-3 rounded-md border px-3 py-2"
                            :class="getEventClass(appointment)"
                            @click="emit('open-details', appointment)"
                        >
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-semibold">
                                    {{ appointment.title }}
                                </span>
                                <Badge variant="outline" class="text-[10px]">
                                    {{ formatTime(appointment.start_time) }}
                                </Badge>
                            </div>
                            <div class="text-xs text-muted-foreground">
                                {{ appointment.location || 'Kein Ort angegeben' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
