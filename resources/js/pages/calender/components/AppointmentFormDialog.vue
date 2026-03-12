<script setup lang="ts">
import { computed } from 'vue';
import { Calendar as CalendarIcon, Clock, MapPin, Palette, Type } from 'lucide-vue-next';

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
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
} from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { InputGroupTimePicker } from '@/components/ui/time-picker';

import {
    appointmentColorMap,
    appointmentColorOptions,
    defaultAppointmentColor,
} from '../constants';
import type { AppointmentColor } from '../types';

const props = defineProps<{
    open: boolean;
    isEditMode: boolean;
    form: {
        title: string;
        description: string;
        location: string;
        color: AppointmentColor;
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

const selectedColorOption = computed(() => {
    return (
        appointmentColorMap[props.form.color] ??
        appointmentColorMap[defaultAppointmentColor]
    );
});
</script>

<template>
    <Dialog :open="open" @update:open="emit('update:open', $event)">
        <DialogContent class="max-h-[90vh] overflow-y-auto sm:max-w-lg">
            <DialogHeader>
                <DialogTitle class="text-xl">
                    {{ isEditMode ? 'Termin bearbeiten' : 'Neuer Termin' }}
                </DialogTitle>
                <DialogDescription>
                    {{
                        isEditMode
                            ? 'Aktualisieren Sie die Termindetails.'
                            : 'Erstellen Sie einen neuen Termin.'
                    }}
                </DialogDescription>
            </DialogHeader>

            <form class="space-y-5" @submit.prevent="emit('submit')">
                <div class="space-y-2">
                    <div class="flex items-center gap-2">
                        <Type class="h-4 w-4 text-muted-foreground" />
                        <Label for="title">Titel</Label>
                    </div>
                    <Input
                        id="title"
                        v-model="form.title"
                        placeholder="Termintitel eingeben"
                        class="text-base"
                    />
                    <InputError :message="form.errors.title" />
                </div>

                <div class="space-y-2">
                    <div class="flex items-center gap-2">
                        <CalendarIcon class="h-4 w-4 text-muted-foreground" />
                        <Label>Datum & Zeit</Label>
                    </div>

                    <div class="flex items-center gap-3 rounded-lg border p-3">
                        <Checkbox id="all_day" v-model="isAllDayValue" />
                        <Label for="all_day" class="cursor-pointer text-sm">
                            Ganztägig
                        </Label>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-2">
                        <div class="space-y-2">
                            <Label class="text-xs text-muted-foreground">
                                Beginn
                            </Label>
                            <div v-if="isAllDay">
                                <DatePicker
                                    v-model="allDayStartValue"
                                    placeholder="Startdatum"
                                />
                            </div>
                            <div v-else class="space-y-2">
                                <DatePicker
                                    v-model="startDateValue"
                                    placeholder="Startdatum"
                                />
                                <InputGroupTimePicker
                                    v-model="startTimeValue"
                                    placeholder="Startzeit"
                                />
                            </div>
                            <InputError :message="form.errors.start_time" />
                        </div>

                        <div class="space-y-2">
                            <Label class="text-xs text-muted-foreground">
                                Ende
                            </Label>
                            <div v-if="isAllDay">
                                <DatePicker
                                    v-model="allDayEndValue"
                                    placeholder="Enddatum"
                                />
                            </div>
                            <div v-else class="space-y-2">
                                <DatePicker
                                    v-model="endDateValue"
                                    placeholder="Enddatum"
                                />
                                <InputGroupTimePicker
                                    v-model="endTimeValue"
                                    placeholder="Endzeit"
                                />
                            </div>
                            <InputError :message="form.errors.end_time" />
                        </div>
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="space-y-2">
                        <div class="flex items-center gap-2">
                            <MapPin class="h-4 w-4 text-muted-foreground" />
                            <Label for="location">Ort</Label>
                        </div>
                        <Input
                            id="location"
                            v-model="form.location"
                            placeholder="Ort oder Link"
                        />
                        <InputError :message="form.errors.location" />
                    </div>

                    <div class="space-y-2">
                        <div class="flex items-center gap-2">
                            <Palette class="h-4 w-4 text-muted-foreground" />
                            <Label for="color">Farbe</Label>
                        </div>
                        <Select v-model="form.color">
                            <SelectTrigger id="color">
                                <span class="flex items-center gap-2">
                                    <span
                                        class="h-3 w-3 rounded-full"
                                        :class="selectedColorOption.swatchClass"
                                    />
                                    <span class="text-sm">
                                        {{ selectedColorOption.label }}
                                    </span>
                                </span>
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem
                                    v-for="option in appointmentColorOptions"
                                    :key="option.value"
                                    :value="option.value"
                                >
                                    <span class="flex items-center gap-2">
                                        <span
                                            class="h-3 w-3 rounded-full"
                                            :class="option.swatchClass"
                                        />
                                        <span>{{ option.label }}</span>
                                    </span>
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <InputError :message="form.errors.color" />
                    </div>
                </div>

                <div class="space-y-2">
                    <Label for="description">Beschreibung</Label>
                    <Textarea
                        id="description"
                        v-model="form.description"
                        class="min-h-[80px] resize-none"
                        placeholder="Notizen oder Details hinzufügen..."
                    />
                    <InputError :message="form.errors.description" />
                </div>

                <DialogFooter class="gap-2 sm:gap-0">
                    <Button
                        type="button"
                        variant="ghost"
                        @click="emit('close')"
                    >
                        Abbrechen
                    </Button>
                    <Button type="submit" :disabled="form.processing">
                        {{ isEditMode ? 'Speichern' : 'Erstellen' }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
