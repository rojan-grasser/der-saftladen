<script lang="ts" setup>
import { Head, router } from '@inertiajs/vue3';
import debounce from 'debounce';
import { Pencil, Trash2 } from 'lucide-vue-next';
import { ref, watch } from 'vue';

import PaginationBar from '@/components/PaginationBar.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import {
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger,
} from '@/components/ui/tooltip';
import AppLayout from '@/layouts/AppLayout.vue';
import ProfessionalAreaCreate from '@/pages/admin/ProfessionalAreaCreate.vue';
import ProfessionalAreaDeleteAlert from '@/pages/admin/ProfessionalAreaDeleteAlert.vue';
import ProfessionalAreaEdit from '@/pages/admin/ProfessionalAreaEdit.vue';
import admin from '@/routes/admin';
import type {
    BreadcrumbItem,
    PaginatedResponse,
    ProfessionalArea,
} from '@/types';

// --------------------------------------------------
// Props
// --------------------------------------------------
const props = defineProps<{
    professionalAreas: PaginatedResponse<ProfessionalArea>;
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

const editingProfessionalArea = ref<ProfessionalArea | null>(null);
const deletingProfessionalArea = ref<ProfessionalArea | null>(null);

// --------------------------------------------------
// CRUD Actions
// --------------------------------------------------
const editProfessionalArea = (professionalArea: ProfessionalArea) => {
    editingProfessionalArea.value = professionalArea;
    isEditOpen.value = true;
};

const askDeleteProfessionalArea = (professionalArea: ProfessionalArea) => {
    deletingProfessionalArea.value = professionalArea;
    isDeleteOpen.value = true;
};

// --------------------------------------------------
// Fetch (Search + Pagination)
// --------------------------------------------------
const fetchProfessionalAreas = (page = 1, value = search.value) => {
    router.get(
        admin.professionalArea().url,
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
    fetchProfessionalAreas(1, value);
}, 500);

// Watch search
watch(search, (value) => {
    debouncedFetch(value);
});

// Pagination handler
const handlePageChange = (page: number) => {
    fetchProfessionalAreas(page);
};

// --------------------------------------------------
// Breadcrumbs
// --------------------------------------------------
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin Dashboard', href: admin.dashboard().url },
    { title: 'Berufsbereiche', href: admin.professionalArea().url },
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
                            fetchProfessionalAreas(1);
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
                            v-for="professionalArea in professionalAreas.data"
                            :key="professionalArea.id"
                        >
                            <TableCell class="whitespace-normal">
                                {{ professionalArea.name }}
                            </TableCell>
                            <TableCell class="whitespace-normal">
                                {{ professionalArea.description }}
                            </TableCell>
                            <TableCell class="break-all whitespace-normal">
                                <div
                                    v-if="
                                        professionalArea.instructors &&
                                        professionalArea.instructors.length > 0
                                    "
                                >
                                    <Badge
                                        v-for="teacher in professionalArea.instructors"
                                        :key="teacher.id"
                                        class="mr-2 mb-1"
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
                                                size="icon-sm"
                                                class="mr-2"
                                                @click="
                                                    editProfessionalArea(
                                                        professionalArea,
                                                    )
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
                                                    askDeleteProfessionalArea(
                                                        professionalArea,
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

                        <TableRow v-if="professionalAreas.data.length === 0">
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
                :current-page="professionalAreas.current_page"
                :per-page="professionalAreas.per_page"
                :total="professionalAreas.total"
                @page-change="handlePageChange"
            />
        </div>

        <!-- Modals -->
        <ProfessionalAreaCreate v-model:open="isCreateOpen" />

        <ProfessionalAreaEdit
            v-if="editingProfessionalArea"
            v-model:open="isEditOpen"
            :professionalArea="editingProfessionalArea"
        />

        <ProfessionalAreaDeleteAlert
            v-if="deletingProfessionalArea"
            v-model:open="isDeleteOpen"
            :professionalArea="deletingProfessionalArea"
        />
    </AppLayout>
</template>
