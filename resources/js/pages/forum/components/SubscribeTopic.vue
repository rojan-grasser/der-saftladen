<script lang="ts" setup>
import { router } from '@inertiajs/vue3';
import { Bell, BellOff } from 'lucide-vue-next';
import { ref } from 'vue';

import { Button } from '@/components/ui/button';
import topics from '@/routes/topics';

const props = defineProps<{
    professionId: number;
    topicId: number;
    isSubscribed: boolean;
}>();

const isSubscribed = ref(props.isSubscribed);

const onClick = () => {
    router.post(
        topics.subscribe.toggle({
            professionId: props.professionId,
            topicId: props.topicId,
        }).url,
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
        :class="'h-8 w-8'"
        :title="isSubscribed ? 'Abonnement beenden' : 'Thema abonnieren'"
        variant="ghost"
        @click="onClick"
    >
        <BellOff v-if="isSubscribed" class="h-5 w-5" />
        <Bell v-else class="h-5 w-5" />
    </Button>
</template>
