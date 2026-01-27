<script setup lang="ts">
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';


import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
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
import AppLayout from '@/layouts/AppLayout.vue';
// eslint-disable-next-line vue/no-dupe-keys
import appointments from '@/routes/appointments';
import { type AppPageProps, type BreadcrumbItem } from '@/types';

import AppointmentDetailsDialog from './components/AppointmentDetailsDialog.vue';
import CalendarToolbar from './components/CalendarToolbar.vue';
import CalendarViews from './components/CalendarViews.vue';
import DetailsSidebar from './components/DetailsSidebar.vue';
import MiniCalendarCard from './components/MiniCalendarCard.vue';
import type { Appointment, ViewMode } from './types';

const props = defineProps<{
    appointments: Appointment[];
}>();

const page = usePage<AppPageProps>();
const userId = computed(() => page.props.auth?.user?.id);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Kalender',
        href: '/appointments',
    },
];

const viewMode = ref<ViewMode>('agenda');
const searchQuery = ref('');
const showMine = ref(true);
const showTeam = ref(true);
const includePast = ref(true);

const currentMonth = ref(new Date());
const selectedDate = ref(new Date());
const selectedAppointmentId = ref<number | null>(null);

const isCreateOpen = ref(false);
const isEditMode = ref(false);
const editingAppointmentId = ref<number | null>(null);
const isDetailsOpen = ref(false);

const form = useForm({
    title: '',
    description: '',
    location: '',
    start_time: '',
    end_time: '',
});
const deleteForm = useForm({});
const isAllDay = ref(false);
const allDayStart = ref('');
const allDayEnd = ref('');

const openCreate = () => {
    isEditMode.value = false;
    editingAppointmentId.value = null;
    isCreateOpen.value = true;
    form.reset();
    form.clearErrors();
    isAllDay.value = false;
    const now = new Date();
    form.start_time = toInputDateTime(now);
    form.end_time = toInputDateTime(addMinutes(now, 60));
    allDayStart.value = toInputDate(now);
    allDayEnd.value = toInputDate(now);
};

const closeCreate = () => {
    isCreateOpen.value = false;
    isEditMode.value = false;
    editingAppointmentId.value = null;
    form.clearErrors();
    isAllDay.value = false;
};

const submit = () => {
    if (isAllDay.value) {
        applyAllDayTimes();
    }
    if (isEditMode.value && editingAppointmentId.value != null) {
        form.put(appointments.update(editingAppointmentId.value).url, {
            preserveScroll: true,
            onSuccess: () => {
                form.reset();
                isCreateOpen.value = false;
                isEditMode.value = false;
                editingAppointmentId.value = null;
            },
        });
        return;
    }

    form.post(appointments.store().url, {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            isCreateOpen.value = false;
        },
    });
};

const monthLabel = computed(() => {
    return new Intl.DateTimeFormat('de-DE', {
        month: 'long',
        year: 'numeric',
    }).format(currentMonth.value);
});

const dayLabels = ['Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa', 'So'];
const parseDate = (value: string | number | null | undefined) => {
    if (value === null || value === undefined || value === '') {
        return null;
    }

    if (typeof value === 'number') {
        const milliseconds = value > 9999999999 ? value : value * 1000;
        const parsed = new Date(milliseconds);
        return Number.isNaN(parsed.getTime()) ? null : parsed;
    }

    const trimmed = value.trim();
    if (!trimmed) {
        return null;
    }

    if (/^\d+$/.test(trimmed)) {
        const asNumber = Number(trimmed);
        const milliseconds = asNumber > 9999999999 ? asNumber : asNumber * 1000;
        const parsed = new Date(milliseconds);
        return Number.isNaN(parsed.getTime()) ? null : parsed;
    }

    const normalized = trimmed.includes('T')
        ? trimmed
        : trimmed.replace(' ', 'T');
    const parsed = new Date(normalized);
    return Number.isNaN(parsed.getTime()) ? null : parsed;
};

const toUnixSeconds = (value: string | number) => {
    const parsed = parseDate(value);
    return parsed ? Math.floor(parsed.getTime() / 1000) : null;
};

