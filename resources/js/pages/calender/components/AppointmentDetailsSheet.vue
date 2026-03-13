<script setup lang="ts">
import { ref } from 'vue';
import { Calendar, Clock, MapPin, Pencil, Trash2, User } from 'lucide-vue-next';

import MarkdownContent from '@/components/MarkdownContent.vue';
import { Button } from '@/components/ui/button';
import {
    Sheet,
    SheetContent,
    SheetDescription,
    SheetHeader,
    SheetTitle,
} from '@/components/ui/sheet';

import AppoitmentDeleteAlert from './AppoitmentDeleteAlert.vue';
import type { Appointment } from '../types';

const props = defineProps<{
    open: boolean;
    appointment: Appointment | null;
    canEdit: boolean;
    canDelete: boolean;
    formatTime: (value: string | number) => string;
    formatDate: (value: Date) => string;
    parseDate: (value: string | number) => Date | null;
    getOwnerName: (appointment: Appointment) => string;
    getEventBgClass: (appointment: Appointment) => string;
}>();

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void;
    (e: 'edit', appointment: Appointment): void;
    (e: 'deleted'): void;
}>();

const showDeleteAlert = ref(false);

const handleDeleted = () => {
    showDeleteAlert.value = false;
    emit('update:open', false);
    emit('deleted');
};

const handleEdit = () => {
    if (props.appointment) {
        emit('edit', props.appointment);
    }
};
</script>

<template>
    <Sheet :open="open" @update:open="emit('update:open', $event)">
        <SheetContent class="flex w-full flex-col sm:max-w-md">
            <SheetHeader v-if="appointment" class="space-y-4 pr-4">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div
                            class="h-4 w-4 flex-shrink-0 rounded-full"
                            :class="getEventBgClass(appointment)"
                        />
                        <SheetTitle class="text-xl">
                            {{ appointment.title }}
                        </SheetTitle>
                    </div>
                </div>

                <SheetDescription class="sr-only">
                    Termindetails
                </SheetDescription>
            </SheetHeader>

            <div
                v-if="appointment"
                class="mt-6 flex flex-1 flex-col space-y-6 overflow-hidden pr-4"
            >
                <div class="space-y-4 pl-2">
                    <div class="flex items-center gap-3 text-sm">
                        <Calendar
                            class="h-5 w-5 flex-shrink-0 text-muted-foreground"
                        />
                        <span>
                            {{
                                formatDate(
                                    parseDate(appointment.start_time) ||
                                        new Date(),
                                )
                            }}
                            -
                            {{
                                formatDate(
                                    parseDate(appointment.end_time) ||
                                        new Date(),
                                )
                            }}
                        </span>
                    </div>

                    <div class="flex items-center gap-3 text-sm">
                        <Clock
                            class="h-5 w-5 flex-shrink-0 text-muted-foreground"
                        />
                        <span>
                            {{ formatTime(appointment.start_time) }} -
                            {{ formatTime(appointment.end_time) }}
                        </span>
                    </div>

                    <div
                        v-if="appointment.location"
                        class="flex items-center gap-3 text-sm"
                    >
                        <MapPin
                            class="h-5 w-5 flex-shrink-0 text-muted-foreground"
                        />
                        <span>{{ appointment.location }}</span>
                    </div>

                    <div class="flex items-center gap-3 text-sm">
                        <User
                            class="h-5 w-5 flex-shrink-0 text-muted-foreground"
                        />
                        <span>{{ getOwnerName(appointment) }}</span>
                    </div>
                </div>

                <div
                    v-if="appointment.description"
                    class="flex min-h-0 flex-1 flex-col space-y-2"
                >
                    <h4 class="text-sm font-medium text-muted-foreground">
                        Beschreibung
                    </h4>
                    <div
                        class="flex-1 overflow-y-auto rounded-md border bg-muted/30 p-3"
                    >
                        <MarkdownContent
                            :content="appointment.description"
                            class="text-sm"
                        />
                    </div>
                </div>

                <div
                    v-if="canEdit || canDelete"
                    class="flex flex-shrink-0 gap-2 border-t pt-4 pb-6"
                >
                    <Button
                        v-if="canEdit"
                        variant="outline"
                        class="flex-1 gap-2"
                        @click="handleEdit"
                    >
                        <Pencil class="h-4 w-4" />
                        Bearbeiten
                    </Button>
                    <Button
                        v-if="canDelete"
                        variant="destructive"
                        class="gap-2"
                        @click="showDeleteAlert = true"
                    >
                        <Trash2 class="h-4 w-4" />
                    </Button>
                </div>
            </div>

            <div v-else class="flex h-full items-center justify-center pr-4">
                <p class="text-sm text-muted-foreground">
                    Kein Termin ausgewählt
                </p>
            </div>
        </SheetContent>
    </Sheet>

    <AppoitmentDeleteAlert
        v-if="appointment && canDelete"
        v-model:open="showDeleteAlert"
        :appointment="appointment"
        @deleted="handleDeleted"
    />
</template>
