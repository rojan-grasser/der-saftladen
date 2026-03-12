export type ViewMode = 'month' | 'week' | 'day';
export type AppointmentColor =
    | 'peacock'
    | 'sage'
    | 'grape'
    | 'flamingo'
    | 'banana'
    | 'tangerine'
    | 'lavender'
    | 'graphite'
    | 'blueberry'
    | 'basil'
    | 'tomato';

export interface Appointment {
    id: number;
    title: string;
    description?: string | null;
    location?: string | null;
    color?: AppointmentColor | null;
    start_time: number | string;
    end_time: number | string;
    user_id: number;
    creator?: {
        id: number;
        name: string;
    } | null;
}

export interface CalendarPermissions {
    canCreate: boolean;
    canEditOwn: boolean;
    canEditAll: boolean;
    canDeleteAll: boolean;
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
