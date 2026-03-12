<script setup lang="ts">
import { computed, ref } from 'vue';
import { Calendar, Clock, MapPin, Trash2, User } from 'lucide-vue-next';

import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import AppoitmentDeleteAlert from '@/pages/calender/components/AppoitmentDeleteAlert.vue';

import type { Appointment } from '../types';

const props = defineProps<{
    selectedAppointment: Appointment | null;
    upcomingAppointments: Appointment[];
    formatDate: (value: Date) => string;
    formatTime: (value: string | number) => string;
    parseDate: (value: string | number) => Date | null;
    getEventClass: (appointment: Appointment) => string;
    getOwnerName: (appointment: Appointment) => string;
}>();

const emit = defineEmits<{
    (e: 'open-edit', appointment: Appointment): void;
    (e: 'open-details', appointment: Appointment): void;
    (e: 'deleted'): void;
}>();

const showDeleteAlert = ref(false);

const handleDeleted = () => {
    showDeleteAlert.value = false;
    emit('deleted');
};

const nextAppointment = computed(() => {
    return props.upcomingAppointments[0] ?? null;
});
</script>

<template>
    <aside class="flex flex-col gap-4 xl:sticky xl:top-6">
        <Card>
            <CardHeader class="space-y-4">
                <div class="space-y-1">
                    <div
                        class="text-xs font-semibold tracking-[0.24em] text-muted-foreground uppercase"
                    >
                        Details
                    </div>
                    <CardTitle class="text-lg">
                        Ausgewählter Termin
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
                        Löschen
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
                                        new Date(),
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
                    Wähle links einen Termin aus, um Details zu sehen.
                </div>
            </CardContent>
        </Card>

        <Card>
            <CardHeader>
                <CardTitle class="text-base">Nächster Termin</CardTitle>
            </CardHeader>
            <CardContent class="space-y-3">
                <button
                    v-if="nextAppointment"
                    :key="`upcoming-${nextAppointment.id}`"
                    type="button"
                    class="flex w-full items-start justify-between gap-3 rounded-xl border p-3 text-left text-sm"
                    :class="getEventClass(nextAppointment)"
                    @click="emit('open-details', nextAppointment)"
                >
                    <div class="space-y-1">
                        <div class="font-semibold">
                            {{ nextAppointment.title }}
                        </div>
                        <div class="text-xs text-muted-foreground">
                            {{ formatTime(nextAppointment.start_time) }}
                        </div>
                    </div>
                    <Badge variant="outline" class="text-[10px]">
                        {{
                            formatDate(
                                parseDate(nextAppointment.start_time) ||
                                    new Date(),
                            )
                        }}
                    </Badge>
                </button>
                <div
                    v-else
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
