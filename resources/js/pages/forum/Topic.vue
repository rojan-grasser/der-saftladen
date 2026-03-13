<script lang="ts" setup>
import { Head } from '@inertiajs/vue3';

import AppLayout from '@/layouts/AppLayout.vue';
import CreatePost from '@/pages/forum/components/CreatePost.vue';
import PostsMapper from '@/pages/forum/components/PostsMapper.vue';
import TopicPost from '@/pages/forum/components/TopicPost.vue';
import { Profession, Topic } from '@/pages/forum/types';
import { professions } from '@/routes/forum';
import { index as topicIndex, show } from '@/routes/topics';
import { BreadcrumbItem } from '@/types';

const { topic, profession } = defineProps<{
    profession: Profession;
    topic: Topic;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Berufsbereiche',
        href: professions().url,
    },
    {
        title: profession.name,
        href: topicIndex({ professionId: profession.id }).url,
    },
    {
        title: topic.title,
        href: show.url({ topicId: topic.id, professionId: profession.id }),
    },
];
</script>

<template>
    <Head :title="topic.title" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <CreatePost :profession-id="profession.id" :topic-id="topic.id" />

        <div class="px-2 pt-4 md:px-10">
            <TopicPost
                :profession-id="profession.id"
                :topic="topic"
                class="mb-10"
            />

            <PostsMapper
                :posts="topic.posts"
                :topicId="topic.id"
                :profession-id="profession.id"
            />
        </div>
    </AppLayout>
</template>
