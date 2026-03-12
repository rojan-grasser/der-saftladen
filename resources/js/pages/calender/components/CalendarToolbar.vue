<script setup lang="ts">
import { ChevronLeft, ChevronRight, Plus, Search } from 'lucide-vue-next';
import { computed } from 'vue';

import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';

import type { ViewMode } from '../types';

const props = defineProps<{
    searchQuery: string;
    viewMode: ViewMode;
    monthLabel: string;
}>();

const emit = defineEmits<{
    (e: 'update:searchQuery', value: string): void;
    (e: 'update:viewMode', value: ViewMode): void;
    (e: 'today'): void;
    (e: 'prev-month'): void;
    (e: 'next-month'): void;
    (e: 'create'): void;
}>();

const searchValue = computed({
    get: () => props.searchQuery,
    set: (value: string) => emit('update:searchQuery', value),
});

const viewValue = computed({
    get: () => props.viewMode,
    set: (value: ViewMode) => emit('update:viewMode', value),
});

const viewLabel = computed(() => {
    if (props.viewMode === 'week') {
        return 'Wochenansicht';
    }

    if (props.viewMode === 'day') {
        return 'Tagesansicht';
    }

    return 'Monatsansicht';
});
</script>

<template>
    <div class="rounded-2xl border bg-card p-4 shadow-sm">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
            <div class="space-y-1">
                <div
                    class="text-xs font-semibold tracking-[0.24em] text-muted-foreground uppercase"
                >
                    Kalender
                </div>
                <div class="text-2xl font-semibold">
                    {{ monthLabel }}
                </div>
                <p class="text-sm text-muted-foreground">
                    {{ viewLabel }} mit Übersicht links, Kalender in der Mitte und Details rechts.
                </p>
            </div>

            <div class="flex flex-col gap-3 xl:items-end">
                <div class="flex flex-wrap items-center gap-3">
                    <div class="relative">
                        <Search
                            class="pointer-events-none absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                        />
                        <Input
                            v-model="searchValue"
                            class="h-10 w-60 pl-9"
                            placeholder="Termine suchen"
                        />
                    </div>

                    <div class="flex items-center rounded-xl border bg-muted/20 p-1">
                        <Button
                            variant="ghost"
                            size="icon"
                            class="h-9 w-9 rounded-lg"
                            @click="emit('prev-month')"
                        >
                            <ChevronLeft class="h-4 w-4" />
                        </Button>
                        <span class="px-3 text-sm font-medium">
                            {{ monthLabel }}
                        </span>
                        <Button
                            variant="ghost"
                            size="icon"
                            class="h-9 w-9 rounded-lg"
                            @click="emit('next-month')"
                        >
                            <ChevronRight class="h-4 w-4" />
                        </Button>
                    </div>

                    <Button variant="outline" @click="emit('today')">
                        Heute
                    </Button>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <Select v-model="viewValue">
                        <SelectTrigger class="w-40">
                            <SelectValue placeholder="Ansicht" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="month">Monat</SelectItem>
                            <SelectItem value="week">Woche</SelectItem>
                            <SelectItem value="day">Tag</SelectItem>
                        </SelectContent>
                    </Select>

                    <Button @click="emit('create')">
                        <Plus class="h-4 w-4" />
                        Neuer Termin
                    </Button>
                </div>
            </div>
        </div>
    </div>
</template>
