<script setup lang="ts">
import { router, usePage } from '@inertiajs/vue3';
import { PinIcon, PinOff } from 'lucide-vue-next';
import { ref } from 'vue';

import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';
import topics from '@/routes/topics';

const page = usePage();

const {
    topicId,
    professionId,
    pinned: defaultPinned,
    showPinnedIcon,
    class: className,
} = defineProps<{
    professionId: number;
    topicId: number;
    pinned: boolean;
    showPinnedIcon?: boolean;
    class?: string;
}>();

const pinned = ref(defaultPinned);

const onClick = () => {
    router.put(
        topics.pinToggle({ topicId, professionId }).url,
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                pinned.value = !pinned.value;
            },
        },
    );
};
</script>

<template>
    <Button
        variant="ghost"
        :class="cn('h-8 w-8', className)"
        :onclick="onClick"
        v-if="page.props.auth.user.roles?.find((r) => r.role === 'admin')"
    >
        <PinIcon v-if="!pinned" />
        <PinOff v-if="pinned" />
    </Button>
    <PinIcon
        :class="cn('h-8 w-8', className)"
        v-if="
            !page.props.auth.user.roles?.find((r) => r.role === 'admin') &&
            pinned &&
            showPinnedIcon
        "
    />
</template>
