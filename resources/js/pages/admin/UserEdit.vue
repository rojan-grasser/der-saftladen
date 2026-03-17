<script lang="ts" setup>
import { useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';

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

const roleLabels: Record<string, string> = {
    admin: 'Admin',
    instructor: 'Ausbilder',
    teacher: 'Lehrer',
};

const form = useForm({
    first_name: props.user.first_name,
    last_name: props.user.last_name,
    email: props.user.email,
    company: props.user.company ?? '', // <-- NEU: immer string für v-model
    roles: props.user.roles?.map((r) => r.role) ?? [],
    status: props.user.status,
});

const selectedRoleLabels = computed(() => {
    return form.roles.map((role) => roleLabels[role] ?? role).filter(Boolean);
});

const rolesSummary = computed(() => {
    const count = selectedRoleLabels.value.length;

    if (count === 0) {
        return 'Rolle auswählen';
    }

    if (count <= 2) {
        return selectedRoleLabels.value.join(', ');
    }

    return `${count} Rollen ausgewählt`;
});

watch(
    () => props.user,
    (u) => {
        form.first_name = u.first_name;
        form.last_name = u.last_name;
        form.email = u.email;
        form.company = u.company ?? ''; // <-- NEU
        form.roles = u.roles?.map((r) => r.role) ?? [];
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
                    Hier können Sie die Benutzerinformationen und Berechtigungen aktualisieren.
                </DialogDescription>
            </DialogHeader>

            <form class="space-y-4" @submit.prevent="submit">
                <div class="flex gap-4">
                    <div class="grid min-w-0 flex-1 gap-2">
                        <Label for="first_name">Vorname</Label>
                        <Input id="first_name" v-model="form.first_name" />
                        <div v-if="form.errors.first_name" class="text-sm text-red-600">
                            {{ form.errors.first_name }}
                        </div>
                    </div>
                    <div class="grid min-w-0 flex-1 gap-2">
                        <Label for="last_name">Nachname</Label>
                        <Input id="last_name" v-model="form.last_name" />
                        <div v-if="form.errors.last_name" class="text-sm text-red-600">
                            {{ form.errors.last_name }}
                        </div>
                    </div>
                </div>

                <div class="grid gap-2">
                    <Label for="email">Email</Label>
                    <Input id="email" v-model="form.email" type="email" />
                    <div v-if="form.errors.email" class="text-sm text-red-600">
                        {{ form.errors.email }}
                    </div>
                </div>

                <div class="grid gap-2">
                    <Label for="company">Unternehmen</Label>
                    <Input id="company" v-model="form.company" placeholder="Unternehmen (optional)" />
                    <div v-if="form.errors.company" class="text-sm text-red-600">
                        {{ form.errors.company }}
                    </div>
                </div>

                <div class="flex gap-4">
                    <div class="grid min-w-0 flex-1 gap-2">
                        <Label for="role">Rollen</Label>
                        <Select v-model="form.roles" multiple>
                            <SelectTrigger class="w-full min-w-0">
                                <SelectValue>
                                    {{ rolesSummary }}
                                </SelectValue>
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="admin">Admin</SelectItem>
                                <SelectItem value="instructor">Ausbilder</SelectItem>
                                <SelectItem value="teacher">Lehrer</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <div class="grid min-w-0 flex-1 gap-2">
                        <Label for="status">Status</Label>
                        <Select v-model="form.status">
                            <SelectTrigger class="w-full">
                                <SelectValue placeholder="Status auswählen" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="active">Aktiv</SelectItem>
                                <SelectItem value="pending">Ausstehend</SelectItem>
                                <SelectItem value="inactive">Inaktiv</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4">
                    <Button :disabled="form.processing" type="button" variant="outline" @click="close">
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