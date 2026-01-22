<script lang="ts" setup>
import { Head, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import {
    Pagination,
    PaginationContent,
    PaginationEllipsis,
    PaginationFirst,
    PaginationItem,
    PaginationLast,
    PaginationNext,
    PaginationPrevious,
} from '@/components/ui/pagination';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import UserEdit from '@/pages/admin/UserEdit.vue';
import admin from '@/routes/admin';
import {
    type BreadcrumbItem,
    type PaginatedResponse,
    type User,
    UserStatus,
} from '@/types';

const statusVariants: Record<
    UserStatus,
    'default' | 'destructive' | 'outline'
> = {
    active: 'default',
    inactive: 'destructive',
    pending: 'outline',
};

const props = defineProps<{
    users: PaginatedResponse<User>;
    filters: {
        role?: string;
        status?: string;
    };
}>();

const roleFilter = ref(props.filters?.role || 'all');
const statusFilter = ref(props.filters?.status || 'all');

const editingUser = ref<User | null>(null);
const isEditModalOpen = ref(false);

const openEditModal = (user: User) => {
    editingUser.value = user;
    isEditModalOpen.value = true;
};

const closeEditModal = () => {
    isEditModalOpen.value = false;
    editingUser.value = null;
};

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
    { title: 'Users', href: admin.users().url },
];
</script>

<template>
    <Head title="User Management" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <Select v-model="roleFilter">
                        <SelectTrigger class="w-40">
                            <SelectValue placeholder="All Roles" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">All Roles</SelectItem>
                            <SelectItem value="admin">Admin</SelectItem>
                            <SelectItem value="user">User</SelectItem>
                            <SelectItem value="instructor"
                                >Instructor</SelectItem
                            >
                            <SelectItem value="teacher">Teacher</SelectItem>
                        </SelectContent>
                    </Select>

                    <Select v-model="statusFilter">
                        <SelectTrigger class="w-40">
                            <SelectValue placeholder="All Statuses" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">All Statuses</SelectItem>
                            <SelectItem value="active">Active</SelectItem>
                            <SelectItem value="pending">Pending</SelectItem>
                            <SelectItem value="inactive">Inactive</SelectItem>
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
                        Clear
                    </Button>
                </div>
            </div>

            <div class="rounded-md border bg-card">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Name</TableHead>
                            <TableHead>Email</TableHead>
                            <TableHead>Role</TableHead>
                            <TableHead>Status</TableHead>
                            <TableHead>Actions</TableHead>
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
                            <TableCell>
                                <Badge class="capitalize" variant="secondary">
                                    {{ user.role }}
                                </Badge>
                            </TableCell>
                            <TableCell>
                                <Badge
                                    :variant="statusVariants[user.status]"
                                    class="capitalize"
                                >
                                    {{ user.status }}
                                </Badge>
                            </TableCell>
                            <TableCell>
                                <Button
                                    class="h-auto p-0"
                                    variant="link"
                                    @click="openEditModal(user)"
                                >
                                    Edit
                                </Button>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="users.data.length === 0">
                            <TableCell
                                class="h-24 text-center text-muted-foreground"
                                colspan="5"
                            >
                                No users found.
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
            <div
                v-if="users.total > users.per_page"
                class="flex items-center justify-end px-2"
            >
                <Pagination
                    v-slot="{ page }"
                    :default-page="users.current_page"
                    :items-per-page="users.per_page"
                    :sibling-count="1"
                    :total="users.total"
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
                                    class="h-10 w-10 p-0"
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
        <Dialog :open="isEditModalOpen" @update:open="closeEditModal">
            <DialogContent class="sm:max-w-106.25">
                <DialogHeader>
                    <DialogTitle>Edit User</DialogTitle>
                    <DialogDescription>
                        Update the user's profile information and permissions.
                    </DialogDescription>
                </DialogHeader>
                <UserEdit
                    v-if="editingUser"
                    :user="editingUser"
                    @close="closeEditModal"
                />
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
