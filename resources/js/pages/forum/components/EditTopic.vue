<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { FileWithPreview } from '@/composables/useFileUpload';
import FileTable from '@/pages/forum/components/file-table/FileTable.vue';
import {
    onFileRemoved,
    uploadFiles,
} from '@/pages/forum/components/file-table/onFileChange';
import { Topic } from '@/pages/forum/types';
import topics from '@/routes/topics';

const { topic, professionId } = defineProps<{
    topic: Topic;
    professionId: number;
}>();

const open = ref(false);

const form = useForm({
    title: topic.title,
    description: topic.description,
});

const uploadedFiles = ref<Array<{ beId: string; appId: string }>>(
    topic.files.map((f) => ({ beId: f.id, appId: f.id })),
);

const onFileAdded = async (files: FileWithPreview[]) => {
    await uploadFiles(files, uploadedFiles.value, professionId, topic.id);
};

const removeFile = async (id: string) => {
    await onFileRemoved(id, uploadedFiles.value, professionId, topic.id);
};

const submit = () => {
    form.put(topics.update({ topicId: topic.id, professionId }).url, {
        onSuccess: () => {
            form.defaults({ title: form.title, description: form.description });
            open.value = false;
        },
        preserveScroll: true,
    });
};

watch(open, () => {
    if (!open.value) {
        form.reset();
        uploadedFiles.value = topic.files.map((f) => ({
            beId: f.id,
            appId: f.id,
        }));
    }
});
</script>

<template>
    <Dialog :open="open" @update:open="(o) => (open = o)">
        <DialogTrigger as-child>
            <Button>Bearbeiten</Button>
        </DialogTrigger>
        <DialogContent class="sm:max-w-2xl">
            <form @submit.prevent="submit">
                <DialogHeader>
                    <DialogTitle>Thema Bearbeiten</DialogTitle>
                    <DialogDescription>
                        Ändere dein Thema hier. Um es zu speichern drücke
                        "Speichern"
                    </DialogDescription>
                </DialogHeader>
                <div class="my-5 grid gap-4">
                    <div class="grid gap-3">
                        <Label for="title-1">Titel</Label>
                        <Input
                            id="title-1"
                            v-model="form.title"
                            name="title"
                            :errorMessage="form.errors.title"
                        />
                    </div>
                    <div class="grid gap-3">
                        <Label for="description-1">Beschreibung</Label>
                        <Textarea
                            id="description-1"
                            v-model="form.description"
                            name="description"
                            :errorMessage="form.errors.description"
                            class="max-h-[70vh] resize-y overflow-auto"
                        />
                    </div>
                    <FileTable
                        :initial-files="topic.files"
                        :on-files-added="onFileAdded"
                        :on-file-removed="removeFile"
                    />
                </div>
                <DialogFooter>
                    <Button
                        type="button"
                        variant="outline"
                        :disabled="form.processing"
                        @click="() => (open = false)"
                    >
                        Abbrechen
                    </Button>
                    <Button type="submit" :disabled="form.processing">
                        Speichern
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
