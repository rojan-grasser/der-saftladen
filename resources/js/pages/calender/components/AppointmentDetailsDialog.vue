<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';

import type { Appointment } from '../types';

defineProps<{
    open: boolean;
    appointment: Appointment | null;
    isDeleting?: boolean;
    formatDate: (value: Date) => string;
    formatTime: (value: string | number) => string;
    parseDate: (value: string | number) => Date | null;
    getOwnerName: (appointment: Appointment) => string;
}>();

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void;
    (e: 'edit'): void;
    (e: 'delete'): void;
}>();
</script>

<template>
    <Dialog :open="open" @update:open="emit('update:open', $event)">
        <DialogContent
            class="flex max-h-[85vh] flex-col overflow-hidden sm:max-w-xl"
        >
            <DialogHeader>
                <DialogTitle>
                    {{ appointment ? appointment.title : 'Termin' }}
                </DialogTitle>
                <DialogDescription>Termindetails</DialogDescription>
            </DialogHeader>
            <div class="min-h-0 flex-1 overflow-y-auto pr-2">
                <div v-if="appointment" class="space-y-4">
                    <div class="grid gap-3 text-sm">
                        <div class="flex items-start justify-between gap-4">
                            <span
                                class="text-xs font-semibold tracking-wide text-muted-foreground uppercase"
                            >
                                Datum
                            </span>
                            <div class="text-right font-medium">
                                {{
                                    formatDate(
                                        parseDate(appointment.start_time) ||
                                            new Date(),
                                    )
                                }}
                            </div>
                        </div>
                        <div class="flex items-start justify-between gap-4">
                            <span
                                class="text-xs font-semibold tracking-wide text-muted-foreground uppercase"
                            >
                                Uhrzeit
                            </span>
                            <div class="text-right font-medium">
                                {{ formatTime(appointment.start_time) }} -
                                {{ formatTime(appointment.end_time) }}
                            </div>
                        </div>
                        <div class="flex items-start justify-between gap-4">
                            <span
                                class="text-xs font-semibold tracking-wide text-muted-foreground uppercase"
                            >
                                Ort
                            </span>
                            <div class="text-right font-medium">
                                {{
                                    appointment.location || 'Kein Ort angegeben'
                                }}
                            </div>
                        </div>
                        <div class="flex items-start justify-between gap-4">
                            <span
                                class="text-xs font-semibold tracking-wide text-muted-foreground uppercase"
                            >
                                Besitzer
                            </span>
                            <div class="text-right font-medium">
                                {{ getOwnerName(appointment) }}
                            </div>
                        </div>
                    </div>
                    <div>
                        <div
                            class="text-xs font-semibold tracking-wide text-muted-foreground uppercase"
                        >
                            Beschreibung
                        </div>
                        <p class="mt-2 whitespace-pre-wrap text-sm">
                            {{ appointment.description || 'Keine Beschreibung.' }}
                        </p>
                    </div>
                </div>
                <div v-else class="text-sm text-muted-foreground">
                    Kein Termin ausgewählt.
                </div>
            </div>
            <DialogFooter class="gap-2">
                <Button
                    type="button"
                    variant="outline"
                    @click="emit('update:open', false)"
                >
                    Schließen
                </Button>
                <Button
                    v-if="appointment"
                    type="button"
                    variant="destructive"
                    :disabled="isDeleting"
                    @click="emit('delete')"
                >
                    {{ isDeleting ? 'Löschen...' : 'Löschen' }}
                </Button>
                <Button
                    v-if="appointment"
                    type="button"
                    @click="emit('edit')"
                >
                    Bearbeiten
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
