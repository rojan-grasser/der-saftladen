import { computed, type Ref } from 'vue';

import type { Appointment, CalendarPermissions } from '../types';

type UseCalendarPermissionsOptions = {
    permissions: Ref<CalendarPermissions>;
    userId: Ref<number | null | undefined>;
    selectedAppointment: Ref<Appointment | null>;
};

export const useCalendarPermissions = ({
    permissions,
    userId,
    selectedAppointment,
}: UseCalendarPermissionsOptions) => {
    const canCreateAppointments = computed(() => permissions.value.canCreate);

    const canEditAppointment = (appointment: Appointment | null): boolean => {
        if (!appointment) return false;
        if (permissions.value.canEditAll) return true;
        if (permissions.value.canEditOwn && appointment.user_id === userId.value)
            return true;
        return false;
    };

    const canDeleteAppointment = (appointment: Appointment | null): boolean => {
        if (!appointment) return false;
        return permissions.value.canDeleteAll;
    };

    const canEditSelectedAppointment = computed(() =>
        canEditAppointment(selectedAppointment.value),
    );

    const canDeleteSelectedAppointment = computed(() =>
        canDeleteAppointment(selectedAppointment.value),
    );

    return {
        canCreateAppointments,
        canEditAppointment,
        canDeleteAppointment,
        canEditSelectedAppointment,
        canDeleteSelectedAppointment,
    };
};
