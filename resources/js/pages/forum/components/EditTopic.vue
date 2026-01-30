<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

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
import { Topic } from '@/pages/forum/types';
import topics from '@/routes/topics';

const { topic } = defineProps<{ topic: Topic }>();

const open = ref(false);

const form = useForm({
    title: topic.title,
    description: topic.description,
});

const submit = () => {
    form.put(topics.update({ topic: topic.id }).url, {
        onSuccess: () => {
            open.value = false;
        },
    });
};
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
                <div class="mb-5 grid gap-4">
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
                        />
                    </div>
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
