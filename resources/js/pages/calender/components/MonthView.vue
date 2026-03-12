<script setup lang="ts">
import type { Appointment, CalendarDay } from '../types';

defineProps<{
    dayLabels: string[];
    calendarDays: CalendarDay[];
    formatTime: (value: string | number) => string;
    getEventBgClass: (appointment: Appointment) => string;
}>();

const emit = defineEmits<{
    (e: 'select-day', date: Date): void;
    (e: 'open-details', appointment: Appointment): void;
}>();
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

        <div class="grid flex-1 grid-cols-7 grid-rows-6">
            <div
                v-for="day in calendarDays"
                :key="day.key"
                class="group relative min-h-[100px] cursor-pointer border-b border-r p-1 transition-colors last:border-r-0 hover:bg-muted/30"
                :class="[
                    !day.isCurrentMonth ? 'bg-muted/20' : '',
                    day.isSelected ? 'bg-primary/5' : '',
                ]"
                @click="emit('select-day', day.date)"
            >
                <div class="mb-1 flex items-center justify-center">
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

                <div class="space-y-0.5">
                    <button
                        v-for="appointment in day.appointments.slice(0, 3)"
                        :key="appointment.id"
                        type="button"
                        class="flex w-full items-center gap-1 rounded px-1 py-0.5 text-left text-[11px] text-white transition-opacity hover:opacity-80"
                        :class="getEventBgClass(appointment)"
                        @click.stop="emit('open-details', appointment)"
                    >
                        <span class="truncate font-medium">
                            {{ appointment.title }}
                        </span>
                    </button>

                    <button
                        v-if="day.appointments.length > 3"
                        type="button"
                        class="w-full rounded px-1 py-0.5 text-left text-[10px] font-medium text-muted-foreground hover:bg-muted"
                        @click.stop="emit('select-day', day.date)"
                    >
                        +{{ day.appointments.length - 3 }} weitere
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
