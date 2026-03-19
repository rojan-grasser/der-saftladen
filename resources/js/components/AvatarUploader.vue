<!-- resources/js/components/profile/AvatarUploader.vue -->
<script setup lang="ts">
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { usePage } from '@inertiajs/vue3';
import { getAvatarUrl, getPresignedPutUrl, uploadToPresignedUrl, deleteAvatar } from '@/composables/avatar';

const props = defineProps<{
    folder: string | number;
    initials?: string;
    updatedAt?: string | null;
}>();

const avatarSrc = ref<string | null>(null);
const uploading = ref(false);
const removing = ref(false);
const errorMsg = ref<string | null>(null);

// Initial setzen
function refresh() {
    const base = getAvatarUrl(props.folder);
    avatarSrc.value = props.updatedAt
        ? `${base}?t=${new Date(props.updatedAt).getTime()}`
        : base;
}

refresh();

async function onFileChange(e: Event) {
    const input = e.target as HTMLInputElement;
    const file = input.files?.[0];
    if (!file) return;

    uploading.value = true;
    errorMsg.value = null;

    try {
        const presign = await getPresignedPutUrl(file.type as any);
        await uploadToPresignedUrl(presign.url, file);
        avatarSrc.value = `${presign.publicUrl}?t=${Date.now()}`;
        page.props.auth.user.updated_at = presign.updated_at;
    } catch (err: any) {
        errorMsg.value = err.message ?? 'Upload fehlgeschlagen';
    }

    uploading.value = false;
    input.value = '';
}

const page = usePage();
async function onDelete() {
    if (!confirm('Profilbild wirklich entfernen?')) return;

    removing.value = true;
    errorMsg.value = null;

    try {
        const res = await deleteAvatar();
        avatarSrc.value = null;
        page.props.auth.user.updated_at = res.updated_at;

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
            <!--Button :disabled="uploading">
                {{ uploading ? 'Lädt…' : 'Hochladen' }}
            </Button-->
            <Button variant="secondary" :disabled="removing" @click="onDelete">
                {{ removing ? 'Entfernt…' : 'Entfernen' }}
            </Button>
        </div>

        <div class="mt-3 flex items-center gap-4">
            <div class="relative h-16 w-16 rounded-full border bg-muted flex items-center justify-center overflow-hidden">

                <img
                    v-if="avatarSrc"
                    :src="avatarSrc"
                    alt="Avatar"
                    class="h-full w-full object-cover"
                    @error="avatarSrc = null"
                />

                <span
                    v-else
                    class="text-xl font-semibold text-muted-foreground select-none"
                >
                    {{ initials }}
                </span>
            </div>
        </div>

        <p v-if="errorMsg" class="text-sm text-red-600">{{ errorMsg }}</p>
    </div>
</template>
