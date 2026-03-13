<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';

import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Post } from '@/pages/forum/types';
import posts from '@/routes/posts';

const { professionId, topicId, post } = defineProps<{
    professionId: number;
    topicId: number;
    post: Post;
}>();

const open = defineModel<boolean>('open', { default: false });

const form = useForm({
    content: post.content,
});

const submit = () => {
    form.put(posts.update({ professionId, topicId, post: post.id }).url, {
        onSuccess: () => {
            open.value = false;
        },
    });
};
</script>

<template>
    <Dialog :open="open" @update:open="(o) => (open = o)">
        <DialogContent class="sm:max-w-2xl">
            <form @submit.prevent="submit">
                <DialogHeader>
                    <DialogTitle>Kommentar Bearbeiten</DialogTitle>
                    <DialogDescription>
                        Bearbeite hier einen Kommentar. Um es zu speichern
                        drücke "Speichern"
                    </DialogDescription>
                </DialogHeader>
                <div class="my-5 grid gap-4">
                    <div class="grid gap-3">
                        <Label for="description-1">Inhalt</Label>
                        <Textarea
                            id="description-1"
                            v-model="form.content"
                            name="description"
                            :errorMessage="form.errors.content"
                            class="max-h-[70vh] resize-y overflow-auto"
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
