<script setup lang="ts">
import { Calendar, Clock, MapPin, Plus, Trash2, User } from 'lucide-vue-next';
import { ref } from 'vue';

import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import AppoitmentDeleteAlert from '@/pages/calender/components/AppoitmentDeleteAlert.vue';

import type { Appointment, ViewMode } from '../types';

const props = defineProps<{
    selectedDate: Date;
    selectedAppointments: Appointment[];
    selectedAppointment: Appointment | null;
    upcomingAppointments: Appointment[];
    formatDate: (value: Date) => string;
    formatTime: (value: string | number) => string;
    parseDate: (value: string | number) => Date | null;
    getEventClass: (appointment: Appointment) => string;
    getOwnerName: (appointment: Appointment) => string;
}>();

const emit = defineEmits<{
    (e: 'open-create'): void;
    (e: 'set-view-mode', mode: ViewMode): void;
    (e: 'open-edit', appointment: Appointment): void;
    (e: 'open-details', appointment: Appointment): void;
    (e: 'deleted'): void;
}>();

const showDeleteAlert = ref(false);

const handleDeleted = () => {
    showDeleteAlert.value = false;
    emit('deleted');
};

const isSelectedAppointment = (appointment: Appointment) => {
    return props.selectedAppointment?.id === appointment.id;
};

const getAgendaPreview = (appointment: Appointment) => {
    return (
        appointment.location ||
        appointment.description ||
        'Keine weiteren Angaben'
    );
};
</script>

<template>
    <aside class="flex flex-col gap-4 lg:sticky lg:top-6">
        <Card class="overflow-hidden">
            <CardHeader class="space-y-4">
                <div class="flex items-start justify-between gap-3">
                    <div class="space-y-1">
                        <div
                            class="text-xs font-semibold tracking-[0.24em] text-muted-foreground uppercase"
                        >
                            Agenda
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
                    class="text-xs font-semibold tracking-wide text-muted-foreground uppercase"
                >
                    Tagesagenda
                </div>
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
                    :class="[
                        getEventClass(appointment),
                        isSelectedAppointment(appointment)
                            ? 'border-primary/50 ring-1 ring-primary/25'
                            : '',
                    ]"
                    @click="emit('open-details', appointment)"
                >
                    <div class="flex items-start justify-between gap-3">
                        <div class="space-y-1">
                            <div class="text-sm font-semibold">
                                {{ appointment.title }}
                            </div>
                            <div class="text-xs text-muted-foreground">
                                {{ getAgendaPreview(appointment) }}
                            </div>
                        </div>
                        <Badge variant="outline" class="text-[10px]">
                            {{ formatTime(appointment.start_time) }}
                        </Badge>
                    </div>
                </button>
            </CardContent>
        </Card>

        <Card>
            <CardHeader class="space-y-4">
                <div class="space-y-1">
                    <div
                        class="text-xs font-semibold tracking-[0.24em] text-muted-foreground uppercase"
                    >
                        Details
                    </div>
                    <CardTitle class="text-lg">
                        Ausgewaehlter Termin
                    </CardTitle>
                </div>
                <div
                    v-if="selectedAppointment"
                    class="flex flex-wrap items-center gap-2"
                >
                    <Button
                        variant="outline"
                        size="sm"
                        @click="emit('open-edit', selectedAppointment)"
                    >
                        Bearbeiten
                    </Button>
                    <Button
                        variant="destructive"
                        size="sm"
                        @click="showDeleteAlert = true"
                    >
                        <Trash2 class="h-4 w-4" />
                        Loeschen
                    </Button>
                </div>
            </CardHeader>
            <CardContent>
                <div
                    v-if="selectedAppointment"
                    class="space-y-4 rounded-xl border p-4"
                >
                    <div class="flex items-start justify-between gap-3">
                        <div class="text-base font-semibold">
                            {{ selectedAppointment.title }}
                        </div>
                        <Badge
                            variant="outline"
                            :class="getEventClass(selectedAppointment)"
                        >
                            {{ formatTime(selectedAppointment.start_time) }}
                        </Badge>
                    </div>

                    <div class="grid gap-3 text-sm text-muted-foreground">
                        <div class="flex items-center gap-2">
                            <Calendar class="h-4 w-4" />
                            {{
                                formatDate(
                                    parseDate(selectedAppointment.start_time) ||
                                        selectedDate,
                                )
                            }}
                        </div>
                        <div class="flex items-center gap-2">
                            <Clock class="h-4 w-4" />
                            {{ formatTime(selectedAppointment.start_time) }} -
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

                    <Separator />

                    <div class="space-y-2">
                        <div
                            class="text-xs font-semibold tracking-wide text-muted-foreground uppercase"
                        >
                            Beschreibung
                        </div>
                        <p class="whitespace-pre-wrap text-sm text-muted-foreground">
                            {{
                                selectedAppointment.description ||
                                'Noch keine Beschreibung.'
                            }}
                        </p>
                    </div>
                </div>
                <div
                    v-else
                    class="rounded-xl border border-dashed p-4 text-sm text-muted-foreground"
                >
                    Waehle links einen Termin aus, um Details zu sehen.
                </div>
            </CardContent>
        </Card>

        <Card>
            <CardHeader>
                <CardTitle class="text-base">Naechste Termine</CardTitle>
            </CardHeader>
            <CardContent class="space-y-3">
                <button
                    v-for="appointment in upcomingAppointments"
                    :key="`upcoming-${appointment.id}`"
                    type="button"
                    class="flex w-full items-start justify-between gap-3 rounded-xl border p-3 text-left text-sm"
                    :class="getEventClass(appointment)"
                    @click="emit('open-details', appointment)"
                >
                    <div class="space-y-1">
                        <div class="font-semibold">
                            {{ appointment.title }}
                        </div>
                        <div class="text-xs text-muted-foreground">
                            {{ formatTime(appointment.start_time) }}
                        </div>
                    </div>
                    <Badge variant="outline" class="text-[10px]">
                        {{
                            formatDate(
                                parseDate(appointment.start_time) || new Date(),
                            )
                        }}
                    </Badge>
                </button>
                <div
                    v-if="upcomingAppointments.length === 0"
                    class="rounded-xl border border-dashed p-4 text-sm text-muted-foreground"
                >
                    Keine kommenden Termine.
                </div>
            </CardContent>
        </Card>

        <AppoitmentDeleteAlert
            v-if="props.selectedAppointment"
            v-model:open="showDeleteAlert"
            :appointment="props.selectedAppointment"
            @deleted="handleDeleted"
        />
    </aside>
</template>
