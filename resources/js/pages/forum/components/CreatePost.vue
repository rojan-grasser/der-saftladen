<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
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
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import posts from '@/routes/posts';

const { areaId, topicId } = defineProps<{ areaId: number; topicId: number }>();

const open = ref(false);

const form = useForm({
    content: '',
});

const submit = () => {
    form.post(posts.store({ areaId, topicId }).url, {
        onSuccess: () => {
            open.value = false;
        }
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
        <DialogTrigger as-child>
            <Button class="fixed right-8 bottom-8"
                ><PlusIcon /> Kommentar</Button
            >
        </DialogTrigger>
        <DialogContent class="sm:max-w-2xl">
            <form @submit.prevent="submit">
                <DialogHeader>
                    <DialogTitle>Kommentar Erstellen</DialogTitle>
                    <DialogDescription>
                        Erstelle hier einen Kommentar. Um ihn zu erstellen
                        drücke "Erstellen"
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
                        Erstellen
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
