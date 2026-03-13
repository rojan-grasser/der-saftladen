<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';

import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogTrigger,
} from '@/components/ui/alert-dialog';
import { Button } from '@/components/ui/button';
import {
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger,
} from '@/components/ui/tooltip';
import admin from '@/routes/admin';
import type { User } from '@/types';

const open = ref<boolean>(false);

const { user } = defineProps<{ user: User }>();

const deleteUser = () => {
    router.delete(admin.users.destroy({ id: user.id }).url, {
        preserveScroll: true,
    });
};
</script>

<template>
    <AlertDialog :open="open" @update:open="(o) => (open = o)">
        <TooltipProvider>
            <Tooltip>
                <AlertDialogTrigger as-child>
                    <TooltipTrigger as-child>
                        <Button
                            aria-label="Bearbeiten"
                            size="icon-sm"
                            variant="destructive"
                        >
                            <Trash2 />
                        </Button>
                    </TooltipTrigger>
                </AlertDialogTrigger>
                <TooltipContent>Löschen</TooltipContent>
            </Tooltip>
        </TooltipProvider>
        <AlertDialogContent>
            <AlertDialogHeader>
                <AlertDialogTitle
                    >Sind sie sich absolut sicher?</AlertDialogTitle
                >
                <AlertDialogDescription>
                    Diese Aktion kann nicht rückgängig gemacht werden. Der
                    Nutzer wird permanent gelöscht.
                </AlertDialogDescription>
            </AlertDialogHeader>
            <AlertDialogFooter>
                <AlertDialogCancel>Abbrechen</AlertDialogCancel>
                <AlertDialogAction :onclick="deleteUser">
                    Löschen
                </AlertDialogAction>
            </AlertDialogFooter>
        </AlertDialogContent>
    </AlertDialog>
</template>
