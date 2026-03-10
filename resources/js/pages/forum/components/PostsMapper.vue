<script setup lang="ts">
import {
    Card,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import ReactionButtonGroup from '@/pages/forum/components/ReactionButtonGroup.vue';
import { formatDate, formatTime } from '@/pages/forum/dateStrings';
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
            <Card>
                <CardHeader class="gap-3">
                    <CardTitle>
                        <div class="flex justify-between">
                            <span class="italic">
                                {{ post.user.name }}
                            </span>
                            <span
                                class="text-sm font-normal text-foreground/70"
                            >
                                {{
                                    formatDate(post.created_at)
                                }}&nbsp;&nbsp;&nbsp;{{
                                    formatTime(post.created_at)
                                }}
                            </span>
                        </div>
                        <span class="text-sm font-normal text-foreground/70">
                            {{ post.user.email }}
                        </span>
                    </CardTitle>
                    <CardDescription class="text-black dark:text-white">
                        {{ post.content }}
                    </CardDescription>

                    <ReactionButtonGroup
                        :post="post"
                        :topicId="topicId"
                        :area-id="areaId"
                    />
                </CardHeader>
            </Card>
        </div>
    </div>
</template>