form.transform((data) => ({
    ...data,
    start_time: toUnixSeconds(data.start_time),
    end_time: toUnixSeconds(data.end_time),
}));

const toDateKey = (value: Date) => {
    return `${value.getFullYear()}-${String(value.getMonth() + 1).padStart(
        2,
        '0',
    )}-${String(value.getDate()).padStart(2, '0')}`;
};

const toInputDateTime = (value: Date) => {
    return `${value.getFullYear()}-${String(value.getMonth() + 1).padStart(
        2,
        '0',
    )}-${String(value.getDate()).padStart(2, '0')}T${String(
        value.getHours(),
    ).padStart(2, '0')}:${String(value.getMinutes()).padStart(2, '0')}`;
};

const toInputDate = (value: Date) => {
    return `${value.getFullYear()}-${String(value.getMonth() + 1).padStart(
        2,
        '0',
    )}-${String(value.getDate()).padStart(2, '0')}`;
};

const toDateString = (value: string | number) => {
    if (value === null || value === undefined || value === '') {
        return '';
    }
    const parsed = parseDate(value);
    return parsed ? toInputDate(parsed) : '';
};

const addMinutes = (value: Date, minutes: number) => {
    const next = new Date(value);
    next.setMinutes(next.getMinutes() + minutes);
    return next;
};

const getOwnerName = (appointment: Appointment) => {
    return appointment.creator?.name ?? 'Unbekannt';
};

const truncateWords = (value: string, maxWords = 100) => {
    const trimmed = value.trim();
    if (!trimmed) {
        return '';
    }
    const words = trimmed.split(/\s+/);
    if (words.length <= maxWords) {
        return trimmed;
    }
    return `${words.slice(0, maxWords).join(' ')}...`;
};

const isAllDayRange = (start: Date | null, end: Date | null) => {
    if (!start || !end) {
        return false;
    }
    return (
        start.getHours() === 0 &&
        start.getMinutes() === 0 &&
        end.getHours() === 23 &&
        end.getMinutes() === 59
    );
};

const applyAllDayTimes = () => {
    const startDate = allDayStart.value || toInputDate(new Date());
    let endDate = allDayEnd.value || startDate;

    if (endDate < startDate) {
        endDate = startDate;
    }

    allDayStart.value = startDate;
    allDayEnd.value = endDate;
    form.start_time = `${startDate}T00:00`;
    form.end_time = `${endDate}T23:59`;
};

const openEdit = (appointment: Appointment) => {
    isEditMode.value = true;
    editingAppointmentId.value = appointment.id;
    form.clearErrors();
    form.title = appointment.title ?? '';
    form.description = appointment.description ?? '';
    form.location = appointment.location ?? '';
    const start = parseDate(appointment.start_time);
    const end = parseDate(appointment.end_time) ?? start;
    form.start_time = start ? toInputDateTime(start) : '';
    form.end_time = end ? toInputDateTime(end) : '';
    allDayStart.value = start ? toInputDate(start) : '';
    allDayEnd.value = end ? toInputDate(end) : allDayStart.value;
    isAllDay.value = isAllDayRange(start, end);
    isCreateOpen.value = true;
};

const isSameDay = (a: Date, b: Date) => {
    return (
        a.getFullYear() === b.getFullYear() &&
        a.getMonth() === b.getMonth() &&
        a.getDate() === b.getDate()
    );
};

const startOfWeek = (value: Date) => {
    const start = new Date(value);
    const day = (start.getDay() + 6) % 7;
    start.setDate(start.getDate() - day);
    start.setHours(0, 0, 0, 0);
    return start;
};
const toDayStart = (value: Date) => {
    const date = new Date(value);
    date.setHours(0, 0, 0, 0);
    return date;
};

const getDayKeysBetween = (start: Date, end: Date) => {
    const keys: string[] = [];
    const cursor = toDayStart(start);
    const last = toDayStart(end);
    while (cursor <= last) {
        keys.push(toDateKey(cursor));
        cursor.setDate(cursor.getDate() + 1);
    }
    return keys;
};

