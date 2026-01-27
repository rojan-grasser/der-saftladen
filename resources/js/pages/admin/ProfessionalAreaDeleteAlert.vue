<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';

import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import admin from '@/routes/admin';
import type { ProfessionalArea } from '@/types';

const open = defineModel<boolean>('open', { required: true });

const emit = defineEmits<{
    (e: 'deleted'): void;
}>();

const props = defineProps<{
    professionalArea: ProfessionalArea;
}>();

const deleteForm = useForm({});

const submit = () => {
    deleteForm.delete(
        admin.professionalArea.destroy(props.professionalArea.id).url,
        {
            preserveScroll: true,
            onSuccess: async () => {
                emit('deleted');
                close();
            },
        },
    );
};

function close() {
    open.value = false;
}
</script>

<template>
    <AlertDialog
        :open="open"
        @update:open="(v) => (v ? (open = true) : close())"
    >
        <AlertDialogContent>
            <AlertDialogHeader>
                <AlertDialogTitle>Berufsbereich löschen?</AlertDialogTitle>
                <AlertDialogDescription class="space-y-1">
                    <p>Dieser Vorgang kann nicht rückgängig gemacht werden.</p>

                    <div class="flex flex-wrap items-baseline gap-x-1 gap-y-1">
                        <span class="shrink-0">Möchten Sie</span>

                        <span
                            class="font-medium break-all"
                            :title="props.professionalArea.name"
                        >
                            {{ props.professionalArea.name }}
                        </span>

                        <span class="shrink-0">wirklich löschen?</span>
                    </div>
                </AlertDialogDescription>
            </AlertDialogHeader>

            <AlertDialogFooter>
                <AlertDialogCancel :disabled="deleteForm.processing">
                    Abbrechen
                </AlertDialogCancel>
                <AlertDialogAction
                    :disabled="deleteForm.processing"
                    @click="submit"
                >
                    Löschen
                </AlertDialogAction>
            </AlertDialogFooter>
        </AlertDialogContent>
    </AlertDialog>
</template>
