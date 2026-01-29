<script lang="ts" setup>
import { Head } from '@inertiajs/vue3';

import AppLayout from '@/layouts/AppLayout.vue';
import TopicsMapping from '@/pages/forum/components/TopicsMapping.vue';
import { Topic } from '@/pages/forum/types';
import { index as topicIndex } from '@/routes/topics';
import { BreadcrumbItem, PaginatedResponse } from '@/types';

interface Props {
    topics: PaginatedResponse<Topic>;
}

// Todo: The actual topic type is not in the actual props
defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    // Todo: add professional area
    {
        title: 'Themen',
        href: topicIndex().url,
    },
];
</script>

<template>
    <Head title="Forum Themen" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <TopicsMapping :topics="topics" />
    </AppLayout>
</template>