const filteredAppointments = computed(() => {
    const query = searchQuery.value.trim().toLowerCase();
    const now = new Date();

    return props.appointments.filter((appointment) => {
        const start = parseDate(appointment.start_time);
        const end = parseDate(appointment.end_time) ?? start;
        if (!start) {
            return false;
        }

        const rangeEnd = end && end >= start ? end : start;
        if (!includePast.value && rangeEnd < now) {
            return false;
        }

        const isMine =
            userId.value == null ? true : appointment.user_id === userId.value;

        if ((isMine && !showMine.value) || (!isMine && !showTeam.value)) {
            return false;
        }

        if (!query) {
            return true;
        }

        const haystack = `${appointment.title ?? ''} ${
            appointment.description ?? ''
        } ${appointment.location ?? ''}`.toLowerCase();
        return haystack.includes(query);
    });
});

const sortedAppointments = computed(() => {
    return [...filteredAppointments.value].sort((a, b) => {
        const aDate = parseDate(a.start_time)?.getTime() ?? 0;
        const bDate = parseDate(b.start_time)?.getTime() ?? 0;
        return aDate - bDate;
    });
});

const appointmentsByDate = computed(() => {
    const map = new Map<string, Appointment[]>();
    for (const appointment of sortedAppointments.value) {
        const date = parseDate(appointment.start_time);
        const end = parseDate(appointment.end_time) ?? date;
        if (!date) {
            continue;
        }
        const rangeEnd = end && end >= date ? end : date;
        for (const key of getDayKeysBetween(date, rangeEnd)) {
            if (!map.has(key)) {
                map.set(key, []);
            }
            map.get(key)!.push(appointment);
        }
    }
    return map;
});

const calendarDays = computed(() => {
    const firstOfMonth = new Date(
        currentMonth.value.getFullYear(),
        currentMonth.value.getMonth(),
        1,
    );
    const offset = (firstOfMonth.getDay() + 6) % 7;
    const gridStart = new Date(firstOfMonth);
    gridStart.setDate(firstOfMonth.getDate() - offset);

    const days = [];
    for (let i = 0; i < 42; i += 1) {
        const date = new Date(gridStart);
        date.setDate(gridStart.getDate() + i);
        const key = toDateKey(date);
        days.push({
            date,
            key,
            isCurrentMonth: date.getMonth() === currentMonth.value.getMonth(),
            isToday: isSameDay(date, new Date()),
            isSelected: isSameDay(date, selectedDate.value),
            appointments: appointmentsByDate.value.get(key) ?? [],
        });
    }
    return days;
});

const weekDays = computed(() => {
    const start = startOfWeek(selectedDate.value);
    const days = [];
    for (let i = 0; i < 7; i += 1) {
        const date = new Date(start);
        date.setDate(start.getDate() + i);
        const key = toDateKey(date);
        days.push({
            date,
            key,
            isToday: isSameDay(date, new Date()),
            isSelected: isSameDay(date, selectedDate.value),
            appointments: appointmentsByDate.value.get(key) ?? [],
        });
    }
    return days;
});

const selectedDateKey = computed(() => toDateKey(selectedDate.value));

const selectedAppointments = computed(() => {
    return appointmentsByDate.value.get(selectedDateKey.value) ?? [];
});

const selectedAppointment = computed(() => {
    if (selectedAppointmentId.value != null) {
        return (
            sortedAppointments.value.find(
                (appointment) => appointment.id === selectedAppointmentId.value,
            ) ?? null
        );
    }
    return selectedAppointments.value[0] ?? null;
});

const deleteSelectedAppointment = () => {
    const appointment = selectedAppointment.value;
    if (!appointment || deleteForm.processing) {
        return;
    }

    //TODO Confirm Einbauen fürs Löschen!

    deleteForm.delete(appointments.destroy(appointment.id).url, {
        preserveScroll: true,
        onSuccess: () => {
            if (selectedAppointmentId.value === appointment.id) {
                selectedAppointmentId.value = null;
            }
            isDetailsOpen.value = false;
            router.reload({ only: ['appointments'] });
        },
    });
};

