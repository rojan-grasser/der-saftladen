<script setup lang="ts">
import EditTopic from '@/pages/forum/components/EditTopic.vue';
import FileTableWithAccordion from '@/pages/forum/components/file-table/FileTableWithAccordion.vue';
import PinTopic from '@/pages/forum/components/PinTopic.vue';
import SubscribeTopic from '@/pages/forum/components/SubscribeTopic.vue';
import TopicDescription from '@/pages/forum/components/TopicDescription.vue';
import { Topic } from '@/pages/forum/types';

const {
    topic,
    class: className,
    professionId,
} = defineProps<{
    topic: Topic;
    class?: string;
    professionId: number;
}>();
</script>

<template>
    <div :class="`mx-1 ${className} flex flex-col gap-4`">
        <div class="flex flex-col gap-5">
            <div class="flex justify-between">
                <h2 class="text-2xl font-semibold">{{ topic.title }}</h2>
                <div class="flex gap-2">
                    <SubscribeTopic
                        :profession-id="professionId"
                        :topic-id="topic.id"
                        :is-subscribed="topic.isSubscribed"
                        class="h-auto w-auto"
                    />
                    <PinTopic
                        :profession-id="professionId"
                        :topic-id="topic.id"
                        :pinned="topic.pinned"
                        class="h-auto w-auto"
                    />
                    <EditTopic
                        :topic="topic"
                        v-if="topic.isOwnPost"
                        :profession-id="professionId"
                    />
                </div>
            </div>

            <div>
                <span class="text-xs italic">
                    {{ topic.owner.name }}
                    <span class="text-sm font-normal text-foreground/70">
                        {{ topic.owner.email }}
                    </span>
                </span>
            </div>

            <TopicDescription :description="topic.description" />

            <FileTableWithAccordion
                :initial-files="topic.files"
                readonly
                v-if="topic.files.length > 0"
            />
        </div>
    </div>
</template>
