<script lang="ts" setup>
import { useForm } from '@inertiajs/vue3';
import debounce from 'debounce';
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';

import MultiCombobox from '@/components/MultiCombobox.vue';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import admin from '@/routes/admin';
import { Instructor, PaginatedCursorResponse, type ProfessionalArea } from '@/types';

const open = defineModel<boolean>('open', { required: true });

const props = defineProps<{
    professionalArea: ProfessionalArea;
}>();

const emit = defineEmits<{
    (e: 'updated'): void;
}>();

const instructorOptions = ref<Instructor[]>([]);
const loadingInstructors = ref(false);

const loadingMoreInstructors = ref(false);
const nextInstructorCursor = ref<string | null>(null);
const hasMoreInstructors = computed(() => nextInstructorCursor.value !== null);

const currentQuery = ref('');

const instructorCache = ref<Map<number, Instructor>>(new Map());

function cacheInstructors(instructors: Instructor[]) {
    const map = instructorCache.value;
    for (const o of instructors) map.set(o.id, o);
}

async function fetchInstructors(params: {
    query: string;
    cursor?: string | null;
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

        if (params.cursor) {
            url.searchParams.set('cursor', params.cursor);
        }

        const res = await fetch(url.toString());

        if (!res.ok) throw new Error('Failed to load instructors');

        const instructors =
            (await res.json()) as PaginatedCursorResponse<Instructor>;

        nextInstructorCursor.value = instructors.next_cursor;

        cacheInstructors(instructors.data);

        if (isLoadMore) {
            const map = new Map<number, Instructor>();
            for (const o of instructorOptions.value) map.set(o.id, o);
            for (const o of instructors.data) map.set(o.id, o);
            instructorOptions.value = Array.from(map.values());
        } else {
            instructorOptions.value = instructors.data;
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
        cursor: nextInstructorCursor.value,
        append: true,
    });
}

const debouncedLoadFirstPage = debounce((q: string) => {
    loadFirstPage(q);
}, 250);

function onInstructorSearch(q: string) {
    debouncedLoadFirstPage(q);
}

const selectedFromArea = computed<Instructor[]>(() =>
    (props.professionalArea.instructors ?? []).map((u) => ({
        id: u.id,
        name: u.name,
        email: u.email,
    })),
);

const mergedInstructorOptions = computed<Instructor[]>(() => {
    const map = new Map<number, Instructor>();
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

onBeforeUnmount(() => {
    debouncedLoadFirstPage.clear();
});

function close() {
    open.value = false;

    form.reset();
    form.clearErrors();

    debouncedLoadFirstPage.clear();
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
        <DialogContent class="max-h-[90%] w-full overflow-y-auto sm:max-w-2xl">
            <DialogHeader>
                <DialogTitle>Berufsbereich bearbeiten</DialogTitle>
                <DialogDescription>
                    Hier können Sie den Berufsbereich bearbeiten und Ausbilder
                    hinzufügen.
                </DialogDescription>
            </DialogHeader>

            <form class="space-y-4">
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
                    <Button :disabled="form.processing" @click="submit">
                        Speichern
                    </Button>
                </div>
            </form>
        </DialogContent>
    </Dialog>
</template>
