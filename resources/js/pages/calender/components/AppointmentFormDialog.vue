<script setup lang="ts">
import { computed } from 'vue';

import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { DatePicker } from '@/components/ui/date-picker';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { InputGroupTimePicker } from '@/components/ui/time-picker';

const props = defineProps<{
    open: boolean;
    isEditMode: boolean;
    form: {
        title: string;
        description: string;
        location: string;
        start_time: string;
        end_time: string;
        errors: Record<string, string>;
        processing: boolean;
    };
    isAllDay: boolean;
    allDayStart: string;
    allDayEnd: string;
    startDate: string;
    startTime: string;
    endDate: string;
    endTime: string;
}>();

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void;
    (e: 'submit'): void;
    (e: 'close'): void;
    (e: 'update:isAllDay', value: boolean): void;
    (e: 'update:allDayStart', value: string): void;
    (e: 'update:allDayEnd', value: string): void;
    (e: 'update:startDate', value: string): void;
    (e: 'update:startTime', value: string): void;
    (e: 'update:endDate', value: string): void;
    (e: 'update:endTime', value: string): void;
}>();

const isAllDayValue = computed({
    get: () => props.isAllDay,
    set: (value) => emit('update:isAllDay', value),
});

const allDayStartValue = computed({
    get: () => props.allDayStart,
    set: (value) => emit('update:allDayStart', value),
});

const allDayEndValue = computed({
    get: () => props.allDayEnd,
    set: (value) => emit('update:allDayEnd', value),
});

const startDateValue = computed({
    get: () => props.startDate,
    set: (value) => emit('update:startDate', value),
});

const startTimeValue = computed({
    get: () => props.startTime,
    set: (value) => emit('update:startTime', value),
});

const endDateValue = computed({
    get: () => props.endDate,
    set: (value) => emit('update:endDate', value),
});

const endTimeValue = computed({
    get: () => props.endTime,
    set: (value) => emit('update:endTime', value),
});
</script>

<template>
    <Dialog :open="open" @update:open="emit('update:open', $event)">
        <DialogContent class="sm:max-w-2xl">
            <DialogHeader>
                <DialogTitle>
                    {{ isEditMode ? 'Termin bearbeiten' : 'Neuer Termin' }}
                </DialogTitle>
                <DialogDescription>
                    {{
                        isEditMode
                            ? 'Termin aktualisieren.'
                            : 'Neuen Termin zum Kalender hinzufügen.'
                    }}
                </DialogDescription>
            </DialogHeader>
            <form class="grid gap-4" @submit.prevent="emit('submit')">
                <div class="grid gap-2">
                    <Label for="title">Titel</Label>
                    <Input
                        id="title"
                        v-model="form.title"
                        placeholder="Termintitel"
                    />
                    <InputError :message="form.errors.title" />
                </div>
                <div class="grid gap-2">
                    <Label for="description">Beschreibung</Label>
                    <textarea
                        id="description"
                        v-model="form.description"
                        class="min-h-[100px] w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm outline-none focus-visible:border-ring focus-visible:ring-2 focus-visible:ring-ring/50"
                        placeholder="Details oder Notizen hinzufügen"
                    ></textarea>
                    <InputError :message="form.errors.description" />
                </div>
                <div class="grid gap-2">
                    <Label for="location">Ort</Label>
                    <Input
                        id="location"
                        v-model="form.location"
                        placeholder="Ort oder Meeting-Link"
                    />
                    <InputError :message="form.errors.location" />
                </div>
                <div class="flex items-center gap-3">
                    <Checkbox id="all_day" v-model="isAllDayValue" />
                    <Label for="all_day">Ganztägig</Label>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="grid gap-2">
                        <Label :for="isAllDay ? 'start_date' : 'start_time'">
                            Beginn
                        </Label>
                        <div v-if="isAllDay" class="grid gap-2">
                            <DatePicker
                                id="start_date"
                                v-model="allDayStartValue"
                                placeholder="Datum auswählen"
                            />
                        </div>
                        <div v-else class="grid gap-2">
                            <DatePicker
                                id="start_time"
                                v-model="startDateValue"
                                placeholder="Datum auswählen"
                            />
                            <InputGroupTimePicker
                                v-model="startTimeValue"
                                placeholder="Zeit auswählen"
                            />
                        </div>
                        <InputError :message="form.errors.start_time" />
                    </div>
                    <div class="grid gap-2">
                        <Label :for="isAllDay ? 'end_date' : 'end_time'">
                            Ende
                        </Label>
                        <div v-if="isAllDay" class="grid gap-2">
                            <DatePicker
                                id="end_date"
                                v-model="allDayEndValue"
                                placeholder="Datum auswählen"
                            />
                        </div>
                        <div v-else class="grid gap-2">
                            <DatePicker
                                id="end_time"
                                v-model="endDateValue"
                                placeholder="Datum auswählen"
                            />
                            <InputGroupTimePicker
                                v-model="endTimeValue"
                                placeholder="Zeit auswählen"
                            />
                        </div>
                        <InputError :message="form.errors.end_time" />
                    </div>
                </div>
                <DialogFooter class="gap-2">
                    <Button type="button" variant="outline" @click="emit('close')">
                        Abbrechen
                    </Button>
                    <Button type="submit" :disabled="form.processing">
                        {{
                            isEditMode
                                ? 'Änderungen speichern'
                                : 'Termin speichern'
                        }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
