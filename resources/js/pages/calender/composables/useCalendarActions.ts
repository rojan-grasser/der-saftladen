import { router } from '@inertiajs/vue3';
import { type Ref } from 'vue';

import appointmentAPI from '@/routes/appointments';

import type { Appointment } from '../types';
import { parseDate } from '../utils/date';

type UseCalendarActionsOptions = {
    canCreateAppointments: Ref<boolean>;
    canEditAppointment: (appointment: Appointment | null) => boolean;
    detailsOpen: Ref<boolean>;
    selectedAppointmentId: Ref<number | null>;
    selectedDate: Ref<Date>;
    openEdit: (appointment: Appointment) => void;
    openCreate: (date?: Date) => void;
    selectAppointment: (appointment: Appointment) => void;
    syncCurrentMonth: (date: Date) => void;
};

export const useCalendarActions = ({
    canCreateAppointments,
    canEditAppointment,
    detailsOpen,
    selectedAppointmentId,
    selectedDate,
    openEdit,
    openCreate,
    selectAppointment,
    syncCurrentMonth,
}: UseCalendarActionsOptions) => {
    const startCreate = () => {
        if (!canCreateAppointments.value) return;
        openCreate(new Date(selectedDate.value));
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
            appointmentAPI.update(appointment.id).url,
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

    return {
        startCreate,
        startEdit,
        openDetails,
        handleAppointmentDeleted,
        handleMoveAppointment,
    };
};
