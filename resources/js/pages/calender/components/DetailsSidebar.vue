<script setup lang="ts">
import { Calendar, Clock, MapPin, Plus, User } from 'lucide-vue-next';

import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';

import type { Appointment, ViewMode } from '../types';

defineProps<{
    selectedDate: Date;
    selectedAppointments: Appointment[];
    selectedAppointment: Appointment | null;
    upcomingAppointments: Appointment[];
    formatDate: (value: Date) => string;
    formatTime: (value: string | number) => string;
    parseDate: (value: string | number) => Date | null;
    getEventClass: (appointment: Appointment) => string;
    truncateWords: (value: string, maxWords?: number) => string;
    getOwnerName: (appointment: Appointment) => string;
}>();

const emit = defineEmits<{
    (e: 'open-create'): void;
    (e: 'set-view-mode', mode: ViewMode): void;
    (e: 'open-edit', appointment: Appointment): void;
    (e: 'open-details', appointment: Appointment): void;
}>();
</script>

<template>
    <aside class="flex flex-col gap-6">
        <Card>
            <CardHeader>
                <CardTitle class="text-sm font-medium">
                    Ausgewähltes Datum
                </CardTitle>
            </CardHeader>
            <CardContent class="space-y-4">
                <div class="rounded-md border bg-muted/20 p-3">
                    <div class="text-xs text-muted-foreground">Datum</div>
                    <div class="text-sm font-semibold">
                        {{ formatDate(selectedDate) }}
                    </div>
                    <div class="text-xs text-muted-foreground">
                        {{ selectedAppointments.length }} Termin(e)
                    </div>
                </div>
                <div class="space-y-2">
                    <Button
                        variant="outline"
                        class="w-full"
                        @click="emit('open-create')"
                    >
                        <Plus class="h-4 w-4" />
                        Neuer Termin
                    </Button>
                    <Button
                        variant="ghost"
                        class="w-full"
                        @click="emit('set-view-mode', 'day')"
                    >
                        <Calendar class="h-4 w-4" />
                        Tagesansicht
                    </Button>
                </div>
                <Separator />
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <div
                            class="text-xs font-semibold tracking-wide text-muted-foreground uppercase"
                        >
                            Termindetails
                        </div>
                        <Button
                            v-if="selectedAppointment"
                            variant="outline"
                            size="sm"
                            @click="emit('open-edit', selectedAppointment)"
                        >
                            Bearbeiten
                        </Button>
                    </div>
                    <div
                        v-if="selectedAppointment"
                        class="space-y-2 rounded-md border p-3"
                    >
                        <div class="flex items-start justify-between gap-3">
                            <div class="text-sm font-semibold">
                                {{ selectedAppointment.title }}
                            </div>
                            <Badge
                                variant="outline"
                                :class="getEventClass(selectedAppointment)"
                            >
                                {{
                                    formatTime(
                                        selectedAppointment.start_time,
                                    )
                                }}
                            </Badge>
                        </div>
                        <div class="text-xs text-muted-foreground">
                            {{
                                selectedAppointment.description
                                    ? truncateWords(
                                          selectedAppointment.description,
                                      )
                                    : 'Noch keine Beschreibung.'
                            }}
                        </div>
                        <div
                            class="flex flex-col gap-2 text-xs text-muted-foreground"
                        >
                            <div class="flex items-center gap-2">
                                <Clock class="h-4 w-4" />
                                {{
                                    formatTime(
                                        selectedAppointment.start_time,
                                    )
                                }}
                                -
                                {{ formatTime(selectedAppointment.end_time) }}
                            </div>
                            <div class="flex items-center gap-2">
                                <MapPin class="h-4 w-4" />
                                {{
                                    selectedAppointment.location ||
                                    'Kein Ort angegeben'
                                }}
                            </div>
                            <div class="flex items-center gap-2">
                                <User class="h-4 w-4" />
                                Besitzer {{ getOwnerName(selectedAppointment) }}
                            </div>
                        </div>
                    </div>
                    <div
                        v-else
                        class="rounded-md border border-dashed p-4 text-xs text-muted-foreground"
                    >
                        Termin auswählen, um Details zu sehen.
                    </div>
                </div>
            </CardContent>
        </Card>

        <Card>
            <CardHeader>
                <CardTitle class="text-sm font-medium">
                    Kommende Termine
                </CardTitle>
            </CardHeader>
            <CardContent class="space-y-3">
                <div
                    v-for="appointment in upcomingAppointments"
                    :key="`upcoming-${appointment.id}`"
                    class="flex items-start justify-between gap-3 rounded-md border p-3 text-sm"
                    :class="getEventClass(appointment)"
                    @click="emit('open-details', appointment)"
                >
                    <div class="space-y-1">
                        <div class="font-semibold">
                            {{ appointment.title }}
                        </div>
                        <div
                            class="flex items-center gap-1 text-xs text-muted-foreground"
                        >
                            <Clock class="h-3.5 w-3.5" />
                            {{ formatTime(appointment.start_time) }}
                        </div>
                    </div>
                    <Badge variant="outline" class="text-[10px]">
                        {{
                            formatDate(
                                parseDate(appointment.start_time) ||
                                    new Date(),
                            )
                        }}
                    </Badge>
                </div>
                <div
                    v-if="upcomingAppointments.length === 0"
                    class="rounded-md border border-dashed p-4 text-xs text-muted-foreground"
                >
                    Keine kommenden Termine.
                </div>
            </CardContent>
        </Card>
    </aside>
</template>
