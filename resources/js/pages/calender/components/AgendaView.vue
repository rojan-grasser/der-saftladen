<script setup lang="ts">
import { computed } from 'vue';
import { Calendar, Clock, MapPin } from 'lucide-vue-next';

import MarkdownContent from '@/components/MarkdownContent.vue';
import type { Appointment } from '../types';
import { parseDate, toDateKey } from '../utils/date';

const props = defineProps<{
    appointments: Appointment[];
    formatTime: (value: string | number) => string;
    getEventBgClass: (appointment: Appointment) => string;
    getOwnerName: (appointment: Appointment) => string;
}>();

const emit = defineEmits<{
    (e: 'open-details', appointment: Appointment): void;
}>();

const groupedAppointments = computed(() => {
    const now = new Date();
    now.setHours(0, 0, 0, 0);

    const upcoming = props.appointments
        .filter((apt) => {
            const aptDate = parseDate(apt.start_time);
            if (!aptDate) return false;
            const dayStart = new Date(aptDate);
            dayStart.setHours(0, 0, 0, 0);
            return dayStart >= now;
        })
        .sort((a, b) => {
            const aDate = parseDate(a.start_time);
            const bDate = parseDate(b.start_time);
            return (aDate?.getTime() ?? 0) - (bDate?.getTime() ?? 0);
        });

    const groups: { date: Date; dateKey: string; label: string; appointments: Appointment[] }[] = [];

    for (const apt of upcoming) {
        const aptDate = parseDate(apt.start_time);
        if (!aptDate) continue;

        const dateKey = toDateKey(aptDate);

        let group = groups.find((g) => g.dateKey === dateKey);
        if (!group) {
            group = {
                date: new Date(aptDate.getFullYear(), aptDate.getMonth(), aptDate.getDate()),
                dateKey,
                label: formatDateLabel(aptDate),
                appointments: [],
            };
            groups.push(group);
        }
        group.appointments.push(apt);
    }

    return groups;
});

function formatDateLabel(date: Date): string {
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    const tomorrow = new Date(today);
    tomorrow.setDate(tomorrow.getDate() + 1);

    const dateOnly = new Date(date.getFullYear(), date.getMonth(), date.getDate());

    if (dateOnly.getTime() === today.getTime()) {
        return 'Heute';
    }
    if (dateOnly.getTime() === tomorrow.getTime()) {
        return 'Morgen';
    }

    return new Intl.DateTimeFormat('de-DE', {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    }).format(date);
}
</script>

<template>
    <div class="flex h-full flex-col">
        <div class="border-b px-4 py-4">
            <h2 class="text-xl font-semibold">Agenda</h2>
            <p class="text-sm text-muted-foreground">
                Alle kommenden Termine
            </p>
        </div>

        <div class="flex-1 overflow-auto">
            <div v-if="groupedAppointments.length === 0" class="flex h-full items-center justify-center p-8">
                <p class="text-center text-muted-foreground">
                    Keine kommenden Termine
                </p>
            </div>

            <div v-else class="divide-y">
                <div
                    v-for="group in groupedAppointments"
                    :key="group.dateKey"
                    class="p-4"
                >
                    <div class="mb-3 flex items-center gap-2">
                        <Calendar class="h-4 w-4 text-muted-foreground" />
                        <h3 class="text-sm font-semibold text-muted-foreground">
                            {{ group.label }}
                        </h3>
                    </div>

                    <div class="space-y-2 pl-6">
                        <button
                            v-for="apt in group.appointments"
                            :key="apt.id"
                            type="button"
                            class="flex w-full items-start gap-3 rounded-lg border bg-card p-3 text-left transition-colors hover:bg-muted/50"
                            @click="emit('open-details', apt)"
                        >
                            <div
                                class="mt-1.5 h-3 w-3 flex-shrink-0 rounded-full"
                                :class="getEventBgClass(apt)"
                            />
                            <div class="flex-1 min-w-0">
                                <p class="font-medium">{{ apt.title }}</p>
                                <div class="mt-1 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-muted-foreground">
                                    <span class="flex items-center gap-1">
                                        <Clock class="h-3 w-3" />
                                        {{ formatTime(apt.start_time) }} - {{ formatTime(apt.end_time) }}
                                    </span>
                                    <span v-if="apt.location" class="flex items-center gap-1">
                                        <MapPin class="h-3 w-3" />
                                        {{ apt.location }}
                                    </span>
                                    <span class="text-muted-foreground/70">
                                        {{ getOwnerName(apt) }}
                                    </span>
                                </div>
                                <div
                                    v-if="apt.description"
                                    class="mt-2 max-h-[4.5rem] overflow-hidden text-xs"
                                >
                                    <MarkdownContent
                                        :content="apt.description"
                                        class="text-xs text-muted-foreground"
                                    />
                                </div>
                            </div>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
