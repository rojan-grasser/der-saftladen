import { useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

import appointments from '@/routes/appointments';

import type { Appointment } from '../types';
import {
    addMinutes,
    parseDate,
    toDateString,
    toInputDate,
    toInputDateTime,
    toTimeString,
    toUnixSeconds,
} from '../utils/date';

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

export const useAppointmentForm = () => {
    const isCreateOpen = ref(false);
    const isEditMode = ref(false);
    const editingAppointmentId = ref<number | null>(null);

    const form = useForm({
        title: '',
        description: '',
        location: '',
        start_time: '',
        end_time: '',
    });

    const isAllDay = ref(false);
    const allDayStart = ref('');
    const allDayEnd = ref('');
    const startDate = ref('');
    const startTime = ref('');
    const endDate = ref('');
    const endTime = ref('');

    const syncDateTimeFields = () => {
        startDate.value = toDateString(form.start_time);
        startTime.value = toTimeString(form.start_time);
        endDate.value = toDateString(form.end_time) || startDate.value;
        endTime.value = toTimeString(form.end_time) || startTime.value;
    };

    const applyAllDayTimes = () => {
        const startDateValue = allDayStart.value || toInputDate(new Date());
        let endDateValue = allDayEnd.value || startDateValue;

        if (endDateValue < startDateValue) {
            endDateValue = startDateValue;
        }

        allDayStart.value = startDateValue;
        allDayEnd.value = endDateValue;
        form.start_time = `${startDateValue}T00:00`;
        form.end_time = `${endDateValue}T23:59`;
    };

    const updateStartDateTime = () => {
        const date =
            startDate.value ||
            toDateString(form.start_time) ||
            toInputDate(new Date());
        const time = startTime.value || toTimeString(form.start_time) || '00:00';
        if (!startTime.value && time) {
            startTime.value = time;
        }
        form.start_time = `${date}T${time}`;
    };

    const updateEndDateTime = () => {
        const date =
            endDate.value ||
            startDate.value ||
            toDateString(form.end_time) ||
            toDateString(form.start_time) ||
            toInputDate(new Date());
        const time =
            endTime.value ||
            toTimeString(form.end_time) ||
            toTimeString(form.start_time) ||
            '00:00';
        if (!endDate.value && date) {
            endDate.value = date;
        }
        if (!endTime.value && time) {
            endTime.value = time;
        }
        form.end_time = `${date}T${time}`;
    };

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
        syncDateTimeFields();
    };

    const closeCreate = () => {
        isCreateOpen.value = false;
        isEditMode.value = false;
        editingAppointmentId.value = null;
        form.clearErrors();
        isAllDay.value = false;
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
        syncDateTimeFields();
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

    const handleDialogOpen = (value: boolean) => {
        if (!value) {
            closeCreate();
            return;
        }
        isCreateOpen.value = true;
    };

    form.transform((data) => ({
        ...data,
        start_time: toUnixSeconds(data.start_time),
        end_time: toUnixSeconds(data.end_time),
    }));

    watch(startDate, (value) => {
        if (isAllDay.value || !value) {
            return;
        }
        if (!endDate.value) {
            endDate.value = value;
        }
    });

    watch([startDate, startTime], () => {
        if (isAllDay.value) {
            return;
        }
        updateStartDateTime();
    });

    watch([endDate, endTime], () => {
        if (isAllDay.value) {
            return;
        }
        updateEndDateTime();
    });

    watch(isAllDay, (value) => {
        if (!value) {
            syncDateTimeFields();
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

    return {
        form,
        isCreateOpen,
        isEditMode,
        editingAppointmentId,
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
    };
};
