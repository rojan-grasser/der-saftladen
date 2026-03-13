<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { Bell, BellOff, ChevronLeft, ChevronRight, Plus } from 'lucide-vue-next';
import { computed, onMounted, ref } from 'vue';

import { usePushNotifications } from '@/composables/usePushNotifications';

import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { type AppPageProps, type BreadcrumbItem } from '@/types';

import AgendaView from './components/AgendaView.vue';
import AppointmentDetailsSheet from './components/AppointmentDetailsSheet.vue';
import AppointmentFormDialog from './components/AppointmentFormDialog.vue';
import DayView from './components/DayView.vue';
import MiniCalendar from './components/MiniCalendar.vue';
import MonthView from './components/MonthView.vue';
import WeekView from './components/WeekView.vue';
import {
    appointmentColorMap,
    defaultAppointmentColor,
} from './constants';
import { useAppointmentForm } from './composables/useAppointmentForm';
import { useCalendarAppointments } from './composables/useCalendarAppointments';
import type {
    Appointment,
    CalendarPermissions,
    ViewMode,
} from './types';
import { parseDate } from './utils/date';

const props = defineProps<{
    appointments: Appointment[];
    permissions: CalendarPermissions;
}>();

const page = usePage<AppPageProps>();
const userId = computed(() => page.props.auth?.user?.id ?? null);

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
const detailsOpen = ref(false);

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

const canCreateAppointments = computed(() => props.permissions.canCreate);

const canEditAppointment = (appointment: Appointment | null) => {
    if (!appointment) return false;
    if (props.permissions.canEditAll) return true;
    return props.permissions.canEditOwn && appointment.user_id === userId.value;
};

const canDeleteAppointment = (appointment: Appointment | null) => {
    if (!appointment) return false;
    return props.permissions.canDeleteAll;
};

const canEditSelectedAppointment = computed(() => canEditAppointment(selectedAppointment.value));
const canDeleteSelectedAppointment = computed(() => canDeleteAppointment(selectedAppointment.value));

const getOwnerName = (appointment: Appointment) => appointment.creator?.name ?? 'Unbekannt';

