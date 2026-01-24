
<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

import InputError from '@/components/InputError.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
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
import { Separator } from '@/components/ui/separator';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import AppLayout from '@/layouts/AppLayout.vue';
import appointments from '@/routes/appointments';
import { type AppPageProps, type BreadcrumbItem } from '@/types';
import {
    Calendar,
    ChevronLeft,
    ChevronRight,
    Clock,
    Filter,
    MapPin,
    Plus,
    Search,
    User,
} from 'lucide-vue-next';

interface Appointment {
    id: number;
    title: string;
    description?: string | null;
    location?: string | null;
    start_time: string;
    end_time: string;
    user_id: number;
}

const props = defineProps<{
    appointments: Appointment[];
}>();

const page = usePage<AppPageProps>();
const userId = computed(() => page.props.auth?.user?.id);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Calendar',
        href: '/appointments',
    },
];

const viewMode = ref<'month' | 'week' | 'day' | 'agenda'>('month');
const searchQuery = ref('');
const showMine = ref(true);
const showTeam = ref(true);
const includePast = ref(false);

const currentMonth = ref(new Date());
const selectedDate = ref(new Date());
const selectedAppointmentId = ref<number | null>(null);

const isCreateOpen = ref(false);

const form = useForm({
    title: '',
    description: '',
    location: '',
    start_time: '',
    end_time: '',
});

const openCreate = () => {
    isCreateOpen.value = true;
    if (!form.start_time) {
        const now = new Date();
        form.start_time = toInputDateTime(now);
        form.end_time = toInputDateTime(addMinutes(now, 60));
    }
};

const closeCreate = () => {
    isCreateOpen.value = false;
    form.clearErrors();
};

const submit = () => {
    form.post(appointments.store().url, {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            isCreateOpen.value = false;
        },
    });
};

const monthLabel = computed(() => {
    return new Intl.DateTimeFormat('en-US', {
        month: 'long',
        year: 'numeric',
    }).format(currentMonth.value);
});

const dayLabels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
const parseDate = (value: string) => {
    const normalized = value.includes('T') ? value : value.replace(' ', 'T');
    const parsed = new Date(normalized);
    return Number.isNaN(parsed.getTime()) ? null : parsed;
};

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

