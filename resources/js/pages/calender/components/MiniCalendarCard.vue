<script setup lang="ts">
import { ChevronLeft, ChevronRight } from 'lucide-vue-next';

import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

import type { CalendarDay } from '../types';

defineProps<{
    monthLabel: string;
    dayLabels: string[];
    calendarDays: CalendarDay[];
}>();

const emit = defineEmits<{
    (e: 'select-day', date: Date): void;
    (e: 'prev-month'): void;
    (e: 'next-month'): void;
}>();
</script>

<template>
    <Card>
        <CardHeader
            class="flex flex-row items-center justify-between space-y-0 pb-2"
        >
            <CardTitle class="text-sm font-medium">Datum auswählen</CardTitle>
            <div class="flex items-center gap-1">
                <Button
                    variant="ghost"
                    size="icon"
                    class="h-7 w-7"
                    @click="emit('prev-month')"
                >
                    <ChevronLeft class="h-4 w-4" />
                </Button>
                <Button
                    variant="ghost"
                    size="icon"
                    class="h-7 w-7"
                    @click="emit('next-month')"
                >
                    <ChevronRight class="h-4 w-4" />
                </Button>
            </div>
        </CardHeader>
        <CardContent class="space-y-2">
            <div class="text-xs text-muted-foreground">
                {{ monthLabel }}
            </div>
            <div class="grid grid-cols-7 gap-1 text-xs">
                <div
                    v-for="label in dayLabels"
                    :key="label"
                    class="text-center text-[10px] text-muted-foreground uppercase"
                >
                    {{ label }}
                </div>
                <button
                    v-for="day in calendarDays"
                    :key="`mini-${day.key}`"
                    type="button"
                    class="h-7 w-full rounded-md text-xs transition-colors"
                    :class="[
                        day.isSelected
                            ? 'bg-primary text-primary-foreground'
                            : day.isToday
                              ? 'bg-primary/10 text-primary'
                              : day.isCurrentMonth
                                ? 'hover:bg-muted'
                                : 'text-muted-foreground/60 hover:bg-muted/40',
                    ]"
                    @click="emit('select-day', day.date)"
                >
                    {{ day.date.getDate() }}
                </button>
            </div>
        </CardContent>
    </Card>
</template>