const formatTime = (value: string | number) => {
    const date = parseDate(value);
    if (!date) return '';
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

const formatFullDate = (value: Date) => {
    return new Intl.DateTimeFormat('de-DE', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
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

const handleSelectDay = (date: Date) => {
    syncCurrentMonth(date);
    selectDay(date);
};

const { isSupported: pushSupported, isSubscribed: pushSubscribed, isLoading: pushLoading, checkSubscriptionStatus, requestPermissionAndSubscribe, unsubscribe: unsubscribePush } = usePushNotifications();

onMounted(() => {
    checkSubscriptionStatus();
});

const togglePushNotifications = async () => {
    if (pushSubscribed.value) {
        await unsubscribePush();
    } else {
        await requestPermissionAndSubscribe();
    }
};

const startCreate = () => {
    if (!canCreateAppointments.value) return;
    openCreate();
};

const startEdit = (appointment: Appointment) => {
    if (!canEditAppointment(appointment)) return;
    detailsOpen.value = false;
    openEdit(appointment);
};

const openDetails = (appointment: Appointment) => {
    const appointmentDate = parseDate(appointment.start_time);
    if (appointmentDate) syncCurrentMonth(appointmentDate);
    selectAppointment(appointment);
    detailsOpen.value = true;
};

const handleAppointmentDeleted = () => {
    selectedAppointmentId.value = null;
    detailsOpen.value = false;
};

const getEventBgClass = (appointment: Appointment) => {
    const color = appointment.color ?? defaultAppointmentColor;
    const colorMap: Record<string, string> = {
        peacock: 'bg-sky-500',
        sage: 'bg-emerald-500',
        grape: 'bg-fuchsia-500',
        flamingo: 'bg-pink-500',
        banana: 'bg-yellow-500',
        tangerine: 'bg-orange-500',
        lavender: 'bg-violet-500',
        graphite: 'bg-slate-500',
        blueberry: 'bg-blue-500',
        basil: 'bg-lime-500',
        tomato: 'bg-red-500',
    };
    return colorMap[color] ?? colorMap[defaultAppointmentColor];
};
</script>

<template>
    <Head title="Kalender" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-[calc(100vh-8rem)] gap-4 p-4">
            <!-- Linke Sidebar mit Mini-Kalender -->
            <aside class="hidden w-64 flex-shrink-0 space-y-4 lg:block">
                <Button
                    v-if="canCreateAppointments"
                    class="w-full gap-2 rounded-full shadow-md"
                    size="lg"
                    @click="startCreate"
                >
                    <Plus class="h-5 w-5" />
                    Erstellen
                </Button>

                <div
                    v-else
                    class="flex h-10 items-center justify-center rounded-full border border-dashed text-sm text-muted-foreground"
                >
                    Nur Lesemodus
                </div>

                <MiniCalendar
                    :current-month="currentMonth"
                    :selected-date="selectedDate"
                    :calendar-days="calendarDays"
                    @select-day="handleSelectDay"
                    @prev-month="goPrevMonth"
                    @next-month="goNextMonth"
                />

                <!-- Push-Benachrichtigungen -->
                <Button
                    v-if="pushSupported"
                    variant="outline"
                    class="w-full gap-2"
                    :disabled="pushLoading"
                    @click="togglePushNotifications"
                >
                    <BellOff v-if="pushSubscribed" class="h-4 w-4" />
                    <Bell v-else class="h-4 w-4" />
                    {{ pushSubscribed ? 'Benachrichtigungen aus' : 'Benachrichtigungen an' }}
                </Button>

                <!-- Termine am ausgewählten Tag -->
                <div v-if="selectedAppointments.length > 0" class="space-y-2">
                    <h3 class="text-xs font-medium uppercase tracking-wider text-muted-foreground">
                        Termine am {{ formatDate(selectedDate) }}
                    </h3>
                    <div class="space-y-1">
                        <button
                            v-for="apt in selectedAppointments.slice(0, 5)"
                            :key="apt.id"
                            class="flex w-full items-center gap-2 rounded-lg p-2 text-left text-sm transition-colors hover:bg-muted/50"
                            @click="openDetails(apt)"
                        >
                            <div
                                class="h-2 w-2 rounded-full"
                                :class="getEventBgClass(apt)"
                            />
                            <span class="truncate">{{ apt.title }}</span>
                            <span class="ml-auto text-xs text-muted-foreground">
                                {{ formatTime(apt.start_time) }}
                            </span>
                        </button>
                        <p
                            v-if="selectedAppointments.length > 5"
                            class="pl-4 text-xs text-muted-foreground"
                        >
                            +{{ selectedAppointments.length - 5 }} weitere
                        </p>
                    </div>
                </div>
            </aside>

            <!-- Hauptbereich -->
            <div class="flex flex-1 flex-col overflow-hidden rounded-xl border bg-card">
                <!-- Toolbar -->
                <div class="flex items-center justify-between border-b px-4 py-3">
                    <div class="flex items-center gap-2">
                        <Button
                            variant="outline"
                            size="sm"
                            class="rounded-full"
                            @click="goToday"
                        >
                            Heute
                        </Button>
                        <div class="flex items-center">
                            <Button
                                variant="ghost"
                                size="icon"
                                class="h-8 w-8"
                                @click="goPrevMonth"
                            >
                                <ChevronLeft class="h-4 w-4" />
                            </Button>
                            <Button
                                variant="ghost"
                                size="icon"
                                class="h-8 w-8"
                                @click="goNextMonth"
                            >
                                <ChevronRight class="h-4 w-4" />
                            </Button>
                        </div>
                        <h2 class="text-lg font-semibold">
                            {{ monthLabel }}
                        </h2>
                    </div>

                    <div class="flex items-center gap-1 rounded-lg border p-1">
                        <Button
                            :variant="viewMode === 'day' ? 'secondary' : 'ghost'"
                            size="sm"
                            class="rounded-md px-3"
                            @click="viewMode = 'day'"
                        >
                            Tag
                        </Button>
                        <Button
                            :variant="viewMode === 'week' ? 'secondary' : 'ghost'"
                            size="sm"
                            class="rounded-md px-3"
                            @click="viewMode = 'week'"
                        >
                            Woche
                        </Button>
                        <Button
                            :variant="viewMode === 'month' ? 'secondary' : 'ghost'"
                            size="sm"
                            class="rounded-md px-3"
                            @click="viewMode = 'month'"
                        >
                            Monat
                        </Button>
                        <Button
                            :variant="viewMode === 'agenda' ? 'secondary' : 'ghost'"
                            size="sm"
                            class="rounded-md px-3"
                            @click="viewMode = 'agenda'"
                        >
                            Agenda
                        </Button>
                    </div>
                </div>

                <!-- Kalenderansicht -->
                <div class="flex-1 overflow-auto">
                    <MonthView
                        v-if="viewMode === 'month'"
                        :day-labels="dayLabels"
                        :calendar-days="calendarDays"
                        :format-time="formatTime"
                        :get-event-bg-class="getEventBgClass"
                        @select-day="handleSelectDay"
                        @open-details="openDetails"
                    />
                    <WeekView
                        v-else-if="viewMode === 'week'"
                        :week-days="weekDays"
                        :day-labels="dayLabels"
                        :format-time="formatTime"
                        :get-event-bg-class="getEventBgClass"
                        @select-day="handleSelectDay"
                        @open-details="openDetails"
                    />
                    <DayView
                        v-else-if="viewMode === 'day'"
                        :selected-date="selectedDate"
                        :selected-appointments="selectedAppointments"
                        :format-time="formatTime"
                        :format-full-date="formatFullDate"
                        :get-event-bg-class="getEventBgClass"
                        @open-details="openDetails"
                    />
                    <AgendaView
                        v-else
                        :appointments="appointments"
                        :format-time="formatTime"
                        :get-event-bg-class="getEventBgClass"
                        :get-owner-name="getOwnerName"
                        @open-details="openDetails"
                    />
                </div>
            </div>
        </div>

        <!-- Termindetails Sheet -->
        <AppointmentDetailsSheet
            v-model:open="detailsOpen"
            :appointment="selectedAppointment"
            :can-edit="canEditSelectedAppointment"
            :can-delete="canDeleteSelectedAppointment"
            :format-time="formatTime"
            :format-date="formatDate"
            :parse-date="parseDate"
            :get-owner-name="getOwnerName"
            :get-event-bg-class="getEventBgClass"
            @edit="startEdit"
            @deleted="handleAppointmentDeleted"
        />

        <!-- Termin erstellen/bearbeiten Dialog -->
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
