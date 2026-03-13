<script setup lang="ts">
import { computed } from 'vue';

import type { Appointment, WeekDay } from '../types';

const props = defineProps<{
    weekDays: WeekDay[];
    dayLabels: string[];
    formatTime: (value: string | number) => string;
    getEventBgClass: (appointment: Appointment) => string;
}>();

const emit = defineEmits<{
    (e: 'select-day', date: Date): void;
    (e: 'open-details', appointment: Appointment): void;
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

const getAppointmentPosition = (appointment: Appointment) => {
    const startDate = new Date(appointment.start_time);
    const endDate = new Date(appointment.end_time);
    const startHour = startDate.getHours() + startDate.getMinutes() / 60;
    const endHour = endDate.getHours() + endDate.getMinutes() / 60;
    const duration = Math.max(endHour - startHour, 0.5);

    return {
        top: `${startHour * 48}px`,
        height: `${duration * 48}px`,
    };
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
                    class="relative border-r last:border-r-0"
                    :class="day.isSelected ? 'bg-primary/5' : ''"
                >
                    <div
                        v-for="hour in hours"
                        :key="`${day.key}-${hour}`"
                        class="h-12 border-b"
                    />

                    <div class="absolute inset-0">
                        <button
                            v-for="appointment in day.appointments"
                            :key="appointment.id"
                            type="button"
                            class="absolute left-0.5 right-0.5 overflow-hidden rounded px-1 py-0.5 text-left text-[10px] text-white transition-opacity hover:opacity-80"
                            :class="getEventBgClass(appointment)"
                            :style="getAppointmentPosition(appointment)"
                            @click="emit('open-details', appointment)"
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
