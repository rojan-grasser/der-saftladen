<script setup lang="ts">
import PostCard from '@/pages/forum/components/PostCard.vue';
import PostContextMenu from '@/pages/forum/components/PostContextMenu.vue';
import { Topic } from '@/pages/forum/types';

const { posts, topicId } = defineProps<{
    posts: Topic['posts'];
    topicId: number;
    areaId: number;
}>();
</script>

<template>
    <div class="flex flex-col gap-4">
        <div v-for="post in posts" :key="`post-${post.id}`">
            <PostContextMenu
                v-if="post.isOwnPost"
                :topic-id="topicId"
                :area-id="areaId"
                :post="post"
            >
                <PostCard :post="post" :topic-id="topicId" :area-id="areaId" />
            </PostContextMenu>
            <PostCard
                :post="post"
                :topic-id="topicId"
                :area-id="areaId"
                v-if="!post.isOwnPost"
            />
        </div>
    </div>
</template>
