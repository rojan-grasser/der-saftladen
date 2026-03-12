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
    canCreate: boolean;
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
    <div class="rounded-3xl border bg-card p-5 shadow-sm">
        <div class="flex flex-col gap-5">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div class="space-y-2">
                    <div
                        class="text-xs font-semibold tracking-[0.24em] text-muted-foreground uppercase"
                    >
                        Kalender
                    </div>
                    <div class="text-3xl font-semibold tracking-tight">
                        {{ monthLabel }}
                    </div>
                    <p class="max-w-2xl text-sm text-muted-foreground">
                        {{ viewLabel }} mit klarer &Uuml;bersicht und einem
                        gemeinsamen Kalenderbereich.
                    </p>
                </div>

                <div class="flex items-start gap-3 lg:justify-end">
                    <div
                        v-if="!canCreate"
                        class="inline-flex h-12 items-center rounded-2xl border border-dashed px-4 text-sm font-medium text-muted-foreground"
                    >
                        Nur Lesemodus
                    </div>
                    <Button
                        v-if="canCreate"
                        size="lg"
                        class="h-12 rounded-2xl px-6 text-base font-semibold shadow-sm"
                        @click="emit('create')"
                    >
                        <Plus class="h-5 w-5" />
                        Termin erstellen
                    </Button>
                </div>
            </div>

            <div class="flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between">
                <div class="relative w-full xl:max-w-sm">
                    <Search
                        class="pointer-events-none absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                    />
                    <Input
                        v-model="searchValue"
                        class="h-11 rounded-2xl pl-9"
                        placeholder="Termine suchen"
                    />
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <div class="flex items-center rounded-2xl border bg-muted/20 p-1">
                        <Button
                            variant="ghost"
                            size="icon"
                            class="h-9 w-9 rounded-xl"
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
                            class="h-9 w-9 rounded-xl"
                            @click="emit('next-month')"
                        >
                            <ChevronRight class="h-4 w-4" />
                        </Button>
                    </div>

                    <Button variant="outline" class="rounded-2xl" @click="emit('today')">
                        Heute
                    </Button>

                    <Select v-model="viewValue">
                        <SelectTrigger class="w-40 rounded-2xl">
                            <SelectValue placeholder="Ansicht" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="month">Monat</SelectItem>
                            <SelectItem value="week">Woche</SelectItem>
                            <SelectItem value="day">Tag</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
            </div>
        </div>
    </div>
</template>
