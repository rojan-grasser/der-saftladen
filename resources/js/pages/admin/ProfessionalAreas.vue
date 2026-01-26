<script lang="ts" setup>
import { Head, router, useForm } from '@inertiajs/vue3';
import { Pencil, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';

import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle
} from '@/components/ui/alert-dialog';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Pagination,
    PaginationContent,
    PaginationEllipsis,
    PaginationFirst,
    PaginationItem,
    PaginationLast,
    PaginationNext,
    PaginationPrevious
} from '@/components/ui/pagination';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import AppLayout from '@/layouts/AppLayout.vue';
import ProfessionalAreaCreate from '@/pages/admin/ProfessionalAreaCreate.vue';
import ProfessionalAreaEdit from '@/pages/admin/ProfessionalAreaEdit.vue';
import admin from '@/routes/admin';
import type { BreadcrumbItem, PaginatedResponse, ProfessionalArea } from '@/types';

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

const deleteForm = useForm({});
const isDeleteOpen = ref(false);
const deleteCandidate = ref<ProfessionalArea | null>(null);

const askDeleteProfessionalArea = (professionalArea: ProfessionalArea) => {
    deleteCandidate.value = professionalArea;
    isDeleteOpen.value = true;
};

const confirmDeleteProfessionalArea = () => {
    if (!deleteCandidate.value) return;

    deleteForm.delete(
        admin.professionalArea.destroy(deleteCandidate.value.id).url,
        {
            preserveScroll: true,
            onFinish: () => {
                isDeleteOpen.value = false;
                deleteCandidate.value = null;
            },
        },
    );
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
                            <TableCell>
                                {{ professionalArea.name }}
                            </TableCell>
                            <TableCell class="break-all whitespace-normal">
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
                                        class="mr-3 mb-2"
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
                                                :disabled="
                                                    deleteForm.processing
                                                "
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
            <div
                v-if="professionalAreas.total > professionalAreas.per_page"
                class="flex items-center justify-end px-2"
            >
                <Pagination
                    v-slot="{ page }"
                    :default-page="professionalAreas.current_page"
                    :items-per-page="professionalAreas.per_page"
                    :total="professionalAreas.total"
                    @update:page="handlePageChange"
                >
                    <PaginationContent
                        v-slot="{ items }"
                        class="flex items-center gap-1"
                    >
                        <PaginationFirst />
                        <PaginationPrevious />

                        <template v-for="(item, index) in items">
                            <PaginationItem
                                v-if="item.type === 'page'"
                                :key="index"
                                :value="item.value"
                                as-child
                            >
                                <Button
                                    :variant="
                                        item.value === page
                                            ? 'default'
                                            : 'outline'
                                    "
                                >
                                    {{ item.value }}
                                </Button>
                            </PaginationItem>
                            <PaginationEllipsis
                                v-else
                                :key="item.type"
                                :index="index"
                            />
                        </template>

                        <PaginationNext />
                        <PaginationLast />
                    </PaginationContent>
                </Pagination>
            </div>
        </div>
        <ProfessionalAreaCreate v-model:open="isCreateOpen" />
        <ProfessionalAreaEdit
            v-if="editingProfessionalArea"
            v-model:open="isEditOpen"
            :professionalArea="editingProfessionalArea"
        />
        <AlertDialog
            :open="isDeleteOpen"
            @update:open="(v: boolean) => (isDeleteOpen = v)"
        >
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>Berufsbereich löschen?</AlertDialogTitle>
                    <AlertDialogDescription>
                        Dieser Vorgang kann nicht rückgängig gemacht werden.
                        Möchten Sie
                        <span class="font-medium">
                            {{ deleteCandidate?.name }}
                        </span>
                        wirklich löschen?
                    </AlertDialogDescription>
                </AlertDialogHeader>

                <AlertDialogFooter>
                    <AlertDialogCancel :disabled="deleteForm.processing">
                        Abbrechen
                    </AlertDialogCancel>
                    <AlertDialogAction
                        :disabled="deleteForm.processing"
                        @click="confirmDeleteProfessionalArea"
                    >
                        Löschen
                    </AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    </AppLayout>
</template>
