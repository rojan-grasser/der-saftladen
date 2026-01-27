<script lang="ts" setup>
import { useForm } from '@inertiajs/vue3';

import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import admin from '@/routes/admin';

const open = defineModel<boolean>('open', { required: true });

const emit = defineEmits<{
    (e: 'created'): void;
}>();

const form = useForm({
    name: '',
    description: '',
});

function close() {
    open.value = false;
    form.reset();
    form.clearErrors();
}

function submit() {
    form.post(admin.professionalArea.store().url, {
        preserveScroll: true,
        onSuccess: () => {
            emit('created');
            close();
        },
    });
}
</script>

<template>
    <Dialog :open="open" @update:open="(v) => (v ? (open = true) : close())">
        <DialogContent class="sm:max-w-2xl">
            <DialogHeader>
                <DialogTitle>Berufsbereich erstellen</DialogTitle>
                <DialogDescription>
                    Lege einen neuen Berufsbereich an.
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
                    <Label for="description">Beschreibung</Label>
                    <Textarea id="description" v-model="form.description" />
                    <div
                        v-if="form.errors.description"
                        class="text-sm text-red-600"
                    >
                        {{ form.errors.description }}
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
