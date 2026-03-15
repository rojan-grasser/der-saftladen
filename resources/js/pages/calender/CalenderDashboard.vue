<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3';
import appointments from '@/routes/appointments';
import { CalendarDays, CalendarRange, ChevronLeft, ChevronRight, Keyboard, LayoutGrid, List, Plus, X } from 'lucide-vue-next';
import { computed, onMounted, onUnmounted, ref } from 'vue';

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

const formatMobileSelectedDate = (value: Date) => {
    return new Intl.DateTimeFormat('de-DE', {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
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

onMounted(() => {
    document.addEventListener('keydown', handleKeyDown);
});

onUnmounted(() => {
    document.removeEventListener('keydown', handleKeyDown);
});

// ── Dev-Modus ─────────────────────────────────────────────────────────────────
const showDevPanel = ref(false);
const devTestStatus = ref<string | null>(null);
const devTestLoading = ref(false);

const sendTestEmail = async () => {
    devTestStatus.value = null;
    devTestLoading.value = true;
    try {
        const res = await fetch('/reminder/test-email', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-XSRF-TOKEN': decodeURIComponent(document.cookie.match(/XSRF-TOKEN=([^;]+)/)?.[1] ?? ''),
            },
        });
        const data = await res.json();
        if (res.ok) {
            devTestStatus.value = `✅ E-Mail gesendet an ${data.sent_to}`;
        } else {
            devTestStatus.value = `❌ ${data.message ?? 'Fehler'}`;
        }
    } catch {
        devTestStatus.value = '❌ Netzwerkfehler';
    } finally {
        devTestLoading.value = false;
    }
};

const startCreate = () => {
    if (!canCreateAppointments.value) return;
    openCreate(new Date(selectedDate.value));
};

// ── Kontextsensitive Navigation (←/→ je nach Ansicht) ────────────────────────
const goPrev = () => {
    if (viewMode.value === 'day') {
        const d = new Date(selectedDate.value);
        d.setDate(d.getDate() - 1);
        handleSelectDay(d);
    } else if (viewMode.value === 'week') {
        const d = new Date(selectedDate.value);
        d.setDate(d.getDate() - 7);
        handleSelectDay(d);
    } else {
        goPrevMonth();
    }
};

const goNext = () => {
    if (viewMode.value === 'day') {
        const d = new Date(selectedDate.value);
        d.setDate(d.getDate() + 1);
        handleSelectDay(d);
    } else if (viewMode.value === 'week') {
        const d = new Date(selectedDate.value);
        d.setDate(d.getDate() + 7);
        handleSelectDay(d);
    } else {
        goNextMonth();
    }
};

// ── Tastatur-Shortcuts ────────────────────────────────────────────────────────
const showShortcutsHelp = ref(false);

const handleKeyDown = (e: KeyboardEvent) => {
    // Nicht auslösen beim Tippen in Eingabefeldern oder mit Modifier-Tasten
    const target = e.target as HTMLElement;
    if (
        target.tagName === 'INPUT' ||
        target.tagName === 'TEXTAREA' ||
        target.isContentEditable ||
        e.ctrlKey ||
        e.metaKey ||
        e.altKey
    )
        return;

    // Escape schließt immer das Hilfe-Panel
    if (e.key === 'Escape') {
        if (showShortcutsHelp.value) {
            e.preventDefault();
            showShortcutsHelp.value = false;
        }
        return;
    }

    // Keine weiteren Shortcuts wenn Dialog/Sheet offen
    if (isCreateOpen.value || detailsOpen.value) return;

    switch (e.key) {
        case 't':
        case 'T':
            e.preventDefault();
            goToday();
            break;
        case 'd':
        case 'D':
            e.preventDefault();
            viewMode.value = 'day';
            break;
        case 'w':
        case 'W':
            e.preventDefault();
            viewMode.value = 'week';
            break;
        case 'm':
        case 'M':
            e.preventDefault();
            viewMode.value = 'month';
            break;
        case 'a':
        case 'A':
            e.preventDefault();
            viewMode.value = 'agenda';
            break;
        case 'n':
        case 'N':
            e.preventDefault();
            startCreate();
            break;
        case 'ArrowLeft':
            e.preventDefault();
            goPrev();
            break;
        case 'ArrowRight':
            e.preventDefault();
            goNext();
            break;
        case '?':
            e.preventDefault();
            showShortcutsHelp.value = !showShortcutsHelp.value;
            break;
        case 'F9':
            e.preventDefault();
            showDevPanel.value = !showDevPanel.value;
            devTestStatus.value = null;
            break;
    }
};

// ── Drag & Drop: Termin per API verschieben ───────────────────────────────────
const handleMoveAppointment = ({
    appointment,
    newStart,
    newEnd,
}: {
    appointment: Appointment;
    newStart: Date;
    newEnd: Date;
}) => {
    if (!canEditAppointment(appointment)) return;

    router.put(
        appointments.update(appointment.id).url,
        {
            title: appointment.title,
            description: appointment.description ?? '',
            location: appointment.location ?? '',
            color: appointment.color ?? 'peacock',
            reminders: appointment.reminders ?? [],
            start_time: Math.floor(newStart.getTime() / 1000),
            end_time: Math.floor(newEnd.getTime() / 1000),
        },
        { preserveScroll: true },
    );
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

// ── Mobile: Bottom-Nav Items ──────────────────────────────────────────────────
const mobileNavItems = [
    { mode: 'day' as ViewMode, label: 'Tag' },
    { mode: 'week' as ViewMode, label: 'Woche' },
    { mode: 'month' as ViewMode, label: 'Monat' },
    { mode: 'agenda' as ViewMode, label: 'Agenda' },
] as const;
</script>

<template>
    <Head title="Kalender" />

    <AppLayout :breadcrumbs="breadcrumbs">

        <!-- ═══════════════════════════════════════════════════════════════ -->
        <!-- MOBILE LAYOUT (sichtbar unter lg)                              -->
        <!-- ═══════════════════════════════════════════════════════════════ -->
        <div class="flex flex-col lg:hidden h-[calc(100svh-4rem)] overflow-hidden bg-background">

            <!-- Mobile Header -->
            <div class="flex items-center gap-1 border-b bg-card px-2 py-2 shrink-0">
                <button
                    class="flex h-9 w-9 items-center justify-center rounded-full text-sm font-bold text-primary transition-colors hover:bg-muted/60"
                    title="Heute"
                    @click="goToday"
                >
                    {{ new Date().getDate() }}
                </button>

                <div class="flex flex-1 items-center justify-center gap-1">
                    <button
                        class="flex h-8 w-8 items-center justify-center rounded-full transition-colors hover:bg-muted/60"
                        @click="goPrev"
                    >
                        <ChevronLeft class="h-4 w-4" />
                    </button>
                    <h2 class="min-w-[140px] text-center text-base font-semibold">
                        {{ monthLabel }}
                    </h2>
                    <button
                        class="flex h-8 w-8 items-center justify-center rounded-full transition-colors hover:bg-muted/60"
                        @click="goNext"
                    >
                        <ChevronRight class="h-4 w-4" />
                    </button>
                </div>

            </div>

            <!-- Content-Bereich -->
            <div class="flex min-h-0 flex-1 flex-col overflow-hidden">

                <!-- MONATSANSICHT: Kompaktes Raster + Terminliste -->
                <template v-if="viewMode === 'month'">
                    <!-- Kompaktes Monatsraster -->
                    <div class="shrink-0 border-b bg-card">
                        <MobileMonthView
                            :day-labels="dayLabels"
                            :calendar-days="calendarDays"
                            :selected-date="selectedDate"
                            :get-event-bg-class="getEventBgClass"
                            @select-day="handleSelectDay"
                            @open-details="openDetails"
                        />
                    </div>

                    <!-- Terminliste fuer ausgewaehlten Tag -->
                    <div class="flex min-h-0 flex-1 flex-col overflow-hidden">
                        <!-- Datumsleiste -->
                        <div class="flex shrink-0 items-center justify-between border-b bg-muted/20 px-4 py-2">
                            <span class="text-sm font-medium text-foreground">
                                {{ formatMobileSelectedDate(selectedDate) }}
                            </span>
                            <button
                                v-if="canCreateAppointments"
                                class="flex items-center gap-1 rounded-full bg-primary/10 px-3 py-1 text-xs font-medium text-primary transition-colors hover:bg-primary/20"
                                @click="startCreate"
                            >
                                <Plus class="h-3 w-3" />
                                Neu
                            </button>
                        </div>

                        <!-- Terminliste -->
                        <div class="flex-1 overflow-y-auto">
                            <div
                                v-if="selectedAppointments.length === 0"
                                class="flex h-full items-center justify-center p-8 text-center"
                            >
                                <p class="text-sm text-muted-foreground">Keine Termine</p>
                            </div>
                            <div v-else class="divide-y">
                                <button
                                    v-for="apt in selectedAppointments"
                                    :key="apt.id"
                                    type="button"
                                    class="flex w-full items-center gap-3 px-4 py-3 text-left transition-colors hover:bg-muted/40 active:bg-muted/60"
                                    @click="openDetails(apt)"
                                >
                                    <div
                                        class="w-1 shrink-0 self-stretch rounded-full"
                                        :class="getEventBgClass(apt)"
                                    />
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-sm font-medium">{{ apt.title }}</p>
                                        <p class="text-xs text-muted-foreground">
                                            {{ formatTime(apt.start_time) }} &ndash; {{ formatTime(apt.end_time) }}<span v-if="apt.location"> &middot; {{ apt.location }}</span>
                                        </p>
                                    </div>
                                </button>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- WOCHENANSICHT -->
                <WeekView
                    v-if="viewMode === 'week'"
                    :week-days="weekDays"
                    :day-labels="dayLabels"
                    :format-time="formatTime"
                    :get-event-bg-class="getEventBgClass"
                    :can-edit-appointment="canEditAppointment"
                    @select-day="handleSelectDay"
                    @open-details="openDetails"
                    @move-appointment="handleMoveAppointment"
                />

                <!-- TAGESANSICHT -->
                <DayView
                    v-if="viewMode === 'day'"
                    :selected-date="selectedDate"
                    :selected-appointments="selectedAppointments"
                    :format-time="formatTime"
                    :format-full-date="formatFullDate"
                    :get-event-bg-class="getEventBgClass"
                    :can-edit-appointment="canEditAppointment"
                    @open-details="openDetails"
                    @move-appointment="handleMoveAppointment"
                />

                <!-- AGENDA -->
                <AgendaView
                    v-if="viewMode === 'agenda'"
                    :appointments="props.appointments"
                    :format-time="formatTime"
                    :get-event-bg-class="getEventBgClass"
                    :get-owner-name="getOwnerName"
                    @open-details="openDetails"
                />
            </div>

            <!-- Mobile Bottom Navigation -->
            <nav class="flex shrink-0 border-t bg-card">
                <button
                    v-for="item in mobileNavItems"
                    :key="item.mode"
                    type="button"
                    class="flex flex-1 flex-col items-center gap-1 py-2 transition-colors"
                    :class="viewMode === item.mode ? 'text-primary' : 'text-muted-foreground hover:text-foreground'"
                    @click="viewMode = item.mode"
                >
                    <CalendarDays v-if="item.mode === 'day'" class="h-5 w-5" />
                    <CalendarRange v-else-if="item.mode === 'week'" class="h-5 w-5" />
                    <LayoutGrid v-else-if="item.mode === 'month'" class="h-5 w-5" />
                    <List v-else class="h-5 w-5" />
                    <span class="text-[10px] font-medium leading-none">{{ item.label }}</span>
                </button>
            </nav>
        </div>

        <!-- FAB (nur mobil, nur wenn Erstellen erlaubt) -->
        <button
            v-if="canCreateAppointments"
            type="button"
            class="fixed bottom-[4.5rem] right-4 z-40 flex h-14 w-14 items-center justify-center rounded-full bg-primary text-primary-foreground shadow-lg transition-transform hover:scale-105 active:scale-95 lg:hidden"
            @click="startCreate"
        >
            <Plus class="h-6 w-6" />
        </button>

        <!-- ═══════════════════════════════════════════════════════════════ -->
        <!-- DESKTOP LAYOUT (sichtbar ab lg)                                -->
        <!-- ═══════════════════════════════════════════════════════════════ -->
        <div class="hidden lg:flex h-[calc(100vh-8rem)] gap-4 p-4">
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

                    <div class="flex items-center gap-2">
                        <div class="flex items-center gap-1 rounded-lg border p-1">
                            <Button
                                :variant="viewMode === 'day' ? 'secondary' : 'ghost'"
                                size="sm"
                                class="rounded-md px-3"
                                title="Tag (D)"
                                @click="viewMode = 'day'"
                            >
                                Tag
                            </Button>
                            <Button
                                :variant="viewMode === 'week' ? 'secondary' : 'ghost'"
                                size="sm"
                                class="rounded-md px-3"
                                title="Woche (W)"
                                @click="viewMode = 'week'"
                            >
                                Woche
                            </Button>
                            <Button
                                :variant="viewMode === 'month' ? 'secondary' : 'ghost'"
                                size="sm"
                                class="rounded-md px-3"
                                title="Monat (M)"
                                @click="viewMode = 'month'"
                            >
                                Monat
                            </Button>
                            <Button
                                :variant="viewMode === 'agenda' ? 'secondary' : 'ghost'"
                                size="sm"
                                class="rounded-md px-3"
                                title="Agenda (A)"
                                @click="viewMode = 'agenda'"
                            >
                                Agenda
                            </Button>
                        </div>

                        <!-- Shortcuts-Hilfe -->
                        <Button
                            variant="ghost"
                            size="icon"
                            class="h-8 w-8 text-muted-foreground"
                            title="Tastaturkürzel anzeigen (?)"
                            @click="showShortcutsHelp = !showShortcutsHelp"
                        >
                            <Keyboard class="h-4 w-4" />
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
                        :appointments="props.appointments"
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

        <!-- Dev-Panel (Backtick ` zum Öffnen) -->
        <Transition
            enter-active-class="transition ease-out duration-150"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition ease-in duration-100"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
        >
            <div
                v-if="showDevPanel"
                class="fixed bottom-4 left-4 z-50 w-72 origin-bottom-left overflow-hidden rounded-xl border border-yellow-500/50 bg-yellow-950/95 shadow-xl"
            >
                <div class="flex items-center justify-between border-b border-yellow-500/30 px-4 py-3">
                    <div class="flex items-center gap-2">
                        <span class="text-sm">🛠️</span>
                        <span class="text-sm font-semibold text-yellow-300">Dev-Modus</span>
                        <span class="rounded bg-yellow-500/20 px-1.5 py-0.5 text-[10px] font-medium text-yellow-400">nur intern</span>
                    </div>
                    <Button
                        variant="ghost"
                        size="icon"
                        class="h-6 w-6 text-yellow-400 hover:bg-yellow-500/20 hover:text-yellow-200"
                        @click="showDevPanel = false"
                    >
                        <X class="h-3.5 w-3.5" />
                    </Button>
                </div>
                <div class="space-y-3 p-4">
                    <div>
                        <p class="mb-2 text-[11px] font-medium uppercase tracking-wider text-yellow-500">E-Mail Erinnerung</p>
                        <p class="mb-3 text-[11px] text-yellow-400/70">Sendet eine Test-Erinnerungsmail an deine E-Mail-Adresse.</p>
                        <Button
                            size="sm"
                            class="w-full bg-yellow-500 text-yellow-950 hover:bg-yellow-400"
                            :disabled="devTestLoading"
                            @click="sendTestEmail"
                        >
                            {{ devTestLoading ? 'Sende...' : '📧 Test-E-Mail senden' }}
                        </Button>
                        <p v-if="devTestStatus" class="mt-2 text-xs" :class="devTestStatus.startsWith('✅') ? 'text-green-400' : 'text-red-400'">
                            {{ devTestStatus }}
                        </p>
                    </div>
                </div>
                <p class="border-t border-yellow-500/30 px-4 py-2 text-[10px] text-yellow-600">
                    Öffnen/Schließen: <kbd class="rounded border border-yellow-600 bg-yellow-900 px-1 font-mono">F9</kbd>
                </p>
            </div>
        </Transition>

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
                <div class="flex items-center justify-between border-b px-4 py-3">
                    <div class="flex items-center gap-2">
                        <Keyboard class="h-4 w-4 text-muted-foreground" />
                        <span class="text-sm font-semibold">Tastaturkürzel</span>
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
                    <p class="mb-2 text-[11px] font-medium uppercase tracking-wider text-muted-foreground">Navigation</p>
                    <div v-for="shortcut in [
                        { keys: ['T'], label: 'Heute', action: () => { goToday(); showShortcutsHelp.value = false; } },
                        { keys: ['←'], label: 'Zurück (Tag / Woche / Monat)', action: () => { goPrev(); showShortcutsHelp.value = false; } },
                        { keys: ['→'], label: 'Vor (Tag / Woche / Monat)', action: () => { goNext(); showShortcutsHelp.value = false; } },
                    ]" :key="shortcut.label" class="flex cursor-pointer items-center justify-between rounded px-2 py-1 hover:bg-muted/50" @click="shortcut.action()">
                        <span class="text-xs text-muted-foreground">{{ shortcut.label }}</span>
                        <div class="flex gap-1">
                            <kbd v-for="k in shortcut.keys" :key="k"
                                class="inline-flex h-5 min-w-[1.25rem] items-center justify-center rounded border bg-muted px-1.5 font-mono text-[10px] font-medium shadow-sm">
                                {{ k }}
                            </kbd>
                        </div>
                    </div>

                    <p class="mb-2 mt-3 text-[11px] font-medium uppercase tracking-wider text-muted-foreground">Ansichten</p>
                    <div v-for="shortcut in [
                        { keys: ['D'], label: 'Tagesansicht', action: () => { viewMode.value = 'day'; showShortcutsHelp.value = false; } },
                        { keys: ['W'], label: 'Wochenansicht', action: () => { viewMode.value = 'week'; showShortcutsHelp.value = false; } },
                        { keys: ['M'], label: 'Monatsansicht', action: () => { viewMode.value = 'month'; showShortcutsHelp.value = false; } },
                        { keys: ['A'], label: 'Agenda', action: () => { viewMode.value = 'agenda'; showShortcutsHelp.value = false; } },
                    ]" :key="shortcut.label" class="flex cursor-pointer items-center justify-between rounded px-2 py-1 hover:bg-muted/50" @click="shortcut.action()">
                        <span class="text-xs text-muted-foreground">{{ shortcut.label }}</span>
                        <div class="flex gap-1">
                            <kbd v-for="k in shortcut.keys" :key="k"
                                class="inline-flex h-5 min-w-[1.25rem] items-center justify-center rounded border bg-muted px-1.5 font-mono text-[10px] font-medium shadow-sm">
                                {{ k }}
                            </kbd>
                        </div>
                    </div>

                    <p class="mb-2 mt-3 text-[11px] font-medium uppercase tracking-wider text-muted-foreground">Aktionen</p>
                    <div v-for="shortcut in [
                        { keys: ['N'], label: 'Neuer Termin', action: () => { startCreate(); showShortcutsHelp.value = false; } },
                        { keys: ['?'], label: 'Shortcuts anzeigen', action: () => { showShortcutsHelp.value = !showShortcutsHelp.value; } },
                        { keys: ['Esc'], label: 'Schließen', action: () => { showShortcutsHelp.value = false; } },
                    ]" :key="shortcut.label" class="flex cursor-pointer items-center justify-between rounded px-2 py-1 hover:bg-muted/50" @click="shortcut.action()">
                        <span class="text-xs text-muted-foreground">{{ shortcut.label }}</span>
                        <div class="flex gap-1">
                            <kbd v-for="k in shortcut.keys" :key="k"
                                class="inline-flex h-5 min-w-[1.25rem] items-center justify-center rounded border bg-muted px-1.5 font-mono text-[10px] font-medium shadow-sm">
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
