<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

import AppLayout from '@/layouts/AppLayout.vue';
import { type AppPageProps, type BreadcrumbItem } from '@/types';

import AppointmentDetailsDialog from './components/AppointmentDetailsDialog.vue';
import AppointmentFormDialog from './components/AppointmentFormDialog.vue';
import CalendarToolbar from './components/CalendarToolbar.vue';
import CalendarViews from './components/CalendarViews.vue';
import DetailsSidebar from './components/DetailsSidebar.vue';
import MiniCalendarCard from './components/MiniCalendarCard.vue';
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

const viewMode = ref<ViewMode>('agenda');
const searchQuery = ref('');
const showMine = ref(true);
const showTeam = ref(true);
const includePast = ref(true);

const currentMonth = ref(new Date());
const selectedDate = ref(new Date());
const selectedAppointmentId = ref<number | null>(null);

const isDetailsOpen = ref(false);

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
    sortedAppointments,
    calendarDays,
    weekDays,
    selectedAppointments,
    selectedAppointment,
    upcomingAppointments,
    agendaGroups,
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

const handleAppointmentDeleted = () => {
    selectedAppointmentId.value = null;
    isDetailsOpen.value = false;
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
            :format-date="formatDate"
            :format-time="formatTime"
            :parse-date="parseDate"
            :get-owner-name="getOwnerName"
            @update:open="isDetailsOpen = $event"
            @edit="openEditFromDetails"
            @deleted="handleAppointmentDeleted"
        />
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
