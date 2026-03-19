<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { ChevronLeft, ChevronRight, Keyboard, Plus, X } from 'lucide-vue-next';
import { computed, ref } from 'vue';

import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { type AppPageProps, type BreadcrumbItem } from '@/types';

import AgendaView from './components/AgendaView.vue';
import AppointmentDetailsSheet from './components/AppointmentDetailsSheet.vue';
import AppointmentFormDialog from './components/AppointmentFormDialog.vue';
import DayView from './components/DayView.vue';
import MiniCalendar from './components/MiniCalendar.vue';
import MobileMonthView from './components/MobileMonthView.vue';
import MonthView from './components/MonthView.vue';
import WeekView from './components/WeekView.vue';
import { useAppointmentForm } from './composables/useAppointmentForm';
import { useCalendarActions } from './composables/useCalendarActions';
import { useCalendarAppointments } from './composables/useCalendarAppointments';
import { useCalendarFormatting } from './composables/useCalendarFormatting';
import { useCalendarKeyboard } from './composables/useCalendarKeyboard';
import { useCalendarNavigation } from './composables/useCalendarNavigation';
import { useCalendarPermissions } from './composables/useCalendarPermissions';
import type { Appointment, CalendarPermissions, ViewMode } from './types';
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
    filteredAppointments,
    selectedAppointments,
    selectedAppointment,
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

const { formatTime, formatDate, formatFullDate, getOwnerName, isPastAppointment, getEventBgClass } =
    useCalendarFormatting();

const {
    canCreateAppointments,
    canEditAppointment,
    canEditSelectedAppointment,
    canDeleteSelectedAppointment,
} = useCalendarPermissions({
    permissions: computed(() => props.permissions),
    userId,
    selectedAppointment,
});

const { syncCurrentMonth, goPrevMonth, goNextMonth, handleSelectDay, goToday, goPrev, goNext } =
    useCalendarNavigation({
        currentMonth,
        selectedDate,
        viewMode,
        selectDay,
    });

const { startCreate, startEdit, openDetails, handleAppointmentDeleted, handleMoveAppointment } =
    useCalendarActions({
        canCreateAppointments,
        canEditAppointment,
        detailsOpen,
        selectedAppointmentId,
        selectedDate,
        openEdit,
        openCreate,
        selectAppointment,
        syncCurrentMonth,
    });

const { showShortcutsHelp } = useCalendarKeyboard({
    isCreateOpen,
    detailsOpen,
    viewMode,
    goToday,
    startCreate,
    goPrev,
    goNext,
});
</script>

