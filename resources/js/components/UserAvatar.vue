<script setup lang="ts">
import { computed } from 'vue';

import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { useInitials } from '@/composables/useInitials';
import { cn } from '@/lib/utils';
import type { User } from '@/types';

const { getInitials } = useInitials();

const props = defineProps<{
    user: User;
    class?: string;
}>();

const showAvatar = computed(
    () => !!props.user.avatar && props.user.avatar !== '',
);

// Cache-bust the avatar URL so the browser fetches the new image after updates
const avatarSrc = computed(() => {
    if (!props.user.avatar) return null;
    const t = props.user.updated_at
        ? new Date(props.user.updated_at).getTime()
        : Date.now();
    return `${props.user.avatar}?t=${t}`;
});
</script>

<template>
    <Avatar
        :class="cn('h-8 w-8 overflow-hidden rounded-lg', props.class)"
        :key="avatarSrc ?? 'null'"
    >
        <AvatarImage
            class="object-cover"
            v-if="showAvatar"
            :src="avatarSrc!"
            :alt="user.name"
        />
        <AvatarFallback class="rounded-lg text-black dark:text-white">
            {{ getInitials(user.name) }}
        </AvatarFallback>
    </Avatar>
</template>
