<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import axios from 'axios';
import { PlusIcon } from 'lucide-vue-next';
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
import { uploadFiles } from '@/pages/forum/components/file-table/onFileChange';
import topics from '@/routes/topics';

const { professionId } = defineProps<{ professionId: number }>();

const open = ref(false);

const form = useForm({
    title: '',
    description: '',
    files: [] as string[],
});

const uploadedFiles = ref<Array<{ beId: string; appId: string }>>([]);
const topicId = ref<number>(0);

const setTopicId = async () => {
    const res = await axios.get(topics.initialize({ professionId }).url);
    topicId.value = res.data.id;
};

const onFileChange = async (files: FileWithPreview[]) => {
    await uploadFiles(
        files,
        uploadedFiles.value,
        professionId,
        topicId.value,
        open.value,
    );
};

const submit = () => {
    form.put(topics.update({ professionId: professionId, topicId: topicId.value }).url, {
        onSuccess: () => {
            open.value = false;
        },
    });
};

watch(open, () => {
    if (!open.value) {
        form.reset();
    }
});
</script>

<template>
    <Dialog :open="open" @update:open="(o) => (open = o)">
        <DialogTrigger as-child :onclick="setTopicId">
            <Button><PlusIcon /> Erstellen</Button>
        </DialogTrigger>
        <DialogContent class="sm:max-w-2xl">
            <form @submit.prevent="submit">
                <DialogHeader>
                    <DialogTitle>Thema Erstellen</DialogTitle>
                    <DialogDescription>
                        Erstelle dein Thema hier. Um es zu speichern drücke
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
                        :initial-files="[]"
                        :on-files-change="onFileChange"
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
