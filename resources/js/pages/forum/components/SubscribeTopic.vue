<script lang="ts" setup>
import { router } from '@inertiajs/vue3';
import { Bell, BellOff } from 'lucide-vue-next';
import { ref } from 'vue';

import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';
import topics from '@/routes/topics';

const {
    topicId,
    professionId,
    isSubscribed: defaultIsSubscribed,
    class: className,
} = defineProps<{
    professionId: number;
    topicId: number;
    isSubscribed: boolean;
    class?: string;
}>();

const isSubscribed = ref(defaultIsSubscribed);

const onClick = () => {
    router.post(
        topics.subscribe.toggle({ professionId, topicId }).url,
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                isSubscribed.value = !isSubscribed.value;
            },
        },
    );
};
</script>

<template>
    <Button
        :class="cn('h-8 w-8', className)"
        :title="isSubscribed ? 'Abonnement beenden' : 'Thema abonnieren'"
        variant="ghost"
        @click="onClick"
    >
        <BellOff v-if="isSubscribed" class="h-5 w-5" />
        <Bell v-else class="h-5 w-5" />
    </Button>
</template>
