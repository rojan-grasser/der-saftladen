<script lang="ts" setup>
import { Head } from '@inertiajs/vue3';

import AppLayout from '@/layouts/AppLayout.vue';
import CreateTopic from '@/pages/forum/components/CreateTopic.vue';
import TopicsMapping from '@/pages/forum/components/TopicsMapping.vue';
import { MinimalTopic } from '@/pages/forum/types';
import { index as topicIndex } from '@/routes/topics';
import { BreadcrumbItem, PaginatedResponse } from '@/types';

interface Props {
    topics: PaginatedResponse<MinimalTopic>;
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
        <div class="flex flex-col gap-3 p-5">
            <div>
                <CreateTopic area-id=3 />
            </div>
            <TopicsMapping :topics="topics" />
        </div>
    </AppLayout>
</template>
