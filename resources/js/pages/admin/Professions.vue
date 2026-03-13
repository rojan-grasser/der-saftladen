<script lang="ts" setup>
import { Head, router } from '@inertiajs/vue3';
import debounce from 'debounce';
import { Pencil, Trash2 } from 'lucide-vue-next';
import { ref, watch } from 'vue';

import PaginationBar from '@/components/PaginationBar.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import AppLayout from '@/layouts/AppLayout.vue';
import ProfessionCreate from '@/pages/admin/ProfessionCreate.vue';
import ProfessionDeleteAlert from '@/pages/admin/ProfessionDeleteAlert.vue';
import ProfessionEdit from '@/pages/admin/ProfessionEdit.vue';
import admin from '@/routes/admin';
import type { BreadcrumbItem, PaginatedResponse, Profession } from '@/types';

// --------------------------------------------------
// Props
// --------------------------------------------------
const props = defineProps<{
    professions: PaginatedResponse<Profession>;
    filters?: {
        query?: string;
    };
}>();

// --------------------------------------------------
// State
// --------------------------------------------------
const search = ref(props.filters?.query ?? '');

const isCreateOpen = ref(false);
const isEditOpen = ref(false);
const isDeleteOpen = ref(false);

const editingProfession = ref<Profession | null>(null);
const deletingProfession = ref<Profession | null>(null);

// --------------------------------------------------
// CRUD Actions
// --------------------------------------------------
const editProfession = (profession: Profession) => {
    editingProfession.value = profession;
    isEditOpen.value = true;
};

const askDeleteProfession = (profession: Profession) => {
    deletingProfession.value = profession;
    isDeleteOpen.value = true;
};

// --------------------------------------------------
// Fetch (Search + Pagination)
// --------------------------------------------------
const fetchProfessions = (page = 1, value = search.value) => {
    router.get(
        admin.profession().url,
        {
            query: value || undefined,
            page,
        },
        {
            preserveState: true,
            replace: true,
        },
    );
};

// Debounced search
const debouncedFetch = debounce((value: string) => {
    fetchProfessions(1, value);
}, 500);

// Watch search
watch(search, (value) => {
    debouncedFetch(value);
});

// Pagination handler
const handlePageChange = (page: number) => {
    fetchProfessions(page);
};

// --------------------------------------------------
// Breadcrumbs
// --------------------------------------------------
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin Dashboard', href: admin.dashboard().url },
    { title: 'Berufsbereiche', href: admin.profession().url },
];
</script>

<template>
    <Head title="Berufsbereiche" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-6">
            <!-- Actions -->
            <div class="flex flex-wrap items-center gap-3">
                <Input
                    v-model="search"
                    class="w-64"
                    placeholder="Berufsbereich suchen…"
                />

                <Button @click="isCreateOpen = true">Neu erstellen</Button>

                <Button
                    v-if="search"
                    variant="ghost"
                    @click="
                        () => {
                            search = '';
                            fetchProfessions(1);
                        }
                    "
                >
                    Leeren
                </Button>
            </div>

            <!-- Table -->
            <div class="rounded-md border bg-card">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Name</TableHead>
                            <TableHead>Beschreibung</TableHead>
                            <TableHead>Ausbilder</TableHead>
                            <TableHead>Aktionen</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow
                            v-for="profession in professions.data"
                            :key="profession.id"
                        >
                            <TableCell class="whitespace-normal">
                                {{ profession.name }}
                            </TableCell>
                            <TableCell class="whitespace-normal">
                                {{ profession.description }}
                            </TableCell>
                            <TableCell class="break-all whitespace-normal">
                                <div
                                    v-if="
                                        profession.instructors &&
                                        profession.instructors.length > 0
                                    "
                                >
                                    <Badge
                                        v-for="teacher in profession.instructors"
                                        :key="teacher.id"
                                        class="mt-1 mr-2 mb-1"
                                    >
                                        {{ teacher.name }}
                                    </Badge>
                                </div>
                            </TableCell>
                            <TableCell>
                                <TooltipProvider>
                                    <Tooltip>
                                        <TooltipTrigger as-child>
                                            <Button
                                                class="mr-2"
                                                size="icon-sm"
                                                @click="
                                                    editProfession(profession)
                                                "
                                            >
                                                <Pencil />
                                            </Button>
                                        </TooltipTrigger>
                                        <TooltipContent>
                                            Bearbeiten
                                        </TooltipContent>
                                    </Tooltip>
                                </TooltipProvider>

                                <TooltipProvider>
                                    <Tooltip>
                                        <TooltipTrigger as-child>
                                            <Button
                                                size="icon-sm"
                                                variant="destructive"
                                                @click="
                                                    askDeleteProfession(
                                                        profession,
                                                    )
                                                "
                                            >
                                                <Trash2 />
                                            </Button>
                                        </TooltipTrigger>
                                        <TooltipContent>
                                            Löschen
                                        </TooltipContent>
                                    </Tooltip>
                                </TooltipProvider>
                            </TableCell>
                        </TableRow>

                        <TableRow v-if="professions.data.length === 0">
                            <TableCell
                                class="h-24 text-center text-muted-foreground"
                                colspan="4"
                            >
                                Keine Berufsbereiche gefunden.
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <!-- Pagination -->
            <PaginationBar
                :current-page="professions.current_page"
                :per-page="professions.per_page"
                :total="professions.total"
                @page-change="handlePageChange"
            />
        </div>

        <!-- Modals -->
        <ProfessionCreate v-model:open="isCreateOpen" />

        <ProfessionEdit
            v-if="editingProfession"
            v-model:open="isEditOpen"
            :profession="editingProfession"
        />

        <ProfessionDeleteAlert
            v-if="deletingProfession"
            v-model:open="isDeleteOpen"
            :profession="deletingProfession"
        />
    </AppLayout>
</template>