const upcomingAppointments = computed(() => {
    const now = new Date();
    return sortedAppointments.value
        .filter((appointment) => {
            const start = parseDate(appointment.start_time);
            const end = parseDate(appointment.end_time) ?? start;
            if (!start) {
                return false;
            }
            const rangeEnd = end && end >= start ? end : start;
            return rangeEnd >= now;
        })
        .slice(0, 6);
});

const agendaGroups = computed(() => {
    const groups: {
        key: string;
        date: Date;
        items: Appointment[];
    }[] = [];

    for (const appointment of sortedAppointments.value) {
        const date = parseDate(appointment.start_time);
        if (!date) {
            continue;
        }
        const key = toDateKey(date);
        const last = groups[groups.length - 1];
        if (!last || last.key !== key) {
            groups.push({
                key,
                date,
                items: [appointment],
            });
        } else {
            last.items.push(appointment);
        }
    }

    return groups;
});

const formatTime = (value: string | number) => {
    const date = parseDate(value);
    if (!date) {
        return '';
    }
    return new Intl.DateTimeFormat('de-DE', {
        hour: '2-digit',
        minute: '2-digit',
    }).format(date);
};

const formatDate = (value: Date) => {
    return new Intl.DateTimeFormat('de-DE', {
        weekday: 'short',
        month: 'short',
        day: 'numeric',
    }).format(value);
};

const goPrevMonth = () => {
    currentMonth.value = new Date(
        currentMonth.value.getFullYear(),
        currentMonth.value.getMonth() - 1,
        1,
    );
};

const goNextMonth = () => {
    currentMonth.value = new Date(
        currentMonth.value.getFullYear(),
        currentMonth.value.getMonth() + 1,
        1,
    );
};

const goToday = () => {
    const now = new Date();
    currentMonth.value = new Date(now.getFullYear(), now.getMonth(), 1);
    selectedDate.value = now;
};

const setViewMode = (mode: ViewMode) => {
    viewMode.value = mode;
};

const selectDay = (date: Date) => {
    selectedDate.value = date;
    const key = toDateKey(date);
    const dayAppointments = appointmentsByDate.value.get(key) ?? [];
    selectedAppointmentId.value = dayAppointments[0]?.id ?? null;
};

const selectAppointment = (appointment: Appointment) => {
    selectedAppointmentId.value = appointment.id;
    const date = parseDate(appointment.start_time);
    if (date) {
        selectedDate.value = date;
    }
};

const openDetails = (appointment: Appointment) => {
    selectAppointment(appointment);
    isDetailsOpen.value = true;
};

const openEditFromDetails = () => {
    if (!selectedAppointment.value) {
        return;
    }
    isDetailsOpen.value = false;
    openEdit(selectedAppointment.value);
};
const eventColorClasses = [
    'bg-sky-500/10 text-sky-700 border-sky-200 dark:border-sky-500/40 dark:text-sky-300',
    'bg-emerald-500/10 text-emerald-700 border-emerald-200 dark:border-emerald-500/40 dark:text-emerald-300',
    'bg-amber-500/10 text-amber-700 border-amber-200 dark:border-amber-500/40 dark:text-amber-300',
    'bg-rose-500/10 text-rose-700 border-rose-200 dark:border-rose-500/40 dark:text-rose-300',
];

const getEventClass = (appointment: Appointment) => {
    const index = appointment.id % eventColorClasses.length;
    return eventColorClasses[index];
};

const handleDialogOpen = (value: boolean) => {
    if (!value) {
        closeCreate();
        return;
    }
    isCreateOpen.value = true;
};

watch(isAllDay, (value) => {
    if (!value) {
        return;
    }
    if (!allDayStart.value) {
        allDayStart.value = form.start_time
            ? toDateString(form.start_time)
            : toInputDate(new Date());
    }
    if (!allDayEnd.value) {
        allDayEnd.value = form.end_time
            ? toDateString(form.end_time)
            : allDayStart.value;
    }
    applyAllDayTimes();
});

