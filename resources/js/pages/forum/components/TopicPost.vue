<script setup lang="ts">
import EditTopic from '@/pages/forum/components/EditTopic.vue';
import { Topic } from '@/pages/forum/types';

const { topic, class: className } = defineProps<{
    topic: Topic;
    class?: string;
}>();
</script>

<template>
    <div :class="`mx-6 ${className} flex flex-col gap-4`">
        <div class="flex flex-col gap-5">
            <div class="flex justify-between">
                <h2 class="text-2xl font-semibold">{{ topic.title }}</h2>
                <!-- <Button v-if="topic.isOwnPost">Bearbeiten</Button> -->
                <EditTopic :topic="topic" v-if="topic.isOwnPost" />
            </div>
            <span class="whitespace-pre-line">{{ topic.description }}</span>
        </div>

        <div>
            <span class="italic text-xs">
                {{ topic.owner.name }}
                <span class="text-sm font-normal text-foreground/70">
                    {{ topic.owner.email }}
                </span>
            </span>
        </div>
    </div>
</template>
