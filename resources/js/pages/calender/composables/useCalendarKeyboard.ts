import { onMounted, onUnmounted, ref, type Ref } from 'vue';

import type { ViewMode } from '../types';

type UseCalendarKeyboardOptions = {
    isCreateOpen: Ref<boolean>;
    detailsOpen: Ref<boolean>;
    viewMode: Ref<ViewMode>;
    goToday: () => void;
    startCreate: () => void;
    goPrev: () => void;
    goNext: () => void;
};

export const useCalendarKeyboard = ({
    isCreateOpen,
    detailsOpen,
    viewMode,
    goToday,
    startCreate,
    goPrev,
    goNext,
}: UseCalendarKeyboardOptions) => {
    const showShortcutsHelp = ref(false);

    const handleKeyDown = (e: KeyboardEvent) => {
        const target = e.target as HTMLElement;
        if (
            target.tagName === 'INPUT' ||
            target.tagName === 'TEXTAREA' ||
            target.isContentEditable ||
            e.ctrlKey ||
            e.metaKey ||
            e.altKey
        )
            return;

        if (e.key === 'Escape') {
            if (showShortcutsHelp.value) {
                e.preventDefault();
                showShortcutsHelp.value = false;
            }
            return;
        }

        if (isCreateOpen.value || detailsOpen.value) return;

        switch (e.key) {
            case 't':
            case 'T':
                e.preventDefault();
                goToday();
                break;
            case 'd':
            case 'D':
                e.preventDefault();
                viewMode.value = 'day';
                break;
            case 'w':
            case 'W':
                e.preventDefault();
                viewMode.value = 'week';
                break;
            case 'm':
            case 'M':
                e.preventDefault();
                viewMode.value = 'month';
                break;
            case 'a':
            case 'A':
                e.preventDefault();
                viewMode.value = 'agenda';
                break;
            case 'n':
            case 'N':
                e.preventDefault();
                startCreate();
                break;
            case 'ArrowLeft':
                e.preventDefault();
                goPrev();
                break;
            case 'ArrowRight':
                e.preventDefault();
                goNext();
                break;
            case '?':
                e.preventDefault();
                showShortcutsHelp.value = !showShortcutsHelp.value;
                break;
        }
    };

    onMounted(() => {
        document.addEventListener('keydown', handleKeyDown);
    });

    onUnmounted(() => {
        document.removeEventListener('keydown', handleKeyDown);
    });

    return { showShortcutsHelp };
};
