export type ViewMode = 'month' | 'week' | 'day' | 'agenda';

export interface Appointment {
    id: number;
    title: string;
    description?: string | null;
    location?: string | null;
    start_time: number | string;
    end_time: number | string;
    user_id: number;
    creator?: {
        id: number;
        name: string;
    } | null;
}

export interface CalendarDay {
    date: Date;
    key: string;
    isCurrentMonth: boolean;
    isToday: boolean;
    isSelected: boolean;
    appointments: Appointment[];
}

export interface WeekDay {
    date: Date;
    key: string;
    isToday: boolean;
    isSelected: boolean;
    appointments: Appointment[];
}

export interface AgendaGroup {
    key: string;
    date: Date;
    items: Appointment[];
}
