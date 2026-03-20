<!-- resources/js/components/profile/AvatarUploader.vue -->
<script setup lang="ts">
import { router, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogFooter,
} from '@/components/ui/dialog';

import UserAvatar from '@/components/UserAvatar.vue';
import { deleteAvatar } from '@/composables/avatar';
import avatar from '@/routes/profile/avatar';

const avatarSrc = ref<string | null>(null);
const uploading = ref(false);
const removing = ref(false);
const errorMsg = ref<string | null>(null);
const showConfirm = ref(false);

async function onFileChange(e: Event) {
    const input = e.target as HTMLInputElement;
    const file = input.files?.[0];
    if (!file) return;

    uploading.value = true;
    errorMsg.value = null;

    try {
        router.post(
            avatar.store().url,
            { file },
            {
                forceFormData: true,
            },
        );
    } catch (err: any) {
        errorMsg.value = err.message ?? 'Upload fehlgeschlagen';
    }

    uploading.value = false;
    input.value = '';
}

const page = usePage();

function onDelete() {
    showConfirm.value = true;
}

async function confirmDelete() {
    showConfirm.value = false;

    removing.value = true;
    errorMsg.value = null;

    try {
        const res = await deleteAvatar();
        avatarSrc.value = null;
        page.props.auth.user.updated_at = res.updated_at;
        page.props.auth.user.avatar = undefined;
        router.reload({ only: ['auth'] });
    } catch (err: any) {
        errorMsg.value = err.message ?? 'Löschen fehlgeschlagen';
    }

    removing.value = false;
}
</script>

<template>
    <div class="grid gap-2">
        <Label for="avatar">Profilbild</Label>

        <div class="flex items-center gap-4">
            <Input
                id="avatar"
                type="file"
                accept="image/png,image/jpeg,image/webp"
                @change="onFileChange"
            />
            <Button variant="secondary" :disabled="removing" @click="onDelete">
                {{ removing ? 'Entfernt…' : 'Entfernen' }}
            </Button>
        </div>

        <div class="mt-3 flex items-center gap-4">
            <UserAvatar :user="page.props.auth.user" />
        </div>

        <p v-if="errorMsg" class="text-sm text-red-600">{{ errorMsg }}</p>
    </div>

    <!-- Confirm Modal -->
    <Dialog v-model:open="showConfirm">
        <DialogContent>
            <DialogHeader>
                <DialogTitle>Profilbild löschen?</DialogTitle>
            </DialogHeader>

            <p class="text-sm text-muted-foreground">
                Möchtest du dein Profilbild wirklich entfernen?
            </p>

            <DialogFooter>
                <Button variant="ghost" @click="showConfirm = false">
                    Abbrechen
                </Button>
                <Button
                    variant="destructive"
                    :disabled="removing"
                    @click="confirmDelete"
                >
                    {{ removing ? 'Lösche…' : 'Löschen' }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
