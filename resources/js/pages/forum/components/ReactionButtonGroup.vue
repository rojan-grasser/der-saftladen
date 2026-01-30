<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { ThumbsDownIcon, ThumbsUpIcon } from 'lucide-vue-next';
import { ref } from 'vue';

import { Button } from '@/components/ui/button';
import { Post } from '@/pages/forum/types';
import reactions from '@/routes/posts/reactions';

type Reaction = 'like' | 'dislike';

const { post, topicId } = defineProps<{ post: Post; topicId: number }>();

const reaction = ref<Reaction | null>(post.reaction);
const reactionAmounts = ref<{ like: number; dislike: number }>({
    dislike: post.dislikesCount,
    like: post.likesCount,
});

const changeReaction = (button: Reaction) => {
    if (reaction.value === button) {
        reaction.value = null;
        reactionAmounts.value[button]--;

        router.delete(reactions.destroy({ postId: post.id, topicId }).url, {
            preserveScroll: true,
        });
        return;
    }

    reactionAmounts.value[button]++;
    if (reaction.value !== null) {
        reactionAmounts.value[button === 'like' ? 'dislike' : 'like']--;
    }
    reaction.value = button;

    router.post(
        reactions.store({ postId: post.id, topicId }).url,
        {
            type: button,
        },
        { preserveScroll: true },
    );
};
</script>

<template>
    <div
        class="inline-flex w-fit -space-x-px rounded-md shadow-xs rtl:space-x-reverse"
    >
        <Button
            @click="() => changeReaction('like')"
            variant="outline"
            class="group w-20 justify-start gap-3 overflow-hidden rounded-none rounded-l-md shadow-none transition-all duration-200 not-hover:w-10 hover:bg-lime-500/10 hover:text-lime-500 focus-visible:z-10 dark:hover:bg-lime-400/10 dark:hover:text-lime-400"
        >
            <ThumbsUpIcon
                :class="
                    reaction === 'like' && 'text-lime-500 dark:text-lime-400'
                "
            />
            {{ reactionAmounts.like }}
        </Button>
        <Button
            @click="() => changeReaction('dislike')"
            variant="outline"
            class="group w-24.5 justify-end gap-3 overflow-hidden rounded-none rounded-r-md shadow-none transition-all duration-200 not-hover:w-10 hover:bg-destructive/10! hover:text-destructive focus-visible:z-10"
        >
            {{ reactionAmounts.dislike }}
            <ThumbsDownIcon
                :class="reaction === 'dislike' && 'text-destructive'"
            />
        </Button>
    </div>
</template>
