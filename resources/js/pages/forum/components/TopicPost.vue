<script setup lang="ts">
import Markdown from '@/components/Markdown.vue';
import EditTopic from '@/pages/forum/components/EditTopic.vue';
import { Topic } from '@/pages/forum/types';

const {
    topic,
    class: className,
    areaId,
} = defineProps<{
    topic: Topic;
    class?: string;
    areaId: number;
}>();
</script>

<template>
    <div :class="`mx-6 ${className} flex flex-col gap-4`">
        <div class="flex flex-col gap-5">
            <div class="flex justify-between">
                <h2 class="text-2xl font-semibold">{{ topic.title }}</h2>
                <EditTopic
                    :topic="topic"
                    v-if="topic.isOwnPost"
                    :area-id="areaId"
                />
            </div>

            <div>
                <span class="text-xs italic">
                    {{ topic.owner.name }}
                    <span class="text-sm font-normal text-foreground/70">
                        {{ topic.owner.email }}
                    </span>
                </span>
            </div>

            <Markdown :markdown="topic.description" />
        </div>
    </div>
</template>
