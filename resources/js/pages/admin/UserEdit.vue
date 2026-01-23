<script lang="ts" setup>
import { useForm } from '@inertiajs/vue3';
import { watch } from 'vue';

import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import admin from '@/routes/admin';
import { type User } from '@/types';

const open = defineModel<boolean>('open', { required: true });

const props = defineProps<{
    user: User;
}>();

const emit = defineEmits<{
    (e: 'updated'): void;
}>();

const form = useForm({
    name: props.user.name,
    email: props.user.email,
    role: props.user.role,
    status: props.user.status,
});

watch(
    () => props.user,
    (u) => {
        form.name = u.name;
        form.email = u.email;
        form.role = u.role;
        form.status = u.status;
        form.clearErrors();
    },
    { immediate: true },
);

function close() {
    open.value = false;
    form.reset();
    form.clearErrors();
}

const submit = () => {
    form.put(admin.users.update(props.user.id).url, {
        onSuccess: () => {
            emit('updated');
            close();
        },
        preserveScroll: true,
    });
};
</script>

<template>
    <Dialog :open="open" @update:open="(v) => (v ? (open = true) : close())">
        <DialogContent class="sm:max-w-106.25">
            <DialogHeader>
                <DialogTitle>Benutzer bearbeiten</DialogTitle>
                <DialogDescription>
                    Hier können Sie die Benutzerinformationen und Berechtigungen
                    aktualisieren.
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
                    <Label for="email">Email</Label>
                    <Input id="email" v-model="form.email" type="email" />
                    <div v-if="form.errors.email" class="text-sm text-red-600">
                        {{ form.errors.email }}
                    </div>
                </div>

                <div class="flex gap-4">
                    <div class="grid flex-1 gap-2">
                        <Label for="role">Rolle</Label>
                        <Select v-model="form.role">
                            <SelectTrigger class="w-full">
                                <SelectValue placeholder="Rolle auswählen" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="admin">Admin</SelectItem>
                                <SelectItem value="user">Benutzer</SelectItem>
                                <SelectItem value="instructor"
                                    >Ausbilder</SelectItem
                                >
                                <SelectItem value="teacher">Lehrer</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <div class="grid flex-1 gap-2">
                        <Label for="status">Status</Label>
                        <Select v-model="form.status">
                            <SelectTrigger class="w-full">
                                <SelectValue placeholder="Status auswählen" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="active">Aktiv</SelectItem>
                                <SelectItem value="pending"
                                    >Ausstehend</SelectItem
                                >
                                <SelectItem value="inactive"
                                    >Inaktiv</SelectItem
                                >
                            </SelectContent>
                        </Select>
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
