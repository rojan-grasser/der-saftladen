<script setup lang="ts">
import {
    Card,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import ReactionButtonGroup from '@/pages/forum/components/ReactionButtonGroup.vue';
import TopicDescription from '@/pages/forum/components/TopicDescription.vue';
import { formatDate, formatTime } from '@/pages/forum/dateStrings';
import { Post } from '@/pages/forum/types';

defineProps<{
    post: Post;
    topicId: number;
    professionId: number;
}>();
</script>

<template>
    <Card>
        <CardHeader class="gap-3">
            <CardTitle>
                <div class="flex justify-between">
                    <span class="italic">
                        {{ post.user.name }}
                    </span>
                    <span class="text-sm font-normal text-foreground/70">
                        {{ formatDate(post.created_at) }}&nbsp;&nbsp;&nbsp;{{
                            formatTime(post.created_at)
                        }}
                    </span>
                </div>
                <span class="text-sm font-normal text-foreground/70">
                    {{ post.user.email }}
                </span>
            </CardTitle>
            <CardDescription class="text-black dark:text-white">
                <TopicDescription :description="post.content" />
            </CardDescription>

            <span v-if="post.edited" class="text-xs text-foreground/60"
                >(Bearbeitet)</span
            >

            <ReactionButtonGroup
                :post="post"
                :topicId="topicId"
                :profession-id="professionId"
            />
        </CardHeader>
    </Card>
</template>
