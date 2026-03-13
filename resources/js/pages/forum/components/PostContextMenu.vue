<script setup lang="ts">
import { Pencil, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';

import {
    ContextMenu,
    ContextMenuContent,
    ContextMenuItem,
    ContextMenuSeparator,
    ContextMenuTrigger,
} from '@/components/ui/context-menu';
import DeletePostDialog from '@/pages/forum/components/DeletePostDialog.vue';
import EditPostDialog from '@/pages/forum/components/EditPostDialog.vue';
import { Post } from '@/pages/forum/types';

const { post, areaId, topicId } = defineProps<{
    areaId: number;
    topicId: number;
    post: Post;
}>();

const editOpen = ref(false);
const deleteOpen = ref(false);
</script>

<template>
    <EditPostDialog v-model:open="editOpen" :area-id="areaId" :topic-id="topicId" :post="post" />
    <DeletePostDialog v-model:open="deleteOpen" :area-id="areaId" :topic-id="topicId" :post="post" />

    <ContextMenu>
        <ContextMenuTrigger as-child>
            <div>
                <slot />
            </div>
        </ContextMenuTrigger>
        <ContextMenuContent>
            <ContextMenuItem @click="editOpen = true">
                <Pencil />
                Bearbeiten
            </ContextMenuItem>
            <ContextMenuSeparator />
            <ContextMenuItem variant="destructive" @click="deleteOpen = true">
                <Trash2 />
                Löschen
            </ContextMenuItem>
        </ContextMenuContent>
    </ContextMenu>
</template>
