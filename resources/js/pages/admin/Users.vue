<script lang="ts" setup>
import { Head, router } from '@inertiajs/vue3';
import debounce from 'debounce';
import { Pencil } from 'lucide-vue-next';
import { ref, watch } from 'vue';

import PaginationBar from '@/components/PaginationBar.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import { roleLabels, statusLabels, statusVariants } from '@/constants/user';
import AppLayout from '@/layouts/AppLayout.vue';
import UserEdit from '@/pages/admin/UserEdit.vue';
import admin from '@/routes/admin';
import { type BreadcrumbItem, type PaginatedResponse, type User, type UserRole, type UserStatus } from '@/types';

// ------------------------------
// Props
// ------------------------------
const props = defineProps<{
    users: PaginatedResponse<User>;
    filters: {
        role?: string;
        status?: string;
        search?: string;
    };
}>();

// ------------------------------
// Reactive States
// ------------------------------
const search = ref(props.filters?.search || '');
const roleFilter = ref(props.filters?.role || 'all');
const statusFilter = ref(props.filters?.status || 'all');

const isEditOpen = ref(false);
const editingUser = ref<User | null>(null);

// ------------------------------
// Edit Modal
// ------------------------------
const editUser = (user: User) => {
    editingUser.value = user;
    isEditOpen.value = true;
};

// ------------------------------
// Serverseitige Abfrage / Filter / Pagination
// ------------------------------
const fetchUsers = (page = 1, searchValue = search.value) => {
    router.get(
        admin.users().url,
        {
            search: searchValue || undefined,
            role: roleFilter.value === 'all' ? undefined : roleFilter.value,
            status:
                statusFilter.value === 'all' ? undefined : statusFilter.value,
            page,
        },
        { preserveState: true, replace: true },
    );
};

// Debounced Funktion für Suche
const debouncedFetchUsers = debounce(
    (value: string) => fetchUsers(1, value),
    500,
);

// Watch für Filter → immer Seite 1 laden
watch([roleFilter, statusFilter], () => fetchUsers(1));

// Watch für Suche → Debounced
watch(search, (value) => {
    debouncedFetchUsers(value);
});

// Pagination Handler
const handlePageChange = (page: number) => fetchUsers(page);

// Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin Dashboard', href: admin.dashboard().url },
    { title: 'Benutzerverwaltung', href: admin.users().url },
];
</script>

<template>
    <Head title="Benutzerverwaltung" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-6">
            <!-- Filter-Leiste -->
            <div class="flex flex-wrap items-center gap-3">
                <!-- Suche -->
                <Input
                    v-model="search"
                    class="w-64"
                    placeholder="Nutzer suchen…"
                />

                <!-- Rolle -->
                <Select v-model="roleFilter">
                    <SelectTrigger class="w-40"
                        ><SelectValue placeholder="Alle Rollen"
                    /></SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">Alle Rollen</SelectItem>
                        <SelectItem value="admin">Admin</SelectItem>
                        <SelectItem value="user">Benutzer</SelectItem>
                        <SelectItem value="instructor">Ausbilder</SelectItem>
                        <SelectItem value="teacher">Lehrer</SelectItem>
                    </SelectContent>
                </Select>

                <!-- Status -->
                <Select v-model="statusFilter">
                    <SelectTrigger class="w-40"
                        ><SelectValue placeholder="Alle Status"
                    /></SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">Alle Status</SelectItem>
                        <SelectItem value="active">Aktiv</SelectItem>
                        <SelectItem value="pending">Ausstehend</SelectItem>
                        <SelectItem value="inactive">Inaktiv</SelectItem>
                    </SelectContent>
                </Select>

                <!-- Reset -->
                <Button
                    v-if="
                        search || roleFilter !== 'all' || statusFilter !== 'all'
                    "
                    variant="ghost"
                    @click="
                        () => {
                            search = '';
                            roleFilter = 'all';
                            statusFilter = 'all';
                            fetchUsers(1);
                        }
                    "
                >
                    Leeren
                </Button>
            </div>

            <!-- Benutzer-Tabelle -->
            <div class="rounded-md border bg-card">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Name</TableHead>
                            <TableHead>Email</TableHead>
                            <TableHead>Rolle</TableHead>
                            <TableHead>Status</TableHead>
                            <TableHead>Aktionen</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow
                            v-for="user in props.users.data"
                            :key="user.id"
                        >
                            <TableCell class="font-medium">
                                {{ user.name }}
                            </TableCell>
                            <TableCell class="text-muted-foreground">
                                {{ user.email }}
                            </TableCell>
                            <TableCell>
                                <Badge class="capitalize" variant="secondary">
                                    {{ roleLabels[user.role as UserRole] }}
                                </Badge>
                            </TableCell>
                            <TableCell>
                                <Badge
                                    :variant="
                                        statusVariants[
                                            user.status as UserStatus
                                        ]
                                    "
                                    class="capitalize"
                                >
                                    {{
                                        statusLabels[user.status as UserStatus]
                                    }}
                                </Badge>
                            </TableCell>
                            <TableCell>
                                <TooltipProvider>
                                    <Tooltip>
                                        <TooltipTrigger as-child>
                                            <Button
                                                aria-label="Bearbeiten"
                                                size="icon-sm"
                                                @click="editUser(user)"
                                            >
                                                <Pencil />
                                            </Button>
                                        </TooltipTrigger>
                                        <TooltipContent
                                            >Bearbeiten</TooltipContent
                                        >
                                    </Tooltip>
                                </TooltipProvider>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="props.users.data.length === 0">
                            <TableCell
                                class="h-24 text-center text-muted-foreground"
                                colspan="5"
                            >
                                Keine Benutzer gefunden.
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <!-- Pagination -->
            <PaginationBar
                :current-page="props.users.current_page"
                :per-page="props.users.per_page"
                :total="props.users.total"
                @page-change="handlePageChange"
            />
        </div>

        <!-- Edit Modal -->
        <UserEdit
            v-if="editingUser"
            v-model:open="isEditOpen"
            :user="editingUser"
        />
    </AppLayout>
</template>
