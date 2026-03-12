<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { Pencil, Trash2 } from 'lucide-vue-next';

import {
    ContextMenu,
    ContextMenuContent,
    ContextMenuItem,
    ContextMenuSeparator,
    ContextMenuTrigger,
} from '@/components/ui/context-menu';
import { DialogTrigger } from '@/components/ui/dialog';
import EditPost from '@/pages/forum/components/EditPost.vue';
import { Post } from '@/pages/forum/types';
import posts from '@/routes/posts';

const { post, areaId, topicId } = defineProps<{
    areaId: number;
    topicId: number;
    post: Post;
}>();

const deletePost = () => {
    router.delete(posts.destroy({ post: post.id, topicId, areaId }).url, {
        preserveScroll: true,
    });
};
</script>

<template>
    <EditPost :area-id="areaId" :topic-id="topicId" :post="post">
        <ContextMenu>
            <ContextMenuTrigger as-child>
                <div>
                    <slot />
                </div>
            </ContextMenuTrigger>
            <ContextMenuContent>
                <DialogTrigger as-child>
                    <ContextMenuItem>
                        <Pencil />
                        Bearbeiten
                    </ContextMenuItem>
                </DialogTrigger>
                <ContextMenuSeparator />
                <ContextMenuItem variant="destructive" :onclick="deletePost">
                    <Trash2 />
                    Löschen
                </ContextMenuItem>
            </ContextMenuContent>
        </ContextMenu>
    </EditPost>
</template>
