<script lang="ts" setup>
import { Head, router } from '@inertiajs/vue3';
import { Pencil, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';

import PaginationBar from '@/components/PaginationBar.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
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

defineProps<{
    professionalAreas: PaginatedResponse<ProfessionalArea>;
}>();

const handlePageChange = (page: number) => {
    router.get(
        admin.professionalArea().url,
        { page: page },
        { preserveState: true, replace: true },
    );
};

const isCreateOpen = ref(false);

const isEditOpen = ref(false);
const editingProfessionalArea = ref<ProfessionalArea | null>(null);

const editProfessionalArea = (professionalArea: ProfessionalArea) => {
    editingProfessionalArea.value = professionalArea;
    isEditOpen.value = true;
};

const isDeleteOpen = ref(false);
const deletingProfessionalArea = ref<ProfessionalArea | null>(null);

const askDeleteProfessionalArea = (professionalArea: ProfessionalArea) => {
    deletingProfessionalArea.value = professionalArea;
    isDeleteOpen.value = true;
};

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin Dashboard', href: admin.dashboard().url },
    { title: 'Berufsbereiche', href: admin.professionalArea().url },
];
</script>

<template>
    <Head title="Berufsbereiche" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-6">
            <div class="flex items-center">
                <Button @click="isCreateOpen = true">Neu erstellen</Button>
            </div>
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
                                        class="mr-3 mb-1 mt-1"
                                        >{{ teacher.name }}
                                    </Badge>
                                </div>
                            </TableCell>
                            <TableCell>
                                <TooltipProvider>
                                    <Tooltip>
                                        <TooltipTrigger as-child>
                                            <Button
                                                aria-label="Bearbeiten"
                                                class="mr-2"
                                                size="icon-sm"
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
                                                aria-label="Löschen"
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
                                colspan="3"
                            >
                                Keine Berufsbereiche gefunden.
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
            <PaginationBar
                :current-page="professionalAreas.current_page"
                :per-page="professionalAreas.per_page"
                :total="professionalAreas.total"
                @page-change="handlePageChange"
            />
        </div>
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