<template>
    <Head title="Kalender" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-[calc(100vh-8rem)] gap-2 p-2 md:gap-4 md:p-4">
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

                <!-- Termine am ausgewählten Tag -->
                <div v-if="selectedAppointments.length > 0" class="space-y-2">
                    <h3
                        class="text-xs font-medium tracking-wider text-muted-foreground uppercase"
                    >
                        Termine am {{ formatDate(selectedDate) }}
                    </h3>
                    <div class="space-y-1">
                        <button
                            v-for="apt in selectedAppointments.slice(0, 5)"
                            :key="apt.id"
                            class="flex w-full items-center gap-2 rounded-lg p-2 text-left text-sm transition-colors hover:bg-muted/50"
                            :class="
                                isPastAppointment(apt)
                                    ? 'opacity-50 hover:opacity-70'
                                    : ''
                            "
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
            <div
                class="flex flex-1 flex-col overflow-hidden rounded-xl border bg-card"
            >
                <!-- Toolbar -->
                <div
                    class="flex flex-wrap items-center justify-between gap-2 border-b px-3 py-2 md:px-4 md:py-3"
                >
                    <div class="flex items-center gap-1.5">
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
                                @click="goPrev"
                            >
                                <ChevronLeft class="h-4 w-4" />
                            </Button>
                            <Button
                                variant="ghost"
                                size="icon"
                                class="h-8 w-8"
                                @click="goNext"
                            >
                                <ChevronRight class="h-4 w-4" />
                            </Button>
                        </div>
                        <h2 class="text-base font-semibold md:text-lg">
                            {{ monthLabel }}
                        </h2>
                    </div>

                    <div class="flex items-center gap-2">
                        <div
                            class="flex items-center gap-0.5 rounded-lg border p-1"
                        >
                            <Button
                                :variant="
                                    viewMode === 'day' ? 'secondary' : 'ghost'
                                "
                                size="sm"
                                class="rounded-md px-2 md:px-3"
                                title="Tag (D)"
                                @click="viewMode = 'day'"
                            >
                                Tag
                            </Button>
                            <Button
                                :variant="
                                    viewMode === 'week' ? 'secondary' : 'ghost'
                                "
                                size="sm"
                                class="hidden rounded-md px-2 sm:inline-flex md:px-3"
                                title="Woche (W)"
                                @click="viewMode = 'week'"
                            >
                                Woche
                            </Button>
                            <Button
                                :variant="
                                    viewMode === 'month' ? 'secondary' : 'ghost'
                                "
                                size="sm"
                                class="rounded-md px-2 md:px-3"
                                title="Monat (M)"
                                @click="viewMode = 'month'"
                            >
                                Monat
                            </Button>
                            <Button
                                :variant="
                                    viewMode === 'agenda'
                                        ? 'secondary'
                                        : 'ghost'
                                "
                                size="sm"
                                class="rounded-md px-2 md:px-3"
                                title="Agenda (A)"
                                @click="viewMode = 'agenda'"
                            >
                                Agenda
                            </Button>
                        </div>

                        <!-- Shortcuts-Hilfe (nur Desktop) -->
                        <Button
                            variant="ghost"
                            size="icon"
                            class="hidden h-8 w-8 text-muted-foreground md:flex"
                            title="Tastaturkürzel anzeigen (?)"
                            @click="showShortcutsHelp = !showShortcutsHelp"
                        >
                            <Keyboard class="h-4 w-4" />
                        </Button>
                    </div>
                </div>

                <!-- Kalenderansicht -->
                <div class="flex-1 overflow-auto">
                    <!-- Mobile: kompakte Monatsansicht mit Dot-Indikatoren -->
                    <MobileMonthView
                        v-if="viewMode === 'month'"
                        class="md:hidden"
                        :day-labels="dayLabels"
                        :calendar-days="calendarDays"
                        :selected-date="selectedDate"
                        :get-event-bg-class="getEventBgClass"
                        @select-day="
                            (date) => {
                                handleSelectDay(date);
                                viewMode = 'day';
                            }
                        "
                        @open-details="openDetails"
                    />
                    <!-- Desktop: volle Monatsansicht -->
                    <MonthView
                        v-if="viewMode === 'month'"
                        class="hidden md:flex"
                        :day-labels="dayLabels"
                        :calendar-days="calendarDays"
                        :format-time="formatTime"
                        :get-event-bg-class="getEventBgClass"
                        :can-edit-appointment="canEditAppointment"
                        @select-day="handleSelectDay"
                        @open-details="openDetails"
                        @move-appointment="handleMoveAppointment"
                    />
                    <WeekView
                        v-else-if="viewMode === 'week'"
                        :week-days="weekDays"
                        :day-labels="dayLabels"
                        :format-time="formatTime"
                        :get-event-bg-class="getEventBgClass"
                        :can-edit-appointment="canEditAppointment"
                        @select-day="handleSelectDay"
                        @open-details="openDetails"
                        @move-appointment="handleMoveAppointment"
                    />
                    <DayView
                        v-else-if="viewMode === 'day'"
                        :selected-date="selectedDate"
                        :selected-appointments="selectedAppointments"
                        :format-time="formatTime"
                        :format-full-date="formatFullDate"
                        :get-event-bg-class="getEventBgClass"
                        :can-edit-appointment="canEditAppointment"
                        @open-details="openDetails"
                        @move-appointment="handleMoveAppointment"
                    />
                    <AgendaView
                        v-else
                        :appointments="filteredAppointments"
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

        <!-- Tastaturkürzel-Hilfe-Panel -->
        <Transition
            enter-active-class="transition ease-out duration-150"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition ease-in duration-100"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
        >
            <div
                v-if="showShortcutsHelp"
                class="fixed right-4 bottom-4 z-50 w-72 origin-bottom-right overflow-hidden rounded-xl border bg-popover shadow-xl"
            >
                <div
                    class="flex items-center justify-between border-b px-4 py-3"
                >
                    <div class="flex items-center gap-2">
                        <Keyboard class="h-4 w-4 text-muted-foreground" />
                        <span class="text-sm font-semibold"
                            >Tastaturkürzel</span
                        >
                    </div>
                    <Button
                        variant="ghost"
                        size="icon"
                        class="h-6 w-6"
                        @click="showShortcutsHelp = false"
                    >
                        <X class="h-3.5 w-3.5" />
                    </Button>
                </div>
                <div class="space-y-0.5 p-3">
                    <p
                        class="mb-2 text-[11px] font-medium tracking-wider text-muted-foreground uppercase"
                    >
                        Navigation
                    </p>
                    <div
                        v-for="shortcut in [
                            { keys: ['T'], label: 'Heute' },
                            {
                                keys: ['←'],
                                label: 'Zurück (Tag / Woche / Monat)',
                            },
                            { keys: ['→'], label: 'Vor (Tag / Woche / Monat)' },
                        ]"
                        :key="shortcut.label"
                        class="flex items-center justify-between rounded px-2 py-1 hover:bg-muted/50"
                    >
                        <span class="text-xs text-muted-foreground">{{
                            shortcut.label
                        }}</span>
                        <div class="flex gap-1">
                            <kbd
                                v-for="k in shortcut.keys"
                                :key="k"
                                class="inline-flex h-5 min-w-[1.25rem] items-center justify-center rounded border bg-muted px-1.5 font-mono text-[10px] font-medium shadow-sm"
                            >
                                {{ k }}
                            </kbd>
                        </div>
                    </div>

                    <p
                        class="mt-3 mb-2 text-[11px] font-medium tracking-wider text-muted-foreground uppercase"
                    >
                        Ansichten
                    </p>
                    <div
                        v-for="shortcut in [
                            { keys: ['D'], label: 'Tagesansicht' },
                            { keys: ['W'], label: 'Wochenansicht' },
                            { keys: ['M'], label: 'Monatsansicht' },
                            { keys: ['A'], label: 'Agenda' },
                        ]"
                        :key="shortcut.label"
                        class="flex items-center justify-between rounded px-2 py-1 hover:bg-muted/50"
                    >
                        <span class="text-xs text-muted-foreground">{{
                            shortcut.label
                        }}</span>
                        <div class="flex gap-1">
                            <kbd
                                v-for="k in shortcut.keys"
                                :key="k"
                                class="inline-flex h-5 min-w-[1.25rem] items-center justify-center rounded border bg-muted px-1.5 font-mono text-[10px] font-medium shadow-sm"
                            >
                                {{ k }}
                            </kbd>
                        </div>
                    </div>

                    <p
                        class="mt-3 mb-2 text-[11px] font-medium tracking-wider text-muted-foreground uppercase"
                    >
                        Aktionen
                    </p>
                    <div
                        v-for="shortcut in [
                            { keys: ['N'], label: 'Neuer Termin' },
                            { keys: ['?'], label: 'Shortcuts anzeigen' },
                            { keys: ['Esc'], label: 'Schließen' },
                        ]"
                        :key="shortcut.label"
                        class="flex items-center justify-between rounded px-2 py-1 hover:bg-muted/50"
                    >
                        <span class="text-xs text-muted-foreground">{{
                            shortcut.label
                        }}</span>
                        <div class="flex gap-1">
                            <kbd
                                v-for="k in shortcut.keys"
                                :key="k"
                                class="inline-flex h-5 min-w-[1.25rem] items-center justify-center rounded border bg-muted px-1.5 font-mono text-[10px] font-medium shadow-sm"
                            >
                                {{ k }}
                            </kbd>
                        </div>
                    </div>
                </div>
                <p class="border-t px-4 py-2 text-[10px] text-muted-foreground">
                    Shortcuts sind deaktiviert wenn ein Dialog offen ist.
                </p>
            </div>
        </Transition>

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
