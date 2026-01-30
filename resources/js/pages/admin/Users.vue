<script lang="ts" setup>
import { Head, router } from '@inertiajs/vue3';
import { Pencil } from 'lucide-vue-next';
import { ref, watch } from 'vue';

import PaginationBar from '@/components/PaginationBar.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import { roleLabels, statusLabels, statusVariants } from '@/constants/user';
import AppLayout from '@/layouts/AppLayout.vue';
import UserEdit from '@/pages/admin/UserEdit.vue';
import admin from '@/routes/admin';
import { type BreadcrumbItem, type PaginatedResponse, type User } from '@/types';

const props = defineProps<{
    users: PaginatedResponse<User>;
    filters: {
        role?: string;
        status?: string;
    };
}>();

const roleFilter = ref(props.filters?.role || 'all');
const statusFilter = ref(props.filters?.status || 'all');

const handlePageChange = (page: number) => {
    router.get(
        admin.users().url,
        {
            role: roleFilter.value === 'all' ? undefined : roleFilter.value,
            status:
                statusFilter.value === 'all' ? undefined : statusFilter.value,
            page: page,
        },
        {
            preserveState: true,
            replace: true,
        },
    );
};

const isEditOpen = ref(false);
const editingUser = ref<User | null>(null);

const editUser = (user: User) => {
    editingUser.value = user;
    isEditOpen.value = true;
};

watch([roleFilter, statusFilter], ([newRole, newStatus]) => {
    router.get(
        admin.users().url,
        {
            role: newRole === 'all' ? undefined : newRole,
            status: newStatus === 'all' ? undefined : newStatus,
        },
        {
            preserveState: true,
            replace: true,
        },
    );
});

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin Dashboard', href: admin.dashboard().url },
    { title: 'Benutzerverwaltung', href: admin.users().url },
];
</script>

<template>
    <Head title="Benutzerverwaltung" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <Select v-model="roleFilter">
                        <SelectTrigger class="w-40">
                            <SelectValue placeholder="Alle Rollen" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">Alle Rollen</SelectItem>
                            <SelectItem value="admin">Admin</SelectItem>
                            <SelectItem value="user">Benutzer</SelectItem>
                            <SelectItem value="instructor"
                                >Ausbilder</SelectItem
                            >
                            <SelectItem value="teacher">Lehrer</SelectItem>
                        </SelectContent>
                    </Select>

                    <Select v-model="statusFilter">
                        <SelectTrigger class="w-40">
                            <SelectValue placeholder="Alle Status" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">Alle Status</SelectItem>
                            <SelectItem value="active">Aktiv</SelectItem>
                            <SelectItem value="pending">Ausstehend</SelectItem>
                            <SelectItem value="inactive">Inaktiv</SelectItem>
                        </SelectContent>
                    </Select>

                    <Button
                        v-if="roleFilter !== 'all' || statusFilter !== 'all'"
                        variant="ghost"
                        @click="
                            roleFilter = 'all';
                            statusFilter = 'all';
                        "
                    >
                        Leeren
                    </Button>
                </div>
            </div>

            <div class="rounded-md border bg-card">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Name</TableHead>
                            <TableHead>Email</TableHead>
                            <TableHead>Rollen</TableHead>
                            <TableHead>Status</TableHead>
                            <TableHead>Aktionen</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="user in users.data" :key="user.id">
                            <TableCell class="font-medium">
                                {{ user.name }}
                            </TableCell>
                            <TableCell class="text-muted-foreground">
                                {{ user.email }}
                            </TableCell>
                            <TableCell class="break-all whitespace-normal">
                                <Badge
                                    v-for="role in user.roles"
                                    :key="role.id"
                                    class="mt-1 mr-1 mb-1 capitalize"
                                    variant="secondary"
                                >
                                    {{ roleLabels[role.role] }}
                                </Badge>
                            </TableCell>
                            <TableCell>
                                <Badge
                                    :variant="statusVariants[user.status]"
                                    class="capitalize"
                                >
                                    {{ statusLabels[user.status] }}
                                </Badge>
                            </TableCell>
                            <TableCell
                                ><TooltipProvider>
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
                                        <TooltipContent>
                                            Bearbeiten
                                        </TooltipContent>
                                    </Tooltip>
                                </TooltipProvider>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="users.data.length === 0">
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
            <PaginationBar
                :current-page="users.current_page"
                :per-page="users.per_page"
                :total="users.total"
                @page-change="handlePageChange"
            />
        </div>
        <UserEdit
            v-if="editingUser"
            v-model:open="isEditOpen"
            :user="editingUser"
        />
    </AppLayout>
</template>
