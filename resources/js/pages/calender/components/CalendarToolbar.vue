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
</script>

<template>
    <div class="flex flex-wrap items-center justify-end gap-4">
        <div class="flex flex-wrap items-center gap-3">
            <div class="relative">
                <Search
                    class="pointer-events-none absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                />
                <Input
                    v-model="searchValue"
                    class="h-9 w-48 pl-9"
                    placeholder="Termine suchen"
                />
            </div>

            <Button variant="outline" @click="emit('today')"> Heute </Button>

            <div class="flex items-center rounded-md border bg-card">
                <Button
                    variant="ghost"
                    size="icon"
                    class="h-9 w-9"
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
                    class="h-9 w-9"
                    @click="emit('next-month')"
                >
                    <ChevronRight class="h-4 w-4" />
                </Button>
            </div>

            <Select v-model="viewValue">
                <SelectTrigger class="w-36">
                    <SelectValue placeholder="Ansicht" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem value="month">Monat</SelectItem>
                    <SelectItem value="week">Woche</SelectItem>
                    <SelectItem value="day">Tag</SelectItem>
                    <SelectItem value="agenda">Agenda</SelectItem>
                </SelectContent>
            </Select>

            <Button @click="emit('create')">
                <Plus class="h-4 w-4" />
                Neuer Termin
            </Button>
        </div>
    </div>
</template>
