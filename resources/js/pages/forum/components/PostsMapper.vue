<script setup lang="ts">
import {
    Card,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import ReactionButtonGroup from '@/pages/forum/components/ReactionButtonGroup.vue';
import { Topic } from '@/pages/forum/types';

const { posts, topicId } = defineProps<{
    posts: Topic['posts'];
    topicId: number;
}>();

const formatDate = (dateString: string) => {
    const date = new Date(dateString);

    return `${date.getUTCDate()}.${date.getUTCMonth() + 1}.${date.getUTCFullYear()}`;
};

const formatTime = (dateString: string) => {
    const date = new Date(dateString);

    return `${date.getUTCHours().toString().padStart(2, '0')}:${date.getUTCMinutes().toString().padStart(2, '0')}`;
};
</script>

<template>
    <div class="flex flex-col gap-4">
        <div v-for="post in posts" :key="`post-${post.id}`">
            <Card>
                <CardHeader class="gap-3">
                    <CardTitle class="flex justify-between">
                        <span class="italic">
                            {{ post.user.name }}
                            <span
                                class="text-sm font-normal text-foreground/70"
                            >
                                {{ post.user.email }}
                            </span>
                        </span>
                        <span class="text-sm font-normal text-foreground/70">
                            {{
                                formatDate(post.created_at)
                            }}&nbsp;&nbsp;&nbsp;{{
                                formatTime(post.created_at)
                            }}
                        </span>
                    </CardTitle>
                    <CardDescription class="text-black dark:text-white">
                        {{ post.content }}
                    </CardDescription>

                    <ReactionButtonGroup :post="post" :topicId="topicId" />
                </CardHeader>
            </Card>
        </div>
    </div>
</template>
