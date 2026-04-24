<script setup lang="ts">
import UserAvatar from '@/components/UserAvatar.vue';
import type { User } from '@/types';

interface Props {
    user: User;
    showEmail?: boolean;
}

withDefaults(defineProps<Props>(), {
    showEmail: false,
});
</script>

<template>
    <UserAvatar :user="user" />

    <div class="grid flex-1 text-left text-sm leading-tight">
        <span class="truncate font-medium">{{ user.name }}</span>
        <span v-if="showEmail" class="truncate text-xs text-muted-foreground">{{
            user.email
        }}</span>
    </div>
</template>
