<script lang="ts" setup>
import { Head } from '@inertiajs/vue3';

import AppLayout from '@/layouts/AppLayout.vue';
import PostsMapper from '@/pages/forum/components/PostsMapper.vue';
import TopicPost from '@/pages/forum/components/TopicPost.vue';
import { Topic } from '@/pages/forum/types';
import { index as topicIndex, show } from '@/routes/topics';
import { BreadcrumbItem } from '@/types';

const topic = defineProps<Topic>();

const breadcrumbs: BreadcrumbItem[] = [
    // Todo: add professional area
    {
        title: 'Themen',
        href: topicIndex().url,
    },
    {
        title: topic.title,
        href: show.url({ topic: topic.id }),
    },
];
</script>

<template>
    <Head :title="title" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="px-10 pt-4">
            <TopicPost :topic="topic" class="mb-10" />

            <PostsMapper :posts="topic.posts" />
        </div>
    </AppLayout>
</template>
