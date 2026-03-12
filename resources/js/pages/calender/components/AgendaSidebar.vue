<script setup lang="ts">
import { Calendar, Plus } from 'lucide-vue-next';

import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

import MiniCalendarCard from './MiniCalendarCard.vue';
import type { Appointment, CalendarDay, ViewMode } from '../types';

defineProps<{
    monthLabel: string;
    dayLabels: string[];
    calendarDays: CalendarDay[];
    selectedDate: Date;
    selectedAppointments: Appointment[];
    formatDate: (value: Date) => string;
    formatTime: (value: string | number) => string;
    getEventClass: (appointment: Appointment) => string;
}>();

const emit = defineEmits<{
    (e: 'select-day', date: Date): void;
    (e: 'prev-month'): void;
    (e: 'next-month'): void;
    (e: 'open-create'): void;
    (e: 'open-details', appointment: Appointment): void;
    (e: 'set-view-mode', mode: ViewMode): void;
}>();
</script>

<template>
    <aside class="flex flex-col gap-4 xl:sticky xl:top-6">
        <MiniCalendarCard
            :month-label="monthLabel"
            :day-labels="dayLabels"
            :calendar-days="calendarDays"
            @select-day="(date) => emit('select-day', date)"
            @prev-month="emit('prev-month')"
            @next-month="emit('next-month')"
        />

        <Card class="overflow-hidden">
            <CardHeader class="space-y-4">
                <div class="flex items-start justify-between gap-3">
                    <div class="space-y-1">
                        <div
                            class="text-xs font-semibold tracking-[0.24em] text-muted-foreground uppercase"
                        >
                            Uebersicht
                        </div>
                        <CardTitle class="text-lg">
                            {{ formatDate(selectedDate) }}
                        </CardTitle>
                        <p class="text-sm text-muted-foreground">
                            {{ selectedAppointments.length }} Termin(e) fuer den
                            ausgewaehlten Tag
                        </p>
                    </div>
                    <Button
                        variant="outline"
                        size="sm"
                        @click="emit('set-view-mode', 'day')"
                    >
                        <Calendar class="h-4 w-4" />
                        Tag
                    </Button>
                </div>
                <Button class="w-full" @click="emit('open-create')">
                    <Plus class="h-4 w-4" />
                    Neuer Termin
                </Button>
            </CardHeader>
            <CardContent class="space-y-3">
                <div
                    v-if="selectedAppointments.length === 0"
                    class="rounded-xl border border-dashed p-4 text-sm text-muted-foreground"
                >
                    Keine Termine fuer den ausgewaehlten Tag.
                </div>
                <button
                    v-for="appointment in selectedAppointments"
                    :key="`selected-${appointment.id}`"
                    type="button"
                    class="flex w-full flex-col gap-2 rounded-xl border p-3 text-left transition hover:border-primary/50"
                    :class="getEventClass(appointment)"
                    @click="emit('open-details', appointment)"
                >
                    <div class="flex items-start justify-between gap-3">
                        <div class="space-y-1">
                            <div class="text-sm font-semibold">
                                {{ appointment.title }}
                            </div>
                            <div class="text-xs text-muted-foreground">
                                {{ formatTime(appointment.start_time) }} -
                                {{ formatTime(appointment.end_time) }}
                            </div>
                        </div>
                        <Badge variant="outline" class="text-[10px]">
                            {{ formatTime(appointment.start_time) }}
                        </Badge>
                    </div>
                </button>
            </CardContent>
        </Card>
    </aside>
</template>
