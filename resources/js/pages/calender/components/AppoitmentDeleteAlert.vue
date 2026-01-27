<script setup lang="ts">
import { router, useForm } from '@inertiajs/vue3';

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
import appointments from '@/routes/appointments';
import type { Appointment } from '../types';

const open = defineModel<boolean>('open', { required: true });

const emit = defineEmits<{
    (e: 'deleted'): void;
}>();

const props = defineProps<{
    appointment: Appointment;
}>();

const deleteForm = useForm({});

const submit = () => {
    deleteForm.delete(appointments.destroy(props.appointment.id).url, {
        preserveScroll: true,
        onSuccess: () => {
            emit('deleted');
            close();
            router.reload({ only: ['appointments'] });
        },
    });
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
                <AlertDialogTitle>Termin löschen?</AlertDialogTitle>
                <AlertDialogDescription class="space-y-1">
                    <p>Dieser Vorgang kann nicht rückgängig gemacht werden.</p>

                    <div class="flex flex-wrap items-baseline gap-x-1 gap-y-1">
                        <span class="shrink-0">Möchten Sie den Termin</span>

                        <span
                            class="font-medium break-all"
                            :title="props.appointment.title"
                        >
                            {{ props.appointment.title }}
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
                    class="bg-destructive text-destructive-foreground hover:bg-destructive/90"
                    @click="submit"
                >
                    Löschen
                </AlertDialogAction>
            </AlertDialogFooter>
        </AlertDialogContent>
    </AlertDialog>
</template>
