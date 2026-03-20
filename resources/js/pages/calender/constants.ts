import type { AppointmentColor } from './types';

export const defaultAppointmentColor: AppointmentColor = 'peacock';

export const appointmentColorOptions: Array<{
    value: AppointmentColor;
    label: string;
    tone: string;
    swatchClass: string;
    eventClass: string;
}> = [
    {
        value: 'peacock',
        label: 'Peacock',
        tone: 'Pale Blue',
        swatchClass: 'bg-sky-400',
        eventClass: 'bg-sky-500/10 text-sky-700 border-sky-200 dark:border-sky-500/40 dark:text-sky-300',
    },
    {
        value: 'sage',
        label: 'Sage',
        tone: 'Pale Green',
        swatchClass: 'bg-emerald-400',
        eventClass: 'bg-emerald-500/10 text-emerald-700 border-emerald-200 dark:border-emerald-500/40 dark:text-emerald-300',
    },
    {
        value: 'grape',
        label: 'Grape',
        tone: 'Mauve',
        swatchClass: 'bg-fuchsia-400',
        eventClass: 'bg-fuchsia-500/10 text-fuchsia-700 border-fuchsia-200 dark:border-fuchsia-500/40 dark:text-fuchsia-300',
    },
    {
        value: 'flamingo',
        label: 'Flamingo',
        tone: 'Pale Red',
        swatchClass: 'bg-pink-400',
        eventClass: 'bg-pink-500/10 text-pink-700 border-pink-200 dark:border-pink-500/40 dark:text-pink-300',
    },
    {
        value: 'banana',
        label: 'Banana',
        tone: 'Yellow',
        swatchClass: 'bg-yellow-400',
        eventClass: 'bg-yellow-500/15 text-yellow-700 border-yellow-200 dark:border-yellow-500/40 dark:text-yellow-300',
    },
    {
        value: 'tangerine',
        label: 'Tangerine',
        tone: 'Orange',
        swatchClass: 'bg-orange-400',
        eventClass: 'bg-orange-500/10 text-orange-700 border-orange-200 dark:border-orange-500/40 dark:text-orange-300',
    },
    {
        value: 'lavender',
        label: 'Lavender',
        tone: 'Cyan',
        swatchClass: 'bg-violet-400',
        eventClass: 'bg-violet-500/10 text-violet-700 border-violet-200 dark:border-violet-500/40 dark:text-violet-300',
    },
    {
        value: 'graphite',
        label: 'Graphite',
        tone: 'Gray',
        swatchClass: 'bg-slate-500',
        eventClass: 'bg-slate-500/10 text-slate-700 border-slate-200 dark:border-slate-500/40 dark:text-slate-300',
    },
    {
        value: 'blueberry',
        label: 'Blueberry',
        tone: 'Blue',
        swatchClass: 'bg-blue-500',
        eventClass: 'bg-blue-500/10 text-blue-700 border-blue-200 dark:border-blue-500/40 dark:text-blue-300',
    },
    {
        value: 'basil',
        label: 'Basil',
        tone: 'Green',
        swatchClass: 'bg-lime-500',
        eventClass: 'bg-lime-500/10 text-lime-700 border-lime-200 dark:border-lime-500/40 dark:text-lime-300',
    },
    {
        value: 'tomato',
        label: 'Tomato',
        tone: 'Red',
        swatchClass: 'bg-red-500',
        eventClass: 'bg-red-500/10 text-red-700 border-red-200 dark:border-red-500/40 dark:text-red-300',
    },
];

export const appointmentColorMap = Object.fromEntries(
    appointmentColorOptions.map((option) => [option.value, option]),
) as Record<AppointmentColor, (typeof appointmentColorOptions)[number]>;
