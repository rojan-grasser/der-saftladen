<script setup lang="ts">
import EditTopic from '@/pages/forum/components/EditTopic.vue';
import { Topic } from '@/pages/forum/types';

const { topic, class: className } = defineProps<{
    topic: Topic;
    class?: string;
}>();
</script>

<template>
    <div :class="`mx-6 flex flex-col gap-5 ${className}`">
        <div class="flex justify-between">
            <h2 class="text-2xl font-semibold">{{ topic.title }}</h2>
            <!-- <Button v-if="topic.isOwnPost">Bearbeiten</Button> -->
            <EditTopic :topic="topic" v-if="topic.isOwnPost" />
        </div>
        <span>{{ topic.description }}</span>
    </div>
</template>
