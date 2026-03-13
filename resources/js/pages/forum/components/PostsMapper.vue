<script setup lang="ts">
import PostCard from '@/pages/forum/components/PostCard.vue';
import PostContextMenu from '@/pages/forum/components/PostContextMenu.vue';
import { Topic } from '@/pages/forum/types';

const { posts, topicId } = defineProps<{
    posts: Topic['posts'];
    topicId: number;
    professionId: number;
}>();
</script>

<template>
    <div class="flex flex-col gap-4">
        <div v-for="post in posts" :key="`post-${post.id}`">
            <PostContextMenu
                v-if="post.isOwnPost"
                :topic-id="topicId"
                :post="post"
                :profession-id="professionId"
            >
                <PostCard
                    :post="post"
                    :profession-id="professionId"
                    :topic-id="topicId"
                />
            </PostContextMenu>
            <PostCard
                :post="post"
                :topic-id="topicId"
                :profession-id="professionId"
                v-if="!post.isOwnPost"
            />
        </div>
    </div>
</template>
