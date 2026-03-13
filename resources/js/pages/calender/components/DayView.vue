<script setup lang="ts">
import { computed } from 'vue';

import type { Appointment } from '../types';

const props = defineProps<{
    selectedDate: Date;
    selectedAppointments: Appointment[];
    formatTime: (value: string | number) => string;
    formatFullDate: (value: Date) => string;
    getEventBgClass: (appointment: Appointment) => string;
}>();

const emit = defineEmits<{
    (e: 'open-details', appointment: Appointment): void;
}>();

const hours = computed(() => {
    return Array.from({ length: 24 }, (_, i) => {
        const hour = i.toString().padStart(2, '0');
        return { value: i, label: `${hour}:00` };
    });
});

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

                <div class="relative overflow-hidden">
                    <div
                        v-for="hour in hours"
                        :key="`grid-${hour.value}`"
                        class="h-12 border-b"
                    />

                    <div class="absolute inset-0">
                        <button
                            v-for="appointment in selectedAppointments"
                            :key="appointment.id"
                            type="button"
                            class="absolute left-1 right-1 flex flex-col gap-0.5 overflow-hidden rounded-lg px-3 py-2 text-left text-white shadow-sm transition-opacity hover:opacity-90"
                            :class="getEventBgClass(appointment)"
                            :style="getAppointmentPosition(appointment)"
                            @click="emit('open-details', appointment)"
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
