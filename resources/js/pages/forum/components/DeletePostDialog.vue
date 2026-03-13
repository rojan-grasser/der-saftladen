<script setup lang="ts">
import { router } from '@inertiajs/vue3';

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
import { Post } from '@/pages/forum/types';
import posts from '@/routes/posts';

const { post, areaId, topicId } = defineProps<{
    areaId: number;
    topicId: number;
    post: Post;
}>();

const open = defineModel<boolean>('open', { default: false });

const deletePost = () => {
    router.delete(posts.destroy({ post: post.id, topicId, areaId }).url, {
        preserveScroll: true,
    });
};
</script>

<template>
    <AlertDialog :open="open" @update:open="(o) => (open = o)">
        <AlertDialogContent>
            <AlertDialogHeader>
                <AlertDialogTitle>Sind sie sich absolut sicher?</AlertDialogTitle>
                <AlertDialogDescription>
                    Der kommentar kann danach nicht wieder hergestellt werden.
                </AlertDialogDescription>
            </AlertDialogHeader>
            <AlertDialogFooter>
                <AlertDialogCancel @click="open = false">Abbrechen</AlertDialogCancel>
                <AlertDialogAction :onclick="deletePost">
                    Löschen
                </AlertDialogAction>
            </AlertDialogFooter>
        </AlertDialogContent>
    </AlertDialog>
</template>