watch([allDayStart, allDayEnd], () => {
    if (!isAllDay.value) {
        return;
    }
    applyAllDayTimes();
});
</script>

<template>
    <Head title="Kalender" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-6">
            <CalendarToolbar
                v-model:searchQuery="searchQuery"
                v-model:viewMode="viewMode"
                :month-label="monthLabel"
                @today="goToday"
                @prev-month="goPrevMonth"
                @next-month="goNextMonth"
                @create="openCreate"
            />
            <div class="grid gap-6 lg:grid-cols-[280px_minmax(0,1fr)_320px]">
                <aside class="flex flex-col gap-6">
                    <MiniCalendarCard
                        :month-label="monthLabel"
                        :day-labels="dayLabels"
                        :calendar-days="calendarDays"
                        @select-day="selectDay"
                        @prev-month="goPrevMonth"
                        @next-month="goNextMonth"
                    />
                </aside>
                <section class="flex flex-col gap-4">
                    <CalendarViews
                        :view-mode="viewMode"
                        :day-labels="dayLabels"
                        :calendar-days="calendarDays"
                        :week-days="weekDays"
                        :selected-date="selectedDate"
                        :selected-appointments="selectedAppointments"
                        :agenda-groups="agendaGroups"
                        :sorted-appointments-count="sortedAppointments.length"
                        :format-date="formatDate"
                        :format-time="formatTime"
                        :get-event-class="getEventClass"
                        @select-day="selectDay"
                        @open-details="openDetails"
                        @open-create="openCreate"
                    />
                </section>
                <DetailsSidebar
                    :selected-date="selectedDate"
                    :selected-appointments="selectedAppointments"
                    :selected-appointment="selectedAppointment"
                    :upcoming-appointments="upcomingAppointments"
                    :format-date="formatDate"
                    :format-time="formatTime"
                    :parse-date="parseDate"
                    :get-event-class="getEventClass"
                    :truncate-words="truncateWords"
                    :get-owner-name="getOwnerName"
                    @open-create="openCreate"
                    @open-edit="openEdit"
                    @open-details="openDetails"
                    @set-view-mode="setViewMode"
                />
            </div>
        </div>
        <AppointmentDetailsDialog
            :open="isDetailsOpen"
            :appointment="selectedAppointment"
            :is-deleting="deleteForm.processing"
            :format-date="formatDate"
            :format-time="formatTime"
            :parse-date="parseDate"
            :get-owner-name="getOwnerName"
            @update:open="isDetailsOpen = $event"
            @edit="openEditFromDetails"
            @delete="deleteSelectedAppointment"
        />
        <Dialog :open="isCreateOpen" @update:open="handleDialogOpen">
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
                <form class="grid gap-4" @submit.prevent="submit">
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
                        <Checkbox id="all_day" v-model="isAllDay" />
                        <Label for="all_day">Ganztägig</Label>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="grid gap-2">
                            <Label :for="isAllDay ? 'start_date' : 'start_time'">
                                Beginn
                            </Label>
                            <Input
                                v-if="isAllDay"
                                id="start_date"
                                v-model="allDayStart"
                                type="date"
                            />
                            <Input
                                v-else
                                id="start_time"
                                v-model="form.start_time"
                                type="datetime-local"
                            />
                            <InputError :message="form.errors.start_time" />
                        </div>
                        <div class="grid gap-2">
                            <Label :for="isAllDay ? 'end_date' : 'end_time'">
                                Ende
                            </Label>
                            <Input
                                v-if="isAllDay"
                                id="end_date"
                                v-model="allDayEnd"
                                type="date"
                            />
                            <Input
                                v-else
                                id="end_time"
                                v-model="form.end_time"
                                type="datetime-local"
                            />
                            <InputError :message="form.errors.end_time" />
                        </div>
                    </div>
                    <DialogFooter class="gap-2">
                        <Button
                            type="button"
                            variant="outline"
                            @click="closeCreate"
                        >
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
    </AppLayout>
</template>