const addMinutes = (value: Date, minutes: number) => {
    const next = new Date(value);
    next.setMinutes(next.getMinutes() + minutes);
    return next;
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
    let cursor = toDayStart(start);
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

const upcomingAppointments = computed(() => sortedAppointments.value.slice(0, 6));

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

const formatTime = (value: string) => {
    const date = parseDate(value);
    if (!date) {
        return '';
    }
    return new Intl.DateTimeFormat('en-US', {
        hour: '2-digit',
        minute: '2-digit',
    }).format(date);
};

const formatDate = (value: Date) => {
    return new Intl.DateTimeFormat('en-US', {
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
</script>

<template>
    <Head title="Calendar" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-6">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <Calendar class="h-5 w-5 text-muted-foreground" />
                        <h2 class="text-2xl font-semibold">Calendar</h2>
                    </div>
                    <p class="text-sm text-muted-foreground">
                        Plan and track appointments and team availability.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <div class="relative">
                        <Search
                            class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                        />
                        <Input
                            v-model="searchQuery"
                            class="h-9 w-48 pl-9"
                            placeholder="Search appointments"
                        />
                    </div>

                    <Button variant="outline" @click="goToday">
                        Today
                    </Button>

                    <div class="flex items-center rounded-md border bg-card">
                        <Button
                            variant="ghost"
                            size="icon"
                            class="h-9 w-9"
                            @click="goPrevMonth"
                        >
                            <ChevronLeft class="h-4 w-4" />
                        </Button>
                        <span class="px-3 text-sm font-medium">
                            {{ monthLabel }}
                        </span>
                        <Button
                            variant="ghost"
                            size="icon"
                            class="h-9 w-9"
                            @click="goNextMonth"
                        >
                            <ChevronRight class="h-4 w-4" />
                        </Button>
                    </div>

                    <Select v-model="viewMode">
                        <SelectTrigger class="w-36">
                            <SelectValue placeholder="View" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="month">Month</SelectItem>
                            <SelectItem value="week">Week</SelectItem>
                            <SelectItem value="day">Day</SelectItem>
                            <SelectItem value="agenda">Agenda</SelectItem>
                        </SelectContent>
                    </Select>

                    <Button @click="openCreate">
                        <Plus class="h-4 w-4" />
                        New appointment
                    </Button>
                </div>
            </div>
            <div class="grid gap-6 lg:grid-cols-[280px_minmax(0,1fr)_320px]">
                <aside class="flex flex-col gap-6">
                    <Card>
                        <CardHeader
                            class="flex flex-row items-center justify-between space-y-0 pb-2"
                        >
                            <CardTitle class="text-sm font-medium">
                                Mini calendar
                            </CardTitle>
                            <div class="flex items-center gap-1">
                                <Button
                                    variant="ghost"
                                    size="icon"
                                    class="h-7 w-7"
                                    @click="goPrevMonth"
                                >
                                    <ChevronLeft class="h-4 w-4" />
                                </Button>
                                <Button
                                    variant="ghost"
                                    size="icon"
                                    class="h-7 w-7"
                                    @click="goNextMonth"
                                >
                                    <ChevronRight class="h-4 w-4" />
                                </Button>
                            </div>
                        </CardHeader>
                        <CardContent class="space-y-2">
                            <div class="text-xs text-muted-foreground">
                                {{ monthLabel }}
                            </div>
                            <div class="grid grid-cols-7 gap-1 text-xs">
                                <div
                                    v-for="label in dayLabels"
                                    :key="label"
                                    class="text-center text-[10px] uppercase text-muted-foreground"
                                >
                                    {{ label }}
                                </div>
                                <button
                                    v-for="day in calendarDays"
                                    :key="`mini-${day.key}`"
                                    type="button"
                                    class="h-7 w-full rounded-md text-xs transition-colors"
                                    :class="[
                                        day.isSelected
                                            ? 'bg-primary text-primary-foreground'
                                            : day.isToday
                                              ? 'bg-primary/10 text-primary'
                                              : day.isCurrentMonth
                                                ? 'hover:bg-muted'
                                                : 'text-muted-foreground/60 hover:bg-muted/40',
                                    ]"
                                    @click="selectDay(day.date)"
                                >
                                    {{ day.date.getDate() }}
                                </button>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader class="pb-2">
                            <CardTitle
                                class="text-xs font-semibold uppercase tracking-wide text-muted-foreground"
                            >
                                Filters
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-3 text-xs">
                            <div class="flex items-center justify-between gap-2">
                                <Label
                                    for="filter-mine"
                                    class="text-xs font-medium"
                                >
                                    <Checkbox
                                        id="filter-mine"
                                        class="h-3 w-3"
                                        :checked="showMine"
                                        @update:checked="
                                            (value) => (showMine = value)
                                        "
                                    />
                                    My appointments
                                </Label>
                                <Badge
                                    variant="outline"
                                    class="text-[10px]"
                                >
                                    Mine
                                </Badge>
                            </div>
                            <div class="flex items-center justify-between gap-2">
                                <Label
                                    for="filter-team"
                                    class="text-xs font-medium"
                                >
                                    <Checkbox
                                        id="filter-team"
                                        class="h-3 w-3"
                                        :checked="showTeam"
                                        @update:checked="
                                            (value) => (showTeam = value)
                                        "
                                    />
                                    Team
                                </Label>
                                <Badge
                                    variant="outline"
                                    class="text-[10px]"
                                >
                                    Shared
                                </Badge>
                            </div>
                            <div class="flex items-center justify-between gap-2">
                                <Label
                                    for="filter-past"
                                    class="text-xs font-medium"
                                >
                                    <Checkbox
                                        id="filter-past"
                                        class="h-3 w-3"
                                        :checked="includePast"
                                        @update:checked="
                                            (value) => (includePast = value)
                                        "
                                    />
                                    Past
                                </Label>
                                <Badge
                                    variant="outline"
                                    class="text-[10px]"
                                >
                                    History
                                </Badge>
                            </div>
                            <Separator class="my-1" />
                            <div class="space-y-2">
                                <div
                                    class="flex items-center justify-between text-[11px] text-muted-foreground"
                                >
                                    <span>Calendars</span>
                                    <Filter class="h-3.5 w-3.5" />
                                </div>
                                <div
                                    class="flex items-center justify-between text-xs"
                                >
                                    <div class="flex items-center gap-2">
                                        <span
                                            class="h-2 w-2 rounded-full bg-sky-500"
                                        ></span>
                                        Personal
                                    </div>
                                    <Badge
                                        variant="outline"
                                        class="text-[10px] bg-sky-500/10 text-sky-700 border-sky-200"
                                    >
                                        Active
                                    </Badge>
                                </div>
                                <div
                                    class="flex items-center justify-between text-xs"
                                >
                                    <div class="flex items-center gap-2">
                                        <span
                                            class="h-2 w-2 rounded-full bg-emerald-500"
                                        ></span>
                                        Team
                                    </div>
                                    <Badge
                                        variant="outline"
                                        class="text-[10px] bg-emerald-500/10 text-emerald-700 border-emerald-200"
                                    >
                                        Active
                                    </Badge>
                                </div>
                                <div
                                    class="flex items-center justify-between text-xs"
                                >
                                    <div class="flex items-center gap-2">
                                        <span
                                            class="h-2 w-2 rounded-full bg-amber-500"
                                        ></span>
                                        Resources
                                    </div>
                                    <Badge variant="outline" class="text-[10px]">
                                        Hidden
                                    </Badge>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </aside>
                <section class="flex flex-col gap-4">
                    <div class="rounded-lg border bg-card">
                        <div
                            class="grid grid-cols-7 border-b text-xs text-muted-foreground"
                        >
                            <div
                                v-for="label in dayLabels"
                                :key="label"
                                class="px-2 py-3 text-center font-medium"
                            >
                                {{ label }}
                            </div>
                        </div>

                        <div
                            v-if="viewMode === 'month'"
                            class="grid grid-cols-7 gap-px bg-border"
                        >
                            <div
                                v-for="day in calendarDays"
                                :key="day.key"
                                class="bg-card p-2"
                            >
                                <div
                                    class="flex min-h-[120px] flex-col gap-2 rounded-md border p-2 transition hover:border-primary/40"
                                    :class="[
                                        day.isSelected
                                            ? 'border-primary/60 ring-1 ring-primary/20'
                                            : 'border-border/60',
                                        !day.isCurrentMonth ? 'bg-muted/40' : '',
                                    ]"
                                    @click="selectDay(day.date)"
                                >
                                    <div
                                        class="flex items-center justify-between"
                                    >
                                        <span
                                            class="text-xs font-semibold"
                                            :class="
                                                day.isToday
                                                    ? 'text-primary'
                                                    : day.isCurrentMonth
                                                      ? 'text-foreground'
                                                      : 'text-muted-foreground'
                                            "
                                        >
                                            {{ day.date.getDate() }}
                                        </span>
                                        <span
                                            v-if="day.isToday"
                                            class="rounded-full bg-primary/10 px-2 py-0.5 text-[10px] font-medium text-primary"
                                        >
                                            Today
                                        </span>
                                    </div>
                                    <div class="flex flex-col gap-1">
                                        <button
                                            v-for="appointment in day.appointments.slice(
                                                0,
                                                2,
                                            )"
                                            :key="appointment.id"
                                            type="button"
                                            class="group flex items-center justify-between gap-2 rounded-md border px-2 py-1 text-left text-[11px] font-medium"
                                            :class="
                                                getEventClass(appointment)
                                            "
                                            @click.stop="
                                                selectAppointment(appointment)
                                            "
                                        >
                                            <span class="truncate">
                                                {{ appointment.title }}
                                            </span>
                                            <span
                                                class="text-[10px] font-normal opacity-70"
                                            >
                                                {{
                                                    formatTime(
                                                        appointment.start_time,
                                                    )
                                                }}
                                            </span>
                                        </button>
                                        <span
                                            v-if="day.appointments.length > 2"
                                            class="text-[11px] text-muted-foreground"
                                        >
                                            +{{
                                                day.appointments.length - 2
                                            }}
                                            more
                                        </span>
                                        <span
                                            v-if="day.appointments.length === 0"
                                            class="text-[11px] text-muted-foreground"
                                        >
                                            No events
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div
                            v-else-if="viewMode === 'week'"
                            class="grid grid-cols-7 gap-px bg-border"
                        >
                            <div
                                v-for="day in weekDays"
                                :key="`week-${day.key}`"
                                class="bg-card p-3"
                            >
                                <div
                                    class="flex flex-col gap-2 rounded-md border p-2"
                                    :class="
                                        day.isSelected
                                            ? 'border-primary/60 ring-1 ring-primary/20'
                                            : 'border-border/60'
                                    "
                                    @click="selectDay(day.date)"
                                >
                                    <div
                                        class="flex items-center justify-between"
                                    >
                                        <span class="text-xs font-semibold">
                                            {{ formatDate(day.date) }}
                                        </span>
                                        <span
                                            v-if="day.isToday"
                                            class="rounded-full bg-primary/10 px-2 py-0.5 text-[10px] font-medium text-primary"
                                        >
                                            Today
                                        </span>
                                    </div>
                                    <div class="flex flex-col gap-1">
                                        <button
                                            v-for="appointment in day.appointments"
                                            :key="appointment.id"
                                            type="button"
                                            class="flex items-center justify-between gap-2 rounded-md border px-2 py-1 text-left text-[11px] font-medium"
                                            :class="
                                                getEventClass(appointment)
                                            "
                                            @click.stop="
                                                selectAppointment(appointment)
                                            "
                                        >
                                            <span class="truncate">
                                                {{ appointment.title }}
                                            </span>
                                            <span
                                                class="text-[10px] font-normal opacity-70"
                                            >
                                                {{
                                                    formatTime(
                                                        appointment.start_time,
                                                    )
                                                }}
                                            </span>
                                        </button>
                                        <span
                                            v-if="day.appointments.length === 0"
                                            class="text-[11px] text-muted-foreground"
                                        >
                                            No events
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else-if="viewMode === 'day'" class="p-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="text-sm text-muted-foreground">
                                        Day view
                                    </div>
                                    <div class="text-lg font-semibold">
                                        {{ formatDate(selectedDate) }}
                                    </div>
                                </div>
                                <Button
                                    variant="outline"
                                    size="sm"
                                    @click="openCreate"
                                >
                                    <Plus class="h-4 w-4" />
                                    Add
                                </Button>
                            </div>
                            <Separator class="my-4" />
                            <div class="space-y-3">
                                <div
                                    v-for="appointment in selectedAppointments"
                                    :key="`day-${appointment.id}`"
                                    class="flex items-start justify-between gap-4 rounded-md border p-3"
                                    :class="getEventClass(appointment)"
                                    @click="selectAppointment(appointment)"
                                >
                                    <div>
                                        <div class="text-sm font-semibold">
                                            {{ appointment.title }}
                                        </div>
                                        <div
                                            class="text-xs text-muted-foreground"
                                        >
                                            {{
                                                appointment.description ||
                                                'No description'
                                            }}
                                        </div>
                                    </div>
                                    <div class="text-xs text-muted-foreground">
                                        {{
                                            formatTime(
                                                appointment.start_time,
                                            )
                                        }}
                                        -
                                        {{ formatTime(appointment.end_time) }}
                                    </div>
                                </div>
                                <div
                                    v-if="selectedAppointments.length === 0"
                                    class="rounded-md border border-dashed p-6 text-sm text-muted-foreground"
                                >
                                    No appointments for this day.
                                </div>
                            </div>
                        </div>

                        <div v-else class="p-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="text-sm text-muted-foreground">
                                        Agenda
                                    </div>
                                    <div class="text-lg font-semibold">
                                        Upcoming appointments
                                    </div>
                                </div>
                                <Badge variant="outline">
                                    {{ sortedAppointments.length }} total
                                </Badge>
                            </div>
                            <Separator class="my-4" />
                            <div
                                v-if="agendaGroups.length === 0"
                                class="rounded-md border border-dashed p-6 text-sm text-muted-foreground"
                            >
                                No appointments found.
                            </div>
                            <div v-else class="space-y-4">
                                <div
                                    v-for="group in agendaGroups"
                                    :key="`agenda-${group.key}`"
                                    class="space-y-2"
                                >
                                    <div
                                        class="text-xs font-semibold uppercase tracking-wide text-muted-foreground"
                                    >
                                        {{ formatDate(group.date) }}
                                    </div>
                                    <div class="grid gap-2">
                                        <div
                                            v-for="appointment in group.items"
                                            :key="`agenda-item-${appointment.id}`"
                                            class="flex flex-wrap items-center justify-between gap-3 rounded-md border px-3 py-2"
                                            :class="
                                                getEventClass(appointment)
                                            "
                                            @click="
                                                selectAppointment(appointment)
                                            "
                                        >
                                            <div
                                                class="flex items-center gap-2"
                                            >
                                                <span
                                                    class="text-sm font-semibold"
                                                >
                                                    {{ appointment.title }}
                                                </span>
                                                <Badge
                                                    variant="outline"
                                                    class="text-[10px]"
                                                >
                                                    {{
                                                        formatTime(
                                                            appointment.start_time,
                                                        )
                                                    }}
                                                </Badge>
                                            </div>
                                            <div
                                                class="text-xs text-muted-foreground"
                                            >
                                                {{
                                                    appointment.location ||
                                                    'No location'
                                                }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
                <aside class="flex flex-col gap-6">
                    <Card>
                        <CardHeader>
                            <CardTitle class="text-sm font-medium">
                                Selected date
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="rounded-md border bg-muted/20 p-3">
                                <div class="text-xs text-muted-foreground">
                                    Date
                                </div>
                                <div class="text-sm font-semibold">
                                    {{ formatDate(selectedDate) }}
                                </div>
                                <div class="text-xs text-muted-foreground">
                                    {{ selectedAppointments.length }}
                                    appointment(s)
                                </div>
                            </div>
                            <div class="space-y-2">
                                <Button
                                    variant="outline"
                                    class="w-full"
                                    @click="openCreate"
                                >
                                    <Plus class="h-4 w-4" />
                                    New appointment
                                </Button>
                                <Button
                                    variant="ghost"
                                    class="w-full"
                                    @click="viewMode = 'day'"
                                >
                                    <Calendar class="h-4 w-4" />
                                    Day view
                                </Button>
                            </div>
                            <Separator />
                            <div class="space-y-3">
                                <div
                                    class="text-xs font-semibold uppercase tracking-wide text-muted-foreground"
                                >
                                    Appointment details
                                </div>
                                <div
                                    v-if="selectedAppointment"
                                    class="rounded-md border p-3 space-y-2"
                                >
                                    <div
                                        class="flex items-start justify-between gap-3"
                                    >
                                        <div class="text-sm font-semibold">
                                            {{ selectedAppointment.title }}
                                        </div>
                                        <Badge
                                            variant="outline"
                                            :class="
                                                getEventClass(
                                                    selectedAppointment,
                                                )
                                            "
                                        >
                                            {{
                                                formatTime(
                                                    selectedAppointment.start_time,
                                                )
                                            }}
                                        </Badge>
                                    </div>
                                    <div
                                        class="text-xs text-muted-foreground"
                                    >
                                        {{
                                            selectedAppointment.description ||
                                            'No description added yet.'
                                        }}
                                    </div>
                                    <div
                                        class="flex flex-col gap-2 text-xs text-muted-foreground"
                                    >
                                        <div class="flex items-center gap-2">
                                            <Clock class="h-4 w-4" />
                                            {{
                                                formatTime(
                                                    selectedAppointment.start_time,
                                                )
                                            }}
                                            -
                                            {{
                                                formatTime(
                                                    selectedAppointment.end_time,
                                                )
                                            }}
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <MapPin class="h-4 w-4" />
                                            {{
                                                selectedAppointment.location ||
                                                'No location'
                                            }}
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <User class="h-4 w-4" />
                                            Owner #{{
                                                selectedAppointment.user_id
                                            }}
                                        </div>
                                    </div>
                                </div>
                                <div
                                    v-else
                                    class="rounded-md border border-dashed p-4 text-xs text-muted-foreground"
                                >
                                    Select an appointment to see details.
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle class="text-sm font-medium">
                                Upcoming
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-3">
                            <div
                                v-for="appointment in upcomingAppointments"
                                :key="`upcoming-${appointment.id}`"
                                class="flex items-start justify-between gap-3 rounded-md border p-3 text-sm"
                                :class="getEventClass(appointment)"
                                @click="selectAppointment(appointment)"
                            >
                                <div class="space-y-1">
                                    <div class="font-semibold">
                                        {{ appointment.title }}
                                    </div>
                                    <div
                                        class="flex items-center gap-1 text-xs text-muted-foreground"
                                    >
                                        <Clock class="h-3.5 w-3.5" />
                                        {{
                                            formatTime(
                                                appointment.start_time,
                                            )
                                        }}
                                    </div>
                                </div>
                                <Badge variant="outline" class="text-[10px]">
                                    {{
                                        formatDate(
                                            parseDate(appointment.start_time) ||
                                                new Date(),
                                        )
                                    }}
                                </Badge>
                            </div>
                            <div
                                v-if="upcomingAppointments.length === 0"
                                class="rounded-md border border-dashed p-4 text-xs text-muted-foreground"
                            >
                                No upcoming appointments.
                            </div>
                        </CardContent>
                    </Card>
                </aside>
            </div>
        </div>
        <Dialog :open="isCreateOpen" @update:open="handleDialogOpen">
            <DialogContent class="sm:max-w-2xl">
                <DialogHeader>
                    <DialogTitle>New appointment</DialogTitle>
                    <DialogDescription>
                        Add a new appointment to the calendar.
                    </DialogDescription>
                </DialogHeader>
                <form class="grid gap-4" @submit.prevent="submit">
                    <div class="grid gap-2">
                        <Label for="title">Title</Label>
                        <Input
                            id="title"
                            v-model="form.title"
                            placeholder="Appointment title"
                        />
                        <InputError :message="form.errors.title" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="description">Description</Label>
                        <textarea
                            id="description"
                            v-model="form.description"
                            class="min-h-[100px] w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm outline-none focus-visible:border-ring focus-visible:ring-2 focus-visible:ring-ring/50"
                            placeholder="Add details or notes"
                        ></textarea>
                        <InputError :message="form.errors.description" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="location">Location</Label>
                        <Input
                            id="location"
                            v-model="form.location"
                            placeholder="Location or meeting link"
                        />
                        <InputError :message="form.errors.location" />
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="grid gap-2">
                            <Label for="start_time">Start</Label>
                            <Input
                                id="start_time"
                                v-model="form.start_time"
                                type="datetime-local"
                            />
                            <InputError :message="form.errors.start_time" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="end_time">End</Label>
                            <Input
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
                            Cancel
                        </Button>
                        <Button type="submit" :disabled="form.processing">
                            Save appointment
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
