<script lang="ts" setup>
import { useForm } from '@inertiajs/vue3';

import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import admin from '@/routes/admin';
import { type User } from '@/types';

const props = defineProps<{
    user: User;
}>();

const emit = defineEmits(['close']);

const form = useForm({
    name: props.user.name,
    email: props.user.email,
    role: props.user.role,
    status: props.user.status,
});

const submit = () => {
    form.put(admin.users.update(props.user.id).url, {
        onSuccess: () => emit('close'),
        preserveScroll: true,
    });
};
</script>

<template>
    <form class="space-y-4" @submit.prevent="submit">
        <div class="grid gap-2">
            <Label for="name">Name</Label>
            <Input id="name" v-model="form.name" :error="form.errors.name" />
        </div>

        <div class="grid gap-2">
            <Label for="email">Email</Label>
            <Input
                id="email"
                v-model="form.email"
                :error="form.errors.email"
                type="email"
            />
        </div>

        <div class="flex gap-4">
            <div class="grid flex-1 gap-2">
                <Label for="role">Role</Label>
                <Select v-model="form.role">
                    <SelectTrigger class="w-full">
                        <SelectValue placeholder="Select role" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="admin">Admin</SelectItem>
                        <SelectItem value="user">User</SelectItem>
                        <SelectItem value="instructor">Instructor</SelectItem>
                        <SelectItem value="teacher">Teacher</SelectItem>
                    </SelectContent>
                </Select>
            </div>

            <div class="grid flex-1 gap-2">
                <Label for="status">Status</Label>
                <Select v-model="form.status">
                    <SelectTrigger class="w-full">
                        <SelectValue placeholder="Select status" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="active">Active</SelectItem>
                        <SelectItem value="pending">Pending</SelectItem>
                        <SelectItem value="inactive">Inactive</SelectItem>
                    </SelectContent>
                </Select>
            </div>
        </div>

        <div class="flex justify-end gap-3 pt-4">
            <Button type="button" variant="outline" @click="emit('close')">
                Cancel
            </Button>
            <Button :disabled="form.processing" type="submit">
                Save Changes
            </Button>
        </div>
    </form>
</template>
