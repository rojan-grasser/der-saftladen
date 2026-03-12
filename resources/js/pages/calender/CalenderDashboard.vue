<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

import AppLayout from '@/layouts/AppLayout.vue';
import { type AppPageProps, type BreadcrumbItem } from '@/types';

import AgendaSidebar from './components/AgendaSidebar.vue';
import AppointmentFormDialog from './components/AppointmentFormDialog.vue';
import CalendarToolbar from './components/CalendarToolbar.vue';
import CalendarViews from './components/CalendarViews.vue';
import DetailsSidebar from './components/DetailsSidebar.vue';
import {
    appointmentColorMap,
    defaultAppointmentColor,
} from './constants';
import { useAppointmentForm } from './composables/useAppointmentForm';
import { useCalendarAppointments } from './composables/useCalendarAppointments';
import type { Appointment, ViewMode } from './types';
import { parseDate } from './utils/date';

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

const viewMode = ref<ViewMode>('month');
const searchQuery = ref('');
const showMine = ref(true);
const showTeam = ref(true);
const includePast = ref(true);

const currentMonth = ref(new Date());
const selectedDate = ref(new Date());
const selectedAppointmentId = ref<number | null>(null);

const {
    form,
    isCreateOpen,
    isEditMode,
    isAllDay,
    allDayStart,
    allDayEnd,
    startDate,
    startTime,
    endDate,
    endTime,
    openCreate,
    closeCreate,
    openEdit,
    submit,
    handleDialogOpen,
} = useAppointmentForm();

const {
    monthLabel,
    dayLabels,
    calendarDays,
    weekDays,
    selectedAppointments,
    selectedAppointment,
    upcomingAppointments,
    selectDay,
    selectAppointment,
} = useCalendarAppointments({
    appointments: computed(() => props.appointments),
    userId,
    searchQuery,
    showMine,
    showTeam,
    includePast,
    currentMonth,
    selectedDate,
    selectedAppointmentId,
});

const getOwnerName = (appointment: Appointment) => {
    return appointment.creator?.name ?? 'Unbekannt';
};

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

const syncCurrentMonth = (date: Date) => {
    currentMonth.value = new Date(date.getFullYear(), date.getMonth(), 1);
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
    handleSelectDay(new Date());
};

const setViewMode = (mode: ViewMode) => {
    viewMode.value = mode;
};

const handleSelectDay = (date: Date) => {
    syncCurrentMonth(date);
    selectDay(date);
};

const openDetails = (appointment: Appointment) => {
    const appointmentDate = parseDate(appointment.start_time);
    if (appointmentDate) {
        syncCurrentMonth(appointmentDate);
    }
    selectAppointment(appointment);
};

const handleAppointmentDeleted = () => {
    selectedAppointmentId.value = null;
};

const getEventClass = (appointment: Appointment) => {
    const color = appointment.color ?? defaultAppointmentColor;

    return appointmentColorMap[color]?.eventClass
        ?? appointmentColorMap[defaultAppointmentColor].eventClass;
};
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

            <div
                class="grid items-start gap-6 xl:grid-cols-[300px_minmax(0,1fr)_340px]"
            >
                <AgendaSidebar
                    :month-label="monthLabel"
                    :day-labels="dayLabels"
                    :calendar-days="calendarDays"
                    :selected-date="selectedDate"
                    :selected-appointments="selectedAppointments"
                    :format-date="formatDate"
                    :format-time="formatTime"
                    :get-event-class="getEventClass"
                    @select-day="handleSelectDay"
                    @prev-month="goPrevMonth"
                    @next-month="goNextMonth"
                    @open-create="openCreate"
                    @open-details="openDetails"
                    @set-view-mode="setViewMode"
                />

                <section class="min-w-0 space-y-4">
                    <div class="rounded-2xl border bg-card p-4 shadow-sm">
                        <div
                            class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between"
                        >
                            <div class="space-y-1">
                                <div
                                    class="text-xs font-semibold tracking-[0.24em] text-muted-foreground uppercase"
                                >
                                    Kalenderbereich
                                </div>
                                <div class="text-lg font-semibold">
                                    {{ formatDate(selectedDate) }}
                                </div>
                                <p class="text-sm text-muted-foreground">
                                    {{ selectedAppointments.length }} Termin(e)
                                    im Fokus.
                                </p>
                            </div>

                            <div
                                v-if="selectedAppointment"
                                class="rounded-xl border bg-muted/20 px-4 py-3"
                            >
                                <div
                                    class="text-xs font-semibold tracking-wide text-muted-foreground uppercase"
                                >
                                    Ausgewaehlt
                                </div>
                                <div class="mt-1 text-sm font-semibold">
                                    {{ selectedAppointment.title }}
                                </div>
                                <div class="text-xs text-muted-foreground">
                                    {{
                                        formatTime(
                                            selectedAppointment.start_time,
                                        )
                                    }}
                                    -
                                    {{
                                        formatTime(selectedAppointment.end_time)
                                    }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <CalendarViews
                        :view-mode="viewMode"
                        :day-labels="dayLabels"
                        :calendar-days="calendarDays"
                        :week-days="weekDays"
                        :selected-date="selectedDate"
                        :selected-appointments="selectedAppointments"
                        :format-date="formatDate"
                        :format-time="formatTime"
                        :get-event-class="getEventClass"
                        @select-day="handleSelectDay"
                        @open-details="openDetails"
                        @open-create="openCreate"
                    />
                </section>

                <DetailsSidebar
                    :selected-appointment="selectedAppointment"
                    :upcoming-appointments="upcomingAppointments"
                    :format-date="formatDate"
                    :format-time="formatTime"
                    :parse-date="parseDate"
                    :get-event-class="getEventClass"
                    :get-owner-name="getOwnerName"
                    @open-edit="openEdit"
                    @open-details="openDetails"
                    @deleted="handleAppointmentDeleted"
                />
            </div>
        </div>

        <AppointmentFormDialog
            :open="isCreateOpen"
            :is-edit-mode="isEditMode"
            :form="form"
            v-model:is-all-day="isAllDay"
            v-model:all-day-start="allDayStart"
            v-model:all-day-end="allDayEnd"
            v-model:start-date="startDate"
            v-model:start-time="startTime"
            v-model:end-date="endDate"
            v-model:end-time="endTime"
            @update:open="handleDialogOpen"
            @submit="submit"
            @close="closeCreate"
        />
    </AppLayout>
</template>
