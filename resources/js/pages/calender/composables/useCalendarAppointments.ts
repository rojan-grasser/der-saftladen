import { computed, type Ref } from 'vue';

import type { Appointment } from '../types';
import {
    isSameDay,
    parseDate,
    startOfWeek,
    toDateKey,
    toDayStart,
} from '../utils/date';

type UseCalendarAppointmentsOptions = {
    appointments: Ref<Appointment[]>;
    userId: Ref<number | null | undefined>;
    searchQuery: Ref<string>;
    showMine: Ref<boolean>;
    showTeam: Ref<boolean>;
    includePast: Ref<boolean>;
    currentMonth: Ref<Date>;
    selectedDate: Ref<Date>;
    selectedAppointmentId: Ref<number | null>;
};

export const useCalendarAppointments = ({
    appointments,
    userId,
    searchQuery,
    showMine,
    showTeam,
    includePast,
    currentMonth,
    selectedDate,
    selectedAppointmentId,
}: UseCalendarAppointmentsOptions) => {
    const monthLabel = computed(() => {
        return new Intl.DateTimeFormat('de-DE', {
            month: 'long',
            year: 'numeric',
        }).format(currentMonth.value);
    });

    const dayLabels = ['Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa', 'So'];

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

        return appointments.value.filter((appointment) => {
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
                userId.value == null
                    ? true
                    : appointment.user_id === userId.value;

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
                    (appointment) =>
                        appointment.id === selectedAppointmentId.value,
                ) ?? null
            );
        }
        return selectedAppointments.value[0] ?? null;
    });

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

    return {
        monthLabel,
        dayLabels,
        filteredAppointments,
        sortedAppointments,
        appointmentsByDate,
        calendarDays,
        weekDays,
        selectedDateKey,
        selectedAppointments,
        selectedAppointment,
        upcomingAppointments,
        agendaGroups,
        selectDay,
        selectAppointment,
    };
};
