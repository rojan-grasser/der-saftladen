<script setup lang="ts">
import { Calendar, Plus } from 'lucide-vue-next';

import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';

import type { Appointment, ViewMode } from '../types';

defineProps<{
    selectedDate: Date;
    selectedAppointments: Appointment[];
    formatDate: (value: Date) => string;
    formatTime: (value: string | number) => string;
    getEventClass: (appointment: Appointment) => string;
}>();

const emit = defineEmits<{
    (e: 'open-create'): void;
    (e: 'open-details', appointment: Appointment): void;
    (e: 'set-view-mode', mode: ViewMode): void;
}>();
</script>

<template>
    <aside
        class="flex min-h-0 flex-col gap-4 xl:sticky xl:top-6 xl:h-[calc(100vh-8.5rem)]"
    >
        <div class="space-y-1">
            <div
                class="text-xs font-semibold tracking-[0.24em] text-muted-foreground uppercase"
            >
                Übersicht
            </div>
            <div class="text-lg font-semibold">
                {{ formatDate(selectedDate) }}
            </div>
            <p class="text-sm text-muted-foreground">
                {{ selectedAppointments.length }} Termin(e) für den ausgewählten
                Tag
            </p>
        </div>

        <div class="flex flex-wrap gap-2">
            <Button variant="outline" size="sm" @click="emit('set-view-mode', 'day')">
                <Calendar class="h-4 w-4" />
                Tag
            </Button>
            <Button size="sm" @click="emit('open-create')">
                <Plus class="h-4 w-4" />
                Neuer Termin
            </Button>
        </div>

        <div class="min-h-0 space-y-3 overflow-y-auto pr-1">
            <div
                v-if="selectedAppointments.length === 0"
                class="rounded-xl border border-dashed p-4 text-sm text-muted-foreground"
            >
                Keine Termine für den ausgewählten Tag.
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
        </div>
    </aside>
</template>
