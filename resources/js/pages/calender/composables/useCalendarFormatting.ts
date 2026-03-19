import { defaultAppointmentColor } from '../constants';
import type { Appointment } from '../types';
import { parseDate } from '../utils/date';

export const useCalendarFormatting = () => {
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

    const getOwnerName = (appointment: Appointment) => {
        if (!appointment.creator) return 'Unbekannt';
        const { first_name, last_name } = appointment.creator;
        return `${first_name} ${last_name}`.trim() || 'Unbekannt';
    };

    const isPastAppointment = (appointment: Appointment): boolean => {
        const end = parseDate(appointment.end_time);
        return end !== null && end < new Date();
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

    return {
        formatTime,
        formatDate,
        formatFullDate,
        getOwnerName,
        isPastAppointment,
        getEventBgClass,
    };
};
