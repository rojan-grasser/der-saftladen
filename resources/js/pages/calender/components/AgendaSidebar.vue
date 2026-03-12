<script setup lang="ts">
import { Calendar } from 'lucide-vue-next';

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
    (e: 'open-details', appointment: Appointment): void;
    (e: 'set-view-mode', mode: ViewMode): void;
}>();
</script>

<template>
    <aside
        class="flex min-h-0 flex-col gap-4 rounded-3xl border bg-card p-4 shadow-sm xl:sticky xl:top-6 xl:h-[calc(100vh-10rem)]"
    >
        <div class="space-y-1">
            <div
                class="text-xs font-semibold tracking-[0.24em] text-muted-foreground uppercase"
            >
                &Uuml;bersicht
            </div>
            <div class="text-lg font-semibold">
                {{ formatDate(selectedDate) }}
            </div>
            <p class="text-sm text-muted-foreground">
                {{ selectedAppointments.length }} Termin(e) f&uuml;r den
                ausgew&auml;hlten Tag
            </p>
        </div>

        <div class="flex flex-wrap gap-2">
            <Button
                variant="outline"
                size="sm"
                class="rounded-xl"
                @click="emit('set-view-mode', 'day')"
            >
                <Calendar class="h-4 w-4" />
                Tag
            </Button>
        </div>

        <div class="min-h-0 space-y-3 overflow-y-auto pr-1">
            <div
                v-if="selectedAppointments.length === 0"
                class="rounded-2xl border border-dashed p-4 text-sm text-muted-foreground"
            >
                Keine Termine f&uuml;r den ausgew&auml;hlten Tag.
            </div>

            <button
                v-for="appointment in selectedAppointments"
                :key="`selected-${appointment.id}`"
                type="button"
                class="flex w-full flex-col gap-2 rounded-2xl border p-3 text-left transition hover:border-primary/50"
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
