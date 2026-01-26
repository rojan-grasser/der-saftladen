<script lang="ts" setup>
import { computed, ref, watch } from 'vue';

import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Command, CommandEmpty, CommandGroup, CommandInput, CommandItem } from '@/components/ui/command';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';

type Item = {
    id: number;
    label: string;
    subLabel?: string;
};

const props = defineProps<{
    items: Item[];
    modelValue: number[];
    placeholder?: string;
    searchPlaceholder?: string;
    emptyText?: string;

    hasMore?: boolean;
    loadingMore?: boolean;
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: number[]): void;
    (e: 'search', value: string): void;
    (e: 'loadMore'): void;
}>();

const open = ref(false);
const search = ref('');

const selectedItems = computed(() => {
    const set = new Set(props.modelValue);
    return props.items.filter((i) => set.has(i.id));
});

watch(search, (v) => {
    emit('search', v);
});

function toggle(id: number) {
    const set = new Set(props.modelValue);
    if (set.has(id)) set.delete(id);
    else set.add(id);
    emit('update:modelValue', Array.from(set));
}

function remove(id: number) {
    emit(
        'update:modelValue',
        props.modelValue.filter((x) => x !== id),
    );
}

function clearAll() {
    emit('update:modelValue', []);
}

function onListScroll(e: Event) {
    const el = e.target as HTMLElement | null;
    if (!el) return;

    const thresholdPx = 80;
    const nearBottom =
        el.scrollTop + el.clientHeight >= el.scrollHeight - thresholdPx;

    if (
        nearBottom &&
        (props.hasMore ?? false) &&
        !(props.loadingMore ?? false)
    ) {
        emit('loadMore');
    }
}
</script>

<template>
    <div class="space-y-2">
        <Popover v-model:open="open">
            <PopoverTrigger as-child>
                <Button
                    class="w-full justify-between"
                    type="button"
                    variant="outline"
                >
                    <span class="truncate">
                        <template v-if="selectedItems.length">
                            {{ selectedItems.length }} ausgewählt
                        </template>
                        <template v-else>
                            {{ placeholder ?? 'Ausbilder auswählen…' }}
                        </template>
                    </span>
                    <span class="text-muted-foreground">⌄</span>
                </Button>
            </PopoverTrigger>

            <PopoverContent class="w-[--radix-popover-trigger-width] p-0">
                <Command>
                    <div class="flex items-center gap-2 p-2">
                        <CommandInput
                            v-model="search"
                            :placeholder="searchPlaceholder ?? 'Suchen...'"
                        />
                        <Button
                            :disabled="!modelValue.length"
                            class="h-8 px-2"
                            size="sm"
                            type="button"
                            variant="ghost"
                            @click="clearAll"
                        >
                            Leeren
                        </Button>
                    </div>

                    <CommandEmpty>
                        {{ emptyText ?? 'Keine Treffer.' }}
                    </CommandEmpty>

                    <CommandGroup
                        class="max-h-72 overflow-auto"
                        @scroll="onListScroll"
                    >
                        <CommandItem
                            v-for="item in items"
                            :key="item.id"
                            :value="item.label"
                            @select="toggle(item.id)"
                        >
                            <div
                                class="flex w-full items-center justify-between gap-3"
                            >
                                <div class="min-w-0">
                                    <div class="truncate">{{ item.label }}</div>
                                    <div
                                        v-if="item.subLabel"
                                        class="truncate text-xs text-muted-foreground"
                                    >
                                        {{ item.subLabel }}
                                    </div>
                                </div>

                                <div class="text-sm">
                                    <span v-if="modelValue.includes(item.id)"
                                        >✓</span
                                    >
                                </div>
                            </div>
                        </CommandItem>

                        <div
                            v-if="loadingMore"
                            class="px-3 py-2 text-xs text-muted-foreground"
                        >
                            Lädt mehr...
                        </div>
                    </CommandGroup>
                </Command>
            </PopoverContent>
        </Popover>

        <div v-if="selectedItems.length" class="flex flex-wrap gap-2">
            <Badge
                v-for="item in selectedItems"
                :key="item.id"
                class="gap-2"
                variant="secondary"
            >
                <span class="max-w-[18rem] truncate">{{ item.label }}</span>
                <button
                    aria-label="Remove"
                    class="text-muted-foreground hover:text-foreground"
                    type="button"
                    @click="remove(item.id)"
                >
                    ×
                </button>
            </Badge>
        </div>
    </div>
</template>
