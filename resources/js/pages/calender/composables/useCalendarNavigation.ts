import { type Ref } from 'vue';

import type { ViewMode } from '../types';

type UseCalendarNavigationOptions = {
    currentMonth: Ref<Date>;
    selectedDate: Ref<Date>;
    viewMode: Ref<ViewMode>;
    selectDay: (date: Date) => void;
};

export const useCalendarNavigation = ({
    currentMonth,
    selectedDate,
    viewMode,
    selectDay,
}: UseCalendarNavigationOptions) => {
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

    const handleSelectDay = (date: Date) => {
        syncCurrentMonth(date);
        selectDay(date);
    };

    const goToday = () => {
        handleSelectDay(new Date());
    };

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

    return {
        syncCurrentMonth,
        goPrevMonth,
        goNextMonth,
        handleSelectDay,
        goToday,
        goPrev,
        goNext,
    };
};
