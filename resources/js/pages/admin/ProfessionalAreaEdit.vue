<script lang="ts" setup>
import { useForm } from '@inertiajs/vue3';
import { computed, onMounted, ref, watch } from 'vue';

import MultiCombobox from '@/components/MultiCombobox.vue';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import admin from '@/routes/admin';
import { type ProfessionalArea, User } from '@/types';

const open = defineModel<boolean>('open', { required: true });

const props = defineProps<{
    professionalArea: ProfessionalArea;
}>();

const emit = defineEmits<{
    (e: 'updated'): void;
}>();

type InstructorOption = Pick<User, 'id' | 'name' | 'email'>;

const instructorOptions = ref<InstructorOption[]>([]);
const loadingInstructors = ref(false);

const loadingMoreInstructors = ref(false);
const nextInstructorCursor = ref<number | null>(null);
const hasMoreInstructors = computed(() => nextInstructorCursor.value !== null);

const currentQuery = ref('');
let searchTimer: number | null = null;

type InstructorResponse = {
    items: InstructorOption[];
    next_cursor: number | null;
};

const instructorCache = ref<Map<number, InstructorOption>>(new Map());

function cacheInstructors(items: InstructorOption[]) {
    const map = instructorCache.value;
    for (const o of items) map.set(o.id, o);
}

async function fetchInstructors(params: {
    query: string;
    after?: number;
    append: boolean;
}) {
    const isLoadMore = params.append;

    if (isLoadMore) loadingMoreInstructors.value = true;
    else loadingInstructors.value = true;

    try {
        const url = new URL(admin.instructors().url, window.location.origin);
        if (params.query.trim() !== '')
            url.searchParams.set('query', params.query.trim());
        url.searchParams.set('limit', '25');
        if (typeof params.after === 'number' && params.after > 0) {
            url.searchParams.set('after', String(params.after));
        }

        const res = await fetch(url.toString());

        if (!res.ok) throw new Error('Failed to load instructors');

        const data = (await res.json()) as InstructorResponse;

        nextInstructorCursor.value = data.next_cursor;

        cacheInstructors(data.items);

        if (params.append) {
            const map = new Map<number, InstructorOption>();
            for (const o of instructorOptions.value) map.set(o.id, o);
            for (const o of data.items) map.set(o.id, o);
            instructorOptions.value = Array.from(map.values());
        } else {
            instructorOptions.value = data.items;
        }
    } finally {
        loadingInstructors.value = false;
        loadingMoreInstructors.value = false;
    }
}

function loadFirstPage(query: string) {
    currentQuery.value = query;
    nextInstructorCursor.value = null;
    fetchInstructors({ query, append: false });
}

function loadMore() {
    if (!hasMoreInstructors.value) return;
    if (loadingMoreInstructors.value || loadingInstructors.value) return;

    fetchInstructors({
        query: currentQuery.value,
        after: nextInstructorCursor.value ?? 0,
        append: true,
    });
}

function onInstructorSearch(q: string) {
    if (searchTimer) window.clearTimeout(searchTimer);
    searchTimer = window.setTimeout(() => {
        loadFirstPage(q);
    }, 250);
}

const selectedFromArea = computed<InstructorOption[]>(() =>
    (props.professionalArea.instructors ?? []).map((u) => ({
        id: u.id,
        name: u.name,
        email: u.email,
    })),
);

const mergedInstructorOptions = computed<InstructorOption[]>(() => {
    const map = new Map<number, InstructorOption>();
    for (const id of form.instructor_ids) {
        const cached = instructorCache.value.get(id);
        if (cached) map.set(id, cached);
    }
    for (const o of selectedFromArea.value) map.set(o.id, o);
    for (const o of instructorOptions.value) map.set(o.id, o);
    return Array.from(map.values());
});

const instructorItems = computed(() =>
    mergedInstructorOptions.value.map((u) => ({
        id: u.id,
        label: u.name,
        subLabel: u.email,
    })),
);

const form = useForm({
    name: props.professionalArea.name,
    description: props.professionalArea.description,
    instructor_ids: props.professionalArea.instructors?.map((u) => u.id) ?? [],
});

watch(
    () => props.professionalArea,
    (u) => {
        form.name = u.name;
        form.description = u.description;
        form.instructor_ids = u.instructors?.map((x) => x.id) ?? [];
        form.clearErrors();

        instructorCache.value = new Map();
        cacheInstructors(
            (u.instructors ?? []).map((x) => ({
                id: x.id,
                name: x.name,
                email: x.email,
            })),
        );
    },
    { immediate: true },
);

onMounted(() => {
    cacheInstructors(selectedFromArea.value);
    loadFirstPage('');
});

function close() {
    open.value = false;

    form.reset();
    form.clearErrors();
}

const submit = () => {
    form.put(admin.professionalArea.update(props.professionalArea.id).url, {
        preserveScroll: true,
        onSuccess: async () => {
            emit('updated');
            close();
        },
    });
};
</script>

<template>
    <Dialog :open="open" @update:open="(v) => (v ? (open = true) : close())">
        <DialogContent class="w-full sm:max-w-lg">
            <DialogHeader>
                <DialogTitle>Berufsbereich bearbeiten</DialogTitle>
                <DialogDescription>
                    Hier können Sie den Berufsbereich bearbeiten und Ausbilder
                    hinzufügen.
                </DialogDescription>
            </DialogHeader>

            <form class="space-y-4" @submit.prevent="submit">
                <div class="grid gap-2">
                    <Label for="name">Name</Label>
                    <Input id="name" v-model="form.name" />
                    <div v-if="form.errors.name" class="text-sm text-red-600">
                        {{ form.errors.name }}
                    </div>
                </div>

                <div class="grid gap-2">
                    <Label for="description">Beschreibung</Label>
                    <Textarea id="description" v-model="form.description" />
                    <div
                        v-if="form.errors.description"
                        class="text-sm text-red-600"
                    >
                        {{ form.errors.description }}
                    </div>
                </div>

                <div class="grid gap-2">
                    <Label>Ausbilder</Label>
                    <MultiCombobox
                        v-model="form.instructor_ids"
                        :empty-text="
                            loadingInstructors ? 'Lädt...' : 'Keine Treffer.'
                        "
                        :has-more="hasMoreInstructors"
                        :items="instructorItems"
                        :loading-more="loadingMoreInstructors"
                        placeholder="Ausbilder auswählen..."
                        search-placeholder="Name oder E-Mail..."
                        @loadMore="loadMore"
                        @search="onInstructorSearch"
                    />
                    <div
                        v-if="form.errors.instructor_ids"
                        class="text-sm text-red-600"
                    >
                        {{ form.errors.instructor_ids }}
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4">
                    <Button
                        :disabled="form.processing"
                        type="button"
                        variant="outline"
                        @click="close"
                    >
                        Abbrechen
                    </Button>
                    <Button :disabled="form.processing" type="submit">
                        Speichern
                    </Button>
                </div>
            </form>
        </DialogContent>
    </Dialog>
</template>
